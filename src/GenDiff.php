<?php

namespace Differ\GenDiff;

use function Funct\Collection\flatten;
use function Differ\Parser\parse;
use function Differ\Formatter\getFormattedData;

const FORMAT_PRETTY = 'pretty';
const FORMAT_PLAIN = 'plain';
const FORMAT_JSON = 'json';
const DEFAULT_FORMAT = FORMAT_PRETTY;

const STATUS_NEW = 'added';
const STATUS_REMOVED = 'removed';
const STATUS_CHANGED = 'changed';
const STATUS_UNCHANGED = 'unchanged';

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
    $bothFilesProperties = array_merge(array_keys($data1), array_keys($data2));
    $propertiesList = getUniqueValues($bothFilesProperties);

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
    }, $propertiesList);

    return $ast;
}

function getUniqueValues($arr)
{
    return array_values(array_unique($arr));
}
