<?php

namespace App\Service\Calculator;

use App\DTO\Operation;

interface ICommissionCalculator
{
    public function calculate(Operation $operation): string;
}
