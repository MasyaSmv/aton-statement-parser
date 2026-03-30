# Library Guide

Подробная карта библиотеки `aton-statement-parser`.

Документ нужен для двух задач:

- быстро понять, где находится нужный блок;
- не тратить время на чтение всего проекта подряд, если интересует конкретный слой.

## Навигация

1. Точка входа
2. Поток данных
3. Слои проекта
4. Канонические секции отчёта
5. DTO API
6. Исключения
7. Расширение библиотеки
8. Проверки качества
9. CI и mutation testing

## Точка входа

Основная точка входа:

- `MasyaSmv\AtonStatementParser\AtonStatementParser`

Доступные методы:

- `fromFile(string $path): ReportInterface`
- `fromString(string $xml): ReportInterface`
- `version(): string`

## Поток данных

```mermaid
flowchart TD
    A[XML file/string] --> B[XmlLoader]
    B --> C[DOMDocument]
    C --> D[ReportParserResolver]
    D --> E[LegacyBisReportParser]
    D --> F[ModernXmlReportParser]
    E --> G[Canonical Report]
    F --> G[Canonical Report]
    G --> H[Section / Row API]
    G --> I[DTO API]
```

## Слои проекта

### 1. XML слой

Файлы:

- `src/Xml/XmlLoader.php`
- `src/Xml/XPathFactory.php`

Ответственность:

- чтение XML из файла и строки;
- нормализация кодировки;
- загрузка `DOMDocument`;
- подготовка `DOMXPath` для legacy BIS.

### 2. Parsing слой

Файлы:

- `src/Parsing/ReportParserResolver.php`
- `src/Parsing/LegacyBisReportParser.php`
- `src/Parsing/ModernXmlReportParser.php`
- `src/Parsing/KnownLegacySchema.php`
- `src/Parsing/KnownModernSchema.php`
- `src/Parsing/ModernFieldCanonicalizer.php`
- `src/Parsing/SectionNameResolver.php`

Ответственность:

- определить формат отчёта;
- разобрать legacy и modern XML отдельно;
- свести секции и поля к одной канонической модели.

### 3. Доменная модель отчёта

Файлы:

- `src/Report/Report.php`
- `src/Report/Section.php`
- `src/Report/Row.php`
- `src/Report/AttributeBag.php`
- `src/Report/ParseDiagnostic.php`
- `src/Report/DiagnosticCollection.php`

Ответственность:

- представлять отчёт в общей структуре;
- давать универсальный доступ к секциям и строкам;
- хранить immutable-атрибуты и typed getters;
- возвращать диагностику по неизвестным структурам и synthetic compatibility sections.

### 4. Support слой

Файлы:

- `src/Support/DecimalStringMath.php`

Ответственность:

- точная арифметика над строковыми decimal-значениями;
- derived aggregate-вычисления без перехода на `float`.

### 5. DTO слой

Файлы:

- `src/Dto/*`
- `src/Mappers/*`
- `src/Collections/*`
- `src/Contracts/Mappers/*`

Ответственность:

- давать более удобный API поверх канонических `Row`;
- возвращать immutable DTO и typed collections;
- отделять универсальный `Row` от частых пользовательских сценариев.

## Канонические секции отчёта

Ниже секции, которые библиотека сейчас считает основными.

### Общие данные

- `CommonData`

### Операции

- `Trades`
- `TradesRegRepo`
- `TradesNonRegRepo`
- `MoneyInOut`
- `MoneyInOut_io`
- `ClientMoneyConvert`
- `MoneyConvert`
- `StockInOut`
- `StockPayingOff`
- `CorpActionIn`
- `CorpActionOut`

### Состояние на дату

- `MoneyOnDate`
- `StockOnDate`
- `StockOnDate_Exg`
- `StockOnDate_NonExg`
- `StockOnDate_MTL`

## DTO API

Методы доступны из `ReportInterface`.

### Универсальный API

- `hasSection(string $name): bool`
- `section(string $name): Section`
- `operIds(): OperIdCollection`
- `findOperId(string $operId): ?Row`
- `diagnostics(): DiagnosticCollection`
- `hasDiagnostics(): bool`

### DTO API

- `commonData(): ?CommonData`
- `trades(): TradeCollection`
- `moneyInOut(): MoneyOperationCollection`
- `moneyOnDate(): MoneyBalanceCollection`
- `stockOnDate(): StockBalanceCollection`
- `moneyConvert(): MoneyConvertCollection`
- `stockInOut(): StockTransferCollection`
- `stockPayingOff(): StockPayingOffCollection`
- `corporateActionsIn(): CorporateActionCollection`
- `corporateActionsOut(): CorporateActionCollection`

## Ключевые связи

```mermaid
classDiagram
    class AtonStatementParser
    class ReportParserResolver
    class LegacyBisReportParser
    class ModernXmlReportParser
    class Report
    class Section
    class Row
    class AttributeBag

    AtonStatementParser --> ReportParserResolver
    ReportParserResolver --> LegacyBisReportParser
    ReportParserResolver --> ModernXmlReportParser
    LegacyBisReportParser --> Report
    ModernXmlReportParser --> Report
    Report --> Section
    Section --> Row
    Row --> AttributeBag
```

## Исключения

Текущие исключения библиотеки:

