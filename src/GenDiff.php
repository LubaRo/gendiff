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
const STATUS_COMPLEX = 'complex';

function readFile($filePath)
{
    if (!is_readable($filePath)) {
        throw new \Exception("Cannot read file: '{$filePath}'");
    }
    $data = file_get_contents($filePath);

    return $data;
}

function getParsedData($filePath)
{
    $data = readFile($filePath);
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);
    $parsedData = parse($data, $extension);

    if (is_null($parsedData)) {
        throw new \Exception("Unable to parse correctly '{$filePath}'");
    }

    return $parsedData;
}

function genDiff($filePath1, $filePath2, $format = DEFAULT_FORMAT)
{
    $fileContent1 = getParsedData($filePath1);
    $fileContent2 = getParsedData($filePath2);

    $diff = buildAst($fileContent1, $fileContent2);
    $formatResult = getFormattedData($diff, $format);

    return $formatResult;
}

function getArrayUnion($arr1, $arr2)
{
    $allValues = array_merge($arr1, $arr2);
    return array_values(array_unique($allValues));
}

function buildAst(array $data1, array $data2): array
{
    $properties = getArrayUnion(array_keys($data1), array_keys($data2));

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
                'status' => STATUS_COMPLEX,
                'value' =>  buildAst($data1[$key], $data2[$key])
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
    }, $properties);

    return $ast;
}
