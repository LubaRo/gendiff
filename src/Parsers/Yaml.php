<?php

namespace Differ\Parsers\Yaml;

use Symfony\Component\Yaml\Yaml;

function parse($data)
{
    return Yaml::parse($data);
}
