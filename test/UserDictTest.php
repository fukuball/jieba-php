<?php

use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\Posseg;
use PHPUnit\Framework\TestCase;

class UserDictTest extends TestCase
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
     * Test that loadUserDict processes words even with invalid tags
     */
    public function testLoadUserDictWithInvalidTagsStillProcessesWords()
    {
        // Create a temporary user dictionary file with mixed valid and invalid tags
        $this->tempFile = tempnam(sys_get_temp_dir(), 'user_dict_test');
        $dictContent = "有效詞彙 100 n\n";
        $dictContent .= "無效標籤詞彙 200 <script>\n";  // Invalid tag
        $dictContent .= "另一個有效詞彙 150 v\n";
        
        file_put_contents($this->tempFile, $dictContent);
        
        // Capture error log output
        $errorLogContent = '';
        $originalErrorLog = ini_get('error_log');
        $tempErrorLog = tempnam(sys_get_temp_dir(), 'error_log_test');
        ini_set('error_log', $tempErrorLog);
        
        try {
            // Load the user dictionary
            Jieba::loadUserDict($this->tempFile);
            
            // Check that all words were added to the dictionary (even those with invalid tags)
            $this->assertTrue(isset(Jieba::$original_freq['有效詞彙']));
            $this->assertEquals(100, Jieba::$original_freq['有效詞彙']);
            
            $this->assertTrue(isset(Jieba::$original_freq['無效標籤詞彙']));
            $this->assertEquals(200, Jieba::$original_freq['無效標籤詞彙']);
            
            $this->assertTrue(isset(Jieba::$original_freq['另一個有效詞彙']));
            $this->assertEquals(150, Jieba::$original_freq['另一個有效詞彙']);
            
            // Check that valid tags were added
            $this->assertTrue(isset(Posseg::$word_tag['有效詞彙']));
            $this->assertEquals('n', Posseg::$word_tag['有效詞彙']);
            
            $this->assertTrue(isset(Posseg::$word_tag['另一個有效詞彙']));
            $this->assertEquals('v', Posseg::$word_tag['另一個有效詞彙']);
            
            // Check that invalid tag was NOT added
            $this->assertFalse(isset(Posseg::$word_tag['無效標籤詞彙']));
            
            // Check that error was logged
            $errorLogContent = file_get_contents($tempErrorLog);
            $this->assertStringContainsString('Invalid POS tag', $errorLogContent);
            $this->assertStringContainsString('無效標籤詞彙', $errorLogContent);
            
        } finally {
            // Restore original error log setting
            ini_set('error_log', $originalErrorLog);
            if (file_exists($tempErrorLog)) {
                unlink($tempErrorLog);
            }
        }
    }

    /**
     * Test that user dictionary with only words (no tags) works correctly
     */
    public function testLoadUserDictWithoutTags()
    {
        // Create a temporary user dictionary file with words only
        $this->tempFile = tempnam(sys_get_temp_dir(), 'user_dict_test');
        $dictContent = "只有詞彙 100\n";
        $dictContent .= "另一個詞彙 200\n";
        
        file_put_contents($this->tempFile, $dictContent);
        
        // Load the user dictionary
        Jieba::loadUserDict($this->tempFile);
        
        // Check that words were added to the dictionary
        $this->assertTrue(isset(Jieba::$original_freq['只有詞彙']));
        $this->assertEquals(100, Jieba::$original_freq['只有詞彙']);
        
        $this->assertTrue(isset(Jieba::$original_freq['另一個詞彙']));
        $this->assertEquals(200, Jieba::$original_freq['另一個詞彙']);
        
        // Check that no tags were added (since none were provided)
        $this->assertFalse(isset(Posseg::$word_tag['只有詞彙']));
        $this->assertFalse(isset(Posseg::$word_tag['另一個詞彙']));
    }

    /**
     * Test that mixed format user dictionary works correctly
     */
    public function testLoadUserDictMixedFormat()
    {
        // Create a temporary user dictionary file with mixed formats
        $this->tempFile = tempnam(sys_get_temp_dir(), 'user_dict_test');
        $dictContent = "有標籤詞彙 100 n\n";
        $dictContent .= "無標籤詞彙 200\n";
        $dictContent .= "另一個有標籤詞彙 150 v\n";
        
        file_put_contents($this->tempFile, $dictContent);
        
        // Load the user dictionary
        Jieba::loadUserDict($this->tempFile);
        
        // Check that all words were added
        $this->assertTrue(isset(Jieba::$original_freq['有標籤詞彙']));
        $this->assertTrue(isset(Jieba::$original_freq['無標籤詞彙']));
        $this->assertTrue(isset(Jieba::$original_freq['另一個有標籤詞彙']));
        
        // Check that tags were added only for words that had them
        $this->assertTrue(isset(Posseg::$word_tag['有標籤詞彙']));
        $this->assertEquals('n', Posseg::$word_tag['有標籤詞彙']);
        
        $this->assertFalse(isset(Posseg::$word_tag['無標籤詞彙']));
        
        $this->assertTrue(isset(Posseg::$word_tag['另一個有標籤詞彙']));
        $this->assertEquals('v', Posseg::$word_tag['另一個有標籤詞彙']);
    }
}