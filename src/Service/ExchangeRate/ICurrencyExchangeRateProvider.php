<?php

namespace App\Service\ExchangeRate;

interface ICurrencyExchangeRateProvider
{
    /**
     * Should return an array of exchange rates in the following format:
     * [
     *   'usd' => 1.1497,
     *   'jpy' => 129.53,
     *   ...
     * ].
     */
    public function getExchangeRates(): array;

    public function convertToBaseCurrency(string $amount, string $fromCurrency): string;

    public function convertFromBaseCurrency(string $amount, string $toCurrency): string;
}
