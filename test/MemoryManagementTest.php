<?php

use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\JiebaAnalyse;
use Fukuball\Jieba\Posseg;
use Fukuball\Jieba\JiebaMemory;

/**
 * Memory Management Test
 *
 * This test verifies that the memory management functionality works correctly,
 * including destroy methods, initialization checks, and error handling.
 */
class MemoryManagementTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test that classes are not initialized by default
     */
    public function testClassesNotInitializedByDefault()
    {
        // Make sure we start with fresh state
        JiebaMemory::destroyAll();
        
        $this->assertFalse(Jieba::isInitialized(), 'Jieba should not be initialized by default');
        $this->assertFalse(Finalseg::isInitialized(), 'Finalseg should not be initialized by default');
        $this->assertFalse(JiebaAnalyse::isInitialized(), 'JiebaAnalyse should not be initialized by default');
        $this->assertFalse(Posseg::isInitialized(), 'Posseg should not be initialized by default');
    }

    /**
     * Test that initialization works correctly
     */
    public function testInitializationWorks()
    {
        // Initialize all classes
        ini_set('memory_limit', '1024M');
        JiebaMemory::initAll();
        
        $this->assertTrue(Jieba::isInitialized(), 'Jieba should be initialized after init');
        $this->assertTrue(Finalseg::isInitialized(), 'Finalseg should be initialized after init');
        $this->assertTrue(JiebaAnalyse::isInitialized(), 'JiebaAnalyse should be initialized after init');
        $this->assertTrue(Posseg::isInitialized(), 'Posseg should be initialized after init');
        
        $this->assertTrue(JiebaMemory::isAllInitialized(), 'All classes should be initialized');
    }

    /**
     * Test that destroy methods work correctly
     */
    public function testDestroyMethodsWork()
    {
        // Initialize all classes first
        ini_set('memory_limit', '1024M');
        JiebaMemory::initAll();
        
        // Verify they are initialized
        $this->assertTrue(JiebaMemory::isAllInitialized(), 'All classes should be initialized');
        
        // Destroy all classes
        JiebaMemory::destroyAll();
        
        // Verify they are no longer initialized
        $this->assertFalse(Jieba::isInitialized(), 'Jieba should not be initialized after destroy');
        $this->assertFalse(Finalseg::isInitialized(), 'Finalseg should not be initialized after destroy');
        $this->assertFalse(JiebaAnalyse::isInitialized(), 'JiebaAnalyse should not be initialized after destroy');
        $this->assertFalse(Posseg::isInitialized(), 'Posseg should not be initialized after destroy');
        
        $this->assertFalse(JiebaMemory::isAllInitialized(), 'No classes should be initialized after destroy');
    }

    /**
     * Test that individual destroy methods work
     */
    public function testIndividualDestroyMethods()
    {
        // Initialize all classes
        ini_set('memory_limit', '1024M');
        JiebaMemory::initAll();
        
        // Destroy Jieba individually
        Jieba::destroy();
        $this->assertFalse(Jieba::isInitialized(), 'Jieba should not be initialized after destroy');
        $this->assertTrue(Finalseg::isInitialized(), 'Finalseg should still be initialized');
        
        // Destroy Finalseg individually
        Finalseg::destroy();
        $this->assertFalse(Finalseg::isInitialized(), 'Finalseg should not be initialized after destroy');
        $this->assertTrue(JiebaAnalyse::isInitialized(), 'JiebaAnalyse should still be initialized');
        
        // Destroy JiebaAnalyse individually
        JiebaAnalyse::destroy();
        $this->assertFalse(JiebaAnalyse::isInitialized(), 'JiebaAnalyse should not be initialized after destroy');
        $this->assertTrue(Posseg::isInitialized(), 'Posseg should still be initialized');
        
        // Destroy Posseg individually
        Posseg::destroy();
        $this->assertFalse(Posseg::isInitialized(), 'Posseg should not be initialized after destroy');
    }

    /**
     * Test that exceptions are thrown when using uninitialized classes
     */
    public function testExceptionsForUninitializedClasses()
    {
        // Make sure all classes are destroyed
        JiebaMemory::destroyAll();
        
        // Test Jieba
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Jieba class not initialized. Please call Jieba::init() first.');
        Jieba::cut('测试');
    }

    /**
     * Test that Finalseg throws exception when uninitialized
     */
    public function testFinalsegExceptionWhenUninitialized()
    {
        JiebaMemory::destroyAll();
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Finalseg class not initialized. Please call Finalseg::init() first.');
        Finalseg::cut('测试');
    }

    /**
     * Test that JiebaAnalyse throws exception when uninitialized
     */
    public function testJiebaAnalyseExceptionWhenUninitialized()
    {
        JiebaMemory::destroyAll();
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('JiebaAnalyse class not initialized. Please call JiebaAnalyse::init() first.');
        JiebaAnalyse::extractTags('测试文本');
    }

    /**
     * Test that Posseg throws exception when uninitialized
     */
    public function testPossegExceptionWhenUninitialized()
    {
        JiebaMemory::destroyAll();
        
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Posseg class not initialized. Please call Posseg::init() first.');
        Posseg::cut('测试');
    }

    /**
     * Test that functionality works after re-initialization
     */
    public function testFunctionalityAfterReInitialization()
    {
        // Initialize, destroy, then re-initialize
        ini_set('memory_limit', '1024M');
        JiebaMemory::initAll();
        JiebaMemory::destroyAll();
        JiebaMemory::initAll();
        
        // Test that basic functionality works
        $result = Jieba::cut('中文分词测试');
        $this->assertIsArray($result, 'Jieba::cut should return an array');
        $this->assertGreaterThan(0, count($result), 'Jieba::cut should return non-empty array');
        
        $result = Finalseg::cut('测试');
        $this->assertIsArray($result, 'Finalseg::cut should return an array');
        
        $result = JiebaAnalyse::extractTags('这是一个测试文本用于关键词提取');
        $this->assertIsArray($result, 'JiebaAnalyse::extractTags should return an array');
        
        $result = Posseg::cut('词性标注测试');
        $this->assertIsArray($result, 'Posseg::cut should return an array');
    }

    /**
     * Test memory statistics functionality
     */
    public function testMemoryStatistics()
    {
        ini_set('memory_limit', '1024M');
        JiebaMemory::initAll();
        
        $stats = JiebaMemory::getMemoryStats();
        
        $this->assertArrayHasKey('current_memory_usage', $stats);
        $this->assertArrayHasKey('current_memory_usage_formatted', $stats);
        $this->assertArrayHasKey('peak_memory_usage', $stats);
        $this->assertArrayHasKey('peak_memory_usage_formatted', $stats);
        $this->assertArrayHasKey('initialization_status', $stats);
        
        $this->assertIsInt($stats['current_memory_usage']);
        $this->assertIsString($stats['current_memory_usage_formatted']);
        $this->assertIsInt($stats['peak_memory_usage']);
        $this->assertIsString($stats['peak_memory_usage_formatted']);
        $this->assertIsArray($stats['initialization_status']);
        
        // Test initialization status
        $initStatus = $stats['initialization_status'];
        $this->assertTrue($initStatus['Jieba']);
        $this->assertTrue($initStatus['Finalseg']);
        $this->assertTrue($initStatus['JiebaAnalyse']);
        $this->assertTrue($initStatus['Posseg']);
    }

    /**
     * Test initialization status functionality
     */
    public function testInitializationStatus()
    {
        // Start with destroyed state
        JiebaMemory::destroyAll();
        
        $status = JiebaMemory::getInitializationStatus();
        $this->assertFalse($status['Jieba']);
        $this->assertFalse($status['Finalseg']);
        $this->assertFalse($status['JiebaAnalyse']);
        $this->assertFalse($status['Posseg']);
        
        // Initialize and check again
        ini_set('memory_limit', '1024M');
        JiebaMemory::initAll();
        
        $status = JiebaMemory::getInitializationStatus();
        $this->assertTrue($status['Jieba']);
        $this->assertTrue($status['Finalseg']);
        $this->assertTrue($status['JiebaAnalyse']);
        $this->assertTrue($status['Posseg']);
    }

    /**
     * Test cache clearing functionality
     */
    public function testCacheClearingFunctionality()
    {
        ini_set('memory_limit', '1024M');
        JiebaMemory::initAll();
        
        // Use some functionality to build cache
        Jieba::cut('这是一个测试文本用于建立缓存');
        
        // Clear caches
        JiebaMemory::clearAllCaches();
        
        // Classes should still be initialized
        $this->assertTrue(JiebaMemory::isAllInitialized(), 'All classes should still be initialized after cache clear');
        
        // Functionality should still work
        $result = Jieba::cut('缓存清理后的测试');
        $this->assertIsArray($result, 'Jieba::cut should still work after cache clear');
    }

    /**
     * Test that memory is actually freed (basic test)
     */
    public function testMemoryIsFreed()
    {
        ini_set('memory_limit', '1024M');
        
        // Get initial memory usage
        $initialMemory = memory_get_usage(true);
        
        // Initialize all classes
        JiebaMemory::initAll();
        
        // Use some functionality to load data
        Jieba::cut('这是一个测试文本用于加载数据到内存');
        JiebaAnalyse::extractTags('这是一个测试文本用于关键词提取');
        
        // Get memory usage after initialization
        $afterInitMemory = memory_get_usage(true);
        
        // Destroy all classes
        JiebaMemory::destroyAll();
        
        // Get memory usage after destruction
        $afterDestroyMemory = memory_get_usage(true);
        
        // Memory should have increased after init and decreased after destroy
        $this->assertGreaterThan($initialMemory, $afterInitMemory, 'Memory should increase after initialization');
        $this->assertLessThan($afterInitMemory, $afterDestroyMemory, 'Memory should decrease after destruction');
    }

    /**
     * Clean up after each test
     */
    protected function tearDown(): void
    {
        // Clean up after each test
        JiebaMemory::destroyAll();
        parent::tearDown();
    }
}