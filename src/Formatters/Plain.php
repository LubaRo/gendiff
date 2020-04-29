<?php

namespace Differ\Formatters\Plain;

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
        $children = $property['children'] ?? [];
        $name = $property['name'] ?? '';
        $newPath = [...$path, $name];

        if (!$children) {
            $newData = getPropertyRow($property, $newPath);
            $acc[] = $newData;
            return $acc;
        }

        $childrenData = getPropertiesRows($children, $newPath);

        return array_merge($acc, $childrenData);
    };

    $result = array_reduce($propertiesData, function ($acc, $property) use ($reduce) {
        return $reduce($acc, $property);
    }, []);

    return $result;
}

function getPropertyRow($propertyData, $fullPath)
{
    $status = $propertyData['status'] ?? '';
    $fullName = implode('.', $fullPath);
    $value = $propertyData['value'] ?? '';

    $format = getPropertyFormatter($status);
    return $format($fullName, $value);
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
            return '';
        },
        STATUS_CHANGED => function ($name, $value) {

            ['before' => $before, 'after' => $after] = $value;
            $normalizedBefore = prepareValue($before);
            $normalizedAfter = prepareValue($after);

            return "Property '$name' was changed. From '$normalizedBefore' to '$normalizedAfter'";
        }
    ];

    return $statuses[$status];
}

function prepareValue($value)
{
    return is_array($value) ? 'complex value' : $value;
}
