<?php

namespace Differ;

use PHPUnit\Framework\TestCase;
use Differ\GenDiff;

class GenDiffTest extends TestCase
{
    public $expected = '';
    public $dirPath = '';

    protected function setUp(): void
    {
        $this->dirPath = __DIR__ . '/fixtures/';
    }

    public function testGenDiffJson()
    {
        $beforeJson = $this->dirPath . 'before.json';
        $afterJson = $this->dirPath . 'after.json';

        $expected = file_get_contents($this->dirPath . 'expected.txt');

        $this->assertEquals($expected, GenDiff\genDiff($beforeJson, $afterJson));
    }

    public function testGenDiffYaml()
    {
        $beforeYaml = $this->dirPath . 'before.yaml';
        $afterYaml = $this->dirPath . 'after.yaml';

        $expected = file_get_contents($this->dirPath . 'expected.txt');

        $this->assertEquals($expected, GenDiff\genDiff($beforeYaml, $afterYaml));
    }

    public function testGenDiffJsonRecurse()
    {
        $beforeJson = $this->dirPath . 'recurse_before.json';
        $afterJson = $this->dirPath . 'recurse_after.json';

        $expected = file_get_contents($this->dirPath . 'recurse_expected.txt');

        $this->assertEquals($expected, GenDiff\genDiff($beforeJson, $afterJson));
    }

    public function testGenDiffYamlRecurse()
    {
        $beforeYaml = $this->dirPath . 'recurse_before.yaml';
        $afterYaml = $this->dirPath . 'recurse_after.yaml';

        $expected = file_get_contents($this->dirPath . 'recurse_expected.txt');

        $this->assertEquals($expected, GenDiff\genDiff($beforeYaml, $afterYaml));
    }

    public function testGenDiffPlainReport()
    {
        $expected = file_get_contents($this->dirPath . 'plain_report_expected.txt');

        $beforeJson = $this->dirPath . 'recurse_before.json';
        $afterJson = $this->dirPath . 'recurse_after.json';
        $result1 = GenDiff\genDiff($beforeJson, $afterJson, 'plain');
        $this->assertEquals($expected, $result1);

        $beforeYaml = $this->dirPath . 'recurse_before.yaml';
        $afterYaml = $this->dirPath . 'recurse_after.yaml';
        $result2 = GenDiff\genDiff($beforeJson, $afterJson, 'plain');
        $this->assertEquals($expected, $result2);
    }

    public function testGenDiffJsonReport()
    {
        $arr = [
            STATUS_CHANGED => [
                [
                    'path' => 'group1/baz',
                    'new_value' => 'bars',
                    'old_value' => 'bas'
                ],
            ],
            STATUS_REMOVED => [
                [
                    'path' => 'common/setting2',
                    'value' => 200
                ],
                [
                    'path' => 'common/setting6',
                    'value' => [
                        'key' => 'value'
                    ]
                ],
                [
                    'path' => 'group2',
                    'value' => [
                        'abc' => 12345
                    ]
                ]
            ],
            STATUS_NEW => [
                [
                    'path' => 'common/setting4',
                    'value' => 'blah blah'
                ],
                [
                    'path' => 'common/setting5',
                    'value' => [
                        'key5' => 'value5'
                    ]
                ],
                [
                    'path' => 'group3',
                    'value' => [
                        'fee' => 100500
                    ]
                ]
            ]
        ];
        $expected = json_encode($arr);

        $beforeJson = $this->dirPath . 'recurse_before.json';
        $afterJson = $this->dirPath . 'recurse_after.json';
        $result1 = GenDiff\genDiff($beforeJson, $afterJson, 'json');

        $beforeYaml = $this->dirPath . 'recurse_before.yaml';
        $afterYaml = $this->dirPath . 'recurse_after.yaml';
        $result2 = GenDiff\genDiff($beforeJson, $afterJson, 'json');
        $this->assertEquals($expected, $result2);

        $this->assertSame($expected, $result1);
    }

    public function testParserException()
    {
        $this->expectExceptionMessage("Unknown format: 'invalid_format'.");

        $beforeJson = $this->dirPath . 'before.json';
        $afterJson = $this->dirPath . 'after.json';

        GenDiff\genDiff($beforeJson, $afterJson, 'invalid_format');
    }

    public function testFormatterException()
    {
        $this->expectExceptionMessage("File extension 'zz' is incorrect or not supported.");

        $beforeJson = $this->dirPath . 'before.json';
        $afterJson = $this->dirPath . 'wrong_extention.zz';

        GenDiff\genDiff($beforeJson, $afterJson);
    }
}
