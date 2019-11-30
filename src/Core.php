<?php

namespace Gendiff\Core;

use Docopt;

function run()
{
    $doc = <<<'DOCOPT'
    Generate diff

    Usage:
      gendiff (-h|--help)
      gendiff (-v|--version)

    Options:
      -h --help                     Show this screen
      -v --version                  Show version

    DOCOPT;

    $result = Docopt::handle($doc, array('version' => '1.0.0'));

    foreach ($result as $k => $v) {
        echo $k . ': ' . json_encode($v) . PHP_EOL;
    }
}
