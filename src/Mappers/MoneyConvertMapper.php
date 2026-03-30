<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Mappers;

use MasyaSmv\AtonStatementParser\Contracts\Mappers\MoneyConvertMapperInterface;
use MasyaSmv\AtonStatementParser\Dto\MoneyConvertOperation;
use MasyaSmv\AtonStatementParser\Exceptions\DtoMappingException;
use MasyaSmv\AtonStatementParser\Report\Row;

final class MoneyConvertMapper implements MoneyConvertMapperInterface
{
    public function map(Row $row): MoneyConvertOperation
    {
        $operId = $row->getString('OperID');

        if ($operId === null || $operId === '') {
            throw DtoMappingException::missingRequiredField(MoneyConvertOperation::class, 'OperID', $row);
        }

        return new MoneyConvertOperation(
            $operId,
            $row->section(),
            $row->sourceName(),
            $row->getString('Curr1'),
            $row->getDecimalString('Sum1'),
            $row->getDecimalString('Sum1_RUR'),
            $row->getDate('Date1'),
            $row->getDecimalString('Rate'),
            $row->getString('Curr2'),
            $row->getDecimalString('Sum2'),
            $row->getDecimalString('Sum2_RUR'),
            $row->getDate('Date2'),
            $row->getDate('OperDate')
        );
    }
}
