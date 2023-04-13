<?php

namespace App\Exceptions;

class FailedToConvertCurrencyException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Failed to convert currency');
    }
}
