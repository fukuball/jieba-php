<?php

/**
 * Simple test script for new TF-IDF and POS features
 */

require_once 'src/class/Jieba.php';
require_once 'src/class/Finalseg.php';
require_once 'src/class/JiebaAnalyse.php';
require_once 'src/class/Posseg.php';
require_once 'src/vendor/multi-array/MultiArray.php';

use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\JiebaAnalyse;
use Fukuball\Jieba\Posseg;

ini_set('memory_limit', '1024M');

echo "=== Testing New TF-IDF and POS Features ===\n\n";

try {
    // Initialize
    echo "Initializing...\n";
    Jieba::init();
    Posseg::init();
    echo "Initialization complete!\n\n";

    $test_sentence = "我愛吃蘋果";
    echo "Test sentence: '$test_sentence'\n\n";

    // Test 1: Basic Jieba cut (should still work)
    echo "1. Testing basic Jieba::cut():\n";
    $basic_result = Jieba::cut($test_sentence);
    echo "Result: [" . implode(', ', $basic_result) . "]\n\n";

    // Test 2: Jieba cut with POS
    echo "2. Testing Jieba::cut() with POS:\n";
    $pos_result = Jieba::cut($test_sentence, false, array('with_pos' => true));
    foreach ($pos_result as $item) {
        echo "Word: {$item['word']}, POS: {$item['tag']}\n";
    }
    echo "\n";

    // Test 3: Jieba cut with scores
    echo "3. Testing Jieba::cut() with TF-IDF scores:\n";
    $score_result = Jieba::cut($test_sentence, false, array('with_scores' => true));
    foreach ($score_result as $item) {
        echo "Word: {$item['word']}, TF: {$item['tf']}, TF-IDF: {$item['tfidf']}\n";
    }
    echo "\n";

    // Test 4: Jieba cut with both POS and scores
    echo "4. Testing Jieba::cut() with both POS and TF-IDF:\n";
    $full_result = Jieba::cut($test_sentence, false, array('with_pos' => true, 'with_scores' => true));
    foreach ($full_result as $item) {
        echo "Word: {$item['word']}, POS: {$item['tag']}, TF: {$item['tf']}, TF-IDF: {$item['tfidf']}\n";
    }
    echo "\n";

    // Test 5: Basic Posseg cut (should still work)
    echo "5. Testing basic Posseg::cut():\n";
    $posseg_basic = Posseg::cut($test_sentence);
    foreach ($posseg_basic as $item) {
        echo "Word: {$item['word']}, POS: {$item['tag']}\n";
    }
    echo "\n";

    // Test 6: Posseg cut with scores
    echo "6. Testing Posseg::cut() with TF-IDF scores:\n";
    $posseg_scores = Posseg::cut($test_sentence, array('with_scores' => true));
    foreach ($posseg_scores as $item) {
        echo "Word: {$item['word']}, POS: {$item['tag']}, TF: {$item['tf']}, TF-IDF: {$item['tfidf']}\n";
    }
    echo "\n";

    // Test 7: Modularized TF-IDF calculation
    echo "7. Testing modularized TF-IDF calculation:\n";
    JiebaAnalyse::init();
    $words = array('測試', '中文', '分詞', '測試');
    $tf_values = JiebaAnalyse::calculateTF($words);
    echo "TF values:\n";
    foreach ($tf_values as $word => $tf) {
        echo "  $word: $tf\n";
    }
    
    $tfidf_values = JiebaAnalyse::calculateTFIDF($tf_values, true);
    echo "TF-IDF values:\n";
    foreach ($tfidf_values as $word => $scores) {
        echo "  $word: TF={$scores['tf']}, IDF={$scores['idf']}, TF-IDF={$scores['tfidf']}\n";
    }
    echo "\n";

    echo "=== All tests completed successfully! ===\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}