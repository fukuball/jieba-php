<?php

/**
 * TfIdfPosTest.php
 *
 * PHP version 5
 *
 * @category PHP
 * @package  /test/
 * @author   Claude AI <noreply@anthropic.com>
 * @license  MIT Licence
 * @version  GIT: <fukuball/jieba-php>
 * @link     https://github.com/fukuball/jieba-php
 */

use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\JiebaAnalyse;
use Fukuball\Jieba\Posseg;

/**
 * TfIdfPosTest
 *
 * @category PHP
 * @package  /test/
 * @author   Claude AI <noreply@anthropic.com>
 * @license  MIT Licence
 * @link     https://github.com/fukuball/jieba-php
 */
class TfIdfPosTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        ini_set('memory_limit', '1024M');
    }

    public function tearDown()
    {
        // Clean up memory after each test
        Jieba::destroy();
        JiebaAnalyse::destroy();
        Posseg::destroy();
    }

    /**
     * Test modularized TF calculation method
     */
    public function testCalculateTF()
    {
        JiebaAnalyse::init();
        
        $words = array('測試', '中文', '分詞', '測試');
        $tf_values = JiebaAnalyse::calculateTF($words);
        
        // Check that TF values are calculated correctly
        $this->assertArrayHasKey('測試', $tf_values);
        $this->assertArrayHasKey('中文', $tf_values);
        $this->assertArrayHasKey('分詞', $tf_values);
        
        // 測試 appears twice, should have higher TF
        $this->assertGreaterThan($tf_values['中文'], $tf_values['測試']);
        
        // All TF values should be between 0 and 1
        foreach ($tf_values as $tf) {
            $this->assertGreaterThan(0, $tf);
            $this->assertLessThanOrEqual(1, $tf);
        }
    }

    /**
     * Test modularized TF-IDF calculation method
     */
    public function testCalculateTFIDF()
    {
        JiebaAnalyse::init();
        
        $tf_values = array('測試' => 0.5, '中文' => 0.25, '分詞' => 0.25);
        $tfidf_values = JiebaAnalyse::calculateTFIDF($tf_values, true);
        
        // Check return format with detailed info
        foreach ($tfidf_values as $word => $scores) {
            $this->assertArrayHasKey('tf', $scores);
            $this->assertArrayHasKey('idf', $scores);
            $this->assertArrayHasKey('tfidf', $scores);
            $this->assertEquals($tf_values[$word], $scores['tf']);
        }
        
        // Test without detailed info
        $simple_tfidf = JiebaAnalyse::calculateTFIDF($tf_values, false);
        foreach ($simple_tfidf as $word => $tfidf) {
            $this->assertInternalType('float', $tfidf);
        }
    }

    /**
     * Test Posseg::cut() with with_scores option
     */
    public function testPossegCutWithScores()
    {
        Jieba::init();
        Posseg::init();
        
        $sentence = "我愛吃蘋果";
        
        // Test normal cut (backward compatibility)
        $normal_result = Posseg::cut($sentence);
        foreach ($normal_result as $item) {
            $this->assertArrayHasKey('word', $item);
            $this->assertArrayHasKey('tag', $item);
            $this->assertArrayNotHasKey('tf', $item);
            $this->assertArrayNotHasKey('tfidf', $item);
        }
        
        // Test with scores
        $scored_result = Posseg::cut($sentence, array('with_scores' => true));
        foreach ($scored_result as $item) {
            $this->assertArrayHasKey('word', $item);
            $this->assertArrayHasKey('tag', $item);
            $this->assertArrayHasKey('tf', $item);
            $this->assertArrayHasKey('tfidf', $item);
            $this->assertInternalType('float', $item['tf']);
            $this->assertInternalType('float', $item['tfidf']);
        }
    }

    /**
     * Test Jieba::cut() with with_pos option
     */
    public function testJiebaCutWithPos()
    {
        Jieba::init();
        
        $sentence = "我愛吃蘋果";
        
        // Test normal cut (backward compatibility)
        $normal_result = Jieba::cut($sentence);
        foreach ($normal_result as $word) {
            $this->assertInternalType('string', $word);
        }
        
        // Test with POS tags
        $pos_result = Jieba::cut($sentence, false, array('with_pos' => true));
        foreach ($pos_result as $item) {
            $this->assertArrayHasKey('word', $item);
            $this->assertArrayHasKey('tag', $item);
            $this->assertArrayNotHasKey('tf', $item);
            $this->assertArrayNotHasKey('tfidf', $item);
            $this->assertInternalType('string', $item['word']);
            $this->assertInternalType('string', $item['tag']);
        }
    }

    /**
     * Test Jieba::cut() with with_scores option
     */
    public function testJiebaCutWithScores()
    {
        Jieba::init();
        
        $sentence = "我愛吃蘋果";
        
        // Test with scores only
        $scored_result = Jieba::cut($sentence, false, array('with_scores' => true));
        foreach ($scored_result as $item) {
            $this->assertArrayHasKey('word', $item);
            $this->assertArrayHasKey('tf', $item);
            $this->assertArrayHasKey('tfidf', $item);
            $this->assertArrayNotHasKey('tag', $item);
            $this->assertInternalType('float', $item['tf']);
            $this->assertInternalType('float', $item['tfidf']);
        }
    }

    /**
     * Test Jieba::cut() with both with_pos and with_scores options
     */
    public function testJiebaCutWithPosAndScores()
    {
        Jieba::init();
        
        $sentence = "我愛吃蘋果";
        
        // Test with both POS tags and scores
        $full_result = Jieba::cut($sentence, false, array(
            'with_pos' => true,
            'with_scores' => true
        ));
        
        foreach ($full_result as $item) {
            $this->assertArrayHasKey('word', $item);
            $this->assertArrayHasKey('tag', $item);
            $this->assertArrayHasKey('tf', $item);
            $this->assertArrayHasKey('tfidf', $item);
            $this->assertInternalType('string', $item['word']);
            $this->assertInternalType('string', $item['tag']);
            $this->assertInternalType('float', $item['tf']);
            $this->assertInternalType('float', $item['tfidf']);
        }
    }

    /**
     * Test backward compatibility - ensure original API still works
     */
    public function testBackwardCompatibility()
    {
        Jieba::init();
        Posseg::init();
        
        $sentence = "我愛吃蘋果";
        
        // Test original Jieba::cut() still returns string array
        $jieba_result = Jieba::cut($sentence);
        $this->assertInternalType('array', $jieba_result);
        foreach ($jieba_result as $word) {
            $this->assertInternalType('string', $word);
        }
        
        // Test original Posseg::cut() still returns word-tag pairs
        $posseg_result = Posseg::cut($sentence);
        $this->assertInternalType('array', $posseg_result);
        foreach ($posseg_result as $item) {
            $this->assertArrayHasKey('word', $item);
            $this->assertArrayHasKey('tag', $item);
            $this->assertCount(2, $item); // Should only have word and tag
        }
    }

    /**
     * Test automatic JiebaAnalyse initialization
     */
    public function testAutoInitialization()
    {
        Jieba::init();
        Posseg::init();
        
        // JiebaAnalyse should be auto-initialized when with_scores is used
        $this->assertFalse(JiebaAnalyse::isInitialized());
        
        $sentence = "測試自動初始化";
        $result = Jieba::cut($sentence, false, array('with_scores' => true));
        
        $this->assertTrue(JiebaAnalyse::isInitialized());
        $this->assertNotEmpty($result);
    }

    /**
     * Test empty input handling
     */
    public function testEmptyInputHandling()
    {
        Jieba::init();
        Posseg::init();
        
        $empty_sentence = "";
        
        // Test Jieba with empty input
        $jieba_result = Jieba::cut($empty_sentence, false, array(
            'with_pos' => true,
            'with_scores' => true
        ));
        $this->assertInternalType('array', $jieba_result);
        
        // Test Posseg with empty input
        $posseg_result = Posseg::cut($empty_sentence, array('with_scores' => true));
        $this->assertInternalType('array', $posseg_result);
    }

    /**
     * Test mixed character types (Chinese, English, Numbers)
     */
    public function testMixedCharacterTypes()
    {
        Jieba::init();
        
        $mixed_sentence = "我有3個iPhone手機";
        
        $result = Jieba::cut($mixed_sentence, false, array(
            'with_pos' => true,
            'with_scores' => true
        ));
        
        $this->assertNotEmpty($result);
        foreach ($result as $item) {
            $this->assertArrayHasKey('word', $item);
            $this->assertArrayHasKey('tag', $item);
            $this->assertArrayHasKey('tf', $item);
            $this->assertArrayHasKey('tfidf', $item);
        }
    }

    /**
     * Test single word input
     */
    public function testSingleWordInput()
    {
        Jieba::init();
        Posseg::init();
        
        $single_word = "測試";
        
        // Test Jieba with single word
        $jieba_result = Jieba::cut($single_word, false, array(
            'with_pos' => true,
            'with_scores' => true
        ));
        $this->assertNotEmpty($jieba_result);
        
        // Test Posseg with single word
        $posseg_result = Posseg::cut($single_word, array('with_scores' => true));
        $this->assertNotEmpty($posseg_result);
    }

    /**
     * Test long text processing
     */
    public function testLongTextProcessing()
    {
        Jieba::init();
        
        $long_text = "這是一個很長的測試文本用來測試系統的處理能力和效能表現當文本很長的時候系統應該還是能夠正常工作並且回傳正確的結果";
        
        $result = Jieba::cut($long_text, false, array(
            'with_pos' => true,
            'with_scores' => true
        ));
        
        $this->assertNotEmpty($result);
        $this->assertGreaterThan(10, count($result)); // Should have many words
        
        // Verify all results have required fields
        foreach ($result as $item) {
            $this->assertArrayHasKey('word', $item);
            $this->assertArrayHasKey('tag', $item);
            $this->assertArrayHasKey('tf', $item);
            $this->assertArrayHasKey('tfidf', $item);
        }
    }

    /**
     * Test TF-IDF values are reasonable
     */
    public function testTFIDFValuesReasonableness()
    {
        Jieba::init();
        
        $sentence = "測試測試中文分詞"; // 測試 appears twice
        
        $result = Jieba::cut($sentence, false, array('with_scores' => true));
        
        // Find TF-IDF values for different words
        $tf_values = array();
        foreach ($result as $item) {
            $tf_values[$item['word']] = $item['tf'];
        }
        
        // Word that appears twice should have higher TF
        if (isset($tf_values['測試']) && isset($tf_values['中文'])) {
            $this->assertGreaterThan($tf_values['中文'], $tf_values['測試']);
        }
    }
}