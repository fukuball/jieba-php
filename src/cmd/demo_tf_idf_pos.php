<?php

/**
 * demo_tf_idf_pos.php
 *
 * Demonstration of enhanced TF-IDF and POS tagging features
 *
 * PHP version 5
 *
 * @category PHP
 * @package  /src/cmd/
 * @author   Claude AI <noreply@anthropic.com>
 * @license  MIT Licence
 * @version  GIT: <fukuball/jieba-php>
 * @link     https://github.com/fukuball/jieba-php
 */

require_once dirname(dirname(__FILE__)) . '/vendor/multi-array/MultiArray.php';
require_once dirname(dirname(__FILE__)) . '/vendor/multi-array/Factory/MultiArrayFactory.php';
require_once dirname(dirname(__FILE__)) . '/class/Jieba.php';
require_once dirname(dirname(__FILE__)) . '/class/Finalseg.php';
require_once dirname(dirname(__FILE__)) . '/class/JiebaAnalyse.php';
require_once dirname(dirname(__FILE__)) . '/class/Posseg.php';

use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\JiebaAnalyse;
use Fukuball\Jieba\Posseg;

// 設定記憶體限制
ini_set('memory_limit', '1024M');

// 初始化系統
echo "正在初始化 jieba-php...\n";
Jieba::init();
Finalseg::init();
Posseg::init();
JiebaAnalyse::init();

echo "===== jieba-php TF-IDF 和詞性標註功能示範 =====\n\n";

// 測試文本
$test_sentences = array(
    "我愛吃蘋果和香蕉",
    "中國是一個偉大的國家",
    "機器學習和人工智慧是未來科技發展的重要方向",
    "今天天氣很好，適合出去散步"
);

foreach ($test_sentences as $index => $sentence) {
    echo "【範例 " . ($index + 1) . "】文本：「" . $sentence . "」\n";
    echo str_repeat("=", 60) . "\n";

    // 1. 基本 Jieba 分詞 (向後相容)
    echo "1. 基本 Jieba::cut() 分詞：\n";
    $basic_result = Jieba::cut($sentence);
    echo "結果：[" . implode(', ', $basic_result) . "]\n\n";

    // 2. Jieba 分詞 + 詞性標註
    echo "2. Jieba::cut() 含詞性標註：\n";
    $pos_result = Jieba::cut($sentence, false, array('with_pos' => true));
    foreach ($pos_result as $item) {
        echo sprintf("%-10s [%s]\n", $item['word'], $item['tag']);
    }
    echo "\n";

    // 3. Jieba 分詞 + TF-IDF 分數
    echo "3. Jieba::cut() 含 TF-IDF 分數：\n";
    $score_result = Jieba::cut($sentence, false, array('with_scores' => true));
    foreach ($score_result as $item) {
        echo sprintf("%-10s TF: %.4f, TF-IDF: %.4f\n", 
            $item['word'], $item['tf'], $item['tfidf']);
    }
    echo "\n";

    // 4. Jieba 分詞 + 詞性標註 + TF-IDF 分數
    echo "4. Jieba::cut() 完整功能（詞性 + TF-IDF）：\n";
    $full_result = Jieba::cut($sentence, false, array(
        'with_pos' => true,
        'with_scores' => true
    ));
    foreach ($full_result as $item) {
        echo sprintf("%-10s [%s] TF: %.4f, TF-IDF: %.4f\n", 
            $item['word'], $item['tag'], $item['tf'], $item['tfidf']);
    }
    echo "\n";

    // 5. Posseg 分詞 (向後相容)
    echo "5. 基本 Posseg::cut() 分詞：\n";
    $posseg_basic = Posseg::cut($sentence);
    foreach ($posseg_basic as $item) {
        echo sprintf("%-10s [%s]\n", $item['word'], $item['tag']);
    }
    echo "\n";

    // 6. Posseg 分詞 + TF-IDF 分數
    echo "6. Posseg::cut() 含 TF-IDF 分數：\n";
    $posseg_scores = Posseg::cut($sentence, array('with_scores' => true));
    foreach ($posseg_scores as $item) {
        echo sprintf("%-10s [%s] TF: %.4f, TF-IDF: %.4f\n", 
            $item['word'], $item['tag'], $item['tf'], $item['tfidf']);
    }
    echo "\n";

    echo str_repeat("-", 60) . "\n\n";
}

