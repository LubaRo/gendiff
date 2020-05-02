<?php

namespace Differ\GenDiff;

use function Funct\Collection\flatten;
use function Differ\Parser\parse;
use function Differ\Formatter\getFormattedData;

define('DEFAULT_FORMAT', 'pretty');
define('STATUS_NEW', 'added');
define('STATUS_REMOVED', 'removed');
define('STATUS_CHANGED', 'changed');
define('STATUS_UNCHANGED', 'unchanged');

function getFileData($filePath)
{
    $data = file_get_contents($filePath);
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);

    return parse($data, $extension);
}

function genDiff($filePath1, $filePath2, $format = DEFAULT_FORMAT)
{
    $fileContent1 = (array) getFileData($filePath1);
    $fileContent2 = (array) getFileData($filePath2);

    $diff = buildAst($fileContent1, $fileContent2);
    $formatResult = getFormattedData($diff, $format);

    return $formatResult;
}

function buildAst(array $data1, array $data2): array
{
    $both_files_properties = array_merge(array_keys($data1), array_keys($data2));
    $properties_list = getUniqueValues($both_files_properties);

    $ast = array_map(function ($key) use ($data1, $data2) {
        $common = ['name' => $key];

        if (!isset($data2[$key])) {
            return array_merge($common, [
                'status' => STATUS_REMOVED,
                'value' => $data1[$key]
            ]);
        }
        if (!isset($data1[$key])) {
            return array_merge($common, [
                'status' => STATUS_NEW,
                'value' => $data2[$key]
            ]);
        }
        if (is_array($data1[$key]) && is_array($data1[$key])) {
            return array_merge($common, [
                'children' =>  buildAst($data1[$key], $data2[$key])
            ]);
        }
        if ($data1[$key] === $data2[$key]) {
            return array_merge($common, [
                'status' => STATUS_UNCHANGED,
                'value' => $data1[$key]
            ]);
        }

        return array_merge($common, [
            'status' => STATUS_CHANGED,
            'value' => [
                'before' => $data1[$key],
                'after' => $data2[$key]
            ]
        ]);
    }, $properties_list);

    return $ast;
}

function getUniqueValues($properties_list)
{
    return array_values(array_unique($properties_list));
}
