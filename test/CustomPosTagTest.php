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
}
