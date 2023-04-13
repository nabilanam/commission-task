<?php

namespace App\Service\Config;

use App\Enum\ClientType;

interface ICalculatorConfigProvider
{
    public function getDefaultCurrency(): string;

    public function getDecimalPlacesForCurrency(string $currency): string;

    public function getDepositCommissionForClientType(ClientType $clientType): string|array;

    public function getWithdrawCommissionForClientType(ClientType $clientType): string|array;
}
