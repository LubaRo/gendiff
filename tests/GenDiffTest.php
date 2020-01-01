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
        $this->expectedResult = file_get_contents($this->files_dir . 'expected.json');
    }

    public function testGenDiff()
    {
        $beforeFilePath = $this->files_dir . 'before.json';
        $afterFilePath = $this->files_dir . 'after.json';

        $this->assertEquals($this->expectedResult, GenDiff\genDiff($beforeFilePath, $afterFilePath));
    }
}
