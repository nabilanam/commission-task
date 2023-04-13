<?php

namespace App\Service\ExchangeRate;

use App\Exceptions\FailedToConvertCurrencyException;
use App\Service\Config\ICalculatorConfigProvider;
use App\Service\Math;
use Psr\Cache\CacheItemInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

readonly class CurrencyExchangeRateProvider implements ICurrencyExchangeRateProvider
{
    public function __construct(
        private Math $math,
        private HttpClientInterface $httpClient,
        private ICalculatorConfigProvider $configProvider,
        private CacheInterface $cache,
    ) {
    }

    /**
     * @throws FailedToConvertCurrencyException
     */
    public function convertToBaseCurrency(string $amount, string $fromCurrency): string
    {
        $fromCurrency = strtolower($fromCurrency);

        $currencyDecimalPlaces = $this->configProvider->getDecimalPlacesForCurrency($fromCurrency);

        if ($this->math->compare($amount, '0', $currencyDecimalPlaces) < 0) {
            throw new \InvalidArgumentException('Amount must be greater than zero');
        }

        $exchangeRates = $this->getExchangeRates();

        if (empty($exchangeRates[$fromCurrency])) {
            throw new FailedToConvertCurrencyException();
        }

        $defaultDecimalPlaces = $this->configProvider->getDecimalPlacesForCurrency($this->configProvider->getDefaultCurrency());

        return $this->math->roundUp(bcdiv($amount, $exchangeRates[$fromCurrency], bcadd($defaultDecimalPlaces, '1')), $defaultDecimalPlaces);
    }

    public function getExchangeRates(): array
    {
        try {
            return $this->cache->get('exchange_rates', function (CacheItemInterface $cacheItem) {
                $date = new \DateTime('+1 day');
                $date->setTime(0, 0, 0);
                $cacheItem->expiresAt($date);

                return $this->fetchExchangeRates();
            });
        } catch (\Psr\Cache\InvalidArgumentException $e) {
            return [];
        }
    }

    /**
     * @throws \Exception
     */
    private function fetchExchangeRates(): array
    {
        try {
            $response = $this->httpClient->request('GET', 'https://developers.paysera.com/tasks/api/currency-exchange-rates');
            $data = $response->toArray();

            return empty($data['rates']) ? [] : array_change_key_case($data['rates']);
        } catch (ClientExceptionInterface|DecodingExceptionInterface|RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e) {
            throw new \Exception('Failed to get exchange rates');
        }
    }

    /**
     * @throws FailedToConvertCurrencyException
     */
    public function convertFromBaseCurrency(string $amount, string $toCurrency): string
    {
        $toCurrency = strtolower($toCurrency);
        $defaultDecimalPlaces = $this->configProvider->getDecimalPlacesForCurrency($this->configProvider->getDefaultCurrency());

        if ($this->math->compare($amount, '0', $defaultDecimalPlaces) < 0) {
            throw new \InvalidArgumentException('Amount must be greater than zero');
        }

        $exchangeRates = $this->getExchangeRates();

        if (empty($exchangeRates[$toCurrency])) {
            throw new FailedToConvertCurrencyException();
        }

        $currencyDecimalPlaces = $this->configProvider->getDecimalPlacesForCurrency($toCurrency);

        return $this->math->roundUp(bcmul($amount, $exchangeRates[$toCurrency], bcadd($defaultDecimalPlaces, '1')), $currencyDecimalPlaces);
    }
}
