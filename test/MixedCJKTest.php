<?php

use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use PHPUnit\Framework\TestCase;

class MixedCJKTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        ini_set('memory_limit', '2048M');
        Jieba::init(array('dict' => 'small', 'cjk' => 'all'));
        Finalseg::init();
    }

    public function testKoreanTextSegmentation()
    {
        $korean_text = "안녕하세요 세계입니다";
        $result = Jieba::cut($korean_text, false);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        // Check that Korean characters are properly segmented
        $has_korean_chars = false;
        foreach ($result as $segment) {
            if (preg_match('/[\x{ac00}-\x{d7af}]/u', $segment)) {
                $has_korean_chars = true;
                break;
            }
        }
        $this->assertTrue($has_korean_chars, "Should contain Korean characters");
    }

    public function testJapaneseTextSegmentation()
    {
        $japanese_text = "私は日本に住んでいます";
        $result = Jieba::cut($japanese_text, false);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertContains("私", $result);
    }

    public function testChineseTextSegmentation()
    {
        $chinese_text = "我来到北京清华大学";
        $result = Jieba::cut($chinese_text, false);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertContains("我", $result);
        $this->assertContains("来到", $result);
        $this->assertContains("北京", $result);
    }

    public function testMixedCJKTextSegmentation()
    {
        $mixed_text = "我喜欢这个世界。私は日本に住んでいます。안녕하세요 세계입니다.";
        $result = Jieba::cut($mixed_text, false);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        // Check that all three languages are properly processed
        $contains_chinese = false;
        $contains_japanese = false;
        $contains_korean = false;

        foreach ($result as $segment) {
            if (preg_match('/[\x{4e00}-\x{9fff}]/u', $segment)) {
                $contains_chinese = true;
            }
            if (preg_match('/[\x{3040}-\x{309f}\x{30a0}-\x{30ff}]/u', $segment)) {
                $contains_japanese = true;
            }
            if (preg_match('/[\x{ac00}-\x{d7af}]/u', $segment)) {
                $contains_korean = true;
            }
        }

        $this->assertTrue($contains_chinese, "Should contain Chinese characters");
        $this->assertTrue($contains_japanese, "Should contain Japanese characters");
        $this->assertTrue($contains_korean, "Should contain Korean characters");
    }

    public function testComplexMixedTextSegmentation()
    {
        $complex_mixed = "今天很好，私たちは공원에 갔습니다。";
        $result = Jieba::cut($complex_mixed, false);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        // Check that mixed language text is properly segmented
        $has_chinese = false;
        $has_japanese = false;
        $has_korean = false;

        $all_segments = implode('', $result);

        if (preg_match('/[\x{4e00}-\x{9fff}]/u', $all_segments)) {
            $has_chinese = true;
        }
        if (preg_match('/[\x{3040}-\x{309f}\x{30a0}-\x{30ff}]/u', $all_segments)) {
            $has_japanese = true;
        }
        if (preg_match('/[\x{ac00}-\x{d7af}]/u', $all_segments)) {
            $has_korean = true;
        }

        $this->assertTrue($has_chinese, "Should contain Chinese characters");
        $this->assertTrue($has_japanese, "Should contain Japanese characters");
        $this->assertTrue($has_korean, "Should contain Korean characters");
    }

    public function testCutAllModeWithMixedCJK()
    {
        $mixed_text = "我喜欢这个世界。私は日本に住んでいます。";
        $result_default = Jieba::cut($mixed_text, false);
        $result_full = Jieba::cut($mixed_text, true);

        $this->assertIsArray($result_default);
        $this->assertIsArray($result_full);
        $this->assertNotEmpty($result_default);
        $this->assertNotEmpty($result_full);

        // Full mode should generally produce more segments
        $this->assertGreaterThanOrEqual(count($result_default), count($result_full));
    }

    public function testEmptyStringHandling()
    {
        $empty_text = "";
        $result = Jieba::cut($empty_text, false);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    public function testWhitespaceOnlyHandling()
    {
        $whitespace_text = "   \t\n   ";
        $result = Jieba::cut($whitespace_text, false);

        $this->assertIsArray($result);
        // Should either be empty or contain only whitespace segments
        foreach ($result as $segment) {
            $this->assertTrue(ctype_space($segment) || empty($segment));
        }
    }

    public function testKoreanOnlyText()
    {
        $korean_only = "저는 서울대학교에서 한국어를 공부하고 있습니다";
        $result = Jieba::cut($korean_only, false);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        // Verify all segments contain Korean characters or punctuation
        foreach ($result as $segment) {
            if (!empty(trim($segment))) {
                $this->assertTrue(
                    preg_match('/[\x{ac00}-\x{d7af}\x{1100}-\x{11ff}\x{3130}-\x{318f}\x{a960}-\x{a97f}\x{d7b0}-\x{d7ff}]/u', $segment) ||
                        preg_match('/[\s\p{P}]/u', $segment),
                    "Segment '$segment' should contain Korean characters or punctuation"
                );
            }
        }
    }

    public function testJapaneseOnlyText()
    {
        $japanese_only = "私は東京大学で日本語を勉強しています";
        $result = Jieba::cut($japanese_only, false);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        // Verify segments contain Japanese characters
        $has_japanese_chars = false;
        foreach ($result as $segment) {
            if (preg_match('/[\x{3040}-\x{309f}\x{30a0}-\x{30ff}\x{4e00}-\x{9fff}]/u', $segment)) {
                $has_japanese_chars = true;
                break;
            }
        }
        $this->assertTrue($has_japanese_chars, "Should contain Japanese characters");
    }

    public function testPerformanceWithLongMixedText()
    {
        $long_mixed = str_repeat("我喜欢这个世界。私は日本に住んでいます。안녕하세요 세계입니다.", 10);

        $start_time = microtime(true);
        $result = Jieba::cut($long_mixed, false);
        $end_time = microtime(true);
        $execution_time = $end_time - $start_time;

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        $this->assertLessThan(2.0, $execution_time, "Should process long mixed text within 2 seconds");
    }
}
