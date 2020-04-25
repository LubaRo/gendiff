<?php

namespace Differ\Parser;

use function Differ\Parsers\Yaml\parse as parseYaml;

function parse($data, $extension)
{
    switch ($extension) {
        case 'json':
            return json_decode($data);
        case 'yaml':
            return parseYaml($data);
        default:
            throw new \Exception("File extension '{$extension}' is incorrect or not supported.");
    }
}
