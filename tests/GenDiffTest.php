<?php

namespace Differ;

use PHPUnit\Framework\TestCase;
use Differ\GenDiff;

class GenDiffTest extends TestCase
{
    public $expected = '';
    public $files_dir = '';

    protected function setUp(): void
    {
        $this->files_dir = __DIR__ . '/fixtures/';
    }

    public function testGenDiffJson()
    {
        $beforeJson = $this->files_dir . 'before.json';
        $afterJson = $this->files_dir . 'after.json';

        $expected = file_get_contents($this->files_dir . 'expected.txt');

        $this->assertEquals($expected, GenDiff\genDiff($beforeJson, $afterJson));
    }

    public function testGenDiffYaml()
    {
        $beforeYaml = $this->files_dir . 'before.yaml';
        $afterYaml = $this->files_dir . 'after.yaml';

        $expected = file_get_contents($this->files_dir . 'expected.txt');

        $this->assertEquals($expected, GenDiff\genDiff($beforeYaml, $afterYaml));
    }

    public function testGenDiffJsonRecurse()
    {
        $beforeJson = $this->files_dir . 'recurse_before.json';
        $afterJson = $this->files_dir . 'recurse_after.json';

        $expected = file_get_contents($this->files_dir . 'recurse_expected.txt');

        $this->assertEquals($expected, GenDiff\genDiff($beforeJson, $afterJson));
    }

    public function testGenDiffYamlRecurse()
    {
        $beforeYaml = $this->files_dir . 'recurse_before.yaml';
        $afterYaml = $this->files_dir . 'recurse_after.yaml';

        $expected = file_get_contents($this->files_dir . 'recurse_expected.txt');

        $this->assertEquals($expected, GenDiff\genDiff($beforeYaml, $afterYaml));
    }

    public function testGenDiffPlainJson()
    {
        $beforeJson = $this->files_dir . 'recurse_before.json';
        $afterJson = $this->files_dir . 'recurse_after.json';

        $expected = file_get_contents($this->files_dir . 'plain_expected.txt');
        $result = GenDiff\genDiff($beforeJson, $afterJson, 'plain');

        $this->assertEquals($expected, $result);
    }

    public function testGenDiffPlainYaml()
    {
        $beforeJson = $this->files_dir . 'recurse_before.yaml';
        $afterJson = $this->files_dir . 'recurse_after.yaml';

        $expected = file_get_contents($this->files_dir . 'plain_expected.txt');
        $result = GenDiff\genDiff($beforeJson, $afterJson, 'plain');

        $this->assertEquals($expected, $result);
    }

    public function testParserException()
    {
        $this->expectExceptionMessage("Unknown format: 'invalid_format'.");

        $beforeJson = $this->files_dir . 'before.json';
        $afterJson = $this->files_dir . 'after.json';

        GenDiff\genDiff($beforeJson, $afterJson, 'invalid_format');
    }

    public function testFormatterException()
    {
        $this->expectExceptionMessage("File extension 'zz' is incorrect or not supported.");

        $beforeJson = $this->files_dir . 'before.json';
        $afterJson = $this->files_dir . 'wrong_extention.zz';

        GenDiff\genDiff($beforeJson, $afterJson);
    }
}
