<?php

namespace App\Tests\Integration;

use App\DTO\Operation;
use App\Enum\ClientType;
use App\Enum\OperationType;
use App\Service\Calculator\CommissionCalculatorFactory;
use App\Service\Config\CalculatorConfigProvider;
use App\Service\ExchangeRate\CurrencyExchangeRateProvider;
use App\Service\Math;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ConsecutiveOperationsTest extends TestCase
{
    private CommissionCalculatorFactory $calculatorFactory;

    public function testCalculateConsecutiveTransactions()
    {
        $inputData = array_map('str_getcsv', file(__DIR__.'/input.csv'));
        $expectedData = array_map('str_getcsv', file(__DIR__.'/expected.csv'));

        foreach ($inputData as $key => $row) {
            [$date, $clientId, $clientType, $operationType, $amount, $currency] = $row;
            $currency = strtolower($currency);

            $clientType = ClientType::tryFrom(strtolower($clientType));
            $operationType = OperationType::tryFrom(strtolower($operationType));

            $calculator = $this->calculatorFactory->create($operationType, $clientType);

            $commission = $calculator->calculate(new Operation(
                clientId: $clientId,
                clientType: $clientType,
                operationType: $operationType,
                date: $date,
                amount: $amount,
                currency: $currency,
            ));

            $this->assertEquals($expectedData[$key][0], $commission);
        }
    }

    /**
     * @throws \Exception
     */
    protected function setUp(): void
    {
        $configProvider = new CalculatorConfigProvider(
            currencyConfig: ['default' => 'eur', 'decimal_places' => ['eur' => 2, 'jpy' => 0]],
            depositCommissionConfig: ['private' => '0.03', 'business' => '0.03'],
            withdrawCommissionConfig: [
                'private' => [
                    'percentage' => '0.3',
                    'free_per_week' => ['max_amount' => '1000.00', 'max_transactions' => 3],
                ],
                'business' => ['percentage' => '0.5'],
            ]
        );

        $currencyExchangeCache = $this->createMock(CacheInterface::class);
        $currencyExchangeCache->method('get')->willReturn(['eur' => 1, 'jpy' => 129.53, 'usd' => 1.1497]);

        $currencyExchangeRateProvider = new CurrencyExchangeRateProvider(
            math: new Math(),
            httpClient: $this->createMock(HttpClientInterface::class),
            configProvider: $configProvider,
            cache: $currencyExchangeCache
        );

        $this->calculatorFactory = new CommissionCalculatorFactory(
            configProvider: $configProvider,
            currencyExchangeRateProvider: $currencyExchangeRateProvider,
            math: new Math(),
            carbonLocale: 'en_GB',
            cache: new ArrayAdapter(),
        );
    }
}