// 示範模組化的 TF-IDF 計算
echo "===== 模組化 TF-IDF 計算示範 =====\n\n";

// 確保 JiebaAnalyse 已初始化
JiebaAnalyse::init();

$words = array('機器', '學習', '人工', '智慧', '機器', '學習'); // 重複詞彙用於示範

echo "測試詞彙：[" . implode(', ', $words) . "]\n\n";

// 計算 TF 值
echo "1. 計算詞頻 (TF) 值：\n";
$tf_values = JiebaAnalyse::calculateTF($words);
foreach ($tf_values as $word => $tf) {
    echo sprintf("%-10s TF: %.4f\n", $word, $tf);
}
echo "\n";

// 計算 TF-IDF 值 (簡化版)
echo "2. 計算 TF-IDF 值 (簡化版)：\n";
$tfidf_simple = JiebaAnalyse::calculateTFIDF($tf_values, false);
foreach ($tfidf_simple as $word => $tfidf) {
    echo sprintf("%-10s TF-IDF: %.4f\n", $word, $tfidf);
}
echo "\n";

// 計算 TF-IDF 值 (詳細版)
echo "3. 計算 TF-IDF 值 (詳細版)：\n";
$tfidf_detailed = JiebaAnalyse::calculateTFIDF($tf_values, true);
foreach ($tfidf_detailed as $word => $scores) {
    echo sprintf("%-10s TF: %.4f, IDF: %.4f, TF-IDF: %.4f\n", 
        $word, $scores['tf'], $scores['idf'], $scores['tfidf']);
}
echo "\n";

// 性能測試
echo "===== 性能測試 =====\n\n";

$long_text = "中國是世界上人口最多的國家，擁有悠久的歷史和燦爛的文化。" . 
             "中國的經濟發展迅速，已經成為世界第二大經濟體。" .
             "中國在科技創新方面也取得了重大突破，特別是在人工智慧、" .
             "機器學習、量子計算等前沿技術領域。" .
             "未來中國將繼續推動科技進步，為人類文明發展做出更大貢獻。";

echo "長文本測試（" . mb_strlen($long_text, 'UTF-8') . " 字符）\n\n";

// 測試處理時間
$start_time = microtime(true);
$long_result = Jieba::cut($long_text, false, array(
    'with_pos' => true,
    'with_scores' => true
));
$end_time = microtime(true);

echo "處理時間：" . number_format(($end_time - $start_time) * 1000, 2) . " 毫秒\n";
echo "分詞結果數量：" . count($long_result) . " 個詞彙\n\n";

echo "前 10 個結果：\n";
for ($i = 0; $i < min(10, count($long_result)); $i++) {
    $item = $long_result[$i];
    echo sprintf("%-10s [%s] TF: %.4f, TF-IDF: %.4f\n", 
        $item['word'], $item['tag'], $item['tf'], $item['tfidf']);
}

echo "\n===== 示範完成 =====\n";
echo "\n使用說明：\n";
echo "1. 所有新功能都是可選的，預設為 false，保持向後相容性\n";
echo "2. Jieba::cut() 支援 with_pos 和 with_scores 選項\n";
echo "3. Posseg::cut() 支援 with_scores 選項\n";
echo "4. JiebaAnalyse 提供了模組化的 calculateTF() 和 calculateTFIDF() 方法\n";
echo "5. 系統會自動初始化 JiebaAnalyse 當需要計算 TF-IDF 分數時\n";