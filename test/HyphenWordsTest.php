<?php

use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\Posseg;
use PHPUnit\Framework\TestCase;

class HyphenWordsTest extends TestCase
{
    private $tempFile;

    protected function setUp(): void
    {
        // Initialize all classes for each test
        Jieba::init();
        Finalseg::init();
        Posseg::init();
    }

    protected function tearDown(): void
    {
        // Clean up temporary file if it exists
        if ($this->tempFile && file_exists($this->tempFile)) {
            unlink($this->tempFile);
        }

        // Clean up after each test
        Jieba::destroy();
        Posseg::destroy();
        Finalseg::destroy();
    }

    /**
     * Test that words with hyphens can be added via addWord()
     */
    public function testAddWordWithHyphen()
    {
        $word = 'BZ-YQ1722';
        Jieba::addWord($word, 10000);

        // Check that the word was added to the dictionary
        $this->assertTrue(isset(Jieba::$original_freq[$word]));
        $this->assertEquals(10000, Jieba::$original_freq[$word]);

        // Test segmentation
        $text = '今天的編號是BZ-YQ1722，請查看。';
        $result = Jieba::cut($text);

        // The word should appear as a complete token
        $this->assertContains($word, $result);

        // Verify it's not split
        $result_string = implode(' / ', $result);
        $this->assertStringContainsString('BZ-YQ1722', $result_string);
    }

    /**
     * Test that words with hyphens can be loaded from user dictionary
     */
    public function testLoadUserDictWithHyphen()
    {
        // Create a temporary user dictionary file
        $this->tempFile = tempnam(sys_get_temp_dir(), 'user_dict_hyphen_test');
        $dictContent = "BZ-YQ1722 10000 n\n";
        $dictContent .= "TEST-ABC 5000 eng\n";

        file_put_contents($this->tempFile, $dictContent);

        // Load the user dictionary
        Jieba::loadUserDict($this->tempFile);

        // Check that words were added to the dictionary
        $this->assertTrue(isset(Jieba::$original_freq['BZ-YQ1722']));
        $this->assertEquals(10000, Jieba::$original_freq['BZ-YQ1722']);

        $this->assertTrue(isset(Jieba::$original_freq['TEST-ABC']));
        $this->assertEquals(5000, Jieba::$original_freq['TEST-ABC']);

        // Test segmentation
        $text = '今天的編號是BZ-YQ1722，還有TEST-ABC。';
        $result = Jieba::cut($text);

        // Both words should appear as complete tokens
        $this->assertContains('BZ-YQ1722', $result);
        $this->assertContains('TEST-ABC', $result);
    }

    /**
     * Test various special characters in words
     */
    public function testWordsWithVariousSpecialCharacters()
    {
        $testCases = [
            'WORD-123' => '這是WORD-123測試',
            'ABC-DEF-GHI' => '包含ABC-DEF-GHI的文本',
            'NUM-001' => 'NUM-001是編號',
            'TEST_UNDERSCORE' => '這是TEST_UNDERSCORE',
            'DOT.WORD' => '包含DOT.WORD的句子',
        ];

        foreach ($testCases as $word => $text) {
            Jieba::addWord($word, 10000);
            $result = Jieba::cut($text);

            $this->assertContains(
                $word,
                $result,
                "Word '$word' should be preserved in segmentation"
            );
        }
    }

    /**
     * Test that hyphenated words work with different frequencies
     */
    public function testHyphenatedWordFrequency()
    {
        $word = 'HIGH-FREQ';

        // Test with different frequency values
        foreach ([100, 1000, 10000, 100000] as $freq) {
            // Reinitialize to clear previous additions
            Jieba::init();
            Finalseg::init();

            Jieba::addWord($word, $freq);

            $text = 'This is HIGH-FREQ test.';
            $result = Jieba::cut($text);

            $this->assertContains(
                $word,
                $result,
                "Word '$word' with frequency $freq should be preserved"
            );
        }
    }

    /**
     * Test that hyphenated words work with POS tags
     */
    public function testHyphenatedWordWithPosTag()
    {
        $word = 'BZ-YQ1722';
        $tag = 'custom_code';

        Jieba::addWord($word, 10000, $tag);

        // Check that POS tag was added
        $this->assertTrue(isset(Posseg::$word_tag[$word]));
        $this->assertEquals($tag, Posseg::$word_tag[$word]);

        // Test segmentation with POS
        $text = '編號BZ-YQ1722已確認';
        $result = Posseg::cut($text);

        // Find the word in results
        $found = false;
        foreach ($result as $item) {
            if ($item['word'] === $word) {
                $this->assertEquals($tag, $item['tag']);
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, "Word '$word' should be found in POS segmentation");
    }

    /**
     * Test mixed Chinese and English with hyphens
     */
    public function testMixedChineseEnglishWithHyphen()
    {
        $word = 'UTF-8編碼';
        Jieba::addWord($word, 10000);

        $text = '使用UTF-8編碼進行處理';
        $result = Jieba::cut($text);

        $this->assertContains($word, $result);
    }

    /**
     * Test that the original reported case works
     */
    public function testOriginalReportedCase()
    {
        // This is the exact case reported in issue #99
        $word = 'BZ-YQ1722';

        // Test with user dictionary
        $this->tempFile = tempnam(sys_get_temp_dir(), 'user_dict_original_case');
        file_put_contents($this->tempFile, "BZ-YQ1722 10000 n\n");

        Jieba::loadUserDict($this->tempFile);

        $text = 'BZ-YQ1722';
        $result = Jieba::cut($text);

        // Should NOT be split into "BZ" and "YQ1722"
        $this->assertNotContains('BZ', $result, 'BZ should not appear separately');
        $this->assertNotContains('YQ1722', $result, 'YQ1722 should not appear separately');

        // Should appear as complete word
        $this->assertContains('BZ-YQ1722', $result, 'BZ-YQ1722 should appear as a complete word');

        // Result should only have one element (the complete word)
        $this->assertCount(1, $result, 'Result should only contain one token');
    }
}
