<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Mappers;

use MasyaSmv\AtonStatementParser\Contracts\Mappers\StockPayingOffMapperInterface;
use MasyaSmv\AtonStatementParser\Dto\StockPayingOff;
use MasyaSmv\AtonStatementParser\Exceptions\DtoMappingException;
use MasyaSmv\AtonStatementParser\Report\Row;

final class StockPayingOffMapper implements StockPayingOffMapperInterface
{
    public function map(Row $row): StockPayingOff
    {
        $operId = $row->getString('OperID');

        if ($operId === null || $operId === '') {
            throw DtoMappingException::missingRequiredField(StockPayingOff::class, 'OperID', $row);
        }

        return new StockPayingOff(
            $operId,
            $row->section(),
            $row->sourceName(),
            $row->getString('AssetName'),
            $row->getDecimalString('Quantity'),
            $row->getDecimalString('Nominal'),
            $row->getString('NominalCurr'),
            $row->getDecimalString('PayingSum'),
            $row->getDecimalString('PayingSum_RUR'),
            $row->getString('PaymentCurr'),
            $row->getString('IntOperNum'),
            $row->getString('IntOperType'),
            $row->getString('GroupID'),
            $row->getString('Portfolio'),
            $row->getDate('OperDate'),
            $row->getDate('PaymentDate'),
            $row->getDate('SettlementDate'),
            $row->getDate('ExSettlementDate'),
            $row->getDate('OperDateSort'),
            $row->getDate('OperTimeSort')
        );
    }
}
