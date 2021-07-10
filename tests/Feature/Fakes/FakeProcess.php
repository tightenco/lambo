<?php

namespace Tests\Feature\Fakes;

class FakeProcess
{
    public $isSuccessful;
    public $failedCommand;

    private $output;
    private $errorOutput;

    public function __construct(bool $isSuccessful, string $failedCommand = '')
    {
        $this->isSuccessful = $isSuccessful;
        $this->failedCommand = $failedCommand;
    }

    public static function success(): FakeProcess
    {
        return new self(true);
    }

    public static function fail(string $failedCommand): FakeProcess
    {
        return new self(false, $failedCommand);
    }

    public function isSuccessful(): bool
    {
        return $this->isSuccessful;
    }

    public function getCommandLine(): string
    {
        return $this->failedCommand;
    }

    public function withOutput(string $output): FakeProcess
    {
        $this->output = $output;
        return $this;
    }

    public function withErrorOutput(string $errorOutput): FakeProcess
    {
        $this->errorOutput = $errorOutput;
        return $this;
    }

    public function getOutput()
    {
        return $this->output;
    }

    public function getErrorOutput()
    {
        return $this->errorOutput;
    }

    public function getExitCode()
    {
        return $this->isSuccessful ? 0 : 1;
    }
}
