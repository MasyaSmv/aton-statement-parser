<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Normalizers;

final class NumberNormalizer
{
    public static function toInt(?string $value): ?int
    {
        $value = StringNormalizer::clean($value);

        if ($value === null) {
            return null;
        }

        // Убираем пробелы, иногда бывают разделители тысяч (редко, но пусть будет)
        $value = str_replace([' ', "\u{00A0}"], '', $value);

        if (!preg_match('/^-?\d+$/', $value)) {
            return null;
        }

        return (int) $value;
    }

    public static function toFloat(?string $value): ?float
    {
        $value = self::toDecimalString($value);

        if ($value === null) {
            return null;
        }

        return (float) $value;
    }

    /**
     * Возвращает число как строку без потери точности (важно для Quantity / Payment и т.п.).
     * Не округляет. Только нормализует формат.
     */
    public static function toDecimalString(?string $value): ?string
    {
        $value = StringNormalizer::clean($value);

        if ($value === null) {
            return null;
        }

        $value = str_replace([' ', "\u{00A0}"], '', $value);
        $value = str_replace(',', '.', $value);

        // Допустимы: -123, 123.45, -0.0001
        if (!preg_match('/^-?\d+(?:\.\d+)?$/', $value)) {
            return null;
        }

        return $value;
    }

    public static function toBool(?string $value): ?bool
    {
        $value = StringNormalizer::clean($value);

        if ($value === null) {
            return null;
        }

        if ($value === '1') {
            return true;
        }

        if ($value === '0') {
            return false;
        }

        return null;
    }
}
