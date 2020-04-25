<?php

namespace Differ\Formatter;

use function Differ\Formatters\Pretty\format as formatPretty;
use function Differ\Formatters\Plain\format as formatPlain;

function getFormattedData($data, $format)
{
    switch ($format) {
        case 'pretty':
            return formatPretty($data);
        case 'plain':
            return formatPlain($data);
        default:
            throw new \Exception("Unknown format: '{$format}'.");
    }
}
