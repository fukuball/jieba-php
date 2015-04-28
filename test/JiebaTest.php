<?php
use Fukuball\Jieba;
use Fukuball\Finalseg;

class JiebaTest extends PHPUnit_Framework_TestCase
{

    public function testJiebaInit()
    {
        $this->assertGreaterThan(0, Jieba::$total);
    }

    public function testFinalsegInit()
    {
        $array_count = count(Finalseg::$prob_start);
        $this->assertEquals(4, $array_count);
    }

}