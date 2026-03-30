<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Mappers;

use MasyaSmv\AtonStatementParser\Contracts\Mappers\CorporateActionMapperInterface;
use MasyaSmv\AtonStatementParser\Dto\CorporateAction;
use MasyaSmv\AtonStatementParser\Exceptions\DtoMappingException;
use MasyaSmv\AtonStatementParser\Report\Row;

final class CorporateActionMapper implements CorporateActionMapperInterface
{
    public function map(Row $row): CorporateAction
    {
        $operId = $row->getString('OperID');

        if ($operId === null || $operId === '') {
            throw DtoMappingException::missingRequiredField(CorporateAction::class, 'OperID', $row);
        }

        return new CorporateAction(
            $operId,
            $row->section(),
            $row->sourceName(),
            $row->getString('AssetName'),
            $row->getDecimalString('Quantity'),
            $row->getDecimalString('Nominal'),
            $row->getString('NominalCurr'),
            $row->getDecimalString('PayingSum'),
            $row->getDecimalString('PayingSum_RUR'),
            $row->getString('GroupID'),
            $row->getString('Portfolio'),
            $row->getString('IntOperNum'),
            $row->getDate('OperDate'),
            $row->getDate('PaymentDate'),
            $row->getDate('SettlementDate'),
            $row->getDate('ExSettlementDate'),
            $row->getDate('OperDateSort'),
            $row->getDate('OperTimeSort')
        );
    }
}
