<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Mappers;

use MasyaSmv\AtonStatementParser\Contracts\Mappers\StockTransferMapperInterface;
use MasyaSmv\AtonStatementParser\Dto\StockTransfer;
use MasyaSmv\AtonStatementParser\Exceptions\DtoMappingException;
use MasyaSmv\AtonStatementParser\Report\Row;

final class StockTransferMapper implements StockTransferMapperInterface
{
    public function map(Row $row): StockTransfer
    {
        $operId = $row->getString('OperID');

        if ($operId === null || $operId === '') {
            throw DtoMappingException::missingRequiredField(StockTransfer::class, 'OperID', $row);
        }

        return new StockTransfer(
            $operId,
            $row->section(),
            $row->sourceName(),
            $row->getString('AssetName'),
            $row->getDecimalString('Quantity'),
            $row->getDecimalString('Price'),
            $row->getString('Portfolio'),
            $row->getString('Comment'),
            $row->getString('IntOperNum'),
            $row->getDate('OperDate'),
            $row->getDate('SettlementDate'),
            $row->getDate('ExSettlementDate'),
            $row->getDate('OperDateSort'),
            $row->getDate('OperTimeSort')
        );
    }
}
