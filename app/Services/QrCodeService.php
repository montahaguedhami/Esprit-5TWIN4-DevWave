<?php

namespace App\Services;

class QrCodeService
{
    private static array $exp = [];
    private static array $log = [];
    private static bool $tablesInitialized = false;

    private static function initTables(): void
    {
        if (self::$tablesInitialized) {
            return;
        }

        self::$exp = array_fill(0, 512, 0);
        self::$log = array_fill(0, 256, 0);

        $val = 1;
        for ($i = 0; $i < 255; $i++) {
            self::$exp[$i] = $val;
            self::$log[$val] = $i;
            $val <<= 1;
            if ($val & 0x100) {
                $val ^= 0x11d; // Primitive poly: x^8 + x^4 + x^3 + x^2 + 1
            }
        }
        for ($i = 255; $i < 512; $i++) {
            self::$exp[$i] = self::$exp[$i - 255];
        }

        self::$tablesInitialized = true;
    }

    private static function gfMul(int $x, int $y): int
    {
        if ($x === 0 || $y === 0) {
            return 0;
        }
        return self::$exp[self::$log[$x] + self::$log[$y]];
    }

    private static function rsPoly(int $n): array
    {
        $poly = [1];
        for ($i = 0; $i < $n; $i++) {
            $next = array_fill(0, count($poly) + 1, 0);
            $factor = self::$exp[$i];
            for ($j = 0; $j < count($poly); $j++) {
                $next[$j] ^= $poly[$j];
                $next[$j + 1] ^= self::gfMul($poly[$j], $factor);
            }
            $poly = $next;
        }
        return $poly;
    }

    private static function rsEncode(array $data, int $ecCount): array
    {
        $gen = self::rsPoly($ecCount);
        $res = array_merge($data, array_fill(0, $ecCount, 0));

        for ($i = 0; $i < count($data); $i++) {
            $lead = $res[$i];
            if ($lead !== 0) {
                for ($j = 0; $j < count($gen); $j++) {
                    $res[$i + $j] ^= self::gfMul($gen[$j], $lead);
                }
            }
        }

        return array_slice($res, count($data));
    }

