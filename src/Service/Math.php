<?php

namespace App\Service;

class Math
{
    public function add(string $leftOperand, string $rightOperand, int $precision): string
    {
        if ($precision < 0) {
            throw new \InvalidArgumentException('Precision must be a positive integer or zero');
        }

        return bcadd($leftOperand, $rightOperand, $precision);
    }

    public function subtract(string $leftOperand, string $rightOperand, int $precision): string
    {
        if ($precision < 0) {
            throw new \InvalidArgumentException('Precision must be a positive integer or zero');
        }

        return bcsub($leftOperand, $rightOperand, $precision);
    }

    public function multiply(string $leftOperand, string $rightOperand, int $precision): string
    {
        if ($precision < 0) {
            throw new \InvalidArgumentException('Precision must be a positive integer or zero');
        }

        return bcmul($leftOperand, $rightOperand, $precision);
    }

    public function divide(string $leftOperand, string $rightOperand, int $precision): string
    {
        if ($precision < 0) {
            throw new \InvalidArgumentException('Precision must be a positive integer or zero');
        }

        if (0 === bccomp($rightOperand, '0', $precision)) {
            throw new \InvalidArgumentException('Division by zero is not allowed');
        }

        return bcdiv($leftOperand, $rightOperand, $precision);
    }

    public function compare(string $leftOperand, string $rightOperand, int $precision): int
    {
        if ($precision < 0) {
            throw new \InvalidArgumentException('Precision must be a positive integer or zero');
        }

        return bccomp($leftOperand, $rightOperand, $precision);
    }

    /**
     * Rounds up to the nearest value with the specified precision.
     *
     * @throws \InvalidArgumentException
     */
    public function roundUp(float $value, int $precision): string
    {
        if ($precision < 0) {
            throw new \InvalidArgumentException('Precision must be a positive integer or zero');
        }

        $offset = 0.5;

        if (0 !== $precision) {
            $offset /= pow(10, $precision);
        }

        $final = round($value + $offset, $precision, PHP_ROUND_HALF_DOWN);

        return sprintf("%.{$precision}f", -0 == $final ? 0 : $final);
    }
}
