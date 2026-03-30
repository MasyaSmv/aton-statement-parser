<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Mappers;

use MasyaSmv\AtonStatementParser\Contracts\Mappers\MoneyOperationMapperInterface;
use MasyaSmv\AtonStatementParser\Dto\MoneyOperation;
use MasyaSmv\AtonStatementParser\Exceptions\DtoMappingException;
use MasyaSmv\AtonStatementParser\Report\Row;

final class MoneyOperationMapper implements MoneyOperationMapperInterface
{
    public function map(Row $row): MoneyOperation
    {
        $operId = $row->getString('OperID');

        if ($operId === null || $operId === '') {
            throw DtoMappingException::missingRequiredField(MoneyOperation::class, 'OperID', $row);
        }

        return new MoneyOperation(
            $operId,
            $row->section(),
            $row->sourceName(),
            $row->getString('OperType'),
            $row->getDecimalString('Quantity'),
            $row->getDecimalString('Quantity_RUR'),
            $row->getString('Currency'),
            $row->getString('Comment'),
            $row->getDate('OperDate'),
            $row->getDate('PaymentDate'),
            $row->getDate('OperDateSort')
        );
    }
}
