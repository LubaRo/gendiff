<?php

namespace Differ\Formatters\Pretty;

use Funct\Strings;

function getFormater()
{
    $formatter = function ($data, $nestedLevel = 0) use (&$formatter) {
        if (empty($data)) {
            return '{}';
        }

        $result = array();

        $identation = Strings\times(' ', IDENTATION_SIZE);
        $leftIdentation = Strings\times($identation, $nestedLevel);

        if (empty($nestedLevel)) {
            $result[] = $leftIdentation . '{';
        }

        foreach ($data as $key => $data) {
            if (is_array($data) && !isset($data['status'])) {
                $result[] = $leftIdentation . $identation . "$key: {";
                $result[] = $formatter($data, $nestedLevel + 1);
            } else {
                switch ($data['status']) {
                    case 'notChanged':
                        $result[] = formatResultRow($leftIdentation, ' ', $key, $data['value']);
                        break;
                    case 'changed':
                        $result[] = formatResultRow($leftIdentation, '+', $key, $data['valueAfter']);
                        $result[] = formatResultRow($leftIdentation, '-', $key, $data['valueBefore']);
                        break;
                    case 'removed':
                        $result[] = formatResultRow($leftIdentation, '-', $key, $data['value']);
                        break;
                    case 'new':
                        $result[] = formatResultRow($leftIdentation, '+', $key, $data['value']);
                        break;
                }
            }
        }

        $result[] = $leftIdentation . '}';

        $str = Strings\toSentence($result, PHP_EOL, PHP_EOL);

        return $str;
    };
    return $formatter;
}

function formatResultRow($leftIdentation, $operator, $key, $value)
{
    $decodedValue = json_decode($value, true);

    if (!is_array($decodedValue)) {
        $valueData = is_string($decodedValue) ? $decodedValue : $value;
        $result = "$leftIdentation  $operator $key: $valueData";
    } else {
        $tmp[] = "$leftIdentation  $operator $key: {";

        foreach ($decodedValue as $k => $v) {
            $tmp[] = $leftIdentation . Strings\times(' ', IDENTATION_SIZE * 2) . $k . ': ' . $v;
        }

        $tmp[] = $leftIdentation . Strings\times(' ', IDENTATION_SIZE) . '}';

        $result = Strings\toSentence($tmp, PHP_EOL, PHP_EOL);
    }

    return $result;
}
