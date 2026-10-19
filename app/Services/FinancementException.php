<?php

namespace App\Services;

/**
 * Raised when a business rule blocks a funding operation. The message is
 * written for the end user and is always displayed in French.
 */
class FinancementException extends \RuntimeException
{
}
