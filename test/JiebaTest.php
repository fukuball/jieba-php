<?php
use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\JiebaAnalyse;
use Fukuball\Jieba\Posseg;

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

    public function testPossegInit()
    {
        Posseg::init();
        $array_count = count(Posseg::$prob_start);
        $this->assertEquals(256, $array_count);
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

    public function testLoadUserDict()
    {

        $case_array = array(
            "李小福",
            "是",
            "创新办",
            "主任",
            "也",
            "是",
            "云计算",
            "方面",
            "的",
            "专家"
        );

        Jieba::loadUserDict(dirname(dirname(__FILE__)).'/src/dict/user_dict.txt');

        $seg_list = Jieba::cut("李小福是创新办主任也是云计算方面的专家");
        $this->assertEquals($case_array, $seg_list);

    }

    public function testPossegCut()
    {


        $case_array = array(
            array(
                "word" => "这",
                "tag" => "r"
            ),
            array(
                "word" => "是",
                "tag" => "v"
            ),
            array(
                "word" => "一个",
                "tag" => "m"
            ),
            array(
                "word" => "伸手不见五指",
                "tag" => "i"
            ),
            array(
                "word" => "的",
                "tag" => "uj"
            ),
            array(
                "word" => "黑夜",
                "tag" => "n"
            ),
            array(
                "word" => "。",
                "tag" => "w"
            ),
            array(
                "word" => "我",
                "tag" => "r"
            ),
            array(
                "word" => "叫",
                "tag" => "v"
            ),
            array(
                "word" => "孙悟空",
                "tag" => "nr"
            ),
            array(
                "word" => "，",
                "tag" => "w"
            ),
            array(
                "word" => "我",
                "tag" => "r"
            ),
            array(
                "word" => "爱",
                "tag" => "v"
            ),
            array(
                "word" => "北京",
                "tag" => "ns"
            ),
            array(
                "word" => "，",
                "tag" => "w"
            ),
            array(
                "word" => "我",
                "tag" => "r"
            ),
            array(
                "word" => "爱",
                "tag" => "v"
            ),
            array(
                "word" => "Python",
                "tag" => "eng"
            ),
            array(
                "word" => "和",
                "tag" => "c"
            ),
            array(
                "word" => "C++",
                "tag" => "eng"
            ),
            array(
                "word" => "。",
                "tag" => "w"
            )
        );

        $seg_list = Posseg::cut("这是一个伸手不见五指的黑夜。我叫孙悟空，我爱北京，我爱Python和C++。");

        $this->assertEquals($case_array, $seg_list);

    }

}
