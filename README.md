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
* работать с атрибутами строк через удобные геттеры (строки/даты/числа) без «магических» индексов массива.

> Цель: минимальная боль при работе с реальными отчётами старого и нового форматов, сохраняя единый API.

---

## Быстрый старт

```php
use MasyaSmv\AtonStatementParser\AtonStatementParser;

$report = AtonStatementParser::fromFile('/path/to/report.xml');

// Доступ к секциям (универсально)
$trades = $report->section('Trades')->rows();      // RowCollection
$money  = $report->section('MoneyInOut')->rows();  // RowCollection

// Поиск по OperID
$row = $report->findOperId('567890123');           // Row|null

// Список всех OperID (для сверки)
$ids = $report->operIds();                         // OperIdCollection

// Удобные геттеры атрибутов
$type = $report->section('Trades')->rows()[0]->getString('TradeType');
$date = $report->section('Trades')->rows()[0]->getDate('OperDateSort'); // DateTimeImmutable|null
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

---

## Архитектура (как планируем)

### Основные сущности

* **Report** — весь отчёт, доступ к секциям и общим данным.
* **Section** — один блок внутри отчёта (например `Trades`, `MoneyInOut`).
* **Row** — одна строка секции в каноническом виде. Хранит имя секции, исходный тип записи, immutable-атрибуты и типовые геттеры.
* **RowCollection / OperIdCollection / AttributeBag** — immutable коллекции и value objects для публичной модели.

### Уровни удобства

1. **Базовый (универсальный)**: `Report/Section/Row` — работает для любых секций без генерации десятков DTO.
2. **Продвинутый (DTO для популярных секций)**: `TradeDto`, `MoneyInOutDto` и т.п. — добавляются постепенно, когда станет понятно, что реально часто используется.

---

## Структура проекта (план)

```text
src/
  AtonStatementParser.php          // fromFile/fromString
  Collections/
    RowCollection.php              // immutable коллекция строк
    OperIdCollection.php           // immutable коллекция OperID
  Parsing/
    ReportParserResolver.php       // выбор парсера по формату документа
    LegacyBisReportParser.php      // старый BIS-формат
    ModernXmlReportParser.php      // новый root/source формат
    SectionNameResolver.php        // канонизация имён секций
  Report/
    Report.php                     // секции отчёта
    Section.php                    // имя секции + RowCollection
    Row.php                        // строка секции + геттеры
    AttributeBag.php               // immutable-атрибуты строки
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
* [ ] Довести канонизацию полей между old/new форматами

### 🚧 Этап C — DTO (точечно, только нужное)

* [ ] `Report->trades(): array<TradeDto>`
* [ ] `Report->moneyInOut(): array<MoneyInOutDto>`
* [ ] Мапперы секций → DTO
* [ ] Тесты DTO-маппинга

### 🚧 Этап D — удобства для больших отчётов

* [ ] Фильтры/поиск: `section()->where(fn(Row $r) => ...)`
* [ ] Индексация по `OperID` (лениво или при первом вызове)
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
composer cs:fix
composer cs:check
composer stan
```

---

## License

MIT. См. файл [LICENSE](LICENSE).
