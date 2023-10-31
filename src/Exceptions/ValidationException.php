<?php

namespace WezanEnterprises\LaravelAnalytics\Exceptions;

use Exception;
use Illuminate\Contracts\Support\MessageBag;

class ValidationException extends Exception {

    protected MessageBag $errors;

    public function __construct(MessageBag $errors, $message = null, $code = 422, Exception $previous = null)
    {
        $this->errors = $errors;

        if (is_null($message)) {
            $message = $this->getErrorMessage();
        }

        parent::__construct($message, $code, $previous);
    }

    public function getErrorMessage(): string
    {
        return 'Validation failed: ' . implode(' ', $this->errors->all());
    }

    public function getErrors(): MessageBag
    {
        return $this->errors;
    }
}