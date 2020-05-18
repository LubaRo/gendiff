<?php

namespace Differ\Formatters\Plain;

use const Differ\GenDiff\{STATUS_NEW, STATUS_REMOVED, STATUS_CHANGED, STATUS_UNCHANGED, STATUS_COMPLEX};

function format($data)
{
    $propertiesRows = getPropertiesRows($data);
    $filteredRows = array_filter($propertiesRows, function ($elem) {
        return !empty($elem);
    });

    return implode("\n", $filteredRows);
}

function getPropertiesRows($propertiesData, $path = [])
{
    $reduce  = function ($acc, $property) use (&$reduce, $path) {
        $name = $property['name'] ?? '';
        $newPath = [...$path, $name];

        $newData = getPropertyRow($property, $newPath);
        $newAcc = is_array($newData) ? array_merge($acc, $newData) : [...$acc, $newData];

        return $newAcc;
    };

    $result = array_reduce($propertiesData, function ($acc, $property) use ($reduce) {
        return $reduce($acc, $property);
    }, []);

    return $result;
}

function getFullName($path)
{
    return implode('.', $path);
}

function getPropertyRow($propertyData, $fullPath)
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
        STATUS_UNCHANGED => function () {
            return null;
        },
        STATUS_CHANGED => function ($propertyData, $fullPath) {
            ['valueBefore' => $before, 'valueAfter' => $after] = $propertyData;
            $normalizedBefore = prepareValue($before);
            $normalizedAfter = prepareValue($after);
            $name = getFullName($fullPath);

            return "Property '$name' was changed. From '$normalizedBefore' to '$normalizedAfter'";
        },
        STATUS_COMPLEX => function ($propertyData, $fullPath) {
            return getPropertiesRows($propertyData['children'], $fullPath);
        }
    ];

    return $statuses[$status];
}

function prepareValue($value)
{
    return is_array($value) ? 'complex value' : $value;
}
