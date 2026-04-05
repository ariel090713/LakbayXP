<?php

namespace App\Exceptions;

use RuntimeException;

class NoSlotsAvailableException extends RuntimeException
{
    public function __construct(string $message = 'No slots available for this event.')
    {
        parent::__construct($message);
    }
}
