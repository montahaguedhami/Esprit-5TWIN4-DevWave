<?php

namespace App\Services\Ai;

use RuntimeException;

/** Message is safe to display to end users. */
class AiUnavailableException extends RuntimeException
{
}
