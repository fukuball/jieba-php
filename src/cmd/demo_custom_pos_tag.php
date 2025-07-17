#!/usr/bin/php
<?php
/**
 * demo_custom_pos_tag.php
 *
 * PHP version 5
 *
 * @category PHP
 * @package  /src/cmd/
 * @author   Fukuball Lin <fukuball@gmail.com>
 * @license  MIT Licence
 * @version  GIT: <fukuball/jieba-php>
 * @link     https://github.com/fukuball/jieba-php
 */
ini_set('memory_limit', '1024M');

require_once dirname(dirname(__FILE__)) . "/vendor/multi-array/MultiArray.php";
require_once dirname(dirname(__FILE__)) . "/vendor/multi-array/Factory/MultiArrayFactory.php";
require_once dirname(dirname(__FILE__)) . "/class/Jieba.php";
require_once dirname(dirname(__FILE__)) . "/class/Finalseg.php";
require_once dirname(dirname(__FILE__)) . "/class/Posseg.php";

use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\Posseg;

echo "=== 自定義詞性標記演示 ===\n\n";

// 初始化
Jieba::init(array('mode' => 'test'));
Finalseg::init();
Posseg::init();

// 測試句子
$test_sentence = "福球林開發了一個很棒的程式庫";

echo "測試句子: " . $test_sentence . "\n\n";

// 1. 使用預設詞性標記
echo "1. 使用預設詞性標記:\n";
$seg_list = Posseg::cut($test_sentence);
foreach ($seg_list as $word_info) {
    echo "   " . $word_info['word'] . " / " . $word_info['tag'] . "\n";
}
echo "\n";

// 2. 添加自定義詞性標記
echo "2. 添加自定義詞性標記:\n";
Jieba::addWord('福球林', 100, 'custom_person');
Jieba::addWord('開發', 80, 'custom_verb');
Jieba::addWord('程式庫', 90, 'custom_noun');

echo "   已添加自定義詞性標記:\n";
echo "   - 福球林 -> custom_person\n";
echo "   - 開發 -> custom_verb\n";
echo "   - 程式庫 -> custom_noun\n\n";

// 3. 使用自定義詞性標記進行分詞
echo "3. 使用自定義詞性標記進行分詞:\n";
$seg_list = Posseg::cut($test_sentence);
foreach ($seg_list as $word_info) {
    $tag_display = $word_info['tag'];
    if (strpos($tag_display, 'custom_') === 0) {
        $tag_display = "\033[32m" . $tag_display . "\033[0m"; // 綠色顯示自定義標記
    }
    echo "   " . $word_info['word'] . " / " . $tag_display . "\n";
}
echo "\n";

// 4. 測試直接添加詞性標記
echo "4. 測試直接添加詞性標記:\n";
Posseg::addWordTag('直接測試', 'direct_tag');
echo "   已直接添加: 直接測試 -> direct_tag\n\n";

$direct_test = Posseg::cut('這是直接測試');
echo "   測試結果:\n";
foreach ($direct_test as $word_info) {
    $tag_display = $word_info['tag'];
    if ($tag_display === 'direct_tag') {
        $tag_display = "\033[33m" . $tag_display . "\033[0m"; // 黃色顯示直接添加的標記
    }
    echo "   " . $word_info['word'] . " / " . $tag_display . "\n";
}
echo "\n";

// 5. 測試覆蓋現有詞性標記
echo "5. 測試覆蓋現有詞性標記:\n";
$original_seg = Posseg::cut('台北');
echo "   原始標記: ";
foreach ($original_seg as $word_info) {
    if ($word_info['word'] === '台北') {
        echo $word_info['word'] . " / " . $word_info['tag'] . "\n";
        break;
    }
}

// 覆蓋標記
Jieba::addWord('台北', 1000, 'custom_place');
echo "   覆蓋為: 台北 -> custom_place\n";

$new_seg = Posseg::cut('台北');
echo "   新標記: ";
foreach ($new_seg as $word_info) {
    if ($word_info['word'] === '台北') {
        echo $word_info['word'] . " / \033[31m" . $word_info['tag'] . "\033[0m\n"; // 紅色顯示覆蓋的標記
        break;
    }
}
echo "\n";

// 6. 測試複雜句子
echo "6. 測試複雜句子:\n";
$complex_sentence = "福球林在台北開發了一個很棒的程式庫直接測試";
echo "   句子: " . $complex_sentence . "\n";
echo "   分詞結果:\n";

$complex_seg = Posseg::cut($complex_sentence);
foreach ($complex_seg as $word_info) {
    $tag_display = $word_info['tag'];
    if (strpos($tag_display, 'custom_') === 0) {
        $tag_display = "\033[32m" . $tag_display . "\033[0m"; // 綠色
    } elseif ($tag_display === 'direct_tag') {
        $tag_display = "\033[33m" . $tag_display . "\033[0m"; // 黃色
    }
    echo "   " . $word_info['word'] . " / " . $tag_display . "\n";
}

echo "\n=== 演示結束 ===\n";
?>