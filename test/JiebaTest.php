<?php

use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\JiebaAnalyse;
use Fukuball\Jieba\Posseg;
use PHPUnit\Framework\TestCase;

class JiebaTest extends TestCase
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
            "啊",
            "！"
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
            "来到",
            "北京",
            "清华",
            "清华大学",
            "华大",
            "大学"
        );

        $seg_list = Jieba::cut("我来到北京清华大学", true);
        $this->assertEquals($case_array, $seg_list);
    }

    public function testJiebaCutForSearch()
    {
        $case_array = array(
            "小",
            "明",
            "硕士",
            "毕业",
            "于",
            "中国",
            "科学",
            "学院",
            "科学院",
            "中国科学院",
            "计算",
            "计算所",
            "，",
            "后",
            "在",
            "日本",
            "京都",
            "大学",
            "日本京都大学",
            "深造"
        );

        $seg_list = Jieba::cutForSearch("小明硕士毕业于中国科学院计算所，后在日本京都大学深造");
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
            "所謂" => 1.0102620424985915,
            "是否" => 0.7386504806253521,
            "一般" => 0.60759968349154936,
            "沒有" => 0.33675401416619716,
            "肌迫" => 0.33675401416619716,
            "雖然" => 0.33675401416619716,
            "退縮" => 0.33675401416619716,
            "矯作" => 0.33675401416619716,
            "怯懦" => 0.27109891642140843
        );

        $top_k = 9;
        $content = file_get_contents(dirname(dirname(__FILE__)) . "/src/dict/lyric.txt", "r");

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

        Jieba::loadUserDict(dirname(dirname(__FILE__)) . '/src/dict/user_dict.txt');

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

    public function testClearCache()
    {
        Jieba::init();
        
        // Process some text to populate cache
        Jieba::cut("这是一个测试句子用来填充缓存");
        Jieba::cut("另一个测试句子");
        
        // Verify cache has some entries
        $stats_before = Jieba::getCacheStats();
        $this->assertGreaterThan(0, $stats_before['dag_cache_size']);
        
        // Clear cache
        Jieba::clearCache();
        
        // Verify cache is empty
        $stats_after = Jieba::getCacheStats();
        $this->assertEquals(0, $stats_after['dag_cache_size']);
    }

    public function testGetCacheStats()
    {
        Jieba::init();
        
        // Clear cache first
        Jieba::clearCache();
        
        // Get initial stats
        $stats = Jieba::getCacheStats();
        
        // Verify stats structure
        $this->assertIsArray($stats);
        $this->assertArrayHasKey('dag_cache_size', $stats);
        $this->assertArrayHasKey('trie_cache_size', $stats);
        $this->assertArrayHasKey('total_memory_usage', $stats);
        $this->assertArrayHasKey('peak_memory_usage', $stats);
        
        // Initially should be empty
        $this->assertEquals(0, $stats['dag_cache_size']);
        $this->assertGreaterThan(0, $stats['total_memory_usage']);
        $this->assertGreaterThan(0, $stats['peak_memory_usage']);
        
        // Process some text to populate cache
        Jieba::cut("测试缓存统计功能");
        
        // Get stats after processing
        $stats_after = Jieba::getCacheStats();
        $this->assertGreaterThan(0, $stats_after['dag_cache_size']);
    }

    public function testClearCacheIfNeeded()
    {
        Jieba::init();
        
        // Clear cache first
        Jieba::clearCache();
        
        // Test with very low limits to trigger cache clearing
        $result = Jieba::clearCacheIfNeeded(0, 0);
        $this->assertFalse($result); // Should not clear empty cache
        
        // Process some text to populate cache
        Jieba::cut("测试条件缓存清理功能");
        
        // Test with very low limits to trigger cache clearing
        $result = Jieba::clearCacheIfNeeded(0, 0);
        $this->assertTrue($result); // Should clear non-empty cache
        
        // Verify cache is empty after clearing
        $stats = Jieba::getCacheStats();
        $this->assertEquals(0, $stats['dag_cache_size']);
        
        // Test with high limits (should not trigger clearing)
        Jieba::cut("再次测试");
        $result = Jieba::clearCacheIfNeeded(100000, 100000);
        $this->assertFalse($result); // Should not clear
    }

    public function testCacheMemoryGrowth()
    {
        Jieba::init();
        Jieba::clearCache();
        
        $initial_stats = Jieba::getCacheStats();
        $initial_cache_size = $initial_stats['dag_cache_size'];
        
        // Process multiple different texts
        $texts = [
            "第一个测试文本用于缓存增长测试",
            "第二个不同的测试文本",
            "第三个完全不同的句子",
            "第四个用于验证缓存增长的文本",
            "第五个测试句子确保缓存持续增长"
        ];
        
        foreach ($texts as $text) {
            Jieba::cut($text);
        }
        
        $final_stats = Jieba::getCacheStats();
        $final_cache_size = $final_stats['dag_cache_size'];
        
        // Verify cache has grown
        $this->assertGreaterThan($initial_cache_size, $final_cache_size);
        $this->assertGreaterThan(0, $final_cache_size);
    }

    public function testCachePerformanceWithLargeText()
    {
        Jieba::init();
        Jieba::clearCache();
        
        // Create a large text for testing
        $large_text = str_repeat("这是一个用于测试大文本处理性能的句子。", 100);
        
        // First processing (cache miss)
        $start_time = microtime(true);
        Jieba::cut($large_text);
        $first_processing_time = microtime(true) - $start_time;
        
        // Second processing (cache hit)
        $start_time = microtime(true);
        Jieba::cut($large_text);
        $second_processing_time = microtime(true) - $start_time;
        
        // Verify cache provides performance benefit
        // Second processing should be faster due to cache hits
        $this->assertLessThan($first_processing_time, $second_processing_time);
        
        // Verify cache has entries
        $stats = Jieba::getCacheStats();
        $this->assertGreaterThan(0, $stats['dag_cache_size']);
    }

    public function testAutomaticCacheManagement()
    {
        Jieba::init();
        Jieba::clearCache();
        
        // Process multiple texts to populate cache
        for ($i = 0; $i < 10; $i++) {
            Jieba::cut("自动缓存管理测试句子编号" . $i);
        }
        
        // Get current cache size
        $stats = Jieba::getCacheStats();
        $cache_size = $stats['dag_cache_size'];
        
        // Test automatic cache management with very low limit
        $cleared = Jieba::clearCacheIfNeeded(1, 1);
        $this->assertTrue($cleared);
        
        // Verify cache was cleared
        $stats_after = Jieba::getCacheStats();
        $this->assertEquals(0, $stats_after['dag_cache_size']);
    }

    public function testAddWordWithPosTag()
    {
        Jieba::init();
        Posseg::init();
        
        // Test adding a word with POS tag
        Jieba::addWord('測試詞', 100, 'test_tag');
        
        // Verify the word was added to frequency dictionary
        $this->assertTrue(isset(Jieba::$original_freq['測試詞']));
        $this->assertEquals(100, Jieba::$original_freq['測試詞']);
        
        // Verify the POS tag was added to Posseg
        $this->assertTrue(isset(Posseg::$word_tag['測試詞']));
        $this->assertEquals('test_tag', Posseg::$word_tag['測試詞']);
    }

    public function testAddWordWithoutPosTag()
    {
        Jieba::init();
        Posseg::init();
        
        // Test adding a word without POS tag
        Jieba::addWord('無標籤詞', 100);
        
        // Verify the word was added to frequency dictionary
        $this->assertTrue(isset(Jieba::$original_freq['無標籤詞']));
        $this->assertEquals(100, Jieba::$original_freq['無標籤詞']);
        
        // Verify no POS tag was added
        $this->assertFalse(isset(Posseg::$word_tag['無標籤詞']));
    }

    public function testAddWordTagDirectly()
    {
        Posseg::init();
        
        // Test adding word tag directly
        Posseg::addWordTag('直接測試', 'direct_tag');
        
        // Verify it was added
        $this->assertTrue(isset(Posseg::$word_tag['直接測試']));
        $this->assertEquals('direct_tag', Posseg::$word_tag['直接測試']);
    }

    public function testPreservePunctuationOption()
    {
        Jieba::init();
        
        // Test sentence with punctuation
        $sentence = "这是一个测试！有标点符号，对吧？";
        
        // Test with preserve_punctuation = true (default behavior)
        $result_with_punct = Jieba::cut($sentence, false, array("preserve_punctuation" => true));
        $this->assertContains("！", $result_with_punct);
        $this->assertContains("，", $result_with_punct);
        $this->assertContains("？", $result_with_punct);
        
        // Test with preserve_punctuation = false
        $result_without_punct = Jieba::cut($sentence, false, array("preserve_punctuation" => false));
        $this->assertNotContains("！", $result_without_punct);
        $this->assertNotContains("，", $result_without_punct);
        $this->assertNotContains("？", $result_without_punct);
        
        // Test default behavior (should preserve punctuation)
        $result_default = Jieba::cut($sentence);
        $this->assertContains("！", $result_default);
        $this->assertContains("，", $result_default);
        $this->assertContains("？", $result_default);
    }

    public function testPreservePunctuationInCutForSearch()
    {
        Jieba::init();
        
        // Test sentence with punctuation
        $sentence = "小明硕士毕业于中国科学院计算所，后在日本京都大学深造。";
        
        // Test with preserve_punctuation = true
        $result_with_punct = Jieba::cutForSearch($sentence, array("preserve_punctuation" => true));
        $this->assertContains("，", $result_with_punct);
        $this->assertContains("。", $result_with_punct);
        
        // Test with preserve_punctuation = false
        $result_without_punct = Jieba::cutForSearch($sentence, array("preserve_punctuation" => false));
        $this->assertNotContains("，", $result_without_punct);
        $this->assertNotContains("。", $result_without_punct);
    }

    public function testPreservePunctuationInTokenize()
    {
        Jieba::init();
        
        // Test sentence with punctuation
        $sentence = "测试！标点符号。";
        
        // Test with preserve_punctuation = true
        $result_with_punct = Jieba::tokenize($sentence, array("preserve_punctuation" => true));
        $words_with_punct = array_column($result_with_punct, 'word');
        $this->assertContains("！", $words_with_punct);
        $this->assertContains("。", $words_with_punct);
        
        // Test with preserve_punctuation = false
        $result_without_punct = Jieba::tokenize($sentence, array("preserve_punctuation" => false));
        $words_without_punct = array_column($result_without_punct, 'word');
        $this->assertNotContains("！", $words_without_punct);
        $this->assertNotContains("。", $words_without_punct);
    }

    public function testPossegCutDecimalAndPercentage()
    {
        Jieba::init();
        Finalseg::init();
        Posseg::init();
        
        // Test decimal numbers
        $decimal_cases = array(
            "3.14" => array(
                array("word" => "3.14", "tag" => "m")
            ),
            "價格是3.14元" => array(
                array("word" => "價格", "tag" => "n"),
                array("word" => "是", "tag" => "v"),
                array("word" => "3.14", "tag" => "m"),
                array("word" => "元", "tag" => "m")
            ),
            "123.456789" => array(
                array("word" => "123.456789", "tag" => "m")
            )
        );
        
        foreach ($decimal_cases as $sentence => $expected) {
            $result = Posseg::cut($sentence);
            $this->assertEquals($expected, $result, "Failed for sentence: " . $sentence);
        }
        
        // Test percentage
        $percentage_cases = array(
            "50%" => array(
                array("word" => "50%", "tag" => "m")
            ),
            "成功率為90.5%" => array(
                array("word" => "成功率", "tag" => "n"),
                array("word" => "為", "tag" => "zg"),
                array("word" => "90.5%", "tag" => "m")
            ),
            "增長了25%" => array(
                array("word" => "增長", "tag" => "v"),
                array("word" => "了", "tag" => "ul"),
                array("word" => "25%", "tag" => "m")
            )
        );
        
        foreach ($percentage_cases as $sentence => $expected) {
            $result = Posseg::cut($sentence);
            $this->assertEquals($expected, $result, "Failed for sentence: " . $sentence);
        }
        
        // Test mixed numbers and percentages
        $mixed_cases = array(
            "從3.14增長到50%" => array(
                array("word" => "從", "tag" => "zg"),
                array("word" => "3.14", "tag" => "m"),
                array("word" => "增長", "tag" => "v"),
                array("word" => "到", "tag" => "v"),
                array("word" => "50%", "tag" => "m")
            ),
            "產品價格$99.99，銷售額增長了15.5%" => array(
                array("word" => "產品", "tag" => "n"),
                array("word" => "價格", "tag" => "n"),
                array("word" => "99.99", "tag" => "m"),
                array("word" => "，", "tag" => "w"),
                array("word" => "銷售", "tag" => "vn"),
                array("word" => "額", "tag" => "n"),
                array("word" => "增長", "tag" => "v"),
                array("word" => "了", "tag" => "ul"),
                array("word" => "15.5%", "tag" => "m")
            )
        );
        
        foreach ($mixed_cases as $sentence => $expected) {
            $result = Posseg::cut($sentence);
            $this->assertEquals($expected, $result, "Failed for sentence: " . $sentence);
        }
    }
}
