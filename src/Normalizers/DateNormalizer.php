<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Normalizers;

use DateTimeImmutable;

final class DateNormalizer
{
    /**
     * Пробуем распарсить дату/датавремя из строк отчёта.
     * Поддерживаем:
     * - 29.12.2023
     * - 02.10.24
     * - 25.12.2024 0:00:00
     * - 01.01.1900 15:51:56
     * - 26.12.24 /   (с мусором)
     */
    public static function toDate(?string $value): ?DateTimeImmutable
    {
        $value = StringNormalizer::stripTrailingSlashPart($value);

        if ($value === null) {
            return null;
        }

        $value = self::expandTwoDigitYear($value);

        // Часто в Sort-полях время вида "0:00:00" — это валидно,
        // но формат может быть "G:i:s" (без ведущего нуля у часа).
        $formats = [
            '!d.m.Y',
            '!d.m.Y H:i:s',
            '!d.m.Y G:i:s',
            '!d.m.y H:i:s',
            '!d.m.y G:i:s',
            '!d.m.Y H:i',
            '!d.m.Y G:i',
            '!d.m.y H:i',
            '!d.m.y G:i',
        ];

        foreach ($formats as $format) {
            $dt = DateTimeImmutable::createFromFormat($format, $value);

            if ($dt instanceof DateTimeImmutable) {
                return $dt;
            }
        }

        return null;
    }

    private static function expandTwoDigitYear(string $value): string
    {
        // Если год уже 4-значный — ничего не делаем
        if (preg_match('/^\d{2}\.\d{2}\.\d{4}(\D.*)?$/', $value)) {
            return $value;
        }

        // Ищем dd.mm.yy (именно 2 цифры года), и важно: (?!\d) запрещает 3-ю цифру года
        if (preg_match('/^(\d{2})\.(\d{2})\.(\d{2})(?!\d)(.*)$/', $value, $m)) {
            $yy = (int) $m[3];

            // 00-69 => 2000-2069, 70-99 => 1970-1999
            $yyyy = $yy <= 69 ? (2000 + $yy) : (1900 + $yy);

            return sprintf('%s.%s.%04d%s', $m[1], $m[2], $yyyy, $m[4]);
        }

        return $value;
    }
}
