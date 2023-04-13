<?php

namespace App\DTO;

use App\Enum\ClientType;
use App\Enum\OperationType;

readonly class Operation
{
    public function __construct(
        private string $clientId,
        private ClientType $clientType,
        private OperationType $operationType,
        private string $date,
        private string $amount,
        private string $currency,
    ) {
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientType(): ClientType
    {
        return $this->clientType;
    }

    public function getOperationType(): OperationType
    {
        return $this->operationType;
    }

    public function getDate(): string
    {
        return $this->date;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }
}
