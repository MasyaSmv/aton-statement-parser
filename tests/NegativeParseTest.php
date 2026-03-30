<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Tests;

use DOMDocument;
use MasyaSmv\AtonStatementParser\AtonStatementParser;
use MasyaSmv\AtonStatementParser\Exceptions\DtoMappingException;
use MasyaSmv\AtonStatementParser\Exceptions\InvalidXmlException;
use MasyaSmv\AtonStatementParser\Exceptions\MissingBisNamespaceException;
use MasyaSmv\AtonStatementParser\Exceptions\MissingSectionException;
use MasyaSmv\AtonStatementParser\Exceptions\ParseException;
use MasyaSmv\AtonStatementParser\Exceptions\UnsupportedReportFormatException;
use MasyaSmv\AtonStatementParser\Mappers\CommonDataMapper;
use MasyaSmv\AtonStatementParser\Mappers\CorporateActionMapper;
use MasyaSmv\AtonStatementParser\Mappers\MoneyConvertMapper;
use MasyaSmv\AtonStatementParser\Mappers\MoneyOperationMapper;
use MasyaSmv\AtonStatementParser\Mappers\StockPayingOffMapper;
use MasyaSmv\AtonStatementParser\Mappers\StockTransferMapper;
use MasyaSmv\AtonStatementParser\Mappers\TradeMapper;
use MasyaSmv\AtonStatementParser\Parsing\LegacyBisReportParser;
use MasyaSmv\AtonStatementParser\Parsing\ModernXmlReportParser;
use MasyaSmv\AtonStatementParser\Parsing\ReportParserResolver;
use MasyaSmv\AtonStatementParser\Report\AttributeBag;
use MasyaSmv\AtonStatementParser\Report\Row;
use MasyaSmv\AtonStatementParser\Xml\XPathFactory;
use PHPUnit\Framework\TestCase;

final class NegativeParseTest extends TestCase
{
    public function testFromFileThrowsOnMissingFile(): void
    {
        $this->expectException(InvalidXmlException::class);
        $this->expectExceptionMessage('XML file not found');

        AtonStatementParser::fromFile(__DIR__ . '/Fixtures/not-found.xml');
    }

    public function testFromStringThrowsOnEmptyXml(): void
    {
        $this->expectException(InvalidXmlException::class);
        $this->expectExceptionMessage('Invalid XML');

        AtonStatementParser::fromString('');
    }

    public function testFromStringThrowsOnMalformedXml(): void
    {
        $this->expectException(InvalidXmlException::class);
        $this->expectExceptionMessage('Invalid XML');

        AtonStatementParser::fromString('<root><broken></root>');
    }

    public function testResolverThrowsUnsupportedFormatException(): void
    {
        $document = new DOMDocument();
        $document->loadXML('<foo><bar/></foo>');

        $this->expectException(UnsupportedReportFormatException::class);
        $this->expectExceptionMessage('Unsupported XML report format');

        (new ReportParserResolver())->parse($document);
    }

    public function testXPathFactoryThrowsOnMissingBisNamespace(): void
    {
        $document = new DOMDocument();
        $document->loadXML('<BISPeriod><Trades/></BISPeriod>');

        $this->expectException(MissingBisNamespaceException::class);
        $this->expectExceptionMessage('Cannot detect BIS namespace');

        XPathFactory::make($document);
    }

    public function testLegacyParserThrowsWhenBisPeriodNodeIsMissing(): void
    {
        $document = new DOMDocument();
        $document->loadXML('<BIS:root xmlns:BIS="urn:test"><BIS:Trades/></BIS:root>');

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Root node BIS:BISPeriod not found');

        (new LegacyBisReportParser())->parse($document);
    }

    public function testModernParserThrowsWhenSourceNameIsMissing(): void
    {
        $document = new DOMDocument();
        $document->loadXML('<root><source><record/></source></root>');

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Source name is missing in modern XML report');

        (new ModernXmlReportParser())->parse($document);
    }

    public function testSectionThrowsMissingSectionException(): void
    {
        $report = AtonStatementParser::fromFile(__DIR__ . '/Fixtures/sample.xml');

        $this->expectException(MissingSectionException::class);
        $this->expectExceptionMessage('Section not found');

        $report->section('UnknownSection');
    }

    public function testCommonDataMapperThrowsWhenCpidIsMissing(): void
    {
        $row = $this->makeRow('CommonData', 'Header', 'Header');

        $this->expectException(DtoMappingException::class);
        $this->expectExceptionMessage('required field "CPID" is missing');

        (new CommonDataMapper())->map($row);
    }

    public function testTradeMapperThrowsWhenOperIdIsMissing(): void
    {
        $row = $this->makeRow('Trades', 'Trades', 'Row');

        $this->expectException(DtoMappingException::class);
        $this->expectExceptionMessage('required field "OperID" is missing');

        (new TradeMapper())->map($row);
    }

    public function testMoneyOperationMapperThrowsWhenOperIdIsMissing(): void
    {
        $row = $this->makeRow('MoneyInOut', 'MoneyInOut', 'Row');

        $this->expectException(DtoMappingException::class);
        $this->expectExceptionMessage('required field "OperID" is missing');

        (new MoneyOperationMapper())->map($row);
    }

    public function testMoneyConvertMapperThrowsWhenOperIdIsMissing(): void
    {
        $row = $this->makeRow('MoneyConvert', 'TradeFXNonClient', 'record');

        $this->expectException(DtoMappingException::class);
        $this->expectExceptionMessage('required field "OperID" is missing');

        (new MoneyConvertMapper())->map($row);
    }

    public function testStockTransferMapperThrowsWhenOperIdIsMissing(): void
    {
        $row = $this->makeRow('StockInOut', 'OperationStockInOut', 'record');

        $this->expectException(DtoMappingException::class);
        $this->expectExceptionMessage('required field "OperID" is missing');

        (new StockTransferMapper())->map($row);
    }

    public function testStockPayingOffMapperThrowsWhenOperIdIsMissing(): void
    {
        $row = $this->makeRow('StockPayingOff', 'OperationStockPayOff', 'record');

        $this->expectException(DtoMappingException::class);
        $this->expectExceptionMessage('required field "OperID" is missing');

        (new StockPayingOffMapper())->map($row);
    }

    public function testCorporateActionMapperThrowsWhenOperIdIsMissing(): void
    {
        $row = $this->makeRow('CorpActionIn', 'OperationStockCorpActionIn', 'record');

        $this->expectException(DtoMappingException::class);
        $this->expectExceptionMessage('required field "OperID" is missing');

        (new CorporateActionMapper())->map($row);
    }

    private function makeRow(string $section, string $sourceName, string $recordType): Row
    {
        return new Row($section, $sourceName, $recordType, new AttributeBag([]));
    }
}
