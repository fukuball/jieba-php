<?php

use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\Posseg;
use PHPUnit\Framework\TestCase;

class SecurityTest extends TestCase
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
     * Test that addWordTag validates tag input properly
     */
    public function testAddWordTagValidation()
    {
        // Test valid tags
        $validTags = ['n', 'v', 'adj', 'custom_tag', 'my-tag', 'tag123', '名詞', 'custom'];
        
        foreach ($validTags as $tag) {
            Posseg::addWordTag('測試詞', $tag);
            $this->assertEquals($tag, Posseg::$word_tag['測試詞']);
        }
    }

    /**
     * Test that addWordTag rejects invalid tag types
     */
    public function testAddWordTagRejectsInvalidTypes()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('POS tag must be a string');
        
        Posseg::addWordTag('測試詞', 123);
    }

    /**
     * Test that addWordTag rejects empty tags
     */
    public function testAddWordTagRejectsEmptyTags()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('POS tag cannot be empty');
        
        Posseg::addWordTag('測試詞', '');
    }

    /**
     * Test that addWordTag rejects whitespace-only tags
     */
    public function testAddWordTagRejectsWhitespaceOnlyTags()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('POS tag cannot be empty');
        
        Posseg::addWordTag('測試詞', '   ');
    }

    /**
     * Test that addWordTag rejects overly long tags
     */
    public function testAddWordTagRejectsLongTags()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('POS tag cannot exceed 50 characters');
        
        $longTag = str_repeat('a', 51);
        Posseg::addWordTag('測試詞', $longTag);
    }

    /**
     * Test that addWordTag rejects tags with dangerous characters
     */
    public function testAddWordTagRejectsDangerousCharacters()
    {
        $dangerousTags = [
            '<script>',
            'javascript:alert(1)',
            'data:text/html',
            'vbscript:',
            'onload="alert(1)"',
            'onerror="alert(1)"',
            '${code}',
            '<%=code%>',
            '"dangerous"',
            "'dangerous'",
            '<>',
        ];

        foreach ($dangerousTags as $tag) {
            try {
                Posseg::addWordTag('測試詞', $tag);
                $this->fail("Expected InvalidArgumentException for dangerous tag: $tag");
            } catch (InvalidArgumentException $e) {
                $this->assertTrue(
                    strpos($e->getMessage(), 'invalid characters') !== false ||
                    strpos($e->getMessage(), 'dangerous content') !== false,
                    "Expected security-related error message for tag: $tag, got: " . $e->getMessage()
                );
            }
        }
    }

    /**
     * Test that addWordTag trims whitespace from tags
     */
    public function testAddWordTagTrimsWhitespace()
    {
        Posseg::addWordTag('測試詞', '  n  ');
        $this->assertEquals('n', Posseg::$word_tag['測試詞']);
    }

    /**
     * Test that Jieba::addWord properly handles tag validation
     */
    public function testJiebaAddWordTagValidation()
    {
        // Test valid tag
        Jieba::addWord('測試詞', 100, 'custom_tag');
        $this->assertEquals('custom_tag', Posseg::$word_tag['測試詞']);

        // Test invalid tag
        $this->expectException(InvalidArgumentException::class);
        Jieba::addWord('測試詞2', 100, '<script>');
    }

    /**
     * Test that validation allows standard POS tags
     */
    public function testStandardPosTagsAreValid()
    {
        $standardTags = [
            'n', 'nr', 'ns', 'nt', 'nz', 'nl', 'ng',
            'v', 'vd', 'vn', 'vshi', 'vyou', 'vf', 'vx',
            'a', 'ad', 'an', 'ag', 'al',
            'm', 'q', 'r', 'd', 'p', 'c', 'u', 'xc', 'w',
            'x', 'eng'
        ];

        foreach ($standardTags as $tag) {
            Posseg::addWordTag('測試詞_' . $tag, $tag);
            $this->assertEquals($tag, Posseg::$word_tag['測試詞_' . $tag]);
        }
    }

    /**
     * Test that validation allows Chinese characters in tags
     */
    public function testChineseCharactersInTagsAreValid()
    {
        $chineseTags = ['名詞', '動詞', '形容詞', '自定義標籤'];

        foreach ($chineseTags as $tag) {
            Posseg::addWordTag('測試詞_' . $tag, $tag);
            $this->assertEquals($tag, Posseg::$word_tag['測試詞_' . $tag]);
        }
    }

    /**
     * Test that validation rejects special characters that could be used for injection
     */
    public function testSpecialCharactersAreRejected()
    {
        $specialChars = ['/', '\\', '|', '&', ';', '(', ')', '[', ']', '{', '}', '!', '@', '#', '$', '%', '^', '*', '+', '=', '?', '.', ','];

        foreach ($specialChars as $char) {
            try {
                Posseg::addWordTag('測試詞', 'tag' . $char);
                $this->fail("Expected InvalidArgumentException for special character: $char");
            } catch (InvalidArgumentException $e) {
                $this->assertStringContainsString('invalid characters', $e->getMessage());
            }
        }
    }

    /**
     * Test removeWordTag functionality
     */
    public function testRemoveWordTag()
    {
        // Add a word tag
        Posseg::addWordTag('測試詞', 'test_tag');
        $this->assertTrue(isset(Posseg::$word_tag['測試詞']));
        $this->assertEquals('test_tag', Posseg::$word_tag['測試詞']);

        // Remove the tag
        Posseg::removeWordTag('測試詞');
        $this->assertFalse(isset(Posseg::$word_tag['測試詞']));

        // Test removing non-existent tag (should not error)
        Posseg::removeWordTag('不存在的詞');
        $this->assertFalse(isset(Posseg::$word_tag['不存在的詞']));
    }

    /**
     * Test that addWord without tag removes existing tags
     */
    public function testAddWordWithoutTagRemovesExistingTag()
    {
        // Add word with tag
        Jieba::addWord('測試清理', 100, 'test_tag');
        $this->assertTrue(isset(Posseg::$word_tag['測試清理']));
        $this->assertEquals('test_tag', Posseg::$word_tag['測試清理']);

        // Add same word without tag - should remove the tag
        Jieba::addWord('測試清理', 150);
        $this->assertFalse(isset(Posseg::$word_tag['測試清理']));
        $this->assertEquals(150, Jieba::$original_freq['測試清理']);
    }
}