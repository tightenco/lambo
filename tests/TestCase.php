<?php

namespace Tests;

use App\ConsoleWriter;
use App\Shell;
use LaravelZero\Framework\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected $shell;

    function setUp(): void
    {
        parent::setUp();

        $this->mockConsoleWriter();

        $this->shell = $this->mock(Shell::class);
    }

    protected function mockConsoleWriter(): void
    {
        $consoleWriter = $this->mock(ConsoleWriter::class, function ($consoleWriter) {
            $consoleWriter->shouldReceive('logStep');
            $consoleWriter->shouldReceive('title');
            $consoleWriter->shouldReceive('success');
            $consoleWriter->shouldReceive('note');
            $consoleWriter->shouldReceive('text');
            $consoleWriter->shouldReceive('warn');
            $consoleWriter->shouldReceive('fail');
            $consoleWriter->shouldReceive('newLine');
        });

        $this->swap('console-writer', $consoleWriter);
        $this->swap(ConsoleWriter::class, $consoleWriter);
    }

    protected function todo(array $lines)
    {
        $this->skipWithMessage($lines, 'TODO');
    }

    protected function skipWithMessage(array $lines, $title = 'SKIPPED'): void
    {
        $lineLength = 80;
        $header = $this->center(" [ {$title} ] ", '=', $lineLength);
        $testName = $this->center(str_replace('_', ' ', $this->getName()), ' ', $lineLength);
        $section = str_repeat('=', $lineLength);
        $horizontalRule = str_repeat('-', $lineLength);
        $message = implode(PHP_EOL, $lines);
        $this->markTestSkipped("{$header}\n{$testName}\n{$horizontalRule}\n{$message}\n{$section}");
    }

    protected function center(string $title, string $padChar = ' ', int $lineLength = 80): string
    {
        return str_pad(str_repeat($padChar, ($lineLength - strlen($title)) / 2) . $title . str_repeat($padChar, ($lineLength - strlen($title)) / 2), $lineLength, $padChar);
    }

    protected function toSTDOUT($out, string $title = null): void
    {
        $message = sprintf("%s%s\n", $title ? "{$title}\n" : '', print_r($out, true));
        fwrite(STDOUT, $message);
    }
}
