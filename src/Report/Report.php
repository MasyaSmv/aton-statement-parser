<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Report;

use MasyaSmv\AtonStatementParser\Collections\OperIdCollection;
use MasyaSmv\AtonStatementParser\Collections\RowCollection;
use MasyaSmv\AtonStatementParser\Contracts\ReportInterface;
use MasyaSmv\AtonStatementParser\Exceptions\ParseException;

final class Report implements ReportInterface
{
    /** @var array<string, Section> */
    private array $sections = [];

    private function __construct()
    {
    }

    /**
     * @param array<string, list<Row>> $rowsBySection
     */
    public static function fromRowsBySection(array $rowsBySection): self
    {
        $self = new self();

        foreach ($rowsBySection as $sectionName => $rows) {
            if ($rows === []) {
                continue;
            }

            $self->sections[$sectionName] = new Section($sectionName, new RowCollection($rows));
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

    public function operIds(): OperIdCollection
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

        return new OperIdCollection(array_values(array_unique($ids)));
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
