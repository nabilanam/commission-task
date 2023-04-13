<?php

namespace App\Tests\Unit\Service\Config;

use App\Enum\ClientType;
use App\Service\Config\CalculatorConfigProvider;
use PHPUnit\Framework\TestCase;

class CalculatorConfigProviderTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testNoExceptionWhenConfigIsCorrect(): void
    {
        new CalculatorConfigProvider(
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

        $this->assertTrue(true);
    }

    public function testGetDefaultCurrency(): void
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

        $this->assertEquals('eur', $configProvider->getDefaultCurrency());
    }

    public function testGetDecimalPlacesForCurrency(): void
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

        $this->assertEquals(2, $configProvider->getDecimalPlacesForCurrency('eur'));
        $this->assertEquals(0, $configProvider->getDecimalPlacesForCurrency('jpy'));
    }

    public function testGetDepositCommissionForClientType(): void
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

        $this->assertEquals(0.03, $configProvider->getDepositCommissionForClientType(ClientType::Private));
        $this->assertEquals(0.03, $configProvider->getDepositCommissionForClientType(ClientType::Business));
    }

    public function testGetWithdrawCommissionForClientType(): void
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

        $this->assertEquals([
            'percentage' => '0.3',
            'free_per_week' => ['max_amount' => '1000.00', 'max_transactions' => 3],
        ], $configProvider->getWithdrawCommissionForClientType(ClientType::Private));

        $this->assertEquals(['percentage' => '0.5'], $configProvider->getWithdrawCommissionForClientType(ClientType::Business));
    }

    public function testExceptionWhenDefaultCurrencyIsMissing(): void
    {
        $this->expectExceptionMessage('Default currency is not set');

        new CalculatorConfigProvider(
            currencyConfig: ['decimal_places' => ['eur' => 2, 'jpy' => 0]],
            depositCommissionConfig: ['private' => 0.03, 'business' => 0.03],
            withdrawCommissionConfig: [
                'private' => [
                    'percentage' => '0.3',
                    'free_per_week' => ['max_amount' => '1000.00', 'max_transactions' => 3],
                ],
                'business' => ['percentage' => '0.5'],
            ]
        );
    }

    public function testExceptionWhenDefaultCurrencyDecimalPlacesIsMissing(): void
    {
        $this->expectExceptionMessage('Default currency decimal places is not set');

        new CalculatorConfigProvider(
            currencyConfig: ['default' => 'eur', 'decimal_places' => []],
            depositCommissionConfig: ['private' => 0.03, 'business' => 0.03],
            withdrawCommissionConfig: [
                'private' => [
                    'percentage' => '0.3',
                    'free_per_week' => ['max_amount' => '1000.00', 'max_transactions' => 3],
                ],
                'business' => ['percentage' => '0.5'],
            ]
        );
    }

    public function testExceptionWhenDecimalPlacesAreNotANumberOrLessThanZero(): void
    {
        $this->expectExceptionMessage('Decimal places for currency eur is not a number or less than zero');

        new CalculatorConfigProvider(
            currencyConfig: ['default' => 'eur', 'decimal_places' => ['eur' => -1, 'jpy' => 0]],
            depositCommissionConfig: ['private' => 0.03, 'business' => 0.03],
            withdrawCommissionConfig: [
                'private' => [
                    'percentage' => '0.3',
                    'free_per_week' => ['max_amount' => '1000.00', 'max_transactions' => 3],
                ],
                'business' => ['percentage' => '0.5'],
            ]
        );

        $this->expectExceptionMessage('Decimal places for currency jpy is not a number or less than zero');

        new CalculatorConfigProvider(
            currencyConfig: ['default' => 'eur', 'decimal_places' => ['eur' => 2, 'jpy' => 'not-a-number']],
            depositCommissionConfig: ['private' => 0.03, 'business' => 0.03],
            withdrawCommissionConfig: [
                'private' => [
                    'percentage' => '0.3',
                    'free_per_week' => ['max_amount' => '1000.00', 'max_transactions' => 3],
                ],
                'business' => ['percentage' => '0.5'],
            ]
        );
    }

    public function testExceptionWhenPrivateDepositCommissionIsMissing(): void
    {
        $this->expectExceptionMessage('Private deposit commission is not set');

        new CalculatorConfigProvider(
            currencyConfig: ['default' => 'eur', 'decimal_places' => ['eur' => 2, 'jpy' => 0]],
            depositCommissionConfig: ['business' => 0.03],
            withdrawCommissionConfig: [
                'private' => [
                    'percentage' => '0.3',
                    'free_per_week' => ['max_amount' => '1000.00', 'max_transactions' => 3],
                ],
                'business' => ['percentage' => '0.5'],
            ]
        );
    }

    public function testExceptionWhenPrivateDepositCommissionIsNotANumber(): void
    {
        $this->expectExceptionMessage('Private deposit commission is not a number');

        new CalculatorConfigProvider(
            currencyConfig: ['default' => 'eur', 'decimal_places' => ['eur' => 2, 'jpy' => 0]],
            depositCommissionConfig: ['private' => 'not-a-number', 'business' => 0.03],
            withdrawCommissionConfig: [
                'private' => [
                    'percentage' => '0.3',
                    'free_per_week' => ['max_amount' => '1000.00', 'max_transactions' => 3],
                ],
                'business' => ['percentage' => '0.5'],
            ]
        );
    }

    public function testExceptionWhenBusinessDepositCommissionIsMissing(): void
    {
        $this->expectExceptionMessage('Business deposit commission is not set');

        new CalculatorConfigProvider(
            currencyConfig: ['default' => 'eur', 'decimal_places' => ['eur' => 2, 'jpy' => 0]],
            depositCommissionConfig: ['private' => 0.03],
            withdrawCommissionConfig: [
                'private' => [
                    'percentage' => '0.3',
                    'free_per_week' => ['max_amount' => '1000.00', 'max_transactions' => 3],
                ],
                'business' => ['percentage' => '0.5'],
            ]
        );
    }

    public function testExceptionWhenBusinessDepositCommissionIsNotANumber(): void
    {
        $this->expectExceptionMessage('Business deposit commission is not a number');

        new CalculatorConfigProvider(
            currencyConfig: ['default' => 'eur', 'decimal_places' => ['eur' => 2, 'jpy' => 0]],
            depositCommissionConfig: ['private' => 0.03, 'business' => 'not-a-number'],
            withdrawCommissionConfig: [
                'private' => [
                    'percentage' => '0.3',
                    'free_per_week' => ['max_amount' => '1000.00', 'max_transactions' => 3],
                ],
                'business' => ['percentage' => '0.5'],
            ]
        );
    }

    public function testExceptionWhenPrivateWithdrawCommissionIsMissing(): void
    {
        $this->expectExceptionMessage('Private withdraw commission is not set');

        new CalculatorConfigProvider(
            currencyConfig: ['default' => 'eur', 'decimal_places' => ['eur' => 2, 'jpy' => 0]],
            depositCommissionConfig: ['private' => 0.03, 'business' => 0.03],
            withdrawCommissionConfig: [
                'business' => ['percentage' => '0.5'],
            ]
        );
    }

    public function testExceptionWhenPrivateWithdrawCommissionPercentageIsMissing(): void
    {
        $this->expectExceptionMessage('Private withdraw commission percentage is not set');

        new CalculatorConfigProvider(
            currencyConfig: ['default' => 'eur', 'decimal_places' => ['eur' => 2, 'jpy' => 0]],
            depositCommissionConfig: ['private' => 0.03, 'business' => 0.03],
            withdrawCommissionConfig: [
                'private' => [
                    'free_per_week' => ['max_amount' => '1000.00', 'max_transactions' => 3],
                ],
                'business' => ['percentage' => '0.5'],
            ]
        );
    }

    public function testExceptionWhenPrivateWithdrawCommissionPercentageIsNotANumber(): void
    {
        $this->expectExceptionMessage('Private withdraw commission percentage is not a number');

        new CalculatorConfigProvider(
            currencyConfig: ['default' => 'eur', 'decimal_places' => ['eur' => 2, 'jpy' => 0]],
            depositCommissionConfig: ['private' => 0.03, 'business' => 0.03],
            withdrawCommissionConfig: [
                'private' => [
                    'percentage' => 'not-a-number',
                    'free_per_week' => ['max_amount' => '1000.00', 'max_transactions' => 3],
                ],
                'business' => ['percentage' => '0.5'],
            ]
        );
    }

    public function testExceptionWhenPrivateWithdrawCommissionFreePerWeekMaxAmountIsMissing(): void
    {
        $this->expectExceptionMessage('Private withdraw commission free per week max amount is not set');

        new CalculatorConfigProvider(
            currencyConfig: ['default' => 'eur', 'decimal_places' => ['eur' => 2, 'jpy' => 0]],
            depositCommissionConfig: ['private' => 0.03, 'business' => 0.03],
            withdrawCommissionConfig: [
                'private' => [
                    'percentage' => '0.3',
                    'free_per_week' => ['max_transactions' => 3],
                ],
                'business' => ['percentage' => '0.5'],
            ]
        );
    }

    public function testExceptionWhenPrivateWithdrawCommissionFreePerWeekMaxAmountIsNotANumber(): void
    {
        $this->expectExceptionMessage('Private withdraw commission free per week max amount is not a number');

        new CalculatorConfigProvider(
            currencyConfig: ['default' => 'eur', 'decimal_places' => ['eur' => 2, 'jpy' => 0]],
            depositCommissionConfig: ['private' => 0.03, 'business' => 0.03],
            withdrawCommissionConfig: [
                'private' => [
                    'percentage' => '0.3',
                    'free_per_week' => ['max_amount' => 'not-a-number', 'max_transactions' => 3],
                ],
                'business' => ['percentage' => '0.5'],
            ]
        );
    }

    public function testExceptionWhenPrivateWithdrawCommissionFreePerWeekMaxTransactionsIsMissing(): void
    {
        $this->expectExceptionMessage('Private withdraw commission free per week max transactions is not set');

        new CalculatorConfigProvider(
            currencyConfig: ['default' => 'eur', 'decimal_places' => ['eur' => 2, 'jpy' => 0]],
            depositCommissionConfig: ['private' => 0.03, 'business' => 0.03],
            withdrawCommissionConfig: [
                'private' => [
                    'percentage' => '0.3',
                    'free_per_week' => ['max_amount' => '1000.00'],
                ],
                'business' => ['percentage' => '0.5'],
            ]
        );
    }

    public function testExceptionWhenPrivateWithdrawCommissionFreePerWeekMaxTransactionsIsNotANumber(): void
    {
        $this->expectExceptionMessage('Private withdraw commission free per week max transactions is not a number');

        new CalculatorConfigProvider(
            currencyConfig: ['default' => 'eur', 'decimal_places' => ['eur' => 2, 'jpy' => 0]],
            depositCommissionConfig: ['private' => 0.03, 'business' => 0.03],
            withdrawCommissionConfig: [
                'private' => [
                    'percentage' => '0.3',
                    'free_per_week' => ['max_amount' => '1000.00', 'max_transactions' => 'not-a-number'],
                ],
                'business' => ['percentage' => '0.5'],
            ]
        );
    }

    public function testExceptionWhenBusinessWithdrawCommissionIsMissing(): void
    {
        $this->expectExceptionMessage('Business withdraw commission is not set');

        new CalculatorConfigProvider(
            currencyConfig: ['default' => 'eur', 'decimal_places' => ['eur' => 2, 'jpy' => 0]],
            depositCommissionConfig: ['private' => 0.03, 'business' => 0.03],
            withdrawCommissionConfig: [
                'private' => [
                    'percentage' => '0.3',
                    'free_per_week' => ['max_amount' => '1000.00', 'max_transactions' => 3],
                ],
            ]
        );
    }

    public function testExceptionWhenBusinessWithdrawCommissionPercentageIsMissing(): void
    {
        $this->expectExceptionMessage('Business withdraw commission percentage is not set');

        new CalculatorConfigProvider(
            currencyConfig: ['default' => 'eur', 'decimal_places' => ['eur' => 2, 'jpy' => 0]],
            depositCommissionConfig: ['private' => 0.03, 'business' => 0.03],
            withdrawCommissionConfig: [
                'private' => [
                    'percentage' => '0.3',
                    'free_per_week' => ['max_amount' => '1000.00', 'max_transactions' => 3],
                ],
                'business' => [],
            ]
        );
    }

    public function testExceptionWhenBusinessWithdrawCommissionPercentageIsNotANumber(): void
    {
        $this->expectExceptionMessage('Business withdraw commission percentage is not a number');

        new CalculatorConfigProvider(
            currencyConfig: ['default' => 'eur', 'decimal_places' => ['eur' => 2, 'jpy' => 0]],
            depositCommissionConfig: ['private' => 0.03, 'business' => 0.03],
            withdrawCommissionConfig: [
                'private' => [
                    'percentage' => '0.3',
                    'free_per_week' => ['max_amount' => '1000.00', 'max_transactions' => 3],
                ],
                'business' => ['percentage' => 'not-a-number'],
            ]
        );
    }
}
