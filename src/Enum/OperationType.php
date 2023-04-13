<?php

namespace App\Enum;

enum OperationType: string
{
    case Deposit = 'deposit';
    case Withdraw = 'withdraw';
}
