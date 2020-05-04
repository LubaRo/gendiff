<?php

namespace Differ;

use PHPUnit\Framework\TestCase;
use Differ\GenDiff;

use const Differ\GenDiff\{STATUS_NEW, STATUS_REMOVED, STATUS_CHANGED, STATUS_UNCHANGED};
use const Differ\GenDiff\{FORMAT_PRETTY, FORMAT_PLAIN, FORMAT_JSON};

class GenDiffTest extends TestCase
{
    public $dirPath = '';

    protected function setUp(): void
    {
        $this->dirPath = __DIR__ . '/fixtures/';
        $this->prepareJsonReportFixture();
    }

    protected function prepareJsonReportFixture()
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
        $result = json_encode($arr);
        $filePath = self::getFixturesDirPath('json_report_expected.txt');
        file_put_contents($filePath, $result);
    }

    public function getFixturesDirPath($fileName)
    {
        return "{$this->dirPath}{$fileName}";
    }

    /**
     * @dataProvider genDiffDataProvider
     */
    public function testGenDiff($fileNameBefore, $fileNameAfter, $fileNameExpected, $format)
    {
        $before = self::getFixturesDirPath($fileNameBefore);
        $after = self::getFixturesDirPath($fileNameAfter);
        $pathExpected = self::getFixturesDirPath($fileNameExpected);

        $expected = file_get_contents($pathExpected);
        $received = GenDiff\genDiff($before, $after, $format);

        $this->assertSame($expected, $received);
    }

    public function genDiffDataProvider()
    {
        return [
            'plain files: json'  => [
                'before.json',
                'after.json',
                'expected.txt',
                FORMAT_PRETTY
            ],
            'plain files: yaml' => [
                'before.yaml',
                'after.yaml',
                'expected.txt',
                FORMAT_PRETTY
            ],
            'recurse json' => [
                'recurse_before.json',
                'recurse_after.json',
                'recurse_expected.txt',
                FORMAT_PRETTY
            ],
            'recurse yaml' => [
                'recurse_before.yaml',
                'recurse_after.yaml',
                'recurse_expected.txt',
                FORMAT_PRETTY
            ],
            'plain report: json' => [
                'recurse_before.json',
                'recurse_after.json',
                'plain_report_expected.txt',
                FORMAT_PLAIN
            ],
            'plain report: yaml' => [
                'recurse_before.yaml',
                'recurse_after.yaml',
                'plain_report_expected.txt',
                 FORMAT_PLAIN
            ],
            'json report: json' => [
                'recurse_before.json',
                'recurse_after.json',
                'json_report_expected.txt',
                FORMAT_JSON
            ],
            'json report: yaml' => [
                'recurse_before.yaml',
                'recurse_after.yaml',
                'json_report_expected.txt',
                FORMAT_JSON
            ]
        ];
    }

    public function testParserException()
    {
        $this->expectExceptionMessage("Unknown format: 'invalid_format'.");

        $beforeJson = self::getFixturesDirPath('before.json');
        $afterJson = self::getFixturesDirPath('after.json');

        GenDiff\genDiff($beforeJson, $afterJson, 'invalid_format');
    }

    public function testFormatterException()
    {
        $this->expectExceptionMessage("File extension 'zz' is incorrect or not supported.");

        $beforeJson = self::getFixturesDirPath('before.json');
        $afterJson = self::getFixturesDirPath('wrong_extention.zz');

        GenDiff\genDiff($beforeJson, $afterJson);
    }
}
