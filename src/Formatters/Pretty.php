<?php

namespace Differ\Formatters\Pretty;

use function Funct\Collection\flatten;

define('IDENTATION', '    ');

function getRowFormatter($status)
{
    $statuses = [
        'added' => function ($identation, $property, $value) {
            $normalizedValue = normalizePropertyValue($value, $identation);
            return "$identation  + $property: $normalizedValue";
        },
        'removed' => function ($identation, $property, $value) {
            $normalizedValue = normalizePropertyValue($value, $identation);
            return "$identation  - $property: $normalizedValue";
        },
        'unchanged' => function ($identation, $property, $value) {
            $normalizedValue = normalizePropertyValue($value, $identation);
            return "$identation    $property: $normalizedValue";
        },
        'changed' => function ($identation, $property, $value) {
            ['before' => $before, 'after' => $after] = $value;

            $normalizedBefore = normalizePropertyValue($before, $identation);
            $normalizedAfter = normalizePropertyValue($after, $identation);

            $beforeRow = "$identation  - $property: $normalizedBefore";
            $afterRow = "$identation  + $property: $normalizedAfter";

            return "$afterRow\n$beforeRow";
        }
    ];

    return $statuses[$status];
}

function format($data, $nestedLevel = 0)
{
    if (empty($data)) {
        return '{}';
    }

    $leftIdentation = str_repeat(IDENTATION, $nestedLevel);

    $propertiesData = array_reduce($data, function ($acc, $propertyData) use ($leftIdentation, $nestedLevel) {
        $children = $propertyData['children'] ?? [];
        $name = $propertyData['name'] ?? '';

        if ($children) {
            $formattedChildren = format($children, $nestedLevel + 1);
            $acc[] = $leftIdentation . IDENTATION . "$name: $formattedChildren";
            return $acc;
        }
        ['value' => $value, 'status' => $status] = $propertyData;
        $rowFormatter = getRowFormatter($status);
        $acc[] = $rowFormatter($leftIdentation, $name, $value);

        return $acc;
    }, []);

    $propertiesBlock = implode("\n", $propertiesData);

    return "{\n" . $propertiesBlock . "\n$leftIdentation}";
}

function normalizePropertyValue($data, $identation)
{
    if (is_bool($data)) {
        return $data ? 'true' : 'false';
    }

    if (is_array($data)) {
        $properties = array_keys($data);
        $leftIdentation = IDENTATION . $identation;
        $contentIdentation = IDENTATION . $leftIdentation;

        $childrenBlocks = array_map(function ($key) use ($data, $leftIdentation, $contentIdentation) {
            $value = $data[$key];
            $result = "{\n$contentIdentation" . "$key: $value\n" . "$leftIdentation}";

            return $result;
        }, $properties);

        return implode("", $childrenBlocks);
    }
    return $data;
}
