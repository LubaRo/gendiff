<?php

namespace Differ\Formatters\Json;

use const Differ\GenDiff\{STATUS_NEW, STATUS_REMOVED, STATUS_CHANGED, STATUS_UNCHANGED, STATUS_COMPLEX};

function format($data)
{
    $result = json_encode($data, JSON_PRETTY_PRINT);

    return $result;
}
