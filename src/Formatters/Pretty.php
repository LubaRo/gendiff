<?php

namespace Differ\Formatters\Pretty;

use function Funct\Collection\flatten;

use const Differ\GenDiff\{STATUS_NEW, STATUS_REMOVED, STATUS_CHANGED, STATUS_UNCHANGED};

const IDENTATION = '    ';

function formatProperty($property, $value, $identation, $sign = ' ')
{
    $prettyValue = prepareValue($value, $identation);
    return "$identation  $sign $property: $prettyValue";
}

function getPropertyFormatter($status)
{
    $statuses = [
        STATUS_NEW => function ($identation, $property, $value) {
            return formatProperty($property, $value, $identation, '+');
        },
        STATUS_REMOVED => function ($identation, $property, $value) {
            return formatProperty($property, $value, $identation, '-');
        },
        STATUS_UNCHANGED => function ($identation, $property, $value) {
            return formatProperty($property, $value, $identation);
        },
        STATUS_CHANGED => function ($identation, $property, $value) {
            ['before' => $before, 'after' => $after] = $value;
            $beforeRow = formatProperty($property, $before, $identation, '-');
            $afterRow = formatProperty($property, $after, $identation, '+');

            return "$afterRow\n$beforeRow";
        }
    ];

    return $statuses[$status];
}

function format($data, $nestedLevel = 0)
{
    $leftIdentation = str_repeat(IDENTATION, $nestedLevel);

    $propertiesData = array_reduce($data, function ($acc, $propertyData) use ($leftIdentation, $nestedLevel) {
        $children = $propertyData['children'] ?? [];
        $name = $propertyData['name'] ?? '';

        if ($children) {
            $formattedChildren = format($children, $nestedLevel + 1);
            $acc[] = IDENTATION . $leftIdentation . "$name: $formattedChildren";
            return $acc;
        }
        ['value' => $value, 'status' => $status] = $propertyData;
        $propertyFormatter = getPropertyFormatter($status);
        $acc[] = $propertyFormatter($leftIdentation, $name, $value);

        return $acc;
    }, []);

    $propertiesBlock = implode("\n", $propertiesData);

    return "{\n" . $propertiesBlock . "\n$leftIdentation}";
}

function prepareValue($data, $identation)
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
