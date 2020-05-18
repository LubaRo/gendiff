<?php

namespace Differ\Formatters\Pretty;

use const Differ\GenDiff\{STATUS_NEW, STATUS_REMOVED, STATUS_CHANGED, STATUS_UNCHANGED, STATUS_COMPLEX};

const IDENTATION = '    ';

function format($data, $nestedLevel = 0)
{
    $leftIdentation = str_repeat(IDENTATION, $nestedLevel);

    $propertiesData = array_map(function ($propertyData) use ($leftIdentation, $nestedLevel) {
        $status = $propertyData['status'];
        $propertyFormatter = getPropertyFormatter($status);

        return $propertyFormatter($leftIdentation, $propertyData, $nestedLevel);
    }, $data);

    $propertiesBlock = implode("\n", $propertiesData);

    return "{\n" . $propertiesBlock . "\n$leftIdentation}";
}

function formatProperty($property, $value, $identation, $sign = ' ')
{
    $prettyValue = prepareValue($value, $identation);
    return "$identation  $sign $property: $prettyValue";
}

function getPropertyFormatter($status)
{
    $statuses = [
        STATUS_NEW => function ($identation, $propertyData) {
            return formatProperty($propertyData['name'], $propertyData['value'], $identation, '+');
        },
        STATUS_REMOVED => function ($identation, $propertyData) {
            return formatProperty($propertyData['name'], $propertyData['value'], $identation, '-');
        },
        STATUS_UNCHANGED => function ($identation, $propertyData) {
            return formatProperty($propertyData['name'], $propertyData['value'], $identation);
        },
        STATUS_CHANGED => function ($identation, $propertyData) {
            ['valueBefore' => $before, 'valueAfter' => $after] = $propertyData;
            $beforeRow = formatProperty($propertyData['name'], $before, $identation, '-');
            $afterRow = formatProperty($propertyData['name'], $after, $identation, '+');

            return "$afterRow\n$beforeRow";
        },
        STATUS_COMPLEX => function ($identation, $propertyData, $nestedLevel) {
            $formattedValue = format($propertyData['children'], $nestedLevel + 1);
            $leftIdentation = IDENTATION . $identation;

            return $leftIdentation . "{$propertyData['name']}: $formattedValue";
        }
    ];

    return $statuses[$status];
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

        $blocks = array_map(function ($key) use ($data, $leftIdentation, $contentIdentation) {
            $value = $data[$key];
            $result = "{\n$contentIdentation" . "$key: $value\n" . "$leftIdentation}";

            return $result;
        }, $properties);

        return implode("", $blocks);
    }
    return $data;
}
