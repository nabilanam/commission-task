<?php

namespace App\Command;

use App\DTO\Operation;
use App\Enum\ClientType;
use App\Enum\OperationType;
use App\Service\Calculator\CommissionCalculatorFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'CalculateCommission',
    description: 'Calculates commissions based on csv data',
)]
class CalculateCommission extends Command
{
    private CommissionCalculatorFactory $commissionCalculatorFactory;

    public function __construct(
        CommissionCalculatorFactory $commissionCalculatorFactory,
        string $name = null
    ) {
        $this->commissionCalculatorFactory = $commissionCalculatorFactory;
        parent::__construct($name);
    }

    protected function configure(): void
    {
        $this->addArgument('csv-path', InputArgument::REQUIRED, 'csv file path');
    }

    /**
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $csvRows = array_map('str_getcsv', file($input->getArgument('csv-path')));

        foreach ($csvRows as $key => $row) {
            if (6 !== count($row)) {
                $io->error('Invalid row: '.implode(',', $row));
                continue;
            }

            [$date, $clientId, $clientType, $operationType, $amount, $currency] = $row;
            $currency = strtolower($currency);

            $clientType = ClientType::tryFrom(strtolower($clientType));
            $operationType = OperationType::tryFrom(strtolower($operationType));

            $calculator = $this->commissionCalculatorFactory->create($operationType, $clientType);

            $commission = $calculator->calculate(new Operation(
                clientId: $clientId,
                clientType: $clientType,
                operationType: $operationType,
                date: $date,
                amount: $amount,
                currency: $currency,
            ));

            $output->writeln($commission);
        }

        return Command::SUCCESS;
    }
}
