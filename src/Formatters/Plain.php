<?php

namespace Differ\Formatters\Plain;

use const Differ\GenDiff\{STATUS_NEW, STATUS_REMOVED, STATUS_CHANGED, STATUS_UNCHANGED, STATUS_COMPLEX};

function format($data, $path = [])
{
    $reduce  = function ($acc, $property) use (&$reduce, $path) {
        $name = $property['name'];
        $newPath = [...$path, $name];

        $formattedProperty = getPropertyRow($property, $newPath);
        $newAcc = is_array($formattedProperty) ? array_merge($acc, $formattedProperty) : [...$acc, $formattedProperty];

        return $newAcc;
    };

    $propertiesRows = array_reduce($data, function ($acc, $property) use ($reduce) {
        return $reduce($acc, $property);
    }, []);

    $filteredRows = array_filter($propertiesRows, function ($elem) {
        return !empty($elem);
    });

    return implode("\n", $filteredRows);
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

function prepareValue($value)
{
    return is_array($value) ? 'complex value' : $value;
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
            return format($propertyData['children'], $fullPath);
        }
    ];

    return $statuses[$status];
}
