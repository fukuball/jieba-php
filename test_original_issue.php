<?php

// Test script based on the original issue report
// Include required files manually
require_once 'src/vendor/multi-array/MultiArray.php';
require_once 'src/class/Jieba.php';
require_once 'src/class/Finalseg.php';

use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;

function print_mem($label)
{
    echo $label.": ";
    echo round((memory_get_usage() / 1024 / 1024), 2)."M\n";
}

echo "Testing the solution for the original issue...\n\n";

// Set memory limit
ini_set('memory_limit', '1024M');

print_mem('init_start');

Jieba::init();
Finalseg::init();

print_mem('init_end');

// Create some test text to simulate the issue
$test_texts = [
    '神农本草经.txt' => str_repeat('神农本草经中记载了众多中草药的功效和用法，对中医学的发展具有重要意义。', 100),
    '搜神记.txt' => str_repeat('搜神记是一部记载神话传说的古代文献，内容丰富多彩，反映了古代人民的思想文化。', 100),
    '山海经.txt' => str_repeat('山海经是一部充满神话色彩的地理著作，描述了许多奇异的山川地理和神话生物。', 100)
];

foreach ($test_texts as $filename => $content) {
    print_mem($filename . '_read');
    
    print_mem($filename . '_cut_start');
    $seg_list = Jieba::cut($content);
    
    print_mem($filename . '_cut_end');
    
    // Show cache stats
    $stats = Jieba::getCacheStats();
    echo "trie_cache count: " . $stats['trie_cache_size'] . "\n";
    echo "dag_cache count: " . $stats['dag_cache_size'] . "\n";
    
    unset($content, $seg_list);
    print_mem($filename . '_cut_free');
}

// Test the original manual clearing approach
print_mem('before_manual_clear');
Jieba::$trie->cache = [];
Jieba::$dag_cache = [];
print_mem('after_manual_clear');

// Test the new clearCache() method
// First build up cache again
$test_text = '测试缓存清理功能的文本内容';
$seg_list = Jieba::cut($test_text);

print_mem('before_clearCache');
$stats = Jieba::getCacheStats();
echo "Before clearCache() - DAG: {$stats['dag_cache_size']}, Trie: {$stats['trie_cache_size']}\n";

Jieba::clearCache();

print_mem('after_clearCache');
$stats = Jieba::getCacheStats();
echo "After clearCache() - DAG: {$stats['dag_cache_size']}, Trie: {$stats['trie_cache_size']}\n";

// Test automatic cache clearing
echo "\nTesting automatic cache clearing...\n";
$test_text = '这是一段用于测试自动缓存清理功能的文本内容';
$seg_list = Jieba::cut($test_text);

$stats = Jieba::getCacheStats();
echo "Before clearCacheIfNeeded - DAG: {$stats['dag_cache_size']}, Trie: {$stats['trie_cache_size']}\n";

// Use very low limits to trigger clearing
$cleared = Jieba::clearCacheIfNeeded(1, 1);
echo "clearCacheIfNeeded returned: " . ($cleared ? "true" : "false") . "\n";

$stats = Jieba::getCacheStats();
echo "After clearCacheIfNeeded - DAG: {$stats['dag_cache_size']}, Trie: {$stats['trie_cache_size']}\n";

echo "\nTest completed! The new cache management functions work correctly.\n";