<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Tests;

use DOMDocument;
use MasyaSmv\AtonStatementParser\Exceptions\UnsupportedReportFormatException;
use MasyaSmv\AtonStatementParser\Normalizers\DateNormalizer;
use MasyaSmv\AtonStatementParser\Normalizers\NumberNormalizer;
use MasyaSmv\AtonStatementParser\Normalizers\StringNormalizer;
use MasyaSmv\AtonStatementParser\Parsing\LegacyBisReportParser;
use MasyaSmv\AtonStatementParser\Parsing\ModernFieldCanonicalizer;
use MasyaSmv\AtonStatementParser\Parsing\ModernXmlReportParser;
use MasyaSmv\AtonStatementParser\Parsing\ReportParserResolver;
use MasyaSmv\AtonStatementParser\Parsing\SectionNameResolver;
use MasyaSmv\AtonStatementParser\Xml\XmlLoader;
use MasyaSmv\AtonStatementParser\Xml\XPathFactory;
use PHPUnit\Framework\TestCase;

final class NormalizerAndParsingUnitTest extends TestCase
{
    public function testStringNormalizerCleansAndStripsSlashPart(): void
    {
        $this->assertNull(StringNormalizer::clean(null));
        $this->assertNull(StringNormalizer::clean('   '));
        $this->assertSame('value', StringNormalizer::clean(' value '));
        $this->assertNull(StringNormalizer::stripTrailingSlashPart(' / '));
        $this->assertSame('26.12.24', StringNormalizer::stripTrailingSlashPart('26.12.24 / '));
        $this->assertSame('text', StringNormalizer::stripTrailingSlashPart(' text / tail '));
    }

    public function testNumberNormalizerHandlesValidAndInvalidValues(): void
    {
        $this->assertSame(123, NumberNormalizer::toInt('123'));
        $this->assertSame(-123, NumberNormalizer::toInt('-123'));
        $this->assertNull(NumberNormalizer::toInt('123.5'));
        $this->assertNull(NumberNormalizer::toInt('abc'));

        $this->assertSame(10.5, NumberNormalizer::toFloat('10,5'));
        $this->assertNull(NumberNormalizer::toFloat('abc'));

        $this->assertSame('1000.50', NumberNormalizer::toDecimalString(" 1\u{00A0}000,50 "));
        $this->assertSame('-0.25', NumberNormalizer::toDecimalString('-0.25'));
        $this->assertNull(NumberNormalizer::toDecimalString('abc'));

        $this->assertTrue(NumberNormalizer::toBool('1'));
        $this->assertTrue(NumberNormalizer::toBool('true'));
        $this->assertFalse(NumberNormalizer::toBool('0'));
        $this->assertFalse(NumberNormalizer::toBool('false'));
        $this->assertNull(NumberNormalizer::toBool('yes'));
    }

    public function testDateNormalizerHandlesSupportedFormats(): void
    {
        $this->assertNull(DateNormalizer::toDate(null));
        $this->assertSame('2024-12-26 00:00:00', DateNormalizer::toDate('26.12.24 / ') ?->format('Y-m-d H:i:s'));
        $this->assertSame('2024-12-25 00:00:00', DateNormalizer::toDate('25.12.2024 0:00:00')?->format('Y-m-d H:i:s'));
        $this->assertSame('1900-01-01 15:51:00', DateNormalizer::toDate('01.01.1900 15:51')?->format('Y-m-d H:i:s'));
        $this->assertSame('2024-02-01 00:00:00', DateNormalizer::toDate('2024-02-01T00:00:00')?->format('Y-m-d H:i:s'));
        $this->assertNull(DateNormalizer::toDate('not-a-date'));
    }

