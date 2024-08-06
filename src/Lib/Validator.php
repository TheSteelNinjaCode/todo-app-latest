<?php

namespace Lib;

use HTMLPurifier;
use HTMLPurifier_Config;

class Validator
{
    // String Validation

    /**
     * Validate and sanitize a string.
     *
     * @param mixed $value The value to validate.
     * @return string The sanitized string.
     */
    public static function string($value): string
    {
        return $value !== null ? htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8') : '';
    }

    /**
     * Validate an email address.
     *
     * @param mixed $value The value to validate.
     * @return string|null The valid email address or null if invalid.
     */
    public static function email($value): ?string
    {
        return filter_var($value, FILTER_VALIDATE_EMAIL) !== false ? $value : null;
    }

    /**
     * Validate a URL.
     *
     * @param mixed $value The value to validate.
     * @return string|null The valid URL or null if invalid.
     */
    public static function url($value): ?string
    {
        return filter_var($value, FILTER_VALIDATE_URL) !== false ? $value : null;
    }

    /**
     * Validate an IP address.
     *
     * @param mixed $value The value to validate.
     * @return string|null The valid IP address or null if invalid.
     */
    public static function ip($value): ?string
    {
        return filter_var($value, FILTER_VALIDATE_IP) !== false ? $value : null;
    }

    /**
     * Validate a UUID.
     *
     * @param mixed $value The value to validate.
     * @return string|null The valid UUID or null if invalid.
     */
    public static function uuid($value): ?string
    {
        return preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/', $value) ? $value : null;
    }

    /**
     * Validate a size string (e.g., "10MB").
     *
     * @param mixed $value The value to validate.
     * @return string|null The valid size string or null if invalid.
     */
    public static function bytes($value): ?string
    {
        return preg_match('/^[0-9]+[kKmMgGtT]?[bB]?$/', $value) ? $value : null;
    }

    /**
     * Validate an XML string.
     *
     * @param mixed $value The value to validate.
     * @return string|null The valid XML string or null if invalid.
     */
    public static function xml($value): ?string
    {
        return preg_match('/^<\?xml/', $value) ? $value : null;
    }

    // Number Validation

    /**
     * Validate an integer value.
     *
     * @param mixed $value The value to validate.
     * @return int|null The integer value or null if invalid.
     */
    public static function int($value): ?int
    {
        return filter_var($value, FILTER_VALIDATE_INT) !== false ? (int)$value : null;
    }

    /**
     * Validate a big integer value.
     *
     * @param mixed $value The value to validate.
     * @return int|null The integer value or null if invalid.
     */
    public static function bigInt($value): ?int
    {
        return self::int($value);
    }

    /**
     * Validate a float value.
     *
     * @param mixed $value The value to validate.
     * @return float|null The float value or null if invalid.
     */
    public static function float($value): ?float
    {
        return filter_var($value, FILTER_VALIDATE_FLOAT) !== false ? (float)$value : null;
    }

    /**
     * Validate a decimal value.
     *
     * @param mixed $value The value to validate.
     * @return float|null The float value or null if invalid.
     */
    public static function decimal($value): ?float
    {
        return self::float($value);
    }

    // Date Validation

    /**
     * Validate a date in a given format.
     *
     * @param mixed $value The value to validate.
     * @param string $format The date format.
     * @return string|null The valid date string or null if invalid.
     */
    public static function date($value, string $format = 'Y-m-d'): ?string
    {
        $date = \DateTime::createFromFormat($format, $value);
        return $date && $date->format($format) === $value ? $value : null;
    }

    /**
     * Validate a datetime in a given format.
     *
     * @param mixed $value The value to validate.
     * @param string $format The datetime format.
     * @return string|null The valid datetime string or null if invalid.
     */
    public static function dateTime($value, string $format = 'Y-m-d H:i:s'): ?string
    {
        return self::date($value, $format);
    }

    // Boolean Validation

    /**
     * Validate a boolean value.
     *
     * @param mixed $value The value to validate.
     * @return bool|null The boolean value or null if invalid.
     */
    public static function boolean($value): ?bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }

    // Other Validation

    /**
     * Validate a JSON string.
     *
     * @param mixed $value The value to validate.
     * @return bool True if valid JSON, false otherwise.
     */
    public static function json($value): bool
    {
        json_decode($value);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Validate an enum value against allowed values.
     *
     * @param mixed $value The value to validate.
     * @param array $allowedValues The allowed values.
     * @return bool True if value is allowed, false otherwise.
     */
    public static function enum($value, array $allowedValues): bool
    {
        return in_array($value, $allowedValues, true);
    }

    /**
     * Purify and sanitize HTML content.
     *
     * @param string $html The HTML content to purify.
     * @return string The purified HTML content.
     */
    public static function html(string $html): string
    {
        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);
        return $purifier->purify($html);
    }
}
