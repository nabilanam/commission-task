<?php

namespace App\Service\Calculator\Deposit;

use App\DTO\Operation;
use App\Service\Calculator\ICommissionCalculator;
use App\Service\Config\ICalculatorConfigProvider;
use App\Service\Math;

readonly class DepositCommissionCalculator implements ICommissionCalculator
{
    public function __construct(
        private ICalculatorConfigProvider $configProvider,
        private Math $math,
    ) {
    }

    public function calculate(Operation $operation): string
    {
        $decimalPlaces = $this->configProvider->getDecimalPlacesForCurrency($operation->getCurrency());
        $commissionPercentage = $this->configProvider->getDepositCommissionForClientType($operation->getClientType());
        $calculationPrecision = $this->math->add($decimalPlaces, '1', 0);

        $commission = $this->math->divide(
            $this->math->multiply($operation->getAmount(), $commissionPercentage, $calculationPrecision),
            '100',
            $calculationPrecision
        );

        return sprintf("%.{$decimalPlaces}f", $this->math->roundUp($commission, $decimalPlaces));
    }
}
