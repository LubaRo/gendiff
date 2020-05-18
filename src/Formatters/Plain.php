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

function getPropertyRow($propertyData, $fullPath)
{
    ['status' => $status, 'value' => $value] = $propertyData;
    $fullName = implode('.', $fullPath);
    $formatter = getPropertyFormatter($status);

    return $formatter($fullName, $value, $fullPath);
}

function getPropertyFormatter($status)
{
    $statuses = [
        STATUS_NEW => function ($name, $value) {
            $nomalizedValue = prepareValue($value);
            return "Property '$name' was added with value: '$nomalizedValue'";
        },
        STATUS_REMOVED => function ($name) {
            return "Property '$name' was removed";
        },
        STATUS_UNCHANGED => function () {
            return null;
        },
        STATUS_CHANGED => function ($name, $value) {
            ['before' => $before, 'after' => $after] = $value;
            $normalizedBefore = prepareValue($before);
            $normalizedAfter = prepareValue($after);

            return "Property '$name' was changed. From '$normalizedBefore' to '$normalizedAfter'";
        },
        STATUS_COMPLEX => function ($name, $value, $fullPath) {
            return getPropertiesRows($value, $fullPath);
        }
    ];

    return $statuses[$status];
}

function prepareValue($value)
{
    return is_array($value) ? 'complex value' : $value;
}
