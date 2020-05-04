<?php

namespace Differ;

use PHPUnit\Framework\TestCase;
use Differ\GenDiff;

use const Differ\GenDiff\{STATUS_NEW, STATUS_REMOVED, STATUS_CHANGED, STATUS_UNCHANGED};

class GenDiffTest extends TestCase
{
    public $expected = '';
    public $dirPath = '';

    protected function setUp(): void
    {
        $this->dirPath = __DIR__ . '/fixtures/';
    }

    public function getFullPath($fileName)
    {
        return "{$this->dirPath}{$fileName}";
    }

    /**
     * @dataProvider filesDataProvider
     */
    public function testGenDiffWithExpectedFixture($fileNameBefore, $fileNameAfter, $fileNameExpected, $format = '')
    {
        $before = self::getFullPath($fileNameBefore);
        $after = self::getFullPath($fileNameAfter);
        $pathExpected = self::getFullPath($fileNameExpected);

        $expected = file_get_contents($pathExpected);
        $received = $format ? GenDiff\genDiff($before, $after, $format) : GenDiff\genDiff($before, $after);

        $this->assertSame($expected, $received);
    }

    public function filesDataProvider()
    {
        return [
            'plain files: json'  => [
                'before.json',
                'after.json',
                'expected.txt'
            ],
            'plain files: yaml' => [
                'before.yaml',
                'after.yaml',
                'expected.txt'
            ],
            'recurse json' => [
                'recurse_before.json',
                'recurse_after.json',
                'recurse_expected.txt'
            ],
            'recurse yaml' => [
                'recurse_before.yaml',
                'recurse_after.yaml',
                'recurse_expected.txt'
            ],
            'plain report: json' => [
                'recurse_before.json',
                'recurse_after.json',
                'plain_report_expected.txt',
                'plain'
            ],
            'plain report: yaml' => [
                'recurse_before.yaml',
                'recurse_after.yaml',
                'plain_report_expected.txt',
                'plain'
            ]
        ];
    }

    /**
     * @dataProvider jsonReportProvider
     */
    public function testGenDiffJsonReport($fileNameBefore, $fileNameAfter, $expected)
    {
        $pathBefore = self::getFullPath($fileNameBefore);
        $pathAfter = self::getFullPath($fileNameAfter);
        $received = GenDiff\genDiff($pathBefore, $pathAfter, 'json');

        $this->assertSame($expected, $received);
    }

    public function jsonReportProvider()
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

        return [
            'json report: json' => [
                'recurse_before.json',
                'recurse_after.json',
                $expected
            ],
            'json report: yaml' => [
                'recurse_before.yaml',
                'recurse_after.yaml',
                $expected
            ]
        ];
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
