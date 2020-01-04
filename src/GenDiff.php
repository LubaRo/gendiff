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
    $fileContent1 = parseFile($filePath1);
    $fileContent2 = parseFile($filePath2);

    $diff = findDifference($fileContent1, $fileContent2);
    $formatResult = formatResult($diff);

    return $formatResult;
}

function findDifference($fileContent1, $fileContent2)
{
    $result = array();

    foreach ($fileContent1 as $key => $value) {
        $value = is_bool($value) ? var_export($value, true) : $value;

        if (isset($fileContent2[$key])) {
            $value2 = is_bool($fileContent2[$key]) ? var_export($fileContent2[$key], true) : $fileContent2[$key];

            if ($value === $value2) {
                $result[$key] = array(
                    'value' => $value,
                    'status' => 'notChanged'
                );
            } else {
                $result[$key] = array(
                    'valueBefore' => $value,
                    'valueAfter' => $value2,
                    'status' => 'changed'
                );
            }
        } else {
            $result[$key] = array(
                'value' => $value,
                'status' => 'removed'
            );
        }
    }

    $newValues = array_diff_key($fileContent2, $fileContent1);
    foreach ($newValues as $key => $value) {
        $value = is_bool($value) ? var_export($value, true) : $value;

        $result[$key] = array(
            'value' => $value,
            'status' => 'new'
        );
    }

    return $result;
}

function formatResult(array $diff)
{
    if (empty($diff)) {
        return '{}';
    }

    $result = array('{');

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
