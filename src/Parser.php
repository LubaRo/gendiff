<?php

namespace Differ\Parser;

use Symfony\Component\Yaml\Yaml;

function parseFile($filePath)
{
    $file = readFile($filePath);
    $extension = pathinfo($filePath, PATHINFO_EXTENSION);

    if ($extension == 'json') {
        return json_decode($file, true);
    } elseif ($extension == 'yaml') {
        return Yaml::parse($file);
    }

    throw new \Exception("File extension '{$extension}' is incorrect or not supported\n");
}

function readFile($path)
{
    if (!file_exists($path)) {
        throw new \Exception("File '${path}' doesn't exist\n");
    }

    return file_get_contents($path);
}
