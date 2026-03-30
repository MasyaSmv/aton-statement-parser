# aton-statement-parser

Пакет для парсинга XML-отчётов брокера **Атон** в удобную доменную структуру данных. Сейчас библиотека ориентирована на поддержку:

* старого BIS-формата `BIS:BISPeriod`,
* нового XML-формата на основе `<root><source name="...">`.

Цель та же: привести оба формата к единой модели отчёта, чтобы в проекте можно было:

* передать путь до XML-файла,
* получить объект отчёта (`ReportInterface`),
* быстро выбрать нужный блок (`Trades`, `MoneyInOut`, `StockInOut`, `TradesRegRepo`, `ClientMoneyConvert`, `StockPayingOff`, …),
* найти операцию по `OperID`,
* собрать список всех `OperID` (например, для сверки с БД),
* работать с атрибутами строк через удобные геттеры (строки/даты/числа) без «магических» индексов массива,
* получать диагностику по новым или неожиданным структурам отчёта,
* сохранять derived legacy-совместимые секции для modern XML там, где прямого источника нет.

> Цель: минимальная боль при работе с реальными отчётами старого и нового форматов, сохраняя единый API.

---

## Быстрый старт

```php
use MasyaSmv\AtonStatementParser\AtonStatementParser;

$report = AtonStatementParser::fromFile('/path/to/report.xml');

// Доступ к секциям (универсально)
$trades = $report->section('Trades')->rows();      // RowCollection
$money  = $report->section('MoneyInOut')->rows();  // RowCollection

// DTO-уровень для типовых сценариев
$commonData = $report->commonData();               // ?CommonData
$tradeDtos  = $report->trades();                   // TradeCollection
$moneyDtos  = $report->moneyInOut();               // MoneyOperationCollection
$moneyState = $report->moneyOnDate();              // MoneyBalanceCollection
$stockState = $report->stockOnDate();              // StockBalanceCollection
$fxDtos     = $report->moneyConvert();             // MoneyConvertCollection
$stockDtos  = $report->stockInOut();               // StockTransferCollection
$payoffDtos = $report->stockPayingOff();           // StockPayingOffCollection
$corpInDtos = $report->corporateActionsIn();       // CorporateActionCollection
$corpOutDtos = $report->corporateActionsOut();     // CorporateActionCollection

// Поиск по OperID
$row = $report->findOperId('567890123');           // Row|null

// Список всех OperID (для сверки)
$ids = $report->operIds();                         // OperIdCollection

// Диагностика неизвестных структур и ключей
$diagnostics = $report->diagnostics();             // DiagnosticCollection
$hasWarnings = $report->hasDiagnostics();          // bool

// Удобные геттеры атрибутов
$type = $report->section('Trades')->rows()->get(0)->getString('TradeType');
$date = $report->section('Trades')->rows()->get(0)->getDate('OperDateSort'); // DateTimeImmutable|null
```

---

## Проблемы, которые решает пакет

### 1) Два формата отчёта

Старые отчёты используют `BIS:BISPeriod`, новые идут через `<root><source name="...">`. Библиотека должна уметь читать оба формата и сводить их к одной модели.

### 2) Namespace BIS

XML использует `xmlns:BIS=...`, значит нельзя просто «по имени тега» — нужен XPath с корректной регистрацией namespace.

### 3) Кодировка windows-1251 и UTF-8 BOM

В старых отчётах встречается `encoding="windows-1251"`, а в новых возможен `utf-8` с BOM. Пакет должен безопасно привести документ к корректному виду перед парсингом.

### 4) Разные форматы дат

В отчётах встречаются:

* `29.12.2023`
* `02.10.24`
* `16.11.18`
* `26.12.24 / ` (мусорные хвосты)

Нужен нормализатор даты: трим + поддержка `d.m.Y` и `d.m.y`.

В новом формате встречаются и ISO-значения, например `2024-02-01T00:00:00`.

### 5) Десятичные числа

Количество и суммы — строки с точностью (`100000.00000000`). Базовый слой хранит как string, а геттеры умеют приводить к float/int при необходимости.

### 6) Новые структуры и неожиданные ключи

Если брокер меняет XML и в отчёте появляется:

* новый `source` в modern-формате,
* неизвестная legacy-секция,
* неожиданный ключ внутри уже известной структуры,

библиотека не теряет данные молча. Она продолжает парсинг и добавляет предупреждения в `Report->diagnostics()`.

---

## Архитектура (как планируем)

### Основные сущности

