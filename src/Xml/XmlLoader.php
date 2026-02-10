<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Xml;

use DOMDocument;
use MasyaSmv\AtonStatementParser\Exceptions\InvalidXmlException;

final class XmlLoader
{
    /**
     * Читает XML-файл и приводит в UTF-8 строку.
     *
     * В отчётах Атона часто windows-1251 — важно не сломать кириллицу.
     */
    public static function loadFileAsUtf8(string $path): string
    {
        if (!is_file($path)) {
            throw new InvalidXmlException('XML file not found: ' . $path);
        }

        $raw = file_get_contents($path);

        if ($raw === false || trim($raw) === '') {
            throw new InvalidXmlException('XML file is empty or unreadable: ' . $path);
        }

        return self::normalizeEncodingToUtf8($raw);
    }

    /**
     * Загружает XML строку в DOMDocument.
     */
    public static function loadXmlString(string $xml): DOMDocument
    {
        $xml = self::normalizeEncodingToUtf8($xml);

        $dom = new DOMDocument();
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;

        // libxml internal errors — чтобы получить понятное исключение
        $prev = libxml_use_internal_errors(true);
        libxml_clear_errors();

        $ok = $dom->loadXML($xml, LIBXML_NONET | LIBXML_COMPACT);

        $errors = libxml_get_errors();
        libxml_clear_errors();
        libxml_use_internal_errors($prev);

        if (!$ok) {
            $first = $errors[0] ?? null;
            $message = $first ? trim($first->message) : 'Unknown XML parse error';

            throw new InvalidXmlException('Invalid XML: ' . $message);
        }

        return $dom;
    }

    /**
     * Приводит вход к UTF-8 и правит декларацию encoding.
     *
     * Почему так:
     * - DOMDocument в PHP иногда криво ест windows-1251, особенно с кириллицей.
     * - Нам внутри пакета удобно всегда иметь UTF-8.
     */
    private static function normalizeEncodingToUtf8(string $xml): string
    {
        // Если в шапке явно указано windows-1251, конвертируем.
        if (stripos($xml, 'encoding="windows-1251"') !== false || stripos($xml, "encoding='windows-1251'") !== false) {
            $xml = iconv('Windows-1251', 'UTF-8//IGNORE', $xml) ?: $xml;

            return preg_replace('/encoding=(\"|\')windows-1251(\"|\')/i', 'encoding="UTF-8"', $xml) ?? $xml;
        }

        // Если не указано — считаем, что уже UTF-8 (самый частый кейс).
        // Но на всякий случай можно попытаться определить кодировку:
        // (оставим как есть, чтобы не словить "двойную" конвертацию)
        return $xml;
    }
}
