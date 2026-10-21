<?php

namespace App\Services;

use App\Models\Beach;
use App\Models\BeachBathingStatus;
use App\Models\BeachMeasurement;
use App\Models\BeachHistoricalAdvisoryPoint;
use App\Models\Governorate;
use App\Support\TuniBeachCache;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class TuniBeachService
{
    /** @return array<string, mixed> */
    public function governorates(int $page, int $perPage): array
    {
        return $this->remember('governorates:'.$page.':'.$perPage, function () use ($page, $perPage): array {
            $items = Governorate::query()
                ->where('is_verified', true)
                ->where('is_published', true)
                ->orderBy('name')
                ->paginate($perPage, ['id', 'name', 'slug'], 'page', $page);

            return $this->paginated($items, fn (Governorate $governorate): array => [
                'id' => $governorate->id,
                'name' => $governorate->name,
                'slug' => $governorate->slug,
            ]);
        });
    }

    /** @param array{q?: string|null, governorate?: string|null, status?: string|null, page: int, per_page: int} $filters
     *  @return array<string, mixed>
     */
    public function beaches(array $filters): array
    {
        return $this->remember('beaches:'.hash('sha256', json_encode($filters, JSON_THROW_ON_ERROR)), function () use ($filters): array {
            $items = $this->publicBeaches()
                ->when(($filters['q'] ?? '') !== '', fn ($query) => $query->where('name', 'like', '%'.$filters['q'].'%'))
                ->when(($filters['governorate'] ?? '') !== '', fn ($query) => $query->whereHas(
                    'governorate', fn ($governorateQuery) => $governorateQuery->where('slug', $filters['governorate'])
                ))
                ->when(($filters['status'] ?? '') !== '', function ($query) use ($filters): void {
                    $now = now();
                    $validStatuses = fn ($statusQuery) => $this->currentVerifiedStatuses($statusQuery, $now);

                    if ($filters['status'] === 'unknown') {
                        $query->whereDoesntHave('bathingStatuses', $validStatuses);
                    } else {
                        $query->whereHas('bathingStatuses', fn ($statusQuery) => $validStatuses($statusQuery)
                            ->where('status', $filters['status']));
                    }
                })
                ->orderBy('name')
                ->paginate($filters['per_page'], ['*'], 'page', $filters['page']);

            return $this->paginated($items, fn (Beach $beach): array => $this->beachData($beach));
        });
    }

    /** @param array{q?: string|null, governorate?: string|null, status?: string|null, page: int, per_page: int} $filters
     *  @return array<string, mixed>
     */
    public function historicalAdvisories(array $filters): array
    {
        return $this->remember('historical-advisories:'.hash('sha256', json_encode($filters, JSON_THROW_ON_ERROR)), function () use ($filters): array {
            $query = BeachHistoricalAdvisoryPoint::query()
                ->when(($filters['q'] ?? '') !== '', fn ($builder) => $builder->where('point_name', 'like', '%'.$filters['q'].'%'))
                ->when(($filters['status'] ?? '') !== '', fn ($builder) => $builder->where('reported_status', $filters['status']));

            if (($filters['governorate'] ?? '') !== '') {
                $matchingNames = BeachHistoricalAdvisoryPoint::query()
                    ->distinct()
                    ->pluck('governorate_name')
                    ->filter(fn (string $name): bool => Str::slug($name) === $filters['governorate'])
                    ->values();
                $query->whereIn('governorate_name', $matchingNames->isEmpty() ? ['__no_matching_governorate__'] : $matchingNames);
            }

            $items = $query->orderBy('governorate_name')->orderBy('point_name')
                ->paginate($filters['per_page'], ['*'], 'page', $filters['page']);

            return $this->paginated($items, fn (BeachHistoricalAdvisoryPoint $point): array => [
                'reported_at' => $point->reported_at?->toDateString(),
                'governorate' => $point->governorate_name,
                'name' => $point->point_name,
                'reported_status' => $point->reported_status,
                'source' => $this->sourceData($point->source_name, $point->source_url),
            ]);
        });
    }

    /** @return array<string, mixed>|null */
    public function beach(string $slug): ?array
    {
        return $this->remember('beach:'.hash('sha256', $slug), function () use ($slug): ?array {
            $beach = $this->publicBeaches()->where('slug', $slug)->first();

            return $beach ? $this->beachData($beach, true) : null;
        });
    }

    /** @return array<string, mixed>|null */
    public function measurements(string $slug, int $page, int $perPage): ?array
    {
        return $this->remember('measurements:'.hash('sha256', json_encode([$slug, $page, $perPage], JSON_THROW_ON_ERROR)), function () use ($slug, $page, $perPage): ?array {
            $beach = $this->publicBeaches()->where('slug', $slug)->first();
            if (! $beach) {
                return null;
            }

            $items = BeachMeasurement::query()
                ->where('beach_id', $beach->id)
                ->where('is_public', true)
                ->where('is_verified', true)
                ->whereNotNull('sampled_at')
                ->whereNotNull('source_name')
                ->where('source_name', '!=', '')
                ->orderByDesc('sampled_at')
                ->paginate($perPage, ['*'], 'page', $page);

            return $this->paginated($items, fn (BeachMeasurement $measurement): array => $this->measurementData($measurement));
        });
    }

    private function publicBeaches(): \Illuminate\Database\Eloquent\Builder
    {
        $now = now();

        return Beach::query()
            ->where('is_published', true)
            ->whereHas('governorate', fn ($query) => $query->where('is_verified', true)->where('is_published', true))
            ->with([
                'governorate:id,name,slug',
                'measurements' => fn ($query) => $query
                    ->where('is_public', true)
                    ->where('is_verified', true)
                    ->whereNotNull('sampled_at')
                    ->whereNotNull('source_name')
                    ->where('source_name', '!=', '')
                    ->orderByDesc('sampled_at')
                    ->limit(1),
                'bathingStatuses' => fn ($query) => $this->currentVerifiedStatuses($query, $now)
                    ->orderByDesc('verified_at')
                    ->limit(1),
            ]);
    }

    private function currentVerifiedStatuses($query, $now)
    {
        return $query
            ->whereIn('status', BeachBathingStatus::STATUSES)
            ->where('is_verified', true)
            ->whereNotNull('source_name')
            ->where('source_name', '!=', '')
            ->whereNotNull('verified_at')
            ->where('valid_from', '<=', $now)
            ->where('valid_until', '>=', $now);
    }

    /** @return array<string, mixed> */
    private function beachData(Beach $beach, bool $includeDescription = false): array
    {
        $latestMeasurement = $beach->measurements->first();
        $status = $beach->bathingStatuses->first();

        $data = [
            'id' => $beach->id,
            'name' => $beach->name,
            'slug' => $beach->slug,
            'coordinates' => [
                'latitude' => $beach->latitude !== null ? (float) $beach->latitude : null,
                'longitude' => $beach->longitude !== null ? (float) $beach->longitude : null,
                'verified' => $beach->coordinates_verified,
            ],
            'governorate' => [
                'id' => $beach->governorate->id,
                'name' => $beach->governorate->name,
                'slug' => $beach->governorate->slug,
            ],
            'bathing_status' => $this->statusData($status),
            'historical_advisory' => $this->historicalAdvisory($beach->governorate->name, $includeDescription),
            'latest_measurement' => $latestMeasurement ? [
                'sampled_at' => $latestMeasurement->sampled_at?->toISOString(),
                'source' => $this->sourceData($latestMeasurement->source_name, $latestMeasurement->source_url),
            ] : null,
        ];

        if ($includeDescription) {
            $data['description'] = $beach->description;
        }

        return $data;
    }

    /** @return array<string, mixed> */
    private function statusData($status): array
    {
        if (! $status) {
            return [
                'value' => 'unknown',
                'source' => null,
                'issued_at' => null,
                'verified_at' => null,
                'valid_from' => null,
                'valid_until' => null,
            ];
        }

        return [
            'value' => $status->status,
            'source' => $this->sourceData($status->source_name, $status->source_url, $status->source_reference),
            'issued_at' => $status->issued_at?->toISOString(),
            'verified_at' => $status->verified_at?->toISOString(),
            'valid_from' => $status->valid_from?->toISOString(),
            'valid_until' => $status->valid_until?->toISOString(),
        ];
    }

    /** @return array<string, mixed>|null */
    private function historicalAdvisory(string $governorate, bool $includePoints): ?array
    {
        $cacheKey = 'tunibeach:historical-advisory:'.TuniBeachCache::generation();
        $byGovernorate = Cache::remember($cacheKey, now()->addSeconds(max(1, (int) config('tunibeach.cache_ttl', 300))), function (): array {
            return BeachHistoricalAdvisoryPoint::query()
                ->whereDate('reported_at', '2026-06-20')
                ->orderBy('point_name')
                ->get(['governorate_name', 'point_name', 'reported_status', 'source_name', 'source_url'])
                ->groupBy('governorate_name')
                ->map(fn ($points) => [
                    'points_count' => $points->count(),
                    'points' => $points->map(fn (BeachHistoricalAdvisoryPoint $point): array => [
                        'name' => $point->point_name,
                        'reported_status' => $point->reported_status,
                    ])->values()->all(),
                    'source_name' => $points->first()->source_name,
                    'source_url' => $points->first()->source_url,
                ])
                ->all();
        });
        $data = $byGovernorate[$governorate] ?? null;

        if (! $data) {
            return null;
        }

        $advisory = [
            'reported_at' => '2026-06-20',
            'governorate' => $governorate,
            'points_count' => $data['points_count'],
            'source' => $this->sourceData($data['source_name'], $data['source_url']),
            'current_status_confirmed' => false,
        ];
        if ($includePoints) {
            $advisory['points'] = $data['points'];
        }

        return $advisory;
    }

    /** @return array<string, string|null> */
    private function sourceData(?string $name, ?string $url, ?string $reference = null): array
    {
        return [
            'name' => $name,
            'url' => $this->publicHttpUrl($url),
            'reference' => $reference,
        ];
    }

    private function publicHttpUrl(?string $url): ?string
    {
        if (! $url || filter_var($url, FILTER_VALIDATE_URL) === false) {
            return null;
        }

        return in_array(strtolower((string) parse_url($url, PHP_URL_SCHEME)), ['http', 'https'], true) ? $url : null;
    }

    /** @return array<string, mixed> */
    private function measurementData(BeachMeasurement $measurement): array
    {
        $parameters = [];
        $availableParameters = is_array($measurement->parameters) ? $measurement->parameters : [];
        foreach (['ph', 'turbidity', 'residual_chlorine', 'lead', 'nitrates'] as $key) {
            $item = $availableParameters[$key] ?? null;
            if (! is_array($item) || ! isset($item['value']) || ! is_numeric($item['value'])) {
                continue;
            }

            $unit = $item['unit'] ?? null;
            $parameters[$key] = [
                'value' => (float) $item['value'],
                'unit' => is_string($unit) && mb_strlen($unit) <= 32 ? $unit : null,
            ];
        }

        return [
            'reference' => $measurement->reference,
            'sampled_at' => $measurement->sampled_at?->toISOString(),
            'parameters' => $parameters,
            'source' => $this->sourceData($measurement->source_name, $measurement->source_url),
            'verification' => ['verified' => $measurement->is_verified],
        ];
    }

    /** @template T
     *  @param LengthAwarePaginator<T> $paginator
     *  @param callable(T): array<string, mixed> $transform
     *  @return array<string, mixed>
     */
    private function paginated(LengthAwarePaginator $paginator, callable $transform): array
    {
        return [
            'data' => collect($paginator->items())->map($transform)->values()->all(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'last_page' => $paginator->lastPage(),
                'total' => $paginator->total(),
            ],
        ];
    }

    private function remember(string $key, callable $callback): mixed
    {
        $ttl = (int) config('tunibeach.cache_ttl', 300);
        $cacheKey = 'tunibeach:v1:'.TuniBeachCache::generation().':'.$key;

        return $ttl > 0
            ? Cache::remember($cacheKey, now()->addSeconds($ttl), $callback)
            : $callback();
    }
}
