<?php

namespace Gendiff\Core;

use Docopt;
use Funct\Strings;

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

    $file1 = file_get_contents($filePath1);
    $file2 = file_get_contents($filePath2);

    $fileContent1 = json_decode($file1, true);
    $fileContent2 = json_decode($file2, true);

    $diff = genDiff($fileContent1, $fileContent2);
    $formatResult = formatResult($diff);

    echo (PHP_EOL . $formatResult . PHP_EOL);
}

function genDiff($fileContent1, $fileContent2)
{
    $result = array();

    foreach ($fileContent1 as $key => $value) {
        if (isset($fileContent2[$key])) {
            if ($value === $fileContent2[$key]) {
                $result[$key] = array(
                    'value' => $value,
                    'status' => 'notChanged'
                );
            } else {
                $result[$key] = array(
                    'valueBefore' => $value,
                    'valueAfter' => $fileContent2[$key],
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
