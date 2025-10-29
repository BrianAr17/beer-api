<?php

namespace App\Exception;

class Result
{
    private bool $is_success = false;
    private string $message;
    private $data;
    private $errors;

    private function __construct(bool $success, string $message, mixed $data = null, mixed $errors = null)
    {
        $this->is_success = $success;
        $this->message = $message;
        $this->data = $data;
        $this->errors = $errors;
    }

    public static function success($message, mixed $data = null): Result
    {
        return new Result(true, $message, $data);
    }

    public static function failure($message, mixed $errors = null): Result
    {
        return new Result(false, $message, null, $errors);
    }

    public function isSuccess(): bool
    {
        return $this->is_success;
    }

    public function isFailure(): bool
    {
        return !$this->is_success;
    }

    public function getData(): mixed
    {
        if (!$this->is_success) {
            throw new Exception("Cannot get data from a failed result.");
        }
        return $this->data;
    }

    public function getErrors(): mixed
    {
        if ($this->is_success) {
            throw new Exception("Cannot get errors from a successful result.");
        }
        return $this->errors;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function __toString(): string
    {
        if ($this->is_success) {
            $data = $this->data !== null ? 'Data: ' . json_encode($this->data) : 'No data';
            return "Success: {$this->message}, {$data}";
        } else {
            $errors = $this->errors !== null ? 'Errors: ' . json_encode($this->errors) : 'No errors';
            return "Failure: {$this->message}, {$errors}";
        }
    }
}