    /**
     * Génère une matrice QR Code (Version 1 à 3, EC level L).
     */
    public static function matrix(string $text): array
    {
        self::initTables();

        $bytes = array_values(unpack('C*', $text));
        $len = count($bytes);

        // Déterminer la version QR adaptée (EC Level L)
        // V1: 19 bytes, V2: 34 bytes, V3: 55 bytes, V4: 80 bytes
        if ($len <= 17) {
            $version = 1;
            $dataCap = 19;
            $ecCount = 7;
        } elseif ($len <= 32) {
            $version = 2;
            $dataCap = 34;
            $ecCount = 10;
        } elseif ($len <= 53) {
            $version = 3;
            $dataCap = 55;
            $ecCount = 15;
        } else {
            $version = 4;
            $dataCap = 80;
            $ecCount = 20;
        }

        $size = 17 + 4 * $version; // 21, 25, 29, 33

        // Bit stream: 4 bits mode (0100 for Byte), 8 bits length, data bytes
        $bitStream = '';
        $bitStream .= '0100'; // Byte mode
        $bitStream .= sprintf('%08b', $len);
        foreach ($bytes as $b) {
            $bitStream .= sprintf('%08b', $b);
        }

        // Terminator (up to 4 bits)
        $bitCap = $dataCap * 8;
        $bitStream .= str_repeat('0', min(4, max(0, $bitCap - strlen($bitStream))));

        // Pad to 8-bit boundary
        while (strlen($bitStream) % 8 !== 0) {
            $bitStream .= '0';
        }

        // Pad bytes 0xEC, 0x11
        $padBytes = [0xec, 0x11];
        $padIdx = 0;
        while (strlen($bitStream) < $bitCap) {
            $bitStream .= sprintf('%08b', $padBytes[$padIdx]);
            $padIdx = ($padIdx + 1) % 2;
        }

        $dataBytes = [];
        for ($i = 0; $i < strlen($bitStream); $i += 8) {
            $dataBytes[] = bindec(substr($bitStream, $i, 8));
        }

        // Error correction
        $ecBytes = self::rsEncode($dataBytes, $ecCount);
        $allBytes = array_merge($dataBytes, $ecBytes);

        // Convert to bit stream
        $finalBits = '';
        foreach ($allBytes as $b) {
            $finalBits .= sprintf('%08b', $b);
        }

        // Remainder bits for versions (V1: 0, V2: 7, V3: 7, V4: 7)
        $remainder = match($version) {
            2, 3, 4 => 7,
            default => 0,
        };
        $finalBits .= str_repeat('0', $remainder);

        // Grille et masque
        $grid = array_fill(0, $size, array_fill(0, $size, null));
        $reserved = array_fill(0, $size, array_fill(0, $size, false));

        // Placer les 3 mirettes (Finder patterns)
        $finders = [[0, 0], [$size - 7, 0], [0, $size - 7]];
        foreach ($finders as [$fx, $fy]) {
            for ($r = -1; $r <= 7; $r++) {
                for ($c = -1; $c <= 7; $c++) {
                    $row = $fy + $r;
                    $col = $fx + $c;
                    if ($row >= 0 && $row < $size && $col >= 0 && $col < $size) {
                        $isBlack = ($r >= 0 && $r <= 6 && $c >= 0 && $c <= 6) &&
                            ($r === 0 || $r === 6 || $c === 0 || $c === 6 || ($r >= 2 && $r <= 4 && $c >= 2 && $c <= 4));
                        $grid[$row][$col] = $isBlack ? 1 : 0;
                        $reserved[$row][$col] = true;
                    }
                }
            }
        }

        // Alignment pattern for V2+
        if ($version >= 2) {
            $alignCoords = match($version) {
                2 => [18],
                3 => [22],
                4 => [26],
                default => [18],
            };
            foreach ($alignCoords as $ax) {
                foreach ($alignCoords as $ay) {
                    if ($reserved[$ay][$ax]) {
                        continue;
                    }
                    for ($r = -2; $r <= 2; $r++) {
                        for ($c = -2; $c <= 2; $c++) {
                            $row = $ay + $r;
                            $col = $ax + $c;
                            $isBlack = (abs($r) === 2 || abs($c) === 2 || ($r === 0 && $c === 0));
                            $grid[$row][$col] = $isBlack ? 1 : 0;
                            $reserved[$row][$col] = true;
                        }
                    }
                }
            }
        }

        // Timing patterns
        for ($i = 8; $i < $size - 8; $i++) {
            if (!$reserved[6][$i]) {
                $grid[6][$i] = ($i % 2 === 0) ? 1 : 0;
                $reserved[6][$i] = true;
            }
            if (!$reserved[$i][6]) {
                $grid[$i][6] = ($i % 2 === 0) ? 1 : 0;
                $reserved[$i][6] = true;
            }
        }

        // Dark module
        $darkRow = 4 * $version + 9;
        $grid[$darkRow][8] = 1;
        $reserved[$darkRow][8] = true;

        // Reserve format info
        for ($i = 0; $i < 9; $i++) {
            if ($i < $size) {
                $reserved[8][$i] = true;
                $reserved[$i][8] = true;
            }
        }
        for ($i = 0; $i < 8; $i++) {
            $reserved[8][$size - 1 - $i] = true;
            $reserved[$size - 1 - $i][8] = true;
        }

        // Placement des données (zigzag)
        $bitIdx = 0;
        $bitLen = strlen($finalBits);
        $up = true;

        for ($right = $size - 1; $right > 0; $right -= 2) {
            if ($right === 6) {
                $right--; // Skip timing col
            }
            $cols = [$right, $right - 1];
            $rows = $up ? range($size - 1, 0, -1) : range(0, $size - 1, 1);

            foreach ($rows as $row) {
                foreach ($cols as $col) {
                    if (!$reserved[$row][$col]) {
                        $bit = ($bitIdx < $bitLen) ? (int) $finalBits[$bitIdx] : 0;
                        $bitIdx++;

                        // Masque 0: (row + col) % 2 == 0
                        $mask = (($row + $col) % 2 === 0);
                        $grid[$row][$col] = $mask ? ($bit ^ 1) : $bit;
                    }
                }
            }
            $up = !$up;
        }

        // Format info (EC: L=01, Mask: 0=000 => 01000 => with BCH 15,5 => 0x77c4 with mask 0x5412 => 0x23d6 = 010001111010110)
        $formatBits = '010001111010110'; // Pre-computed standard for L + mask 0
        $fmtIdx = 0;
        // Top-left
        $coordsTopLeft = [
            [8, 0], [8, 1], [8, 2], [8, 3], [8, 4], [8, 5],
            [8, 7], [8, 8], [7, 8], [5, 8], [4, 8], [3, 8], [2, 8], [1, 8], [0, 8]
        ];
        foreach ($coordsTopLeft as [$r, $c]) {
            $grid[$r][$c] = (int) $formatBits[$fmtIdx++];
        }

        // Split format around other finders
        $fmtIdx = 0;
        // Bottom-left
        for ($r = $size - 1; $r >= $size - 7; $r--) {
            $grid[$r][8] = (int) $formatBits[$fmtIdx++];
        }
        $grid[$darkRow][8] = 1;
        // Top-right
        for ($c = $size - 8; $c < $size; $c++) {
            $grid[8][$c] = (int) $formatBits[$fmtIdx++];
        }

        return $grid;
    }

    /**
     * Génère le SVG du QR Code.
     */
    public static function svg(string $text, int $size = 200, string $color = '#087e9b', string $background = '#ffffff'): string
    {
        $matrix = self::matrix($text);
        $gridSize = count($matrix);
        $margin = 2;
        $totalSize = $gridSize + ($margin * 2);

        $rects = [];
        for ($r = 0; $r < $gridSize; $r++) {
            for ($c = 0; $c < $gridSize; $c++) {
                if ($matrix[$r][$c] === 1) {
                    $x = $c + $margin;
                    $y = $r + $margin;
                    $rects[] = "<rect x=\"{$x}\" y=\"{$y}\" width=\"1\" height=\"1\" fill=\"{$color}\" />";
                }
            }
        }

        $rectsSvg = implode("\n    ", $rects);

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 {$totalSize} {$totalSize}" width="{$size}" height="{$size}" shape-rendering="crispEdges">
    <rect width="100%" height="100%" fill="{$background}" rx="4"/>
    {$rectsSvg}
</svg>
SVG;
    }

    public static function dataUri(string $text, int $size = 200, string $color = '#087e9b', string $background = '#ffffff'): string
    {
        $svg = self::svg($text, $size, $color, $background);
        return 'data:image/svg+xml;utf8,' . rawurlencode($svg);
    }
}
