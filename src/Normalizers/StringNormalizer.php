<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Normalizers;

final class StringNormalizer
{
    public static function clean(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim($value);

        if ($value === '') {
            return null;
        }

        return $value;
    }

    /**
     * Иногда встречаются значения вида "26.12.24 / " или "26.12.24 /".
     * Убираем хвосты после слеша.
     */
    public static function stripTrailingSlashPart(?string $value): ?string
    {
        $value = self::clean($value);

        if ($value === null) {
            return null;
        }

        // Берём часть ДО первого "/"
        $parts = explode('/', $value, 2);
        $value = trim($parts[0]);

        return $value === '' ? null : $value;
    }
}
