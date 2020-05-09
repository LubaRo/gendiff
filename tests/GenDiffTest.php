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
    }

    public function getFixtureFullPath($fileName)
    {
        return "{$this->dirPath}{$fileName}";
    }

    public function getTestFilePath($prefix, $extension)
    {
        $fileName = "{$prefix}.{$extension}";
        return self::getFixtureFullPath($fileName);
    }
    public function getExpectedFilePath($reportFormat)
    {
        $fileName = "expected/{$reportFormat}_report.txt";
        return self::getFixtureFullPath($fileName);
    }

    /**
     * @dataProvider genDiffDataProvider
     */
    public function testGenDiff($fileExt, $reportFormat)
    {
        $pathBefore = self::getTestFilePath('before', $fileExt);
        $pathAfter = self::getTestFilePath('after', $fileExt);
        $pathExpected = self::getExpectedFilePath($reportFormat);

        $expected = file_get_contents($pathExpected);
        $received = GenDiff\genDiff($pathBefore, $pathAfter, $reportFormat);

        $this->assertSame($expected, $received);
    }

    public function genDiffDataProvider()
    {
        return [
            'pretty report: json' => ['json', FORMAT_PRETTY],
            'pretty report: yaml' => ['yaml', FORMAT_PRETTY],

            'plain report: json' => ['json', FORMAT_PLAIN],
            'plain report: yaml' => ['yaml', FORMAT_PLAIN],

            'json report: json' => ['json', FORMAT_JSON],
            'json report: yaml' => ['yaml', FORMAT_JSON]
        ];
    }

    public function testParserException()
    {
        $this->expectExceptionMessage("Unknown format: 'invalid_format'.");

        $pathBefore = self::getTestFilePath('before', 'json');
        $pathAfter = self::getTestFilePath('after', 'json');

        GenDiff\genDiff($pathBefore, $pathAfter, 'invalid_format');
    }

    public function testFormatterTypeException()
    {
        $this->expectExceptionMessage("Data type 'zz' is incorrect or not supported.");

        $pathBefore = self::getTestFilePath('before', 'json');
        $pathAfter = self::getTestFilePath('wrong_extention', 'zz');

        GenDiff\genDiff($pathBefore, $pathAfter);
    }

    public function testParseException()
    {
        $wrongFilePath = self::getTestFilePath('empty', 'json');
        $this->expectExceptionMessage("Unable to parse correctly '{$wrongFilePath}'");

        GenDiff\genDiff($wrongFilePath, $wrongFilePath);
    }

    public function testFileNotExistsException()
    {
        $this->expectExceptionMessage("File 'notExists' does not exist");

        GenDiff\genDiff('notExists', 'notExists');
    }
}
