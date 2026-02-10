<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Report;

use DOMElement;
use DOMXPath;
use MasyaSmv\AtonStatementParser\Contracts\ReportInterface;
use MasyaSmv\AtonStatementParser\Exceptions\ParseException;

final class Report implements ReportInterface
{
    /** @var array<string, Section> */
    private array $sections = [];

    private function __construct()
    {
    }

    public static function fromXPath(DOMXPath $xpath): self
    {
        $self = new self();

        // Берём все дочерние секции внутри BIS:BISPeriod (кроме CommonData отдельно обработаем позже)
        $periodNodes = $xpath->query('/BIS:BISPeriod');

        if ($periodNodes === false || $periodNodes->length === 0) {
            throw new ParseException('Root node BIS:BISPeriod not found.');
        }

        /** @var DOMElement $period */
        $period = $periodNodes->item(0);
        $rootNs = $period->namespaceURI; // может быть null в теории

        // Каждая секция — это BIS:* элемент (BIS:Trades, BIS:MoneyInOut, …)
        foreach ($period->childNodes as $child) {
            if (!$child instanceof DOMElement) {
                continue;
            }

            $localName = $child->localName; // например CommonData, Trades, MoneyInOut

            if ($localName === null || $localName === '') {
                continue;
            }

            $ns = $child->namespaceURI ?? $rootNs;

            if ($ns === null) {
                // это уже странный документ: ожидаем BIS namespace
                throw new ParseException('Namespace URI is missing for section: ' . $localName);
            }

            // Собираем строки BIS:Row внутри секции
            $rows = [];

            foreach ($child->getElementsByTagNameNS($ns, 'Row') as $rowEl) {
                if (!$rowEl instanceof DOMElement) {
                    continue;
                }

                $attrs = [];

                foreach ($rowEl->attributes as $attr) {
                    $key = $attr->localName ?? $attr->name;
                    $attrs[$key] = $attr->value;
                }

                $rows[] = new Row($localName, $attrs);
            }

            // Если секция без Row — тоже сохраняем (иногда такие бывают), но пока можно пропустить.
            if ($rows !== []) {
                $self->sections[$localName] = new Section($localName, $rows);
            }
        }

        return $self;
    }

    public function hasSection(string $name): bool
    {
        return isset($this->sections[$name]);
    }

    public function section(string $name): Section
    {
        if (!isset($this->sections[$name])) {
            throw new ParseException('Section not found: ' . $name);
        }

        return $this->sections[$name];
    }

    /**
     * Возвращает список всех OperID из всех секций, где он присутствует.
     *
     * @return array<int, string>
     */
    public function operIds(): array
    {
        $ids = [];

        foreach ($this->sections as $section) {
            foreach ($section->rows() as $row) {
                $id = $row->getString('OperID');

                if ($id !== null && $id !== '') {
                    $ids[] = $id;
                }
            }
        }

        // Уникализируем, сохраняя порядок
        return array_values(array_unique($ids));
    }

    public function findOperId(string $operId): ?Row
    {
        foreach ($this->sections as $section) {
            foreach ($section->rows() as $row) {
                if ($row->getString('OperID') === $operId) {
                    return $row;
                }
            }
        }

        return null;
    }
}
