<?php
use Fukuball\Jieba;
use Fukuball\Finalseg;

class JiebaTest extends PHPUnit_Framework_TestCase
{

    public function testJiebaInit()
    {
        Jieba::init();
        $this->assertGreaterThan(0, Jieba::$total);
    }

    public function testFinalsegInit()
    {
        Finalseg::init();
        $array_count = count(Finalseg::$prob_start);
        $this->assertEquals(4, $array_count);
    }

    public function testJiebaCut()
    {
        $case_array = array(
            "怜香惜玉",
            "也",
            "得",
            "要",
            "看",
            "对象",
            "啊"
        );

        $seg_list = Jieba::cut("怜香惜玉也得要看对象啊！");
        $this->assertEquals($case_array, $seg_list);

        $case_array = array(
            "我",
            "来到",
            "北京",
            "清华大学"
        );

        $seg_list = Jieba::cut("我来到北京清华大学");
        $this->assertEquals($case_array, $seg_list);

        $case_array = array(
            "他",
            "来到",
            "了",
            "网易",
            "杭研",
            "大厦"
        );

        $seg_list = Jieba::cut("他来到了网易杭研大厦");
        $this->assertEquals($case_array, $seg_list);

    }

    public function testJiebaCutAll()
    {

        $case_array = array(
            "我",
            "来",
            "来到",
            "到",
            "北",
            "北京",
            "京",
            "清",
            "清华",
            "清华大学",
            "华",
            "华大",
            "大",
            "大学",
            "学"
        );

        $seg_list = Jieba::cut("我来到北京清华大学");
        $this->assertEquals($case_array, $seg_list);

    }

    public function testFinalsegCut()
    {
        $case_array = array(
            "怜香惜",
            "玉",
            "也",
            "得",
            "要",
            "看",
            "对象",
            "啊"
        );

        $seg_list = Finalseg::cut("怜香惜玉也得要看对象啊！");
        $this->assertEquals($case_array, $seg_list);
    }
}
