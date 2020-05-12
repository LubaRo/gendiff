<?php

namespace Differ\Formatters\Json;

function format($data)
{
    $result = json_encode($data, JSON_PRETTY_PRINT);

    return $result;
}