    public function testParserSupportsAndResolverBehaviour(): void
    {
        $legacy = new DOMDocument();
        $legacy->loadXML('<?xml version="1.0"?><BIS:BISPeriod xmlns:BIS="urn:test"/>');

        $modern = new DOMDocument();
        $modern->loadXML('<?xml version="1.0"?><root><source name="Header"/></root>');

        $unknown = new DOMDocument();
        $unknown->loadXML('<?xml version="1.0"?><unknown/>');

        $legacyParser = new LegacyBisReportParser();
        $modernParser = new ModernXmlReportParser();

        $this->assertTrue($legacyParser->supports($legacy));
        $this->assertFalse($legacyParser->supports($modern));
        $this->assertTrue($modernParser->supports($modern));
        $this->assertFalse($modernParser->supports($legacy));

        $resolver = new ReportParserResolver();
        $this->assertTrue($resolver->parse($legacy)->hasSection('BISPeriod') === false);
        $this->assertTrue($resolver->parse($modern)->hasSection('CommonData') === false);

        $this->expectException(UnsupportedReportFormatException::class);
        $resolver->parse($unknown);
    }

    public function testLegacyBisParserCanParseRowsFromInlineXml(): void
    {
        $document = new DOMDocument();
        $document->loadXML(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<BIS:BISPeriod xmlns:BIS="urn:test">
  <BIS:Trades>
    <BIS:Row BIS:OperID="1" BIS:TradeType="Buy"/>
  </BIS:Trades>
</BIS:BISPeriod>
XML);

        $report = (new LegacyBisReportParser())->parse($document);

        $this->assertTrue($report->hasSection('Trades'));
        $row = $report->section('Trades')->rows()->first();
        $this->assertNotNull($row);
        $this->assertSame('1', $row->getString('OperID'));
        $this->assertSame('Buy', $row->getString('TradeType'));
    }

    public function testModernXmlParserCanParseRowsFromInlineXml(): void
    {
        $document = new DOMDocument();
        $document->loadXML(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
  <source name="OperationMoneyBrok">
    <OperationMoneyBrok>
      <OperID>10</OperID>
      <OperDate>24.12.2025</OperDate>
      <QuantityRUR>5.00000000</QuantityRUR>
    </OperationMoneyBrok>
  </source>
</root>
XML);

        $report = (new ModernXmlReportParser())->parse($document);

        $this->assertTrue($report->hasSection('MoneyInOut'));
        $row = $report->section('MoneyInOut')->rows()->first();
        $this->assertNotNull($row);
        $this->assertSame('10', $row->getString('OperID'));
        $this->assertSame('Brok', $row->getString('OperType'));
        $this->assertSame('5.00000000', $row->getString('Quantity_RUR'));
    }

    public function testXPathFactoryRegistersNamespace(): void
    {
        $dom = new DOMDocument();
        $dom->loadXML('<?xml version="1.0"?><BIS:BISPeriod xmlns:BIS="urn:test"><BIS:Trades/></BIS:BISPeriod>');

        $xpath = XPathFactory::make($dom);
        $nodes = $xpath->query('/BIS:BISPeriod/BIS:Trades');

        $this->assertNotFalse($nodes);
        $this->assertSame(1, $nodes->length);
    }

    public function testXmlLoaderCanNormalizeWindows1251Content(): void
    {
        $path = sys_get_temp_dir() . '/aton-windows1251.xml';
        $xml = <<<XML
<?xml version="1.0" encoding="windows-1251"?>
<root><source name="Header"><Header><CompanyName>Тест</CompanyName></Header></source></root>
XML;

        file_put_contents($path, iconv('UTF-8', 'Windows-1251//IGNORE', $xml));

        try {
            $utf8 = XmlLoader::loadFileAsUtf8($path);
            $this->assertStringContainsString('encoding="UTF-8"', $utf8);
            $this->assertStringContainsString('Тест', $utf8);

            $document = XmlLoader::loadXmlString($utf8);
            $this->assertSame('root', $document->documentElement?->tagName);
        } finally {
            @unlink($path);
        }
    }

    public function testXmlLoaderThrowsOnEmptyFile(): void
    {
        $path = tempnam(sys_get_temp_dir(), 'aton-empty-');
        self::assertNotFalse($path);
        file_put_contents($path, '');

        try {
            $this->expectExceptionMessage('XML file is empty or unreadable');
            XmlLoader::loadFileAsUtf8($path);
        } finally {
            @unlink($path);
        }
    }

