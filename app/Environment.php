<?php

namespace App;

class Environment
{
    public static function isMac(): bool
    {
        return PHP_OS === 'Darwin';
    }
}
