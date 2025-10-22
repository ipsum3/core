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


if (! function_exists('prix')) {
    /**
     * Generate an asset path with versioned for the application.
     *
     * @param  float  $expression
     * @return string
     */
    function prix(float $expression): string
    {
        return number_format($expression, (intval($expression) == $expression ? 0 : 2), ',', '&nbsp;');
    }
}

