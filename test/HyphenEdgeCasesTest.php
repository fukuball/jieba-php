<?php

use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\Posseg;
use PHPUnit\Framework\TestCase;

/**
 * Test edge cases for hyphen support in regex patterns
 * This validates that adding \- to regex patterns doesn't cause issues
 */
class HyphenEdgeCasesTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        Jieba::init();
        Finalseg::init();
        Posseg::init();
    }

    /**
     * Test that existing decimal number parsing is not affected
     */
    public function testDecimalNumbersUnaffected()
    {
        $seg_list = Jieba::cut("價格是3.14元");
        $this->assertContains("3.14", $seg_list, "Decimal numbers should still be parsed correctly");
    }

    /**
     * Test that existing underscore parsing is not affected
     */
    public function testUnderscoreWordsUnaffected()
    {
        // Add a word with underscore
        Jieba::addWord("test_variable", 1000);
        $seg_list = Jieba::cut("這是test_variable測試");
        $this->assertContains("test_variable", $seg_list, "Words with underscores should be preserved");
    }

    /**
     * Test continuous hyphens
     */
    public function testContinuousHyphens()
    {
        Jieba::addWord("ABC---DEF", 1000);
        $seg_list = Jieba::cut("測試ABC---DEF文字");
        $this->assertContains("ABC---DEF", $seg_list, "Continuous hyphens should be handled");
    }

    /**
     * Test hyphen at start
     */
    public function testHyphenAtStart()
    {
        Jieba::addWord("-OPTION", 1000);
        $seg_list = Jieba::cut("命令行-OPTION參數");
        $this->assertContains("-OPTION", $seg_list, "Hyphen at start should be handled");
    }

    /**
     * Test hyphen at end
     */
    public function testHyphenAtEnd()
    {
        Jieba::addWord("PREFIX-", 1000);
        $seg_list = Jieba::cut("這是PREFIX-的測試");
        $this->assertContains("PREFIX-", $seg_list, "Hyphen at end should be handled");
    }

    /**
     * Test standalone hyphen with spaces
     */
    public function testStandaloneHyphenWithSpaces()
    {
        $seg_list = Jieba::cut("今天 - 明天");
        // The hyphen might be kept with surrounding chars or separate
        // Either behavior is acceptable
        $all_text = implode('', $seg_list);
        $this->assertStringContainsString("-", $all_text, "Hyphen should be preserved in output");
    }

    /**
     * Test math-like expressions
     */
    public function testMathExpression()
    {
        $seg_list = Jieba::cut("計算5-3=2的結果");
        $all_text = implode('', $seg_list);
        // The expression should be preserved somewhere in the output
        $this->assertStringContainsString("5", $all_text);
        $this->assertStringContainsString("3", $all_text);
        $this->assertStringContainsString("2", $all_text);
    }

    /**
     * Test mixed special characters
     */
    public function testMixedSpecialCharacters()
    {
        Jieba::addWord("test_var-v1.0", 1000);
        $seg_list = Jieba::cut("版本test_var-v1.0已發布");
        $this->assertContains("test_var-v1.0", $seg_list, "Mixed special chars should work");
    }

    /**
     * Test that original issue case works
     */
    public function testOriginalIssueCase()
    {
        Jieba::addWord("BZ-YQ1722", 10000);
        $seg_list = Jieba::cut("編號是BZ-YQ1722測試");
        $this->assertContains("BZ-YQ1722", $seg_list, "Original issue case should be fixed");
    }

    /**
     * Test Chinese-English-hyphen combinations
     */
    public function testChineseEnglishHyphen()
    {
        Jieba::addWord("北京-上海", 1000);
        $seg_list = Jieba::cut("今天去北京-上海旅行");
        $this->assertContains("北京-上海", $seg_list, "Chinese-hyphen combinations should work");
    }

    /**
     * Test backward compatibility: words without hyphens should still work
     */
    public function testBackwardCompatibility()
    {
        $seg_list = Jieba::cut("我来到北京清华大学");
        $expected = ["我", "来到", "北京", "清华大学"];
        $this->assertEquals($expected, $seg_list, "Non-hyphen words should work as before");
    }

    /**
     * Test POS tagging with hyphenated words
     */
    public function testPosTaggingWithHyphens()
    {
        Jieba::addWord("API-KEY", 1000, "n");
        $result = Posseg::cut("這是我的API-KEY密鑰");

        $found = false;
        foreach ($result as $pair) {
            if ($pair['word'] === 'API-KEY') {
                $found = true;
                $this->assertEquals('n', $pair['tag'], "POS tag should be preserved for hyphenated words");
                break;
            }
        }
        $this->assertTrue($found, "Hyphenated word with POS tag should be found");
    }
}
