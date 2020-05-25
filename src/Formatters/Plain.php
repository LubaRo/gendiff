<?php

namespace Differ\Formatters\Plain;

use const Differ\GenDiff\{STATUS_NEW, STATUS_REMOVED, STATUS_CHANGED, STATUS_UNCHANGED, STATUS_COMPLEX};

function format($data, $path = [])
{
    $mappedProperties = array_map(function ($property) use ($path) {
        ['status' => $status, 'name' => $name] = $property;
        $newPath = [...$path, $name];

        return formatProperty($property, $newPath);
    }, $data);

    $filteredRows = array_filter($mappedProperties, fn($elem) => !empty($elem));

    return implode("\n", $filteredRows);
}

function getFullName($path)
{
    return implode('.', $path);
}

function prepareValue($value)
{
    return is_array($value) ? 'complex value' : $value;
}

function formatProperty($propertyData, $fullPath)
{
    $status = $propertyData['status'];
    $formatter = getPropertyFormatter($status);

    return $formatter($propertyData, $fullPath);
}

function getPropertyFormatter($status)
{
    $statuses = [
        STATUS_NEW => function ($propertyData, $fullPath) {
            $nomalizedValue = prepareValue($propertyData['value']);
            $name = getFullName($fullPath);
            return "Property '$name' was added with value: '$nomalizedValue'";
        },
        STATUS_REMOVED => function ($propertyData, $fullPath) {
            $name = getFullName($fullPath);
            return "Property '$name' was removed";
        },
        STATUS_UNCHANGED => fn() => null,
        STATUS_CHANGED => function ($propertyData, $fullPath) {
            ['valueBefore' => $before, 'valueAfter' => $after] = $propertyData;
            $normalizedBefore = prepareValue($before);
            $normalizedAfter = prepareValue($after);
            $name = getFullName($fullPath);

            return "Property '$name' was changed. From '$normalizedBefore' to '$normalizedAfter'";
        },
        STATUS_COMPLEX => fn($propertyData, $fullPath) => format($propertyData['children'], $fullPath)
    ];

    return $statuses[$status];
}
