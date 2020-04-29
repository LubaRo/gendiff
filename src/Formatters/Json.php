<?php

namespace Differ\Formatters\Json;

function format($data)
{
    $diff = getPropertiesData($data);
    $filtered = filterDiff($diff);
    $sorted = sortDiff($filtered);

    return json_encode($sorted);
}

function getPropertiesData($propertiesData, $path = [])
{
    $reduce  = function ($acc, $property) use (&$reduce, $path) {
        $children = $property['children'] ?? [];
        $name = $property['name'] ?? '';
        $status = $property['status'] ?? '';

        $newPath = [...$path, $name];

        if (!$children) {
            $formatted = getFormattedProperty($property, $newPath);

            $acc[$status][] = $formatted;
            return $acc;
        }

        $childrenData = getPropertiesData($children, $newPath);

        return array_merge($acc, $childrenData);
    };

    $result = array_reduce($propertiesData, function ($acc, $property) use ($reduce) {
        return $reduce($acc, $property);
    }, []);

    return $result;
}

function getFormattedProperty($propertyData, $path)
{
    $fullName = implode('/', $path);

    if ($propertyData['status'] === 'changed') {
        ['before' => $before, 'after' => $after] = $propertyData['value'];

        return [
            'path' => $fullName,
            'new_value' => $after,
            'old_value' => $before
        ];
    }

    return [
        'path' => $fullName,
        'value' => $propertyData['value']
    ];
}

function filterDiff($diff)
{
    $displayedStatuses = ['changed', 'removed', 'added'];

    return array_filter($diff, function ($status) use ($displayedStatuses) {
        return in_array($status, $displayedStatuses);
    }, ARRAY_FILTER_USE_KEY);
}

function sortDiff($diff)
{
    $sortOrder = [
        'changed' => 1,
        'removed' => 2,
        'added' => 3
    ];

    uksort($diff, function ($key1, $key2) use ($sortOrder) {
        $a = $sortOrder[$key1];
        $b = $sortOrder[$key2];
        return $a - $b;
    });

    return $diff;
}
