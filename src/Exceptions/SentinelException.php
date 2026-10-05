<?php

namespace Abdelhmed\SentinelAi\Exceptions;

use RuntimeException;

/**
 * Thrown for expected Sentinel failures (missing key, API error...).
 * The messages are safe to show on the dashboard: they never contain secrets.
 */
class SentinelException extends RuntimeException
{
}
