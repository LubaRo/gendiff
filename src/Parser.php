<?php

namespace Differ\Parser;

use Symfony\Component\Yaml\Yaml;

function parse($data, $type)
{
    switch ($type) {
        case 'json':
            return json_decode($data, true);
        case 'yaml':
            return Yaml::parse($data);
        default:
            throw new \Exception("Data type '{$type}' is incorrect or not supported.");
    }
}
