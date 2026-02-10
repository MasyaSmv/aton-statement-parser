<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser;

/**
 * Main entry point of the package.
 *
 * Сейчас это минимальный "скелет" для первого коммита:
 * - Проверяем, что пакет корректно подключается через Composer autoload.
 * - Даём стабильную точку входа, от которой дальше будем плясать (fromFile/fromString).
 *
 * В следующих итерациях сюда логично добавить:
 * - fromFile(string $path): Report
 * - fromString(string $xml): Report
 * - нормализацию структуры (DTO), ошибки и т.д.
 */
final class AtonStatementParser
{
    /**
     * Возвращает версию пакета (или "идентификатор сборки").
     *
     * Зачем это нужно:
     * - быстрый smoke-check, что пакет установлен и вызывается.
     * - удобно для дебага в проде: видно, какая версия пакета реально крутится.
     */
    public static function version(): string
    {
        return '0.1.0';
    }
}