    public function testSectionNameResolverResolvesKnownAndFallbackSections(): void
    {
        $this->assertSame('CommonData', SectionNameResolver::resolveForNewFormat('Header', 'Header'));
        $this->assertSame('Trades', SectionNameResolver::resolveForNewFormat('TradeCommonSettled', 'TradeCommonSettled'));
        $this->assertSame('ClientMoneyConvert', SectionNameResolver::resolveForNewFormat('TradeFXClient', 'TradeFXClient'));
        $this->assertSame('MoneyConvert', SectionNameResolver::resolveForNewFormat('TradeFXNonClient', 'TradeFXNonClient'));
        $this->assertSame('TradesRegRepo', SectionNameResolver::resolveForNewFormat('TradeRepoSettled', 'TradeRepoSettled'));
        $this->assertSame('TradesNonRegRepo', SectionNameResolver::resolveForNewFormat('TradeRepoNotSettled', 'TradeRepoNotSettled'));
        $this->assertSame('MoneyInOut', SectionNameResolver::resolveForNewFormat('OperationMoneyBrok', 'OperationMoneyBrok'));
        $this->assertSame('MoneyInOut_io', SectionNameResolver::resolveForNewFormat('OperationMoneyInOut', 'OperationMoneyInOut'));
        $this->assertSame('CorpActionIn', SectionNameResolver::resolveForNewFormat('OperationStockCorpActionIn', 'OperationStockCorpActionIn'));
        $this->assertSame('CorpActionOut', SectionNameResolver::resolveForNewFormat('OperationStockCorpActionOut', 'OperationStockCorpActionOut'));
        $this->assertSame('StockOnDate_MTL', SectionNameResolver::resolveForNewFormat('PortfolioStockMetal', 'PortfolioStockMetal'));
        $this->assertSame('CustomRecord', SectionNameResolver::resolveForNewFormat('UnknownSource', 'CustomRecord'));
        $this->assertSame('UnknownSource', SectionNameResolver::resolveForNewFormat('UnknownSource', ''));
    }

