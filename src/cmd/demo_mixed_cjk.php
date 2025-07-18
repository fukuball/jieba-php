#!/usr/bin/php
<?php
/**
 * demo_mixed_cjk.php
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

require_once dirname(dirname(__FILE__))."/vendor/multi-array/MultiArray.php";
require_once dirname(dirname(__FILE__))."/vendor/multi-array/Factory/MultiArrayFactory.php";
require_once dirname(dirname(__FILE__))."/class/Jieba.php";
require_once dirname(dirname(__FILE__))."/class/Finalseg.php";
use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;

// Initialize with CJK support for all languages (Chinese, Japanese, Korean)
Jieba::init(array('mode'=>'test','dict'=>'big', 'cjk'=>'all'));
Finalseg::init();

// Test mixed Chinese, Japanese, and Korean text
$mixed_text = "我喜欢这个世界。私は日本に住んでいます。안녕하세요 세계입니다.";

echo "=== Mixed CJK Text Processing Demo ===\n";
echo "Test text: " . $mixed_text . "\n\n";

echo "--- Chinese Part: 我喜欢这个世界 ---\n";
echo "中文：我喜欢这个世界（簡体中文）\n\n";

echo "--- Japanese Part: 私は日本に住んでいます ---\n";
echo "日本語：私は日本に住んでいます（日本語）\n\n";

echo "--- Korean Part: 안녕하세요 세계입니다 ---\n";
echo "한국어：안녕하세요 세계입니다（한국어）\n\n";

echo "=== Segmentation Results ===\n";

// Test with cut_all = false (default mode)
echo "【Default Mode (cut_all=false)】\n";
$seg_list_default = Jieba::cut($mixed_text, false);
echo "Result: ";
var_dump($seg_list_default);
echo "\n";

// Test with cut_all = true (full mode)
echo "【Full Mode (cut_all=true)】\n";
$seg_list_full = Jieba::cut($mixed_text, true);
echo "Result: ";
var_dump($seg_list_full);
echo "\n";

// Test individual languages
echo "=== Individual Language Testing ===\n";

// Chinese text
$chinese_text = "我来到北京清华大学学习中文";
echo "Chinese Text: " . $chinese_text . "\n";
echo "Default Mode: ";
var_dump(Jieba::cut($chinese_text, false));
echo "Full Mode: ";
var_dump(Jieba::cut($chinese_text, true));
echo "\n";

// Japanese text
$japanese_text = "私は東京大学で日本語を勉強しています";
echo "Japanese Text: " . $japanese_text . "\n";
echo "Default Mode: ";
var_dump(Jieba::cut($japanese_text, false));
echo "Full Mode: ";
var_dump(Jieba::cut($japanese_text, true));
echo "\n";

// Korean text
$korean_text = "저는 서울대학교에서 한국어를 공부하고 있습니다";
echo "Korean Text: " . $korean_text . "\n";
echo "Default Mode: ";
var_dump(Jieba::cut($korean_text, false));
echo "Full Mode: ";
var_dump(Jieba::cut($korean_text, true));
echo "\n";

// Test complex mixed sentence
$complex_mixed = "今天weather很好，私たちは공원에 갔습니다。";
echo "Complex Mixed Text: " . $complex_mixed . "\n";
echo "Default Mode: ";
var_dump(Jieba::cut($complex_mixed, false));
echo "Full Mode: ";
var_dump(Jieba::cut($complex_mixed, true));
echo "\n";

// Memory usage
function memory_usage()
{
    $mem_usage = memory_get_usage(true);
    if ($mem_usage < 1024) {
        $mem_usage .= ' bytes';
    } elseif ($mem_usage < 1048576) {
        $mem_usage = round($mem_usage/1024, 2) . ' kilobytes';
    } else {
        $mem_usage = round($mem_usage/1048576, 2) . ' megabytes';
    }
    return $mem_usage;
}

echo "Memory Usage: " . memory_usage() . "\n";
