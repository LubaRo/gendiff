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
        $this->expectedResult = file_get_contents($this->files_dir . 'expected.txt');
    }

    public function testGenDiff()
    {
        $beforeJson = $this->files_dir . 'before.json';
        $afterJson = $this->files_dir . 'after.json';

        $this->assertEquals($this->expectedResult, GenDiff\genDiff($beforeJson, $afterJson));

        $beforeYaml = $this->files_dir . 'before.yaml';
        $afterYaml = $this->files_dir . 'after.yaml';

        $this->assertEquals($this->expectedResult, GenDiff\genDiff($beforeYaml, $afterYaml));
    }
}
