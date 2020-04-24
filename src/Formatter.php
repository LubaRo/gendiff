<?php

namespace Differ\Formatter;

use function Differ\Formatters\Pretty\getFormater as getPrettyFormatter;
use function Differ\Formatters\Plain\getFormater as getPlainFormatter;

function getFormatter($format)
{
    if ($format === 'pretty') {
        $func = getPrettyFormatter();
        return $func;
    } elseif ($format === 'plain') {
        $func = getPlainFormatter();
        return $func;
    }
}
