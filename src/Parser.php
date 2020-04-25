<?php

namespace Differ\Parser;

use Symfony\Component\Yaml\Yaml;

function parse($data, $extension)
{
    switch ($extension) {
        case 'json':
            return json_decode($data);
        case 'yaml':
            return Yaml::parse($data, Yaml::PARSE_OBJECT_FOR_MAP);
        default:
            throw new \Exception("File extension '{$extension}' is incorrect or not supported.");
    }
}
