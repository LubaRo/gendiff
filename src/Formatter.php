<?php

namespace Differ\Formatter;

use function Differ\Formatters\Pretty\format as formatPretty;
use function Differ\Formatters\Plain\format as formatPlain;
use function Differ\Formatters\Json\format as formatJson;

use const Differ\GenDiff\{FORMAT_PRETTY, FORMAT_PLAIN, FORMAT_JSON};

function getFormattedData($data, $format)
{
    switch ($format) {
        case FORMAT_PRETTY:
            return formatPretty($data);
        case FORMAT_PLAIN:
            return formatPlain($data);
        case FORMAT_JSON:
            return formatJson($data);
        default:
            throw new \Exception("Unknown format: '{$format}'.");
    }
}
