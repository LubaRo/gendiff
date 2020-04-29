<?php

namespace Differ\Formatter;

use function Differ\Formatters\Pretty\format as formatPretty;
use function Differ\Formatters\Plain\format as formatPlain;
use function Differ\Formatters\Json\format as formatJson;

function getFormattedData($data, $format)
{
    switch ($format) {
        case 'pretty':
            return formatPretty($data);
        case 'plain':
            return formatPlain($data);
        case 'json':
            return formatJson($data);
        default:
            throw new \Exception("Unknown format: '{$format}'.");
    }
}
