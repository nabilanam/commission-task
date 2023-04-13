<?php

namespace App\Service\Calculator\Withdraw;

use App\DTO\Operation;
use App\DTO\Transaction;
use App\Service\Calculator\ICommissionCalculator;
use App\Service\Config\ICalculatorConfigProvider;
use App\Service\ExchangeRate\ICurrencyExchangeRateProvider;
use App\Service\Math;
use Carbon\Carbon;
use Symfony\Contracts\Cache\CacheInterface;

class PrivateWithdrawCommissionCalculator implements ICommissionCalculator
{
    public function __construct(
        private ICalculatorConfigProvider $configProvider,
        private ICurrencyExchangeRateProvider $currencyExchangeRateProvider,
        private Math $math,
        private CacheInterface $cache,
        private string $carbonLocale,
    ) {
    }

    public function calculate(Operation $operation): string
    {
        $decimalPlaces = $this->configProvider->getDecimalPlacesForCurrency($operation->getCurrency());
        $calculationPrecision = $this->math->add($decimalPlaces, '1', 0);

        $commissionConfig = $this->configProvider->getWithdrawCommissionForClientType($operation->getClientType());
        $eligibleFreeAmount = $this->getEligibleFreeAmount($operation, $commissionConfig);
        $applicableAmount = $this->getCommissionApplicableAmount($operation, $eligibleFreeAmount, $calculationPrecision);
        $commission = $this->calculateCommission($applicableAmount, $commissionConfig, $decimalPlaces, $calculationPrecision);

        $this->saveTransaction($operation, $commission);

        return $commission;
    }

    private function getEligibleFreeAmount(Operation $operation, array $commissionConfig): string
    {
        $maxFreeTransactions = $commissionConfig['free_per_week']['max_transactions'];
        $weeklyTransactions = $this->getWeeklyTransactions($operation);
        $calculationPrecision = $this->math->add(
            $this->configProvider->getDecimalPlacesForCurrency($this->configProvider->getDefaultCurrency()),
            '1',
            0
        );

        if (count($weeklyTransactions) >= $maxFreeTransactions) {
            return '0';
        }

        $weeklyTransactionsAmount = array_reduce(
            $weeklyTransactions,
            fn ($carry, $transaction) => $this->math->add($carry, $transaction->getBaseCurrencyAmount(), $calculationPrecision),
            '0'
        );

        $maxFreeAmount = $commissionConfig['free_per_week']['max_amount'];

        if ($this->math->compare($weeklyTransactionsAmount, $maxFreeAmount, $calculationPrecision) > 0) {
            return '0';
        }

        $difference = $this->math->subtract($maxFreeAmount, $weeklyTransactionsAmount, $calculationPrecision);

        return $this->currencyExchangeRateProvider->convertFromBaseCurrency($difference, $operation->getCurrency());
    }

    /**
     * @return array|Transaction[]
     */
    private function getWeeklyTransactions(Operation $operation): array
    {
        $weeklyTransactions = [];
        $allTransactions = $this->getAllTransactions($operation->getClientId());
        $operationDate = Carbon::parse($operation->getDate())->locale($this->carbonLocale);

        foreach ($allTransactions as $transaction) {
            $transactionDate = Carbon::parse($transaction->getDate())->locale($this->carbonLocale);

            if (!$transactionDate->isSameWeek($operationDate)) {
                break;
            }

            $weeklyTransactions[] = $transaction;
        }

        return $weeklyTransactions;
    }

    /**
     * @return array|Transaction[]
     */
    private function getAllTransactions(string $clientId): array
    {
        $cacheItem = $this->cache->getItem($this->getCacheKey($clientId));

        return $cacheItem->get() ?? [];
    }

    private function getCacheKey(string $clientId): string
    {
        return sprintf('private_transactions_%s', $clientId);
    }

    private function getCommissionApplicableAmount(Operation $operation, string $eligibleFreeAmount, string $calculationPrecision): string
    {
        if ($this->math->compare($eligibleFreeAmount, '0', $calculationPrecision) <= 0) {
            return $operation->getAmount();
        }

        return $this->math->compare($eligibleFreeAmount, $operation->getAmount(), $calculationPrecision) >= 0
            ? '0'
            : $this->math->subtract($operation->getAmount(), $eligibleFreeAmount, $calculationPrecision);
    }

    private function calculateCommission(string $amount, array $commissionConfig, string $decimalPlaces, string $calculationPrecision): string
    {
        $commission = $this->math->divide(
            $this->math->multiply($amount, $commissionConfig['percentage'], $calculationPrecision),
            '100',
            $calculationPrecision
        );

        return $this->math->roundUp($commission, $decimalPlaces);
    }

    public function saveTransaction(Operation $operation, string $commission): void
    {
        $cacheItem = $this->cache->getItem($this->getCacheKey($operation->getClientId()));
        $transactions = $cacheItem->get() ?? [];

        array_unshift($transactions, new Transaction(
            clientId: $operation->getClientId(),
            clientType: $operation->getClientType()->value,
            operationType: $operation->getOperationType()->value,
            date: $operation->getDate(),
            amount: $operation->getAmount(),
            currency: $operation->getCurrency(),
            commission: $commission,
            baseCurrencyAmount: $this->currencyExchangeRateProvider->convertToBaseCurrency($operation->getAmount(), $operation->getCurrency()),
        ));

        $cacheItem->set($transactions);
        $this->cache->save($cacheItem);
    }
}
