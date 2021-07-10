<?php

namespace App\Helpers;

use IntlTimeZone;

class GetTimezone
{
    public function __invoke(): string
    {
        if ($timezone = ini_get('date.timezone')) {
            return $timezone;
        }

        return IntlTimeZone::createDefault()->getID();
    }
}
