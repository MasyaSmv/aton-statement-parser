<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Tests;

use MasyaSmv\AtonStatementParser\AtonStatementParser;
use MasyaSmv\AtonStatementParser\Contracts\ReportInterface;
use MasyaSmv\AtonStatementParser\Report\Report;
use MasyaSmv\AtonStatementParser\Report\Row;
use MasyaSmv\AtonStatementParser\Report\Section;
use PHPUnit\Framework\TestCase;

final class RealFixtureParityTest extends TestCase
{
    /**
     * @dataProvider pairedFixtureProvider
     */
    public function testCoreSectionsMatchBetweenLegacyAndModernFixtures(string $file): void
    {
        $legacyPath = __DIR__ . '/FixturesLocal/old/' . $file;
        $modernPath = __DIR__ . '/FixturesLocal/new/' . $file;

        if (!is_file($legacyPath) || !is_file($modernPath)) {
            $this->markTestSkipped('Paired local fixtures are not available.');
        }

        $legacy = AtonStatementParser::fromFile($legacyPath);
        $modern = AtonStatementParser::fromFile($modernPath);

        self::assertSame(
            $this->sectionRowCounts($legacy),
            $this->sectionRowCounts($modern)
        );

        $legacyOperIds = $legacy->operIds()->toArray();
        $modernOperIds = $modern->operIds()->toArray();
        sort($legacyOperIds);
        sort($modernOperIds);

        self::assertSame(
            $legacyOperIds,
            $modernOperIds
        );
    }

    public function testModernPortfolioMoneyIsSplitIntoCanonicalSections(): void
    {
        $path = __DIR__ . '/FixturesLocal/new/report_30385480_20241228_20251228_client.xml';

        if (!is_file($path)) {
            $this->markTestSkipped('Local modern fixture is not available.');
        }

        $report = AtonStatementParser::fromFile($path);

        self::assertCount(3, $report->section('MoneyOnDate')->rows());
        self::assertCount(5, $report->section('MoneyOnDate_MarketPrc')->rows());
        self::assertCount(1, $report->section('MoneyOnDate_ByOperPlace')->rows());

        $moneyRow = $report->section('MoneyOnDate')->rows()->first();
        self::assertInstanceOf(Row::class, $moneyRow);
        self::assertSame('2_PortfolioMoney_ByType', $moneyRow->getString('Section'));

        $marketRow = $report->section('MoneyOnDate_MarketPrc')->rows()->first();
        self::assertInstanceOf(Row::class, $marketRow);
        self::assertSame('1_PortfolioMoney_Value', $marketRow->getString('Section'));

        $operPlaceRow = $report->section('MoneyOnDate_ByOperPlace')->rows()->first();
        self::assertInstanceOf(Row::class, $operPlaceRow);
        self::assertSame('4_PortfolioMoney_ByOperPlace', $operPlaceRow->getString('Section'));
    }

    public function testModernMoneyInOutCollapsesOppositePairsToLegacyCompatibleRows(): void
    {
        $path = __DIR__ . '/FixturesLocal/new/report_25077200_20211231_20221231_client.xml';

        if (!is_file($path)) {
            $this->markTestSkipped('Local modern fixture is not available.');
        }

        $report = AtonStatementParser::fromFile($path);
        $rows = $this->rowsWithOperId($report->section('MoneyInOut_io'), '409456016');

        self::assertCount(1, $rows);
        self::assertSame('-6000.00000000', $rows[0]->getString('Quantity'));
        self::assertSame('-464821.20000000', $rows[0]->getString('Quantity_RUR'));
    }

    public function testModernCanDeriveLegacyCompatibilitySections(): void
    {
        $path = __DIR__ . '/FixturesLocal/new/report_22824900_20200101_20201231_client.xml';

        if (!is_file($path)) {
            $this->markTestSkipped('Local modern fixture is not available.');
        }

        $report = AtonStatementParser::fromFile($path);

        $moneySingleRow = $report->section('MoneyOnDate_single')->rows()->first();
        self::assertInstanceOf(Row::class, $moneySingleRow);
        self::assertSame('0.00', $moneySingleRow->getString('ChangeDebtsMZ'));
        self::assertSame('0.00', $moneySingleRow->getString('MoneyFSIn'));
        self::assertSame('19.12.2016', $moneySingleRow->getString('MicexRenamePAODate'));

        $stockExgSumRow = $report->section('StockOnDate_Exg_Sum')->rows()->first();
        self::assertInstanceOf(Row::class, $stockExgSumRow);
        self::assertSame('22447092.89000000', $stockExgSumRow->getString('ValueIn'));
        self::assertSame('14348463.96000000', $stockExgSumRow->getString('ValueOut'));
        self::assertSame('0.00000000', $stockExgSumRow->getString('NKDIn'));
        self::assertSame('0.00000000', $stockExgSumRow->getString('NKDOut'));
    }

