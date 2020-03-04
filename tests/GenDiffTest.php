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
}
