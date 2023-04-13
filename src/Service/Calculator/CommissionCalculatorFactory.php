<?php

namespace App\Service\Calculator;

use App\Enum\ClientType;
use App\Enum\OperationType;
use App\Service\Calculator\Deposit\DepositCommissionCalculator;
use App\Service\Calculator\Withdraw\BusinessWithdrawCommissionCalculator;
use App\Service\Calculator\Withdraw\PrivateWithdrawCommissionCalculator;
use App\Service\Config\ICalculatorConfigProvider;
use App\Service\ExchangeRate\ICurrencyExchangeRateProvider;
use App\Service\Math;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Cache\CacheInterface;

readonly class CommissionCalculatorFactory
{
    /**
     * @param string $carbonLocale
     */
    public function __construct(
        private ICalculatorConfigProvider $configProvider,
        private ICurrencyExchangeRateProvider $currencyExchangeRateProvider,
        private Math $math,
        #[Autowire('%app.carbon.locale%')]
        private string $carbonLocale,
        private CacheInterface $cache,
    ) {
    }

    /**
     * @throws \Exception
     */
    public function create(OperationType $operationType, ClientType $clientType): ICommissionCalculator
    {
        if (OperationType::Deposit === $operationType) {
            return new DepositCommissionCalculator(
                configProvider: $this->configProvider,
                math: $this->math,
            );
        }

        if (OperationType::Withdraw === $operationType) {
            if (ClientType::Private === $clientType) {
                return new PrivateWithdrawCommissionCalculator(
                    configProvider: $this->configProvider,
                    currencyExchangeRateProvider: $this->currencyExchangeRateProvider,
                    math: $this->math,
                    cache: $this->cache,
                    carbonLocale: $this->carbonLocale,
                );
            } elseif (ClientType::Business === $clientType) {
                return new BusinessWithdrawCommissionCalculator(
                    configProvider: $this->configProvider,
                    math: $this->math,
                );
            }
        }

        throw new \Exception('Unknown operation type or client type');
    }
}