    /**
     * @dataProvider pairedFixtureProvider
     */
    public function testPairedFixturesOnlyDifferByExpectedSyntheticOrModernOnlySections(string $file): void
    {
        $legacyPath = __DIR__ . '/FixturesLocal/old/' . $file;
        $modernPath = __DIR__ . '/FixturesLocal/new/' . $file;

        if (!is_file($legacyPath) || !is_file($modernPath)) {
            $this->markTestSkipped('Paired local fixtures are not available.');
        }

        $legacy = AtonStatementParser::fromFile($legacyPath);
        $modern = AtonStatementParser::fromFile($modernPath);

        self::assertSame(
            $this->expectedSectionCountDiffs($legacy, $modern),
            $this->actualSectionCountDiffs($legacy, $modern)
        );
    }

    /**
     * @return array<string, array{0: string}>
     */
    public function pairedFixtureProvider(): array
    {
        return [
            '21898500_20240808_20240809' => ['report_21898500_20240808_20240809_client.xml'],
            '22824900_20180101_20190101' => ['report_22824900_20180101_20190101_client.xml'],
            '22824900_20200101_20201231' => ['report_22824900_20200101_20201231_client.xml'],
            '22824900_20201231_20211231' => ['report_22824900_20201231_20211231_client.xml'],
            '22824900_20211231_20221231' => ['report_22824900_20211231_20221231_client.xml'],
            '23828900_20190101_20200101' => ['report_23828900_20190101_20200101_client.xml'],
            '24260600_20240201_20240212' => ['report_24260600_20240201_20240212_client.xml'],
            '24467800_20221231_20231110' => ['report_24467800_20221231_20231110_client.xml'],
            '25077200_20211231_20221231' => ['report_25077200_20211231_20221231_client.xml'],
            '27656300_20260126_20260127' => ['report_27656300_20260126_20260127_client.xml'],
            '30385480_20241228_20251228' => ['report_30385480_20241228_20251228_client.xml'],
        ];
    }

    /**
     * @return array<string, int>
     */
    private function sectionRowCounts(object $report): array
    {
        $sections = (new \ReflectionClass(Report::class))->getProperty('sections');
        $sections->setAccessible(true);

        /** @var array<string, Section> $items */
        $items = $sections->getValue($report);
        $counts = [];
        $ignored = [
            'MoneyOnDate_MarketPrc' => true,
            'MoneyOnDate_ByOperPlace' => true,
            'MoneyOnDate_single' => true,
            'StockOnDate_Exg_Sum' => true,
        ];

        foreach ($items as $name => $section) {
            if (isset($ignored[$name])) {
                continue;
            }

            $counts[$name] = $section->rows()->count();
        }

        ksort($counts);

        return $counts;
    }

    /**
     * @return list<Row>
     */
    private function rowsWithOperId(Section $section, string $operId): array
    {
        $rows = [];

        foreach ($section->rows() as $row) {
            if ($row->getString('OperID') === $operId) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    /**
     * @return array<string, string>
     */
    private function actualSectionCountDiffs(ReportInterface $legacy, ReportInterface $modern): array
    {
        $legacySections = $this->sections($legacy);
        $modernSections = $this->sections($modern);
        $names = array_unique(array_merge(array_keys($legacySections), array_keys($modernSections)));
        sort($names);

        $diffs = [];

        foreach ($names as $name) {
            $legacyCount = isset($legacySections[$name]) ? $legacySections[$name]->rows()->count() : 0;
            $modernCount = isset($modernSections[$name]) ? $modernSections[$name]->rows()->count() : 0;

            if ($legacyCount !== $modernCount) {
                $diffs[$name] = $legacyCount . '!=' . $modernCount;
            }
        }

        return $diffs;
    }

    /**
     * @return array<string, string>
     */
    private function expectedSectionCountDiffs(ReportInterface $legacy, ReportInterface $modern): array
    {
        $expected = [];
        $actualDiffs = $this->actualSectionCountDiffs($legacy, $modern);
        $allowedDiffs = [
            'MoneyOnDate_ByOperPlace' => true,
            'MoneyOnDate_MarketPrc' => true,
            'MoneyOnDate_single' => true,
            'StockOnDate_Exg_Sum' => true,
        ];

        foreach ($actualDiffs as $name => $diff) {
            if (isset($allowedDiffs[$name])) {
                $expected[$name] = $diff;
            }
        }

        ksort($expected);

        return $expected;
    }

    /**
     * @return array<string, Section>
     */
    private function sections(ReportInterface $report): array
    {
        $sections = (new \ReflectionClass(Report::class))->getProperty('sections');
        $sections->setAccessible(true);

        /** @var array<string, Section> $items */
        $items = $sections->getValue($report);

        return $items;
    }
}
