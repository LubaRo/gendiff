<?php

namespace Differ\Parsers\Yaml;

use Symfony\Component\Yaml\Yaml;

function parse($data)
{
    return Yaml::parse($data, Yaml::PARSE_OBJECT_FOR_MAP);
}
