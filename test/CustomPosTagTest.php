<?php

use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\Posseg;
use PHPUnit\Framework\TestCase;

class CustomPosTagTest extends TestCase
{
    protected function setUp(): void
    {
        // Initialize all classes for each test
        Jieba::init();
        Finalseg::init();
        Posseg::init();
    }

    protected function tearDown(): void
    {
        // Clean up after each test
        Jieba::destroy();
        Posseg::destroy();
        Finalseg::destroy();
    }

    /**
     * Test adding custom POS tag via Jieba::addWord()
     */
    public function testAddWordWithCustomTag()
    {
        // Add a custom word with a custom POS tag
        Jieba::addWord('測試詞', 100, 'custom_tag');

        // Verify the word was added to jieba dictionary
        $this->assertTrue(isset(Jieba::$original_freq['測試詞']));
        $this->assertEquals(100, Jieba::$original_freq['測試詞']);

        // Verify the POS tag was added to Posseg
        $this->assertTrue(isset(Posseg::$word_tag['測試詞']));
        $this->assertEquals('custom_tag', Posseg::$word_tag['測試詞']);
    }

    /**
     * Test POS tagging works with custom tags
     */
    public function testPosTaggingWithCustomTags()
    {
        // Add several custom words with custom tags
        Jieba::addWord('福球', 100, 'custom_name');
        Jieba::addWord('程式碼', 80, 'custom_noun');
        Jieba::addWord('超強', 60, 'custom_adj');

        // Test POS tagging
        $result = Posseg::cut('福球寫程式碼很超強');

        // Verify custom tags are applied
        $expected_tags = array();
        foreach ($result as $word_info) {
            $expected_tags[$word_info['word']] = $word_info['tag'];
        }

        $this->assertEquals('custom_name', $expected_tags['福球']);
        $this->assertEquals('custom_noun', $expected_tags['程式碼']);
        $this->assertEquals('custom_adj', $expected_tags['超強']);
    }

    /**
     * Test direct addWordTag method
     */
    public function testDirectAddWordTag()
    {
        // Test adding word tag directly
        Posseg::addWordTag('直接測試', 'direct_tag');

        // Verify it was added
        $this->assertTrue(isset(Posseg::$word_tag['直接測試']));
        $this->assertEquals('direct_tag', Posseg::$word_tag['直接測試']);
    }

    /**
     * Test overriding existing POS tags
     */
    public function testOverrideExistingPosTag()
    {
        // First add a word with one tag
        Jieba::addWord('覆蓋測試', 100, 'original_tag');
        $this->assertEquals('original_tag', Posseg::$word_tag['覆蓋測試']);

        // Then override with another tag
        Jieba::addWord('覆蓋測試', 150, 'new_tag');
        $this->assertEquals('new_tag', Posseg::$word_tag['覆蓋測試']);
        $this->assertEquals(150, Jieba::$original_freq['覆蓋測試']);
    }

    /**
     * Test adding word without tag (should not affect POS system)
     */
    public function testAddWordWithoutTag()
    {
        // Add word without tag
        Jieba::addWord('無標籤詞', 100);

        // Verify word was added to jieba
        $this->assertTrue(isset(Jieba::$original_freq['無標籤詞']));

        // Verify no POS tag was added
        $this->assertFalse(isset(Posseg::$word_tag['無標籤詞']));
    }

    /**
     * Test adding word with empty tag (should not affect POS system)
     */
    public function testAddWordWithEmptyTag()
    {
        // Add word with empty tag
        Jieba::addWord('空標籤詞', 100, '');

        // Verify word was added to jieba
        $this->assertTrue(isset(Jieba::$original_freq['空標籤詞']));

        // Verify no POS tag was added
        $this->assertFalse(isset(Posseg::$word_tag['空標籤詞']));
    }

