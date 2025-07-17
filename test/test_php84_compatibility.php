<?php

/**
 * PHP 8.4 Compatibility Test
 * 
 * This script tests basic functionality to ensure PHP 8.4 compatibility
 */

ini_set('memory_limit', '1024M');

// Test basic requires
require_once dirname(dirname(__FILE__)) . "/src/vendor/multi-array/MultiArray.php";
require_once dirname(dirname(__FILE__)) . "/src/vendor/multi-array/Factory/MultiArrayFactory.php";
require_once dirname(dirname(__FILE__)) . "/src/class/Jieba.php";
require_once dirname(dirname(__FILE__)) . "/src/class/Finalseg.php";
require_once dirname(dirname(__FILE__)) . "/src/class/JiebaAnalyse.php";
require_once dirname(dirname(__FILE__)) . "/src/class/Posseg.php";

use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\JiebaAnalyse;
use Fukuball\Jieba\Posseg;

echo "PHP Version: " . phpversion() . "\n";
echo "Testing PHP 8.4 Compatibility...\n\n";

try {
    // Initialize components
    echo "1. Initializing Jieba...\n";
    Jieba::init();

    echo "2. Initializing Finalseg...\n";
    Finalseg::init();

    echo "3. Initializing JiebaAnalyse...\n";
    JiebaAnalyse::init();

    echo "4. Initializing Posseg...\n";
    Posseg::init();

    // Test basic segmentation
    echo "5. Testing basic segmentation...\n";
    $test_text = "我来到北京清华大学";
    $seg_list = Jieba::cut($test_text);
    echo "Input: " . $test_text . "\n";
    echo "Output: " . implode(" / ", $seg_list) . "\n\n";

    // Test full mode
    echo "6. Testing full mode...\n";
    $seg_list = Jieba::cut($test_text, true);
    echo "Full mode: " . implode(" / ", $seg_list) . "\n\n";

    // Test search mode
    echo "7. Testing search mode...\n";
    $seg_list = Jieba::cutForSearch("小明硕士毕业于中国科学院计算所");
    echo "Search mode: " . implode(" / ", $seg_list) . "\n\n";

    // Test keyword extraction
    echo "8. Testing keyword extraction...\n";
    $keywords = JiebaAnalyse::extractTags($test_text, 3);
    echo "Keywords: " . implode(", ", array_keys($keywords)) . "\n\n";

    // Test POS tagging
    echo "9. Testing POS tagging...\n";
    $pos_list = Posseg::cut($test_text);
    foreach ($pos_list as $word_pos) {
        echo $word_pos['word'] . "/" . $word_pos['tag'] . " ";
    }
    echo "\n\n";

    // Test tokenization
    echo "10. Testing tokenization...\n";
    $tokens = Jieba::tokenize($test_text);
    foreach ($tokens as $token) {
        echo $token['word'] . " [" . $token['start'] . "-" . $token['end'] . "] ";
    }
    echo "\n\n";

    echo "✅ All tests passed! PHP 8.4 compatibility verified.\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
} catch (Error $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    exit(1);
}