* **Report** — весь отчёт, доступ к секциям и общим данным.
* **Section** — один блок внутри отчёта (например `Trades`, `MoneyInOut`).
* **Row** — одна строка секции в каноническом виде. Хранит имя секции, исходный тип записи, immutable-атрибуты и типовые геттеры.
* **RowCollection / OperIdCollection / AttributeBag** — immutable коллекции и value objects для публичной модели.

### Уровни удобства

1. **Базовый (универсальный)**: `Report/Section/Row` — работает для любых секций без генерации десятков DTO.
2. **Продвинутый (DTO для популярных секций)**: `CommonData`, `Trade`, `MoneyOperation` и т.п. — добавляются постепенно поверх канонической модели.
   На текущем этапе доступны также DTO для `MoneyOnDate`, `StockOnDate*`, `MoneyConvert`, `StockInOut`, `StockPayingOff`, `CorpActionIn`, `CorpActionOut`.

---

## Структура проекта (план)

```text
src/
  AtonStatementParser.php          // fromFile/fromString
  Collections/
    CorporateActionCollection.php   // immutable коллекция корпоративных действий
    MoneyBalanceCollection.php      // immutable коллекция денежных остатков
    MoneyConvertCollection.php      // immutable коллекция конверсионных операций
    MoneyOperationCollection.php    // immutable коллекция денежных операций
    RowCollection.php              // immutable коллекция строк
    OperIdCollection.php           // immutable коллекция OperID
    StockBalanceCollection.php     // immutable коллекция остатков по бумагам
    StockPayingOffCollection.php   // immutable коллекция погашений/выплат
    StockTransferCollection.php    // immutable коллекция переводов бумаг
    TradeCollection.php            // immutable коллекция сделок
  Contracts/
    Mappers/
      ...MapperInterface.php       // интерфейсы DTO-мапперов
  Dto/
    CommonData.php                 // DTO общих данных отчёта
    CorporateAction.php            // DTO корпоративного действия
    MoneyBalance.php               // DTO денежного остатка
    MoneyConvertOperation.php      // DTO валютной конверсии
    Trade.php                      // DTO сделки
    MoneyOperation.php             // DTO денежной операции
    StockBalance.php               // DTO остатка по бумаге
    StockPayingOff.php             // DTO погашения/выплаты по бумаге
    StockTransfer.php              // DTO перевода/списания бумаги
  Mappers/
    CommonDataMapper.php           // Row -> CommonData
    CorporateActionMapper.php      // Row -> CorporateAction
    MoneyBalanceMapper.php         // Row -> MoneyBalance
    MoneyConvertMapper.php         // Row -> MoneyConvertOperation
    TradeMapper.php                // Row -> Trade
    MoneyOperationMapper.php       // Row -> MoneyOperation
    StockBalanceMapper.php         // Row -> StockBalance
    StockPayingOffMapper.php       // Row -> StockPayingOff
    StockTransferMapper.php        // Row -> StockTransfer
  Parsing/
    ReportParserResolver.php       // выбор парсера по формату документа
    LegacyBisReportParser.php      // старый BIS-формат
    ModernXmlReportParser.php      // новый root/source формат
    KnownLegacySchema.php          // known-schema legacy секций и ключей
    KnownModernSchema.php          // known-schema modern source и ключей
    SectionNameResolver.php        // канонизация имён секций
  Report/
    Report.php                     // секции отчёта
    Section.php                    // имя секции + RowCollection
    Row.php                        // строка секции + геттеры
    AttributeBag.php               // immutable-атрибуты строки
    ParseDiagnostic.php            // диагностика структуры парсинга
    DiagnosticCollection.php       // immutable коллекция диагностик
  Support/
    DecimalStringMath.php          // точная строковая арифметика для derived aggregate-секций
  Xml/
    XmlLoader.php                  // чтение файла, encoding -> utf8, DOM
    XPathFactory.php               // DOMXPath + регистрация namespace
  Normalizers/
    DateNormalizer.php             // даты (d.m.y / d.m.Y, мусорные хвосты)
    NumberNormalizer.php           // числа/десятичные строки
    StringNormalizer.php           // trim/cleanup
  Exceptions/
    ParseException.php
    InvalidXmlException.php

tests/
  Fixtures/
    aton/
      ...xml
  ...
```

---

## Roadmap / План работ

### ✅ Уже сделано

* [x] Базовый каркас пакета (autoload, тесты)
* [x] Fixtures подключены в тесты
* [x] Скрипты composer для `test`, `cs:*`, `stan` (если настроено)
* [x] Поддержка старого BIS-формата
* [x] Базовая поддержка нового root/source формата
* [x] Immutable collections в публичной модели

### 🚧 Этап A — «сразу полезно»

