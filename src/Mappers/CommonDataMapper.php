<?php

declare(strict_types=1);

namespace MasyaSmv\AtonStatementParser\Mappers;

use MasyaSmv\AtonStatementParser\Contracts\Mappers\CommonDataMapperInterface;
use MasyaSmv\AtonStatementParser\Dto\CommonData;
use MasyaSmv\AtonStatementParser\Exceptions\DtoMappingException;
use MasyaSmv\AtonStatementParser\Report\Row;

final class CommonDataMapper implements CommonDataMapperInterface
{
    public function map(Row $row): CommonData
    {
        $cpId = $row->getString('CPID') ?? $row->getString('CpID');

        if ($cpId === null || $cpId === '') {
            throw DtoMappingException::missingRequiredField(CommonData::class, 'CPID', $row);
        }

        return new CommonData(
            $cpId,
            $row->getDate('BegDate'),
            $row->getDate('EndDate'),
            $row->getDate('MakeDate'),
            $row->getString('ContractNum'),
            $row->getDate('ContractDate'),
            $row->getBool('WithSubAccounts'),
            $row->getBool('IsFilial'),
            $row->getString('CompanyName')
        );
    }
}
