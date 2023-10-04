<?php

namespace VendorName\Skeleton\Exceptions;

use Exception;

class MissingPaymentInfoException extends Exception
{
    public function __construct($missing_payment_parameter)
    {
        parent::__construct($missing_payment_parameter.' is required');
    }
}
