<?php

namespace App\Service\Config;

use App\Enum\ClientType;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class CalculatorConfigProvider implements ICalculatorConfigProvider
{
    private array $currencyConfig;
    private array $depositCommissionConfig;
    private array $withdrawCommissionConfig;

    /**
     * @param array $currencyConfig
     * @param array $depositCommissionConfig
     * @param array $withdrawCommissionConfig
     *
     * @throws \Exception
     */
    public function __construct(
        #[Autowire('%app.currency%')]
        array $currencyConfig,
        #[Autowire('%app.commission.deposit%')]
        array $depositCommissionConfig,
        #[Autowire('%app.commission.withdraw%')]
        array $withdrawCommissionConfig,
    ) {
        $this->currencyConfig = array_change_key_case($currencyConfig);
        $this->depositCommissionConfig = array_change_key_case($depositCommissionConfig);
        $this->withdrawCommissionConfig = array_change_key_case($withdrawCommissionConfig);

        // currency

        if (!isset($this->currencyConfig['default'])) {
            throw new \Exception('Default currency is not set');
        }

        if (!isset($this->currencyConfig['decimal_places'])) {
            throw new \Exception('Decimal places config is not set');
        }

        if (!isset($this->currencyConfig['decimal_places'][$this->currencyConfig['default']])) {
            throw new \Exception('Default currency decimal places is not set');
        }

        foreach ($this->currencyConfig['decimal_places'] as $currency => $decimalPlaces) {
            if (!is_numeric($decimalPlaces) || $decimalPlaces < 0) {
                throw new \Exception(sprintf('Decimal places for currency %s is not a number or less than zero', $currency));
            }
        }

        // both client

        foreach (ClientType::cases() as $clientType) {
            if (!isset($this->depositCommissionConfig[$clientType->value])) {
                throw new \Exception("{$clientType->name} deposit commission is not set");
            }

            if (!is_numeric($this->depositCommissionConfig[$clientType->value])) {
                throw new \Exception("{$clientType->name} deposit commission is not a number");
            }

            if (!isset($this->withdrawCommissionConfig[$clientType->value])) {
                throw new \Exception("{$clientType->name} withdraw commission is not set");
            }

            if (!is_array($this->withdrawCommissionConfig[$clientType->value])) {
                throw new \Exception("{$clientType->name} withdraw commission is not a array");
            }
        }

        // private client

        if (!isset($this->withdrawCommissionConfig[ClientType::Private->value]['percentage'])) {
            throw new \Exception(sprintf('%s withdraw commission percentage is not set', ClientType::Private->name));
        }

        if (!is_numeric($this->withdrawCommissionConfig[ClientType::Private->value]['percentage'])) {
            throw new \Exception(sprintf('%s withdraw commission percentage is not a number', ClientType::Private->name));
        }

        if (!isset($this->withdrawCommissionConfig[ClientType::Private->value]['free_per_week']['max_amount'])) {
            throw new \Exception(sprintf('%s withdraw commission free per week max amount is not set', ClientType::Private->name));
        }

        if (!is_numeric($this->withdrawCommissionConfig[ClientType::Private->value]['free_per_week']['max_amount'])) {
            throw new \Exception(sprintf('%s withdraw commission free per week max amount is not a number', ClientType::Private->name));
        }

        if (!isset($this->withdrawCommissionConfig[ClientType::Private->value]['free_per_week']['max_transactions'])) {
            throw new \Exception(sprintf('%s withdraw commission free per week max transactions is not set', ClientType::Private->name));
        }

        if (!is_numeric($this->withdrawCommissionConfig[ClientType::Private->value]['free_per_week']['max_transactions'])) {
            throw new \Exception(sprintf('%s withdraw commission free per week max transactions is not a number', ClientType::Private->name));
        }

        // business client

        if (!isset($this->withdrawCommissionConfig[ClientType::Business->value]['percentage'])) {
            throw new \Exception(sprintf('%s withdraw commission percentage is not set', ClientType::Business->name));
        }

        if (!is_numeric($this->withdrawCommissionConfig[ClientType::Business->value]['percentage'])) {
            throw new \Exception(sprintf('%s withdraw commission percentage is not a number', ClientType::Business->name));
        }
    }

    public function getDefaultCurrency(): string
    {
        return $this->currencyConfig['default'];
    }

    public function getDecimalPlacesForCurrency(string $currency): string
    {
        return (string) ($this->currencyConfig['decimal_places'][strtolower($currency)] ?? $this->currencyConfig['decimal_places'][$this->currencyConfig['default']]);
    }

    public function getDepositCommissionForClientType(ClientType $clientType): string|array
    {
        return $this->depositCommissionConfig[strtolower($clientType->value)];
    }

    public function getWithdrawCommissionForClientType(ClientType $clientType): string|array
    {
        return $this->withdrawCommissionConfig[strtolower($clientType->value)];
    }
}
