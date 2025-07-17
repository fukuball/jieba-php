#!/usr/bin/php
<?php
/**
 * test_implementation.php
 * 
 * Simple test to verify the custom POS tag implementation works
 */
ini_set('memory_limit', '1024M');

require_once dirname(__FILE__) . "/src/vendor/multi-array/MultiArray.php";
require_once dirname(__FILE__) . "/src/vendor/multi-array/Factory/MultiArrayFactory.php";
require_once dirname(__FILE__) . "/src/class/Jieba.php";
require_once dirname(__FILE__) . "/src/class/Finalseg.php";
require_once dirname(__FILE__) . "/src/class/Posseg.php";

use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\Posseg;

echo "=== 測試自定義詞性標記功能 ===\n\n";

try {
    // 初始化
    echo "1. 初始化系統...\n";
    Jieba::init();
    Finalseg::init();
    Posseg::init();
    echo "   ✓ 初始化成功\n\n";

    // 測試1: 添加自定義詞性標記
    echo "2. 測試添加自定義詞性標記...\n";
    Jieba::addWord('測試詞', 100, 'custom_tag');
    
    // 驗證詞語被添加到 Jieba 字典
    if (isset(Jieba::$original_freq['測試詞'])) {
        echo "   ✓ 詞語已添加到 Jieba 字典\n";
    } else {
        echo "   ✗ 詞語未添加到 Jieba 字典\n";
    }
    
    // 驗證詞性標記被添加到 Posseg
    if (isset(Posseg::$word_tag['測試詞']) && Posseg::$word_tag['測試詞'] === 'custom_tag') {
        echo "   ✓ 詞性標記已添加到 Posseg\n";
    } else {
        echo "   ✗ 詞性標記未添加到 Posseg\n";
    }
    echo "\n";

    // 測試2: 直接添加詞性標記
    echo "3. 測試直接添加詞性標記...\n";
    Posseg::addWordTag('直接測試', 'direct_tag');
    
    if (isset(Posseg::$word_tag['直接測試']) && Posseg::$word_tag['直接測試'] === 'direct_tag') {
        echo "   ✓ 直接添加詞性標記成功\n";
    } else {
        echo "   ✗ 直接添加詞性標記失敗\n";
    }
    echo "\n";

    // 測試3: 在詞性標記中使用自定義標記
    echo "4. 測試詞性標記功能...\n";
    
    // 添加一些測試詞語
    Jieba::addWord('福球', 100, 'custom_name');
    Jieba::addWord('程式', 80, 'custom_noun');
    
    // 測試詞性標記
    $test_sentence = "福球程式測試詞";
    $result = Posseg::cut($test_sentence);
    
    echo "   測試句子: " . $test_sentence . "\n";
    echo "   詞性標記結果:\n";
    
    $success = true;
    foreach ($result as $word_info) {
        echo "   - " . $word_info['word'] . " / " . $word_info['tag'] . "\n";
        
        // 驗證自定義標記
        if ($word_info['word'] === '福球' && $word_info['tag'] !== 'custom_name') {
            $success = false;
        }
        if ($word_info['word'] === '程式' && $word_info['tag'] !== 'custom_noun') {
            $success = false;
        }
        if ($word_info['word'] === '測試詞' && $word_info['tag'] !== 'custom_tag') {
            $success = false;
        }
    }
    
    if ($success) {
        echo "   ✓ 詞性標記功能正常\n";
    } else {
        echo "   ✗ 詞性標記功能異常\n";
    }
    echo "\n";

    // 測試4: 測試空標記
    echo "5. 測試空標記...\n";
    Jieba::addWord('空標記詞', 100, '');
    
    if (!isset(Posseg::$word_tag['空標記詞'])) {
        echo "   ✓ 空標記正確處理（未添加到詞性字典）\n";
    } else {
        echo "   ✗ 空標記處理異常\n";
    }
    echo "\n";

    // 測試5: 測試覆蓋現有標記
    echo "6. 測試覆蓋現有標記...\n";
    Jieba::addWord('覆蓋測試', 100, 'original_tag');
    $original_tag = Posseg::$word_tag['覆蓋測試'];
    
    Jieba::addWord('覆蓋測試', 150, 'new_tag');
    $new_tag = Posseg::$word_tag['覆蓋測試'];
    
    if ($original_tag === 'original_tag' && $new_tag === 'new_tag') {
        echo "   ✓ 標記覆蓋功能正常\n";
    } else {
        echo "   ✗ 標記覆蓋功能異常\n";
    }
    echo "\n";

    echo "=== 所有測試完成 ===\n";
    
} catch (Exception $e) {
    echo "錯誤: " . $e->getMessage() . "\n";
    echo "堆疊追蹤:\n" . $e->getTraceAsString() . "\n";
}
?>