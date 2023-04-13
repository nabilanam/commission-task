<?php

namespace App\Tests\Unit\Service\Calculator\Withdraw;

use App\DTO\Operation;
use App\Enum\ClientType;
use App\Enum\OperationType;
use App\Service\Calculator\Withdraw\BusinessWithdrawCommissionCalculator;
use App\Service\Config\CalculatorConfigProvider;
use App\Service\Math;
use PHPUnit\Framework\TestCase;

class BusinessWithdrawCommissionCalculatorTest extends TestCase
{
    private BusinessWithdrawCommissionCalculator $calculator;

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
            'withdraw 300 EUR' => [
                'operation' => new Operation(
                    clientId: '1',
                    clientType: ClientType::Business,
                    operationType: OperationType::Deposit,
                    date: '2021-01-01',
                    amount: '300.00',
                    currency: 'EUR'
                ),
                'expected' => '1.50',
            ],
            'withdraw 10000 EUR' => [
                'operation' => new Operation(
                    clientId: '1',
                    clientType: ClientType::Business,
                    operationType: OperationType::Deposit,
                    date: '2021-01-01',
                    amount: '10000.00',
                    currency: 'EUR'
                ),
                'expected' => '50.00',
            ],
            'withdraw 10000 JPY' => [
                'operation' => new Operation(
                    clientId: '1',
                    clientType: ClientType::Business,
                    operationType: OperationType::Deposit,
                    date: '2021-01-01',
                    amount: '10000',
                    currency: 'JPY'
                ),
                'expected' => '50',
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

        $this->calculator = new BusinessWithdrawCommissionCalculator(
            configProvider: $configProvider,
            math: new Math()
        );
    }
}
