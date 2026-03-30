<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Support;

use MasyaSmv\AtonStatementParser\Normalizers\NumberNormalizer;

final class DecimalStringMath
{
    public static function add(string $left, string $right): string
    {
        $left = NumberNormalizer::toDecimalString($left) ?? '0';
        $right = NumberNormalizer::toDecimalString($right) ?? '0';

        $scale = max(self::fractionLength($left), self::fractionLength($right));
        [$leftSign, $leftDigits] = self::normalizeForScale($left, $scale);
        [$rightSign, $rightDigits] = self::normalizeForScale($right, $scale);

        if ($leftSign === $rightSign) {
            $resultDigits = self::addUnsigned($leftDigits, $rightDigits);

            return self::format($leftSign, $resultDigits, $scale);
        }

        $comparison = self::compareUnsigned($leftDigits, $rightDigits);

        if ($comparison === 0) {
            return self::zeroForScale($scale);
        }

        if ($comparison > 0) {
            return self::format($leftSign, self::subtractUnsigned($leftDigits, $rightDigits), $scale);
        }

        return self::format($rightSign, self::subtractUnsigned($rightDigits, $leftDigits), $scale);
    }

    private static function fractionLength(string $value): int
    {
        $parts = explode('.', $value, 2);

        return isset($parts[1]) ? strlen($parts[1]) : 0;
    }

    /**
     * @return array{0: int, 1: string}
     */
    private static function normalizeForScale(string $value, int $scale): array
    {
        $sign = str_starts_with($value, '-') ? -1 : 1;
        $unsigned = ltrim($value, '+-');
        [$integer, $fraction] = array_pad(explode('.', $unsigned, 2), 2, '');

        $integer = ltrim($integer, '0');
        $integer = $integer === '' ? '0' : $integer;
        $fraction = str_pad($fraction, $scale, '0');

        return [$sign, $integer . $fraction];
    }

    private static function addUnsigned(string $left, string $right): string
    {
        $left = strrev($left);
        $right = strrev($right);
        $length = max(strlen($left), strlen($right));
        $carry = 0;
        $result = '';

        for ($index = 0; $index < $length; $index++) {
            $sum = ((int) ($left[$index] ?? '0')) + ((int) ($right[$index] ?? '0')) + $carry;
            $result .= (string) ($sum % 10);
            $carry = intdiv($sum, 10);
        }

        if ($carry > 0) {
            $result .= (string) $carry;
        }

        return strrev($result);
    }

    private static function compareUnsigned(string $left, string $right): int
    {
        $left = ltrim($left, '0');
        $right = ltrim($right, '0');
        $left = $left === '' ? '0' : $left;
        $right = $right === '' ? '0' : $right;

        if (strlen($left) !== strlen($right)) {
            return strlen($left) <=> strlen($right);
        }

        return $left <=> $right;
    }

    private static function subtractUnsigned(string $left, string $right): string
    {
        $left = strrev($left);
        $right = strrev($right);
        $length = strlen($left);
        $borrow = 0;
        $result = '';

        for ($index = 0; $index < $length; $index++) {
            $digit = ((int) $left[$index]) - ((int) ($right[$index] ?? '0')) - $borrow;

            if ($digit < 0) {
                $digit += 10;
                $borrow = 1;
            } else {
                $borrow = 0;
            }

            $result .= (string) $digit;
        }

        return ltrim(strrev($result), '0') ?: '0';
    }

    private static function format(int $sign, string $digits, int $scale): string
    {
        $digits = ltrim($digits, '0');
        $digits = $digits === '' ? '0' : $digits;

        if ($scale > 0) {
            $digits = str_pad($digits, $scale + 1, '0', STR_PAD_LEFT);
            $integer = substr($digits, 0, -$scale);
            $fraction = substr($digits, -$scale);
            $value = $integer . '.' . $fraction;
        } else {
            $value = $digits;
        }

        if ($value === self::zeroForScale($scale)) {
            return $value;
        }

        return $sign < 0 ? '-' . $value : $value;
    }

    private static function zeroForScale(int $scale): string
    {
        return $scale > 0 ? '0.' . str_repeat('0', $scale) : '0';
    }
}
