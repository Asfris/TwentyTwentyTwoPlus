<?php

namespace ErrorHandle;

use Exception;

interface ErrorI
{
    public function fullErrorMessage(): string;
}

class Error extends Exception implements ErrorI
{
    /**
     * Returns full error
     * @return string
     */
    public function fullErrorMessage(): string
    {
        return 'Error caught on line ' . $this->getLine() . ' in ' . $this->getFile()
                . ': <b>' . $this->getMessage() . '</b> is no valid E-Mail address';
    }
}