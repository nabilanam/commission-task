<?php

namespace App\Tests\Unit\Service\Calculator\Deposit;

use App\DTO\Operation;
use App\Enum\ClientType;
use App\Enum\OperationType;
use App\Service\Calculator\Deposit\DepositCommissionCalculator;
use App\Service\Config\CalculatorConfigProvider;
use App\Service\Math;
use PHPUnit\Framework\TestCase;

class DepositCommissionCalculatorTest extends TestCase
{
    private DepositCommissionCalculator $calculator;

    /**
     * @dataProvider calculationDataProvider
     */
    public function testCalculate(Operation $operation, string $expected): void
    {
        $actual = $this->calculator->calculate($operation);

        $this->assertEquals($expected, $actual);
    }

    public function calculationDataProvider(): array
    {
        return [
            'deposit 200 EUR' => [
                'operation' => new Operation(
                    clientId: '1',
                    clientType: ClientType::Private,
                    operationType: OperationType::Deposit,
                    date: '2021-01-01',
                    amount: '200.00',
                    currency: 'EUR'
                ),
                'expected' => '0.06',
            ],
            'deposit 200 USD' => [
                'operation' => new Operation(
                    clientId: '1',
                    clientType: ClientType::Private,
                    operationType: OperationType::Deposit,
                    date: '2021-01-01',
                    amount: '200.00',
                    currency: 'USD'
                ),
                'expected' => '0.06',
            ],
            'deposit 200 JPY' => [
                'operation' => new Operation(
                    clientId: '1',
                    clientType: ClientType::Private,
                    operationType: OperationType::Deposit,
                    date: '2021-01-01',
                    amount: '200',
                    currency: 'JPY'
                ),
                'expected' => '0',
            ],
            'deposit 2000 JPY' => [
                'operation' => new Operation(
                    clientId: '1',
                    clientType: ClientType::Private,
                    operationType: OperationType::Deposit,
                    date: '2021-01-01',
                    amount: '2000',
                    currency: 'JPY'
                ),
                'expected' => '1',
            ],
        ];
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

        $this->calculator = new DepositCommissionCalculator(
            configProvider: $configProvider,
            math: new Math()
        );
    }
}
