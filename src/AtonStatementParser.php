<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser;

use MasyaSmv\AtonStatementParser\Contracts\ReportInterface;
use MasyaSmv\AtonStatementParser\Parsing\ReportParserResolver;
use MasyaSmv\AtonStatementParser\Xml\XmlLoader;

final class AtonStatementParser
{
    /**
     * Парсит XML-файл отчёта Атон и возвращает объект Report.
     */
    public static function fromFile(string $path): ReportInterface
    {
        $xml = XmlLoader::loadFileAsUtf8($path);

        return self::fromString($xml);
    }

    /**
     * Парсит XML-строку отчёта Атон и возвращает объект Report.
     */
    public static function fromString(string $xml): ReportInterface
    {
        $dom = XmlLoader::loadXmlString($xml);

        return (new ReportParserResolver())->parse($dom);
    }

    public static function version(): string
    {
        return '0.1.0';
    }
}