* [x] `AtonStatementParser::fromFile(string $path): ReportInterface`
* [x] `AtonStatementParser::fromString(string $xml): ReportInterface`
* [x] `Report->section(string $name): Section`
* [x] `Section->rows(): RowCollection`
* [x] `Report->operIds(): OperIdCollection`
* [x] `Report->findOperId(string $id): ?Row`
* [x] Unit-тесты на fixtures (operIds/findOperId/section)

### 🚧 Этап B — нормализация типов

* [x] `Row->getString($key)` / `getInt($key)` / `getFloat($key)`
* [x] `Row->getDecimalString($key)` (без потери точности)
* [x] `Row->getDate($key): ?DateTimeImmutable` (с поддержкой форматов)
* [x] Базовые тесты на даты/числа
* [x] Фактический coverage доведён до `100%` по строкам/методам/классам
* [x] Подтверждена паритетная канонизация core-секций на парных real fixtures `old/new`
* [x] `PortfolioMoney` нового формата разложен на `MoneyOnDate`, `MoneyOnDate_MarketPrc`, `MoneyOnDate_ByOperPlace`
* [x] Для `MoneyInOut_io` добавлено схлопывание строго симметричных `+/-` дублей до legacy-compatible результата
* [x] `StockOnDate_Exg_Sum` нового формата вычисляется как derived aggregate по строкам `StockOnDate_Exg`
* [x] Synthetic legacy-совместимые секции помечаются диагностикой `synthetic_legacy_section`
* [ ] Уточнить бизнес-смысл `MoneyOnDate_single`, если для него появится прямой modern-источник или подтверждённая формула

### 🚧 Этап C — DTO (точечно, только нужное)

* [x] `Report->trades(): TradeCollection`
* [x] `Report->moneyInOut(): MoneyOperationCollection`
* [x] `Report->moneyOnDate(): MoneyBalanceCollection`
* [x] `Report->stockOnDate(): StockBalanceCollection`
* [x] `Report->moneyConvert(): MoneyConvertCollection`
* [x] `Report->stockInOut(): StockTransferCollection`
* [x] `Report->stockPayingOff(): StockPayingOffCollection`
* [x] `Report->corporateActionsIn(): CorporateActionCollection`
* [x] `Report->corporateActionsOut(): CorporateActionCollection`
* [x] Базовые мапперы секций → DTO
* [x] Расширить DTO-покрытие на дополнительные секции
* [x] Довести базовые тесты DTO-маппинга

### 🚧 Этап D — удобства для больших отчётов

* [ ] Фильтры/поиск: `section()->where(fn(Row $r) => ...)`
* [ ] Индексация по `OperID` (лениво или при первом вызове)
* [ ] Сортировка операций по дате/времени
* [ ] Поиск по бумаге по названию и `ISIN`
* [ ] Collection-style методы в духе ORM/Laravel: `count()`, `sum()`, `filter()`, `map()`, `firstWhere()`, `sortBy()`
* [ ] Улучшение ошибок парсинга (контекст, позиция)

---

## Fixtures и безопасность

* В репозиторий коммитим **только обезличенные** XML.
* Реальные отчёты для локальной отладки — держать в `tests/FixturesLocal/` (и добавить в `.gitignore`).

Рекомендуется добавить `.gitattributes`, чтобы не ловить «невидимые» правки концов строк (LF/CRLF).

---

## Installation

```bash
composer require masyasmv/aton-statement-parser
```
---

## Development

```bash
composer install
composer test
composer deptrac
composer cs:fix
composer cs:check
composer stan
composer psalm
composer test:coverage
composer coverage:check
```

Mutation testing вынесен в отдельный workflow и отдельную команду:

```bash
composer infection
```

Локально `composer infection` и `composer test:coverage` требуют coverage driver (`pcov` или `xdebug`).
В CI они запускаются через `setup-php` с `pcov`.

Текущее состояние локального test coverage:

* строки: `100%`
* методы: `100%`
* классы: `100%`

## Documentation

Подробная карта библиотеки, слоёв, канонических секций, DTO API и точек расширения:

- [docs/LibraryGuide.md](docs/LibraryGuide.md)

## Versioning

Проект следует Semantic Versioning.

- `MAJOR` для несовместимых изменений API
- `MINOR` для обратно совместимого функционала
- `PATCH` для обратно совместимых исправлений

Формат релизного тега:

```bash
vX.Y.Z
```

## Releases

- `CHANGELOG.md` ведётся в репозитории и должен обновляться перед релизом.
- GitHub Release создаётся автоматически при push тега формата `v*.*.*`.
- Для выпуска релиза достаточно создать и отправить тег:

```bash
git tag v0.1.1
git push origin v0.1.1
```

---

## License

MIT. См. файл [LICENSE](LICENSE).