    public function testModernFieldCanonicalizerMapsRepresentativeSources(): void
    {
        $header = ModernFieldCanonicalizer::canonicalize('Header', 'Header', [
            'CpID' => '24260600',
            'DateBegin' => '2024-02-01T00:00:00',
            'DateEnd' => '2024-02-12T00:00:00',
            'ReportDate' => '2024-02-13T00:00:00',
            'WithSubaccount' => 'true',
        ]);
        $this->assertSame('24260600', $header['CPID']);
        $this->assertSame('01.02.2024', $header['BegDate']);
        $this->assertSame('12.02.2024', $header['EndDate']);
        $this->assertSame('13.02.2024', $header['MakeDate']);
        $this->assertSame('1', $header['WithSubAccounts']);

        $trade = ModernFieldCanonicalizer::canonicalize('TradeCommonNotSettled', 'TradeCommonNotSettled', [
            'IntOperNum' => 'abc, 09.02.24, 14:15:16',
        ]);
        $this->assertSame('0', $trade['isComplete']);
        $this->assertSame('09.02.2024 0:00:00', $trade['OperDateSort']);
        $this->assertSame('01.01.1900 14:15:16', $trade['OperTimeSort']);

        $tradeFallback = ModernFieldCanonicalizer::canonicalize('TradeCommonSettled', 'TradeCommonSettled', [
            'PaymentDate' => '22.03.21 / text',
        ]);
        $this->assertSame('1', $tradeFallback['isComplete']);
        $this->assertSame('22.03.2021 0:00:00', $tradeFallback['OperDateSort']);
        $this->assertSame('01.01.1900 0:00:00', $tradeFallback['OperTimeSort']);

        $repo = ModernFieldCanonicalizer::canonicalize('TradeRepoSettled', 'TradeRepoSettled', [
            'IntOperNum' => 'abc, 25.12.25, 14:55:03',
        ]);
        $this->assertSame('1', $repo['isComplete']);
        $this->assertSame('', $repo['RepoMarginCall']);
        $this->assertSame('25.12.2025 0:00:00', $repo['OperDateSort']);

        $fx = ModernFieldCanonicalizer::canonicalize('TradeFXClient', 'TradeFXClient', [
            'Date1' => '2021-03-22T00:00:00',
            'Sum1RUR' => '22252.50000000',
            'Sum2RUR' => '366403.00000000',
        ]);
        $this->assertSame('22.03.2021', $fx['OperDate']);
        $this->assertSame('22.03.2021 0:00:00', $fx['OperDateSort']);
        $this->assertSame('ClientMoneyConvert', $fx['Section']);
        $this->assertSame('22252.50000000', $fx['Sum1_RUR']);
        $this->assertSame('366403.00000000', $fx['Sum2_RUR']);

        $moneyOperation = ModernFieldCanonicalizer::canonicalize('OperationMoneyInOut', 'OperationMoneyInOut', [
            'OperDate' => '24.12.2025',
            'Quantity' => '10.00000000',
        ]);
        $this->assertSame('InOut', $moneyOperation['OperType']);
        $this->assertSame('10', $moneyOperation['OperTypeSort']);
        $this->assertSame('10.00000000', $moneyOperation['Quantity_RUR']);
        $this->assertSame('', $moneyOperation['IntOperType']);

        $portfolioMoney = ModernFieldCanonicalizer::canonicalize('PortfolioMoney', 'PortfolioMoney', [
            'QuantityBegin' => '1',
            'QuantityEnd' => '2',
            'QuantityBeginRUR' => '3',
            'QuantityEndRUR' => '4',
            'PartOrder' => '5',
            'RecordOrder' => '6',
            'SectionName' => 'Cash',
        ]);
        $this->assertSame('1', $portfolioMoney['QtyBeg']);
        $this->assertSame('2', $portfolioMoney['QtyEnd']);
        $this->assertSame('3', $portfolioMoney['QtyBeg_rub']);
        $this->assertSame('4', $portfolioMoney['QtyEnd_rub']);
        $this->assertSame('5', $portfolioMoney['n1']);
        $this->assertSame('6', $portfolioMoney['n2']);
        $this->assertSame('Cash', $portfolioMoney['Name']);

        $portfolioStock = ModernFieldCanonicalizer::canonicalize('PortfolioStockMetal', 'PortfolioStockMetal', [
            'QuantityBegin' => '1',
            'QuantityAvailableBegin' => '2',
            'QuantitytWillBe' => '3',
            'QuantityEnd' => '4',
            'QuantityAvailableEnd' => '5',
            'PriceBegin' => '6',
            'PriceEnd' => '7',
            'AmountBegin' => '8',
            'AmountEnd' => '9',
            'NKDBegin' => '10',
            'NKDEnd' => '11',
            'PriceCurrBegin' => 'USD',
            'PriceCurrEnd' => 'RUR',
        ]);
        $this->assertSame('1', $portfolioStock['QttyIn']);
        $this->assertSame('2', $portfolioStock['QttyInAft']);
        $this->assertSame('3', $portfolioStock['QttyPlan']);
        $this->assertSame('4', $portfolioStock['QttyOut']);
        $this->assertSame('5', $portfolioStock['QttyOutAft']);
        $this->assertSame('6', $portfolioStock['PriceIn']);
        $this->assertSame('7', $portfolioStock['PriceOut']);
        $this->assertSame('8', $portfolioStock['ValueIn']);
        $this->assertSame('9', $portfolioStock['ValueOut']);
        $this->assertSame('10', $portfolioStock['NKDIn']);
        $this->assertSame('11', $portfolioStock['NKDOut']);
        $this->assertSame('1', $portfolioStock['OperPlace']);

        $stockOperation = ModernFieldCanonicalizer::canonicalize('OperationStockPayOff', 'OperationStockPayOff', [
            'OperDate' => '24.12.2025',
            'PayingSumRUR' => '3906405.00000000',
        ]);
        $this->assertSame('24.12.2025 0:00:00', $stockOperation['OperDateSort']);
        $this->assertSame('01.01.1900 0:00:00', $stockOperation['OperTimeSort']);
        $this->assertSame('StockPayingOff', $stockOperation['Section']);
        $this->assertSame('Stk', $stockOperation['GroupID']);
        $this->assertSame('3906405.00000000', $stockOperation['PayingSum_RUR']);
        $this->assertSame('MTRT', $stockOperation['IntOperType']);

        $corpAction = ModernFieldCanonicalizer::canonicalize('OperationStockCorpActionIn', 'OperationStockCorpActionIn', [
            'OperDate' => '24.12.2025',
        ]);
        $this->assertSame('Stk', $corpAction['GroupID']);
        $this->assertSame('', $corpAction['NominalCurr']);
        $this->assertSame('0.00000000', $corpAction['PayingSum']);
        $this->assertSame('0.000000', $corpAction['PayingSum_RUR']);
    }

