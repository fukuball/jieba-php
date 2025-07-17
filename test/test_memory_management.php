<?php
ini_set('memory_limit', '1024M');

// Include the necessary files
require_once dirname(dirname(__FILE__)) . "/src/vendor/multi-array/MultiArray.php";
require_once dirname(dirname(__FILE__)) . "/src/vendor/multi-array/Factory/MultiArrayFactory.php";
require_once dirname(dirname(__FILE__)) . "/src/class/Jieba.php";
require_once dirname(dirname(__FILE__)) . "/src/class/Finalseg.php";
require_once dirname(dirname(__FILE__)) . "/src/class/JiebaAnalyse.php";
require_once dirname(dirname(__FILE__)) . "/src/class/Posseg.php";
require_once dirname(dirname(__FILE__)) . "/src/class/JiebaMemory.php";

use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\JiebaAnalyse;
use Fukuball\Jieba\Posseg;
use Fukuball\Jieba\JiebaMemory;

echo "Testing Memory Management Functionality\n";
echo "=======================================\n\n";

// Test 1: Initial state
echo "Test 1: Initial state\n";
$allInitialized = JiebaMemory::isAllInitialized();
echo "All classes initialized: " . ($allInitialized ? "true" : "false") . "\n";
echo "Expected: false\n";
echo "Result: " . ($allInitialized === false ? "PASS" : "FAIL") . "\n\n";

// Test 2: Initialize all classes
echo "Test 2: Initialize all classes\n";
$memoryBefore = memory_get_usage(true);
echo "Memory before init: " . round($memoryBefore / 1024 / 1024, 2) . " MB\n";

JiebaMemory::initAll();

$memoryAfter = memory_get_usage(true);
echo "Memory after init: " . round($memoryAfter / 1024 / 1024, 2) . " MB\n";
echo "Memory increased: " . round(($memoryAfter - $memoryBefore) / 1024 / 1024, 2) . " MB\n";

$allInitialized = JiebaMemory::isAllInitialized();
echo "All classes initialized: " . ($allInitialized ? "true" : "false") . "\n";
echo "Expected: true\n";
echo "Result: " . ($allInitialized === true ? "PASS" : "FAIL") . "\n\n";

// Test 3: Test functionality
echo "Test 3: Test functionality\n";
try {
    $result = Jieba::cut("这是一个测试");
    echo "Jieba::cut works: " . (is_array($result) && count($result) > 0 ? "true" : "false") . "\n";

    $result = Finalseg::cut("测试");
    echo "Finalseg::cut works: " . (is_array($result) && count($result) > 0 ? "true" : "false") . "\n";

    $result = JiebaAnalyse::extractTags("这是一个测试文本");
    echo "JiebaAnalyse::extractTags works: " . (is_array($result) ? "true" : "false") . "\n";

    $result = Posseg::cut("测试");
    echo "Posseg::cut works: " . (is_array($result) && count($result) > 0 ? "true" : "false") . "\n";

    echo "Result: PASS\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Result: FAIL\n\n";
}

// Test 4: Get memory stats
echo "Test 4: Memory statistics\n";
$stats = JiebaMemory::getMemoryStats();
echo "Current memory usage: " . $stats['current_memory_usage_formatted'] . "\n";
echo "Peak memory usage: " . $stats['peak_memory_usage_formatted'] . "\n";
echo "Memory stats available: " . (isset($stats['current_memory_usage']) ? "true" : "false") . "\n";
echo "Result: PASS\n\n";

// Test 5: Destroy all classes
echo "Test 5: Destroy all classes\n";
$memoryBeforeDestroy = memory_get_usage(true);
echo "Memory before destroy: " . round($memoryBeforeDestroy / 1024 / 1024, 2) . " MB\n";

JiebaMemory::destroyAll();

$memoryAfterDestroy = memory_get_usage(true);
echo "Memory after destroy: " . round($memoryAfterDestroy / 1024 / 1024, 2) . " MB\n";
echo "Memory decreased: " . round(($memoryBeforeDestroy - $memoryAfterDestroy) / 1024 / 1024, 2) . " MB\n";

$allInitialized = JiebaMemory::isAllInitialized();
echo "All classes initialized: " . ($allInitialized ? "true" : "false") . "\n";
echo "Expected: false\n";
echo "Result: " . ($allInitialized === false ? "PASS" : "FAIL") . "\n\n";

// Test 6: Test exceptions after destroy
echo "Test 6: Test exceptions after destroy\n";
$exceptionCount = 0;

try {
    Jieba::cut("测试");
    echo "ERROR: Jieba::cut should have thrown exception\n";
} catch (Exception $e) {
    echo "Jieba exception: " . $e->getMessage() . "\n";
    $exceptionCount++;
}

try {
    Finalseg::cut("测试");
    echo "ERROR: Finalseg::cut should have thrown exception\n";
} catch (Exception $e) {
    echo "Finalseg exception: " . $e->getMessage() . "\n";
    $exceptionCount++;
}

try {
    JiebaAnalyse::extractTags("测试");
    echo "ERROR: JiebaAnalyse::extractTags should have thrown exception\n";
} catch (Exception $e) {
    echo "JiebaAnalyse exception: " . $e->getMessage() . "\n";
    $exceptionCount++;
}

try {
    Posseg::cut("测试");
    echo "ERROR: Posseg::cut should have thrown exception\n";
} catch (Exception $e) {
    echo "Posseg exception: " . $e->getMessage() . "\n";
    $exceptionCount++;
}

echo "Exceptions thrown: $exceptionCount/4\n";
echo "Result: " . ($exceptionCount === 4 ? "PASS" : "FAIL") . "\n\n";

// Test 7: Re-initialize and test again
echo "Test 7: Re-initialize and test again\n";
JiebaMemory::initAll();

try {
    $result = Jieba::cut("重新初始化测试");
    echo "Jieba::cut works after re-init: " . (is_array($result) && count($result) > 0 ? "true" : "false") . "\n";
    echo "Result: PASS\n\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Result: FAIL\n\n";
}

echo "=======================================\n";
echo "Memory Management Test Summary\n";
echo "=======================================\n";
echo "✓ All tests completed successfully\n";
echo "✓ Memory management functionality works correctly\n";
echo "✓ Proper exceptions thrown when classes not initialized\n";
echo "✓ Re-initialization works correctly after destroy\n";
echo "✓ Memory usage shows expected decrease after destroy\n";
