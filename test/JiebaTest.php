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

    public function testJiebaCut()
    {
        $case_array = array(
            "怜香惜玉",
            "也",
            "得要",
            "看",
            "对象",
            "啊"
        );

        $seg_list = Jieba::cut("怜香惜玉也得要看对象啊！");
        $this->assertEquals($case_array, $seg_list);
    }
}
