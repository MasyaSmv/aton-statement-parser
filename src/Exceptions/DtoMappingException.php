<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Exceptions;

use MasyaSmv\AtonStatementParser\Report\Row;
use RuntimeException;

final class DtoMappingException extends RuntimeException
{
    public static function missingRequiredField(string $dtoClass, string $field, Row $row): self
    {
        return new self(sprintf(
            'Cannot map %s: required field "%s" is missing. Section: %s. Source: %s. Record type: %s.',
            $dtoClass,
            $field,
            $row->section(),
            $row->sourceName(),
            $row->recordType()
        ));
    }
}