    public function testModernFieldCanonicalizerCoversEdgeDateBranches(): void
    {
        $headerKeepsExistingTarget = ModernFieldCanonicalizer::canonicalize('Header', 'Header', [
            'DateBegin' => 'not-iso-date',
            'BegDate' => '01.01.2024',
        ]);
        $this->assertSame('01.01.2024', $headerKeepsExistingTarget['BegDate']);

        $headerInvalidIso = ModernFieldCanonicalizer::canonicalize('Header', 'Header', [
            'DateBegin' => 'bad-date',
        ]);
        $this->assertSame('bad-date', $headerInvalidIso['BegDate']);

        $fxWithEmptyDate = ModernFieldCanonicalizer::canonicalize('TradeFXNonClient', 'TradeFXNonClient', [
            'Date1' => '',
        ]);
        $this->assertArrayNotHasKey('OperDate', $fxWithEmptyDate);

        $fxWithFullDotDate = ModernFieldCanonicalizer::canonicalize('TradeFXNonClient', 'TradeFXNonClient', [
            'Date1' => '24.12.2025',
        ]);
        $this->assertSame('24.12.2025', $fxWithFullDotDate['OperDate']);

        $fxWithUnknownDate = ModernFieldCanonicalizer::canonicalize('TradeFXNonClient', 'TradeFXNonClient', [
            'Date1' => 'not-a-date',
        ]);
        $this->assertSame('not-a-date', $fxWithUnknownDate['OperDate']);

        $tradeWithoutMatchingDates = ModernFieldCanonicalizer::canonicalize('TradeCommonSettled', 'TradeCommonSettled', [
            'PaymentDate' => 'invalid',
        ]);
        $this->assertArrayNotHasKey('OperDateSort', $tradeWithoutMatchingDates);
        $this->assertSame('01.01.1900 0:00:00', $tradeWithoutMatchingDates['OperTimeSort']);

        $repoWithoutIntOperNumMatch = ModernFieldCanonicalizer::canonicalize('TradeRepoNotSettled', 'TradeRepoNotSettled', [
            'IntOperNum' => 'invalid-value',
        ]);
        $this->assertArrayNotHasKey('OperDateSort', $repoWithoutIntOperNumMatch);

        $tradeWithExplicitEmptyFallbackDate = ModernFieldCanonicalizer::canonicalize('TradeCommonSettled', 'TradeCommonSettled', [
            'PaymentDate' => '',
        ]);
        $this->assertArrayNotHasKey('OperDateSort', $tradeWithExplicitEmptyFallbackDate);

        $tradeWithPredefinedSortDates = ModernFieldCanonicalizer::canonicalize('TradeCommonSettled', 'TradeCommonSettled', [
            'OperDateSort' => 'keep-date',
            'OperTimeSort' => 'keep-time',
            'IntOperNum' => 'abc, 01.02.24, 10:11:12',
        ]);
        $this->assertSame('keep-date', $tradeWithPredefinedSortDates['OperDateSort']);
        $this->assertSame('keep-time', $tradeWithPredefinedSortDates['OperTimeSort']);
    }
}
