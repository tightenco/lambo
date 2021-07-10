<?php

namespace App;

use Illuminate\Console\OutputStyle;
use Symfony\Component\Process\Process;

class ConsoleWriter extends OutputStyle
{
    public const BLUE = 'fg=blue';
    public const GREEN = 'fg=green';
    public const RED = 'fg=red';
    public const MAGENTA = 'fg=magenta';

    public static function formatString(string $string, string $format): string
    {
        return "<{$format}>{$string}</>";
    }

    public function panel(string $prefix, string $message, string $style)
    {
        parent::block($message, $prefix, $style, ' ', true, false);
    }

    public function sectionTitle($sectionTitle)
    {
        $this->newLine();
        $this->text([
            "<fg=yellow;bg=default>{$sectionTitle}</>",
            '<fg=yellow;bg=default>' . str_repeat('#', strlen($sectionTitle)) . '</>',
        ]);
    }

    public function logStep($message)
    {
        parent::block($message, null, 'fg=yellow;bg=default', ' // ', false, false);
    }

    public function exec(string $command)
    {
        $this->labeledLine('EXEC', $command, 'bg=blue;fg=black');
    }

    public function success($message, $label = 'PASS'): void
    {
        $this->labeledLine($label, $message, 'fg=black;bg=green');
    }

    public function ok($message): void
    {
        $this->success($message, 'OK');
    }

    public function note($message, $label = 'NOTE'): void
    {
        $this->labeledLine($label, $message, 'fg=black;bg=yellow');
    }

    public function warn($message, $label = 'WARN'): void
    {
        $this->labeledLine($label, "<fg=red;bg=default>{$message}</>", 'fg=black;bg=red');
    }

    public function warnCommandFailed($command): void
    {
        $this->warn("Failed to run {$command}");
    }

    public function showOutputErrors(string $errors)
    {
        parent::text([
            '<fg=red;bg=default>--------------------------------------------------------------------------------',
            str_replace(PHP_EOL, PHP_EOL . ' ', trim($errors)),
            '--------------------------------------------------------------------------------</>',
        ]);
    }

    public function showOutput(string $errors)
    {
        parent::text([
            '--------------------------------------------------------------------------------',
            str_replace(PHP_EOL, PHP_EOL . ' ', trim($errors)),
            '--------------------------------------------------------------------------------',
        ]);
    }

    public function exception($message)
    {
        parent::block($message, null, 'fg=black;bg=red', ' ', true, false);
    }

    public function text($message)
    {
        parent::text($message);
    }

    public function listing(array $items): void
    {
        parent::newLine();
        $text = collect($items)->map(function ($dependency) {
            return '  - ' . $dependency;
        })->toArray();
        parent::text($text);
        parent::newLine();
    }

    public function table(array $columnHeadings, array $rowData)
    {
        parent::table($columnHeadings, $rowData);
    }

    public function consoleOutput(string $line, $type)
    {
        if (config('lambo.store.with_output')) {
            ($type === Process::ERR)
                ? $this->labeledLine('!️', '┃ ' . $line, 'fg=yellow')
                : $this->labeledLine('✓︎', '┃ ' . $line, 'fg=green;');
        }
    }

    public function labeledLine(string $label, string $message, string $labelFormat = 'fg=default;bg=default', int $indentColumns = 0): void
    {
        $indent = str_repeat(' ', $indentColumns);
        $this->isDecorated()
            ? parent::text("{$indent}<{$labelFormat}> {$label} </> {$message}")
            : parent::text("{$indent}[ {$label} ] {$message}");
    }
}
