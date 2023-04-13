<?php

namespace App\Service\Calculator\Withdraw;

use App\DTO\Operation;
use App\Service\Calculator\ICommissionCalculator;
use App\Service\Config\ICalculatorConfigProvider;
use App\Service\Math;

readonly class BusinessWithdrawCommissionCalculator implements ICommissionCalculator
{
    public function __construct(
        private ICalculatorConfigProvider $configProvider,
        private Math $math,
    ) {
    }

    public function calculate(Operation $operation): string
    {
        $decimalPlaces = $this->configProvider->getDecimalPlacesForCurrency($operation->getCurrency());
        $commissionConfig = $this->configProvider->getWithdrawCommissionForClientType($operation->getClientType());
        $calculationPrecision = $this->math->add($decimalPlaces, '1', 0);

        $commission = $this->math->divide(
            $this->math->multiply($operation->getAmount(), $commissionConfig['percentage'], $calculationPrecision),
            '100',
            $calculationPrecision
        );

        return sprintf("%.{$decimalPlaces}f", $this->math->roundUp($commission, $decimalPlaces));
    }
}