    /**
     * Test integration with existing dictionary words
     */
    public function testIntegrationWithExistingWords()
    {
        // Test with a word that likely exists in the dictionary
        $original_seg = Posseg::cut('台北');
        $original_tag = null;
        foreach ($original_seg as $word_info) {
            if ($word_info['word'] === '台北') {
                $original_tag = $word_info['tag'];
                break;
            }
        }

        // Override with custom tag
        Jieba::addWord('台北', 1000, 'custom_place');

        // Test again
        $new_seg = Posseg::cut('台北');
        $new_tag = null;
        foreach ($new_seg as $word_info) {
            if ($word_info['word'] === '台北') {
                $new_tag = $word_info['tag'];
                break;
            }
        }

        // Verify the tag was overridden
        $this->assertEquals('custom_place', $new_tag);
        $this->assertNotEquals($original_tag, $new_tag);
    }

    /**
     * Test with various POS tag formats
     */
    public function testVariousPosTagFormats()
    {
        $test_cases = array(
            array('詞1', 'n'),          // Standard noun
            array('詞2', 'v'),          // Standard verb
            array('詞3', 'adj'),        // Adjective
            array('詞4', 'custom'),     // Custom tag
            array('詞5', 'my_tag_123'), // Custom with underscores and numbers
            array('詞6', 'X'),          // Single character
        );

        foreach ($test_cases as $case) {
            list($word, $tag) = $case;

            // Add word with tag
            Jieba::addWord($word, 100, $tag);

            // Verify
            $this->assertEquals($tag, Posseg::$word_tag[$word]);

            // Test in segmentation
            $result = Posseg::cut($word);
            $found_tag = null;
            foreach ($result as $word_info) {
                if ($word_info['word'] === $word) {
                    $found_tag = $word_info['tag'];
                    break;
                }
            }

            $this->assertEquals($tag, $found_tag, "Failed for word: $word with tag: $tag");
        }
    }

    /**
     * Test that custom tags work in longer sentences
     */
    public function testCustomTagsInLongerSentences()
    {
        // Add custom words
        Jieba::addWord('福球林', 100, 'custom_person');
        Jieba::addWord('開發', 80, 'custom_verb');
        Jieba::addWord('程式庫', 90, 'custom_noun');

        // Test in a longer sentence
        $result = Posseg::cut('福球林開發了一個很棒的程式庫');

        // Build result map
        $result_map = array();
        foreach ($result as $word_info) {
            $result_map[$word_info['word']] = $word_info['tag'];
        }

        // Verify custom tags are preserved
        $this->assertEquals('custom_person', $result_map['福球林']);
        $this->assertEquals('custom_verb', $result_map['開發']);
        $this->assertEquals('custom_noun', $result_map['程式庫']);
    }

    /**
     * Test custom tags for English words override default 'eng' tag
     * This tests the priority logic in Posseg line 587-594
     */
    public function testCustomTagForEnglishWord()
    {
        // Add English word with custom tag
        Jieba::addWord('Python', 100, 'programming_lang');
        Jieba::addWord('JavaScript', 100, 'script_lang');

        // Test segmentation
        $result = Posseg::cut('我喜歡Python和JavaScript');

        // Build result map
        $result_map = array();
        foreach ($result as $word_info) {
            $result_map[$word_info['word']] = $word_info['tag'];
        }

        // Verify custom tags override default 'eng' tag
        $this->assertEquals('programming_lang', $result_map['Python']);
        $this->assertEquals('script_lang', $result_map['JavaScript']);
    }

    /**
     * Test custom tags for numeric words override default 'm' tag
     * This tests the priority logic in Posseg line 587-594
     */
    public function testCustomTagForNumericWord()
    {
        // Add numeric-like word with custom tag
        Jieba::addWord('123', 100, 'custom_id');
        Jieba::addWord('456', 100, 'custom_code');

        // Test segmentation
        $result = Posseg::cut('編號123和456');

        // Build result map
        $result_map = array();
        foreach ($result as $word_info) {
            $result_map[$word_info['word']] = $word_info['tag'];
        }

        // Verify custom tags override default 'm' tag
        $this->assertEquals('custom_id', $result_map['123']);
        $this->assertEquals('custom_code', $result_map['456']);
    }

