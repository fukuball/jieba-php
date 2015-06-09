<?php
use Fukuball\Jieba;
use Fukuball\Finalseg;
use Fukuball\JiebaAnalyse;

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

    public function testJiebaAnalyseInit()
    {
        Jieba::init();
        JiebaAnalyse::init();
        $this->assertGreaterThan(0, JiebaAnalyse::$max_idf);

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

        $seg_list = Jieba::cut("我来到北京清华大学", true);
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

    public function testExtractTags()
    {
        $case_array = array(
            "是否"=>1.2196321889395,
            "一般"=>1.0032459890209,
            "肌迫"=>0.64654314660465,
            "怯懦"=>0.44762844339349,
            "藉口"=>0.32327157330233,
            "逼不得已"=>0.32327157330233,
            "不安全感"=>0.26548304656279,
            "同感"=>0.23929673812326,
            "有把握"=>0.21043366018744,
            "空洞"=>0.20598261709442
        );

        $top_k = 10;
        $content = file_get_contents(dirname(dirname(__FILE__))."/src/dict/lyric.txt", "r");

        $tags = JiebaAnalyse::extractTags($content, $top_k);
        $this->assertEquals($case_array, $tags);

    }
}
