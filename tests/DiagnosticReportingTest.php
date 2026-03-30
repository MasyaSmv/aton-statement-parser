<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Tests;

use DOMDocument;
use MasyaSmv\AtonStatementParser\Parsing\LegacyBisReportParser;
use MasyaSmv\AtonStatementParser\Parsing\ModernXmlReportParser;
use PHPUnit\Framework\TestCase;

final class DiagnosticReportingTest extends TestCase
{
    public function testModernParserReportsUnknownSourceAndUnexpectedField(): void
    {
        $document = new DOMDocument();
        $document->loadXML(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
  <source name="UnknownSource">
    <UnknownSource>
      <KnownLikeField>1</KnownLikeField>
    </UnknownSource>
  </source>
  <source name="Header">
    <Header>
      <CpID>10</CpID>
      <UnexpectedField>value</UnexpectedField>
    </Header>
  </source>
</root>
XML);

        $report = (new ModernXmlReportParser())->parse($document);
        $diagnostics = $report->diagnostics();
        $firstDiagnostic = $diagnostics->get(0);
        $secondDiagnostic = $diagnostics->get(1);

        $this->assertTrue($report->hasDiagnostics());
        $this->assertCount(2, $diagnostics);
        $this->assertNotNull($firstDiagnostic);
        $this->assertNotNull($secondDiagnostic);
        $this->assertSame('unknown_modern_source', $firstDiagnostic->code());
        $this->assertSame('UnknownSource', $firstDiagnostic->structure());
        $this->assertSame('unexpected_modern_field', $secondDiagnostic->code());
        $this->assertSame('Header', $secondDiagnostic->structure());
        $this->assertSame('UnexpectedField', $secondDiagnostic->key());
    }

    public function testModernParserReportsUnexpectedRecordType(): void
    {
        $document = new DOMDocument();
        $document->loadXML(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
  <source name="Header">
    <OtherRecord>
      <CpID>10</CpID>
    </OtherRecord>
  </source>
</root>
XML);

        $report = (new ModernXmlReportParser())->parse($document);
        $diagnostic = $report->diagnostics()->first();

        $this->assertNotNull($diagnostic);
        $this->assertSame('unexpected_modern_record_type', $diagnostic->code());
        $this->assertSame('Header', $diagnostic->structure());
    }

    public function testModernParserReportsSyntheticLegacySections(): void
    {
        $document = new DOMDocument();
        $document->loadXML(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
  <source name="PortfolioMoney">
    <PortfolioMoney>
      <Section>2_PortfolioMoney_ByType</Section>
      <SectionName>Money</SectionName>
      <PartOrder>1</PartOrder>
      <RecordOrder>1</RecordOrder>
      <AssetCode>RUR</AssetCode>
      <QuantityBegin>10.00</QuantityBegin>
      <QuantityBeginRUR>10.00</QuantityBeginRUR>
      <QuantityEnd>20.00</QuantityEnd>
      <QuantityEndRUR>20.00</QuantityEndRUR>
    </PortfolioMoney>
  </source>
  <source name="PortfolioStockEX">
    <PortfolioStockEX>
      <AssetCode>AAA</AssetCode>
      <OperPlace>X</OperPlace>
      <QuantityBegin>1.00</QuantityBegin>
      <QuantityAvailableBegin>1.00</QuantityAvailableBegin>
      <QuantitytWillBe>1.00</QuantitytWillBe>
      <QuantityEnd>1.00</QuantityEnd>
      <QuantityAvailableEnd>1.00</QuantityAvailableEnd>
      <PriceBegin>10.50</PriceBegin>
      <PriceEnd>20.25</PriceEnd>
      <AmountBegin>10.50</AmountBegin>
      <AmountEnd>20.25</AmountEnd>
      <NKDBegin>0.10</NKDBegin>
      <NKDEnd>0.20</NKDEnd>
      <PriceCurrBegin>RUR</PriceCurrBegin>
      <PriceCurrEnd>RUR</PriceCurrEnd>
    </PortfolioStockEX>
    <PortfolioStockEX>
      <AssetCode>BBB</AssetCode>
      <OperPlace>X</OperPlace>
      <QuantityBegin>2.00</QuantityBegin>
      <QuantityAvailableBegin>2.00</QuantityAvailableBegin>
      <QuantitytWillBe>2.00</QuantitytWillBe>
      <QuantityEnd>2.00</QuantityEnd>
      <QuantityAvailableEnd>2.00</QuantityAvailableEnd>
      <PriceBegin>1.05</PriceBegin>
      <PriceEnd>2.10</PriceEnd>
      <AmountBegin>1.05</AmountBegin>
      <AmountEnd>2.10</AmountEnd>
      <NKDBegin>0.01</NKDBegin>
      <NKDEnd>0.02</NKDEnd>
      <PriceCurrBegin>RUR</PriceCurrBegin>
      <PriceCurrEnd>RUR</PriceCurrEnd>
    </PortfolioStockEX>
  </source>
</root>
XML);

        $report = (new ModernXmlReportParser())->parse($document);
        $diagnostics = $report->diagnostics()->toArray();
        $codes = array_map(static fn ($item) => $item->code(), $diagnostics);
        $structures = array_map(static fn ($item) => $item->structure(), $diagnostics);
        $sumRow = $report->section('StockOnDate_Exg_Sum')->rows()->first();

        $this->assertContains('synthetic_legacy_section', $codes);
        $this->assertContains('MoneyOnDate_single', $structures);
        $this->assertContains('StockOnDate_Exg_Sum', $structures);
        $this->assertNotNull($sumRow);
        $this->assertSame('11.55', $sumRow->getString('ValueIn'));
        $this->assertSame('22.35', $sumRow->getString('ValueOut'));
        $this->assertSame('0.11', $sumRow->getString('NKDIn'));
        $this->assertSame('0.22', $sumRow->getString('NKDOut'));
    }

    public function testLegacyParserReportsUnknownSectionAndUnexpectedField(): void
    {
        $document = new DOMDocument();
        $document->loadXML(<<<XML
<?xml version="1.0" encoding="UTF-8"?>
<BIS:BISPeriod xmlns:BIS="urn:test">
  <BIS:Trades>
    <BIS:Row BIS:OperID="1" BIS:UnexpectedAttr="x"/>
  </BIS:Trades>
  <BIS:UnknownSection>
    <BIS:Row BIS:SomeField="1"/>
  </BIS:UnknownSection>
</BIS:BISPeriod>
XML);

        $report = (new LegacyBisReportParser())->parse($document);
        $diagnostics = $report->diagnostics();
        $firstDiagnostic = $diagnostics->get(0);
        $secondDiagnostic = $diagnostics->get(1);

        $this->assertTrue($report->hasDiagnostics());
        $this->assertCount(2, $diagnostics);
        $this->assertNotNull($firstDiagnostic);
        $this->assertNotNull($secondDiagnostic);
        $this->assertSame('unexpected_legacy_field', $firstDiagnostic->code());
        $this->assertSame('Trades', $firstDiagnostic->structure());
        $this->assertSame('UnexpectedAttr', $firstDiagnostic->key());
        $this->assertSame('unknown_legacy_section', $secondDiagnostic->code());
        $this->assertSame('UnknownSection', $secondDiagnostic->structure());
    }
}