- `InvalidXmlException`
  - файл не найден;
  - XML пустой;
  - XML синтаксически некорректен.
- `ParseException`
  - базовое исключение парсинга.
- `UnsupportedReportFormatException`
  - XML не похож ни на legacy BIS, ни на modern root/source.
- `MissingBisNamespaceException`
  - legacy XML без корректного BIS namespace.
- `MissingSectionException`
  - пользователь запросил несуществующую секцию.
- `DtoMappingException`
  - отсутствует обязательное поле для DTO-маппинга.

## Расширение библиотеки

Если нужно добавить новую секцию или новый тип DTO, ориентир такой:

1. определить, как секция выглядит в old и new формате;
2. добавить каноническое имя секции в `SectionNameResolver`;
3. при необходимости добавить old-compatible поля в `ModernFieldCanonicalizer`;
4. проверить, достаточно ли базового `Row` API;
5. если секция часто нужна пользователю, добавить DTO, mapper, collection и метод в `Report`.

## Где искать нужное

Если нужен конкретный блок, ориентируйтесь так:

- проблемы чтения XML: `src/Xml`
- выбор формата: `src/Parsing/ReportParserResolver.php`
- legacy BIS: `src/Parsing/LegacyBisReportParser.php`
- modern XML: `src/Parsing/ModernXmlReportParser.php`
- канонизация секций: `src/Parsing/SectionNameResolver.php`
- канонизация полей: `src/Parsing/ModernFieldCanonicalizer.php`
- общий API отчёта: `src/Report`
- DTO: `src/Dto`, `src/Mappers`, `src/Collections`
- контракты DTO-мапперов: `src/Contracts/Mappers`
- исключения: `src/Exceptions`
- диагностика структуры: `Report->diagnostics()`
- тесты happy path: `tests/*Parse*`, `tests/DtoMappingTest.php`
- тесты negative path: `tests/NegativeParseTest.php`
- тесты immutable/guards: `tests/ImmutableCollectionsTest.php`
- тесты паритета real fixtures: `tests/RealFixtureParityTest.php`

## Подтверждённая канонизация old/new

На текущем этапе библиотека уже проверяется не только на синтетических fixtures, но и на парных реальных отчётах из:

- `tests/FixturesLocal/old`
- `tests/FixturesLocal/new`

Что уже подтверждено на совпадающих old/new парах:

- core-секции совпадают по количеству строк после канонизации;
- множества `OperID` совпадают между legacy и modern представлением;
- `PortfolioMoney` нового формата больше не смешивается в один блок:
  - `2_PortfolioMoney_ByType` -> `MoneyOnDate`
  - `1_PortfolioMoney_Value` -> `MoneyOnDate_MarketPrc`
  - `4_PortfolioMoney_ByOperPlace` -> `MoneyOnDate_ByOperPlace`
- для `MoneyInOut_io` modern-отчётов библиотека схлопывает только строго симметричные `+/-` дубли одной операции, чтобы привести результат к legacy-compatible виду.
- для modern-отчётов без прямого источника legacy singleton/aggregate-блоков библиотека сейчас добавляет совместимые derived sections:
  - `MoneyOnDate_single` как synthetic legacy-compatibility row;
  - `StockOnDate_Exg_Sum` как derived aggregate по всем строкам `StockOnDate_Exg`.
- каждая synthetic compatibility section помечается диагностикой `synthetic_legacy_section`.
- новые `source`, неизвестные legacy-секции и неожиданные поля в известных блоках попадают в `Report->diagnostics()` и не теряются молча.

Что пока остаётся честно формат-специфичным:

- семантика `MoneyOnDate_single` в modern-формате, так как прямого источника для её полей в XML не найдено и секция воспроизводится как compatibility row;
- `MoneyOnDate_ByOperPlace`, так как это modern-only разбиение `PortfolioMoney`, для которого в legacy fixtures нет универсального прямого аналога.

Эти блоки пока не принудительно смешиваются с основной канонической моделью без отдельного бизнес-решения.

## Проверки качества

Перед PR должны быть зелёными:

- `composer cs:check`
- `composer deptrac`
- `composer stan`
- `composer psalm`
- `composer test`
- `composer test:coverage`
- `composer coverage:check`

Mutation testing поддерживается отдельно:

- `composer infection`

Практически это значит:

- основной CI блокирует PR на style, architecture, static analysis, tests и coverage threshold;
- mutation testing вынесен в отдельный workflow, потому что он ощутимо тяжелее обычного CI;
- локально `infection` и coverage-команды требуют coverage driver (`pcov` или `xdebug`).

Текущее подтверждённое локальное покрытие:

- строки: `100%`
- методы: `100%`
- классы: `100%`
- тесты: `115`
- assertions: `790`

## CI и mutation testing

Основной workflow:

- `.github/workflows/ci.yml`

Проверяет:

- `composer cs:check`
- `composer deptrac`
- `composer stan`
- `composer psalm`
- `composer test`
- `composer test:coverage`
- `composer coverage:check`

Отдельный mutation workflow:

- `.github/workflows/mutation.yml`

Он запускается вручную или по расписанию и выполняет:

- `composer infection`

## Релизы

Релизы идут по тегам формата:

```bash
vX.Y.Z
```

GitHub Release создаётся автоматически workflow-файлом:

- `.github/workflows/release.yml`
