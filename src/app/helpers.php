<?php

if (! function_exists('asset_versioned')) {
    /**
     * Generate an asset path with versioned for the application.
     *
     * @param  string  $path
     * @param  bool|null  $secure
     * @return string
     */
    function asset_versioned(string $path, ?bool $secure = null) : string
    {
        // https://www.impressivewebs.com/cache-busting-front-end-resources-file-name-revving-still-necessary/
        return asset($path, $secure).(file_exists(public_path($path)) ? '?t='.filemtime(public_path($path)) : '');
    }
}


if (! function_exists('round_prix')) {
    /**
     * Arrondi un prix
     *
     * @param int|float|null $expression
     * @param int $precision
     * @return float
     */
    function round_prix(int|float|null $expression, int $precision = 2): float
    {
        return round($expression ?? 0, $precision, PHP_ROUND_HALF_EVEN); // Arrondi bancaire
    }
}


if (! function_exists('prix')) {
    /**
     * Format un prix
     *
     * @param  ?float  $expression
     * @return string
     */
    function prix(?float $expression): string
    {
        return number_format(round_prix($expression), (intval($expression) == $expression ? 0 : 2), ',', '&nbsp;');
    }
}

