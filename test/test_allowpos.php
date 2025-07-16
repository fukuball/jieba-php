#!/usr/bin/php
<?php
/**
 * test_allowpos.php
 * 
 * Test script to verify the allowPOS fix
 */
ini_set('memory_limit', '1024M');

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

// Initialize only Jieba and JiebaAnalyse (not Posseg)
Jieba::init(array('mode' => 'test', 'dict' => 'big'));
Finalseg::init();
JiebaAnalyse::init(array('dict' => 'big'));

$text = "这是一个伸手不见五指的黑夜。我叫孙悟空，我爱北京，我爱Python和C++。";
$top = 10;

echo "Testing allowPOS parameter:\n";
echo "Text: $text\n\n";

try {
  // Test the allowPOS parameter (this should work with the fix)
  $words = JiebaAnalyse::extractTags($text, $top, ['allowPOS' => ['ns', 'n', 'vn', 'v']]);
  echo "SUCCESS: allowPOS parameter works!\n";
  echo "Extracted words:\n";
  foreach ($words as $word => $score) {
    echo "  $word: $score\n";
  }
} catch (Exception $e) {
  echo "ERROR: " . $e->getMessage() . "\n";
}

echo "\nDone!\n";
?>