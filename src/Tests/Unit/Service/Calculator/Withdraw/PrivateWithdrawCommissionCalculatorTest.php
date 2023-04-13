<?php

namespace App\Tests\Unit\Service\Calculator\Withdraw;

use App\DTO\Operation;
use App\Enum\ClientType;
use App\Enum\OperationType;
use App\Service\Calculator\Withdraw\PrivateWithdrawCommissionCalculator;
use App\Service\Config\CalculatorConfigProvider;
use App\Service\ExchangeRate\CurrencyExchangeRateProvider;
use App\Service\ExchangeRate\ICurrencyExchangeRateProvider;
use App\Service\Math;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\PhpArrayAdapter;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PrivateWithdrawCommissionCalculatorTest extends TestCase
{
    private CacheInterface $cache;
    private PrivateWithdrawCommissionCalculator $calculator;

    private ICurrencyExchangeRateProvider $currencyExchangeRateProvider;

    public function testCalculateWhenZeroWeeklyTransactionsExist(): void
    {
        $actual = $this->calculator->calculate(new Operation(
            clientId: '1',
            clientType: ClientType::Private,
            operationType: OperationType::Withdraw,
            date: '2021-01-01',
            amount: '1000.00',
            currency: 'eur'
        ));

        $this->assertEquals('0.00', $actual);
    }

    public function testCalculateWhenZeroWeeklyTransactionsExistButAmountExceedsMaxFreeAmount(): void
    {
        $actual = $this->calculator->calculate(new Operation(
            clientId: '1',
            clientType: ClientType::Private,
            operationType: OperationType::Withdraw,
            date: '2021-01-01',
            amount: '1200.00',
            currency: 'eur'
        ));

        $this->assertEquals('0.60', $actual);
    }

    public function testCalculateWhenTwoWeeklyTransactionsExist(): void
    {
        foreach (range(1, 2) as $_) {
            $this->calculator->calculate(new Operation(
                clientId: '1',
                clientType: ClientType::Private,
                operationType: OperationType::Withdraw,
                date: '2021-01-01',
                amount: '200.00',
                currency: 'eur',
            ));
        }

        $actual = $this->calculator->calculate(new Operation(
            clientId: '1',
            clientType: ClientType::Private,
            operationType: OperationType::Withdraw,
            date: '2021-01-01',
            amount: '200.00',
            currency: 'eur'
        ));

        $this->assertEquals('0.00', $actual);
    }

    public function testCalculateWhenWeeklyThreeTransactionsExist(): void
    {
        foreach (range(1, 3) as $_) {
            $this->calculator->calculate(new Operation(
                clientId: '1',
                clientType: ClientType::Private,
                operationType: OperationType::Withdraw,
                date: '2021-01-01',
                amount: '200.00',
                currency: 'eur',
            ));
        }

        $actual = $this->calculator->calculate(new Operation(
            clientId: '1',
            clientType: ClientType::Private,
            operationType: OperationType::Withdraw,
            date: '2021-01-01',
            amount: '200.00',
            currency: 'eur'
        ));

        $this->assertEquals('0.60', $actual);
    }

    public function testCalculateWhenWeeklyThreeTransactionsExistAndOneIsInDifferentWeek(): void
    {
        foreach (range(1, 3) as $_) {
            $this->calculator->calculate(new Operation(
                clientId: '1',
                clientType: ClientType::Private,
                operationType: OperationType::Withdraw,
                date: '2021-01-01',
                amount: '200.00',
                currency: 'eur',
            ));
        }

        $actual = $this->calculator->calculate(new Operation(
            clientId: '1',
            clientType: ClientType::Private,
            operationType: OperationType::Withdraw,
            date: '2021-01-08',
            amount: '200.00',
            currency: 'eur'
        ));

        $this->assertEquals('0.00', $actual);
    }

    public function testCalculateWhenWeeklyThreeTransactionsExistAndOneIsInSameWeekButDifferentYear(): void
    {
        foreach (range(1, 3) as $_) {
            $this->calculator->calculate(new Operation(
                clientId: '1',
                clientType: ClientType::Private,
                operationType: OperationType::Withdraw,
                date: '2021-01-03',
                amount: '200.00',
                currency: 'eur',
            ));
        }

        $actual = $this->calculator->calculate(new Operation(
            clientId: '1',
            clientType: ClientType::Private,
            operationType: OperationType::Withdraw,
            date: '2022-01-03',
            amount: '200.00',
            currency: 'eur'
        ));

        $this->assertEquals('0.00', $actual);
    }

    public function testCalculateWhenWeeklyMaxFreeAmountIsWithdrawn(): void
    {
        foreach (range(1, 3) as $_) {
            $this->calculator->calculate(new Operation(
                clientId: '1',
                clientType: ClientType::Private,
                operationType: OperationType::Withdraw,
                date: '2021-01-01',
                amount: '1000.00',
                currency: 'eur',
            ));
        }

        $actual = $this->calculator->calculate(new Operation(
            clientId: '1',
            clientType: ClientType::Private,
            operationType: OperationType::Withdraw,
            date: '2021-01-01',
            amount: '500.00',
            currency: 'eur'
        ));

        $this->assertEquals('1.50', $actual);
    }

    public function testCalculateConsecutiveTransactions()
    {
        $clientData = [
            '4' => [
                ['date' => '2014-12-31', 'amount' => '1200.00', 'currency' => 'EUR', 'commission' => '0.60'],
                ['date' => '2015-01-01', 'amount' => '1000.00', 'currency' => 'EUR', 'commission' => '3.00'],
                ['date' => '2016-01-05', 'amount' => '1000.00', 'currency' => 'EUR', 'commission' => '0.00'],
            ],
            '1' => [
                ['date' => '2016-01-06', 'amount' => '30000', 'currency' => 'JPY', 'commission' => '0'],
                ['date' => '2016-01-07', 'amount' => '1000.00', 'currency' => 'EUR', 'commission' => '0.70'],
                ['date' => '2016-01-07', 'amount' => '100.00', 'currency' => 'USD', 'commission' => '0.30'],
                ['date' => '2016-01-10', 'amount' => '100.00', 'currency' => 'EUR', 'commission' => '0.30'],
                ['date' => '2016-02-15', 'amount' => '300.00', 'currency' => 'EUR', 'commission' => '0.00'],
            ],
            '5' => [
                ['date' => '2016-02-19', 'amount' => '3000000', 'currency' => 'JPY', 'commission' => '8612'],
            ],
        ];

        foreach ($clientData as $clientId => $items) {
            foreach ($items as $item) {
                $commission = $this->calculator->calculate(new Operation(
                    clientId: $clientId,
                    clientType: ClientType::Private,
                    operationType: OperationType::Withdraw,
                    date: $item['date'],
                    amount: $item['amount'],
                    currency: $item['currency']
                ));

                $this->assertEquals($item['commission'], $commission);
            }
        }
    }

    /**
     * @throws \Exception
     */
    protected function setUp(): void
    {
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

        $currencyExchangeCache = $this->createMock(CacheInterface::class);
        $currencyExchangeCache->method('get')->willReturn(['eur' => 1, 'jpy' => 129.53, 'usd' => 1.1497]);

        $this->currencyExchangeRateProvider = new CurrencyExchangeRateProvider(
            math: new Math(),
            httpClient: $this->createMock(HttpClientInterface::class),
            configProvider: $configProvider,
            cache: $currencyExchangeCache
        );

        $this->cache = $this->createMock(PhpArrayAdapter::class);

        $this->calculator = new PrivateWithdrawCommissionCalculator(
            configProvider: $configProvider,
            currencyExchangeRateProvider: $this->currencyExchangeRateProvider,
            math: new Math(),
            cache: new ArrayAdapter(),
            carbonLocale: 'en_GB'
        );
    }
}