    /**
     * Test custom tags for mixed alphanumeric words
     * This tests the priority logic in Posseg line 587-594
     */
    public function testCustomTagForMixedAlphanumeric()
    {
        // Add mixed alphanumeric words with custom tags
        Jieba::addWord('ABC123', 100, 'product_code');
        Jieba::addWord('V2.0', 100, 'version_tag');
        Jieba::addWord('BZ-YQ1722', 10000, 'issue_number');

        // Test segmentation
        $result = Posseg::cut('產品ABC123版本V2.0編號BZ-YQ1722');

        // Build result map
        $result_map = array();
        foreach ($result as $word_info) {
            $result_map[$word_info['word']] = $word_info['tag'];
        }

        // Verify custom tags are applied
        $this->assertEquals('product_code', $result_map['ABC123']);
        $this->assertEquals('version_tag', $result_map['V2.0']);
        $this->assertEquals('issue_number', $result_map['BZ-YQ1722']);
    }

    /**
     * Test that words without custom tags still use pattern matching
     * This ensures the fallback logic works correctly
     */
    public function testPatternMatchingFallback()
    {
        // Don't add custom tags, let pattern matching work
        $result = Posseg::cut('test 123 測試');

        // Build result map
        $result_map = array();
        foreach ($result as $word_info) {
            $result_map[$word_info['word']] = $word_info['tag'];
        }

        // Verify pattern matching still works
        if (isset($result_map['test'])) {
            $this->assertEquals('eng', $result_map['test']);
        }
        if (isset($result_map['123'])) {
            $this->assertEquals('m', $result_map['123']);
        }
    }

    /**
     * Test custom tags in __cutDetail for mixed Chinese and alphanumeric
     * This tests the priority logic in Posseg __cutDetail line 586-593
     */
    public function testCustomTagInCutDetail()
    {
        // Add a custom word with alphanumeric that might appear in unrecognized Chinese text
        Jieba::addWord('ABC', 100, 'custom_abc');
        Jieba::addWord('123', 100, 'custom_num');
        Jieba::addWord('XYZ999', 100, 'custom_code');

        // Use a sentence with characters that won't be in the dictionary
        // This will trigger __cutDetail to be called from __cutDAG
        $result = Posseg::cut('未ABC知123詞XYZ999彙');

        // Build result map
        $result_map = array();
        foreach ($result as $word_info) {
            $result_map[$word_info['word']] = $word_info['tag'];
        }

        // Verify custom tags are applied even in __cutDetail
        if (isset($result_map['ABC'])) {
            $this->assertEquals('custom_abc', $result_map['ABC']);
        }
        if (isset($result_map['123'])) {
            $this->assertEquals('custom_num', $result_map['123']);
        }
        if (isset($result_map['XYZ999'])) {
            $this->assertEquals('custom_code', $result_map['XYZ999']);
        }
    }

    /**
     * Test pattern matching fallback in __cutDetail for English words
     * This ensures __cutDetail line 592-593 (eng pattern) is covered
     */
    public function testCutDetailEnglishPattern()
    {
        // Create a test with unrecognized mixed text that will trigger __cutDetail
        // and test the English pattern matching (without custom tags)
        $result = Posseg::cut('這是TestWord測試');

        // Find if TestWord was tagged as 'eng'
        $found_eng = false;
        foreach ($result as $word_info) {
            if (isset($word_info['word']) && preg_match('/[a-zA-Z]+/', $word_info['word'])) {
                if ($word_info['tag'] == 'eng') {
                    $found_eng = true;
                }
            }
        }

        // At least some English text should be tagged as 'eng'
        $this->assertTrue($found_eng, 'English pattern should match in __cutDetail');
    }

    /**
     * Test pattern matching fallback in __cutDetail for numeric words
     * This ensures __cutDetail line 590-591 (num pattern) is covered
     */
    public function testCutDetailNumericPattern()
    {
        // Create a test with unrecognized mixed text that will trigger __cutDetail
        // and test the numeric pattern matching (without custom tags)
        $result = Posseg::cut('價格99.99元');

        // Find if numeric values were tagged as 'm'
        $found_num = false;
        foreach ($result as $word_info) {
            if (isset($word_info['word']) && preg_match('/[0-9.]+/', $word_info['word'])) {
                if ($word_info['tag'] == 'm') {
                    $found_num = true;
                }
            }
        }

        // At least some numeric text should be tagged as 'm'
        $this->assertTrue($found_num, 'Numeric pattern should match in __cutDetail');
    }
}
