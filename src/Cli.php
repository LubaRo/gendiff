<?php

namespace Differ\Cli;

use Docopt;

const VERSION = '1.0';

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

    return [$data['<firstFile>'], $data['<secondFile>'], $data['--format']];
}
