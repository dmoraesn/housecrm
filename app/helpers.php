<?php

if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        try {
            if (app()->bound('db') && class_exists(\App\Models\GlobalSetting::class)) {
                return \App\Models\GlobalSetting::getValue($key, $default);
            }
        } catch (\Throwable $e) {
            // Silencia qualquer erro durante boot (migrate, clear, etc.)
        }

        return $default;
    }
}