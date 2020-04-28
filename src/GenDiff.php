<?php

namespace Differ\GenDiff;

use Docopt;

use function Funct\Collection\flatten;
use function Differ\Parser\parse;
use function Differ\Formatter\getFormattedData;

define('VERSION', '1.0');
define('DEFAULT_FORMAT', 'pretty');

function run()
{
    list($filePath1, $filePath2, $format) = getInputData();
    $result = genDiff($filePath1, $filePath2, $format);

    echo (PHP_EOL . $result . PHP_EOL);
}

function getInputData()
{
    $doc = <<<'DOCOPT'
    Generate diff

    Usage:
      gendiff (-h|--help)
      gendiff (-v|--version)
      gendiff [--format <fmt>] <firstFile> <secondFile>

    Options:
      -h --help                     Show this screen
      -v --version                  Show version
      --format <fmt>                Report format [default: pretty]

DOCOPT;

    $data = Docopt::handle($doc, array('version' => VERSION));

    $format = $data['--format'] ?? DEFAULT_FORMAT;
    $filePath1 = $data['<firstFile>'] ?? '';
    $filePath2 = $data['<secondFile>'] ?? '';

    return [$filePath1, $filePath2, $format];
}

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
    $properties_list = removeDuplicateProperties($both_files_properties);

    $ast = array_map(function ($key) use ($data1, $data2) {
        $common = ['name' => $key];

        if (!isset($data2[$key])) {
            return array_merge($common, [
                'status' => 'removed',
                'value' => $data1[$key]
            ]);
        }
        if (!isset($data1[$key])) {
            return array_merge($common, [
                'status' => 'added',
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
                'status' => 'unchanged',
                'value' => $data1[$key]
            ]);
        }

        return array_merge($common, [
            'status' => 'changed',
            'value' => [
                'before' => $data1[$key],
                'after' => $data2[$key]
            ]
        ]);
    }, $properties_list);

    return $ast;
}

function removeDuplicateProperties($properties_list)
{
    return array_values(array_unique($properties_list));
}
