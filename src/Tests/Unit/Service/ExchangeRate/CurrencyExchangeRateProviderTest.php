<?php

namespace App\Tests\Unit\Service\ExchangeRate;

use App\Exceptions\FailedToConvertCurrencyException;
use App\Service\Config\CalculatorConfigProvider;
use App\Service\ExchangeRate\CurrencyExchangeRateProvider;
use App\Service\ExchangeRate\ICurrencyExchangeRateProvider;
use App\Service\Math;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CurrencyExchangeRateProviderTest extends TestCase
{
    private ICurrencyExchangeRateProvider $currencyExchangeRateProvider;

    public function testConvertToBaseCurrencyForUSD(): void
    {
        $amount = '100.00';
        $fromCurrency = 'usd';
        $expected = '86.98';

        $actual = $this->currencyExchangeRateProvider->convertToBaseCurrency($amount, $fromCurrency);
        $this->assertEquals($expected, $actual);
    }

    public function testConvertToBaseCurrencyForJPY(): void
    {
        $amount = '30000';
        $fromCurrency = 'jpy';
        $expected = '231.61';

        $actual = $this->currencyExchangeRateProvider->convertToBaseCurrency($amount, $fromCurrency);
        $this->assertEquals($expected, $actual);
    }

    public function testExceptionForConvertToBaseCurrencyWhenNegativeAmount(): void
    {
        $amount = '-10';
        $fromCurrency = 'eur';

        $this->expectException(\InvalidArgumentException::class);
        $this->currencyExchangeRateProvider->convertToBaseCurrency($amount, $fromCurrency);
    }

    public function testExceptionForConvertToBaseCurrencyWhenUnknownCurrency(): void
    {
        $amount = '10';
        $fromCurrency = 'nbl';

        $this->expectException(FailedToConvertCurrencyException::class);
        $this->currencyExchangeRateProvider->convertToBaseCurrency($amount, $fromCurrency);
    }

    public function testConvertFromBaseCurrencyForUSD(): void
    {
        $amount = '100.23';
        $toCurrency = 'usd';
        $expected = '115.24';

        $actual = $this->currencyExchangeRateProvider->convertFromBaseCurrency($amount, $toCurrency);
        $this->assertEquals($expected, $actual);
    }

    public function testConvertFromBaseCurrencyForJPY(): void
    {
        $amount = '100.23';
        $toCurrency = 'jpy';
        $expected = '12983';

        $actual = $this->currencyExchangeRateProvider->convertFromBaseCurrency($amount, $toCurrency);
        $this->assertEquals($expected, $actual);
    }

    public function testExceptionForConvertFromBaseCurrencyWhenNegativeAmount(): void
    {
        $amount = '-10';
        $toCurrency = 'eur';

        $this->expectException(\InvalidArgumentException::class);
        $this->currencyExchangeRateProvider->convertFromBaseCurrency($amount, $toCurrency);
    }

    public function testExceptionForConvertFromBaseCurrencyWhenUnknownCurrency(): void
    {
        $amount = '10';
        $toCurrency = 'nbl';

        $this->expectException(FailedToConvertCurrencyException::class);
        $this->currencyExchangeRateProvider->convertFromBaseCurrency($amount, $toCurrency);
    }

    protected function setUp(): void
    {
        $httpClient = $this->createMock(HttpClientInterface::class);

        $mockCache = $this->createMock(CacheInterface::class);
        $mockCache->method('get')->willReturn(['eur' => 1, 'jpy' => 129.53, 'usd' => 1.1497]);

        $configProvider = new CalculatorConfigProvider(
            currencyConfig: ['default' => 'eur', 'decimal_places' => ['eur' => 2, 'jpy' => 0]],
            depositCommissionConfig: ['private' => 0.03, 'business' => 0.03],
            withdrawCommissionConfig: [
                'private' => [
                    'percentage' => '0.3',
                    'free_per_week' => ['max_amount' => '1000.00', 'max_transactions' => 3],
                ],
                'business' => ['percentage' => '0.5'],
            ]
        );

        $this->currencyExchangeRateProvider = new CurrencyExchangeRateProvider(
            math: new Math(),
            httpClient: $httpClient,
            configProvider: $configProvider,
            cache: $mockCache
        );
    }
}
