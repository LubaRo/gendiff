<?php

namespace Differ\GenDiff;

use Docopt;

use function Differ\Parser\parseFile;
use function Differ\Formatter\getFormatter;

define('VERSION', '1.0');
define('DEFAULT_FORMAT', 'pretty');
define('IDENTATION_SIZE', 4);

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

    $formatResult = genDiff($filePath1, $filePath2, $format);

    echo (PHP_EOL . $formatResult . PHP_EOL);
}

function genDiff($filePath1, $filePath2, $format = DEFAULT_FORMAT)
{
    $fileContent1 = (array) parseFile($filePath1);
    $fileContent2 = (array) parseFile($filePath2);

    $diff = findDifference($fileContent1, $fileContent2);
    $formatResult = formatDifference($diff, $format);

    return $formatResult;
}

function findDifference(array $data1, array $data2): array
{
    $diff = array_reduce(array_keys($data1), function ($acc, $key) use ($data1, $data2) {
        if (!isset($data2[$key])) {
            $acc[$key] = array(
                'value' => is_string($data1[$key]) ? $data1[$key] : json_encode($data1[$key]),
                'status' => 'removed'
            );
        } elseif (json_encode($data1[$key]) === json_encode($data2[$key])) {
            $acc[$key] = array(
                'value' => is_string($data1[$key]) ? $data1[$key] : json_encode($data1[$key]),
                'status' => 'notChanged'
            );
        } else {
            if (gettype($data1[$key]) == 'object' || gettype($data2[$key]) == 'object') {
                $acc[$key] = findDifference((array) $data1[$key], (array) $data2[$key]);
            } else {
                $acc[$key] = array(
                    'valueBefore' =>  is_string($data1[$key]) ? $data1[$key] : json_encode($data1[$key]),
                    'valueAfter' =>  is_string($data2[$key]) ? $data2[$key] : json_encode($data2[$key]),
                    'status' => 'changed'
                );
            }
        }
        
        return $acc;
    }, []);

    $newValues = array_diff_key($data2, $data1);
    foreach ($newValues as $key => $value) {
        $diff[$key] = array(
            'value' => json_encode($value),
            'status' => 'new'
        );
    }

    return $diff;
}

function formatDifference($diff, $format)
{
    $formatter = getFormatter($format);
    return $formatter($diff);
}
