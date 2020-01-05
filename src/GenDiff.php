<?php

namespace Differ\GenDiff;

use Docopt;
use Funct\Strings;

use function Differ\Parser\parseFile;

define('VERSION', '1.0');
define('DEFAULT_FORMAT', 'pretty');

function run()
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

    $format = isset($data['--format']) ? $data['--format'] : DEFAULT_FORMAT;

    $filePath1 = isset($data['<firstFile>']) ? $data['<firstFile>'] : '';
    $filePath2 = isset($data['<secondFile>']) ? $data['<secondFile>'] : '';

    $formatResult = genDiff($filePath1, $filePath2);

    echo (PHP_EOL . $formatResult . PHP_EOL);
}

function genDiff($filePath1, $filePath2)
{
    $fileContent1 = (array) parseFile($filePath1);
    $fileContent2 = (array) parseFile($filePath2);

    $diff = findDifference($fileContent1, $fileContent2);
    $formatResult = formatResult($diff);

    return $formatResult;
}

function findDifference(array $parsedData1, array $parsedData2): array
{
    $data1 = prepareData($parsedData1);
    $data2 = prepareData($parsedData2);

    $diff = array_reduce(array_keys($data1), function ($acc, $key) use ($data1, $data2) {
        if (!isset($data2[$key])) {
            $acc[$key] = array(
                'value' => $data1[$key],
                'status' => 'removed'
            );
        } elseif ($data1[$key] == $data2[$key]) {
            $acc[$key] = array(
                'value' => $data1[$key],
                'status' => 'notChanged'
            );
        } else {
            $acc[$key] = array(
                'valueBefore' => $data1[$key],
                'valueAfter' => $data2[$key],
                'status' => 'changed'
            );
        }
        
        return $acc;
    }, []);

    $newValues = array_diff_key($data2, $data1);
    foreach ($newValues as $key => $value) {
        $diff[$key] = array(
            'value' => $value,
            'status' => 'new'
        );
    }

    return $diff;
}

function formatResult($diff)
{
    if (empty($diff)) {
        return '{}';
    }

    $result = array();
    $result[] = '{';

    foreach ($diff as $key => $data) {
        switch ($data['status']) {
            case 'notChanged':
                $result[] = "   $key: {$data['value']}";
                break;
            case 'changed':
                $result[] = " + $key: {$data['valueAfter']}";
                $result[] = " - $key: {$data['valueBefore']}";
                break;
            case 'removed':
                $result[] = " - $key: {$data['value']}";
                break;
            case 'new':
                $result[] = " + $key: {$data['value']}";
                break;
        }
    }

    $result[] = '}';

    $str = Strings\toSentence($result, PHP_EOL, PHP_EOL);
    
    return $str;
}

function prepareData($data)
{
    foreach ($data as &$value) {
        if (is_bool($value) || is_null($value)) {
            $value = var_export($value, true);
        }
    }

    return $data;
}