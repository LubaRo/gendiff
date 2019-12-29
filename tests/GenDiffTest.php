<?php

namespace Differ;

use \PHPUnit\Framework\TestCase;
use Differ\GenDiff;

class GenDiffTest extends TestCase
{
    public function testGenDiff()
    {
        $this->assertEquals(GenDiff\genDiff("/home/luba/test_files/1.json", "/home/luba/test_files/2.json"), "{
 + timeout: 30
 - timeout: 20
}");
    }
}
