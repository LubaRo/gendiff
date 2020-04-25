<?php

namespace Differ\Formatters\Plain;

function format($data)
{
    $result = [];

    foreach ($data as $key => $value) {
        if ($value['status'] === 'notChanged') {
            continue;
        } elseif ($value['status'] === 'changed') {
            $result[] = "Property '{$key}' was changed. From '{$value['valueBefore']}' to '{$value['valueAfter']}'";
        } elseif ($value['status'] === 'removed') {
            $result[] = "Property '{$key}' was removed.";
        } elseif ($value['status'] === 'new') {
            $result[] = "Property '{$key}' was added with value: '{$value['value']}'";
        }
    }

    return implode("\n", $result);
}
