<?php

namespace App\Services;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Str;

class TemplateRenderer
{
    /**
     * Render a template string by replacing placeholders with provided data.
     * Placeholders supported: {{ name }}, {{ employee_code }}, {{ period_start }}, {{ period_end }}, {{ basic }}, etc.
     * Also supports nested array keys: data.user.name => {{ user.name }}
     */
    public static function render(string $html, array $data = []): string
    {
        // Flatten dot notation
        $flat = self::dot($data);

        $rendered = $html;
        foreach ($flat as $key => $value) {
            $rendered = str_replace('{{ ' . $key . ' }}', e((string) $value), $rendered);
        }

        // Basic currency helper
        $rendered = preg_replace_callback('/{{\s*currency\(([^)]+)\)\s*}}/', function ($matches) use ($flat) {
            $key = trim($matches[1]);
            $value = data_get($flat, $key, data_get($flat, $key));
            return number_format((float) $value, 2);
        }, $rendered);

        return $rendered;
    }

    protected static function dot(array $array, string $prepend = ''): array
    {
        $results = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $results = array_merge($results, self::dot($value, $prepend.$key.'.'));
            } else {
                $results[$prepend.$key] = $value;
            }
        }
        return $results;
    }
}
