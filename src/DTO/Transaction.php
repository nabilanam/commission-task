<?php

namespace App\DTO;

readonly class Transaction
{
    public function __construct(
        private string $clientId,
        private string $clientType,
        private string $operationType,
        private string $date,
        private string $amount,
        private string $currency,
        private string $commission,
        private string $baseCurrencyAmount,
    ) {
    }

    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getClientType(): string
    {
        return $this->clientType;
    }

    public function getOperationType(): string
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

    public function getCommission(): string
    {
        return $this->commission;
    }

    public function getBaseCurrencyAmount(): string
    {
        return $this->baseCurrencyAmount;
    }
}
