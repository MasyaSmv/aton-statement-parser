<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Mappers;

use MasyaSmv\AtonStatementParser\Contracts\Mappers\TradeMapperInterface;
use MasyaSmv\AtonStatementParser\Dto\Trade;
use MasyaSmv\AtonStatementParser\Exceptions\DtoMappingException;
use MasyaSmv\AtonStatementParser\Report\Row;

final class TradeMapper implements TradeMapperInterface
{
    public function map(Row $row): Trade
    {
        $operId = $row->getString('OperID');

        if ($operId === null || $operId === '') {
            throw DtoMappingException::missingRequiredField(Trade::class, 'OperID', $row);
        }

        return new Trade(
            $operId,
            $row->section(),
            $row->sourceName(),
            $row->getBool('isComplete'),
            $row->getString('TradeType'),
            $row->getString('AssetName'),
            $row->getDecimalString('Quantity'),
            $row->getDecimalString('Price'),
            $row->getString('PriceCurr'),
            $row->getDecimalString('Payment'),
            $row->getString('PaymentCurr'),
            $row->getDate('PaymentDate'),
            $row->getDate('SettlementDate'),
            $row->getDate('OperDateSort'),
            $row->getDate('OperTimeSort')
        );
    }
}
