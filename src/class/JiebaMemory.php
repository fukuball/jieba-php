<?php
/**
 * JiebaMemory.php
 *
 * PHP version 5
 *
 * @category PHP
 * @package  /src/class/
 * @author   Fukuball Lin <fukuball@gmail.com>
 * @license  MIT Licence
 * @version  GIT: <fukuball/jieba-php>
 * @link     https://github.com/fukuball/jieba-php
 */

namespace Fukuball\Jieba;

/**
 * JiebaMemory - Memory management utility for all jieba-php classes
 *
 * This class provides convenient methods to manage memory usage across
 * all jieba-php classes. It includes methods to destroy all classes at once,
 * check initialization status, and get memory usage statistics.
 *
 * @category PHP
 * @package  /src/class/
 * @author   Fukuball Lin <fukuball@gmail.com>
 * @license  MIT Licence
 * @version  Release: <0.16>
 * @link     https://github.com/fukuball/jieba-php
 */
class JiebaMemory
{
    /**
     * Destroy all jieba-php classes to free memory
     *
     * This method calls destroy() on all jieba-php classes that have been
     * initialized. After calling this method, all classes will need to be
     * re-initialized before use.
     *
     * @return void
     */
    public static function destroyAll()
    {
        // Destroy all classes
        Jieba::destroy();
        Finalseg::destroy();
        JiebaAnalyse::destroy();
        Posseg::destroy();
        
        // Force garbage collection
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }// end function destroyAll

    /**
     * Check which classes are initialized
     *
     * Returns an array indicating which jieba-php classes are currently
     * initialized and ready for use.
     *
     * @return array Array of class names and their initialization status
     */
    public static function getInitializationStatus()
    {
        return array(
            'Jieba' => Jieba::isInitialized(),
            'Finalseg' => Finalseg::isInitialized(),
            'JiebaAnalyse' => JiebaAnalyse::isInitialized(),
            'Posseg' => Posseg::isInitialized()
        );
    }// end function getInitializationStatus

    /**
     * Get memory usage statistics
     *
     * Returns detailed memory usage information including current usage,
     * peak usage, and cache statistics if available.
     *
     * @return array Array containing memory usage statistics
     */
    public static function getMemoryStats()
    {
        $stats = array(
            'current_memory_usage' => memory_get_usage(true),
            'current_memory_usage_formatted' => self::formatBytes(memory_get_usage(true)),
            'peak_memory_usage' => memory_get_peak_usage(true),
            'peak_memory_usage_formatted' => self::formatBytes(memory_get_peak_usage(true)),
            'initialization_status' => self::getInitializationStatus()
        );
        
        // Add Jieba cache stats if initialized
        if (Jieba::isInitialized()) {
            $stats['jieba_cache_stats'] = Jieba::getCacheStats();
        }
        
        return $stats;
    }// end function getMemoryStats

    /**
     * Format bytes into human readable format
     *
     * @param int $bytes Number of bytes
     * @param int $precision Number of decimal places
     *
     * @return string Formatted string (e.g., "1.5 MB")
     */
    private static function formatBytes($bytes, $precision = 2)
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }// end function formatBytes

    /**
     * Initialize all jieba-php classes with default settings
     *
     * This convenience method initializes all jieba-php classes with their
     * default settings. Useful for quick setup.
     *
     * @param array $options Options to pass to each init method
     *
     * @return void
     */
    public static function initAll($options = array())
    {
        Jieba::init($options);
        Finalseg::init($options);
        JiebaAnalyse::init($options);
        Posseg::init($options);
    }// end function initAll

    /**
     * Check if all classes are initialized
     *
     * Returns true if all jieba-php classes are initialized and ready for use.
     *
     * @return bool True if all classes are initialized, false otherwise
     */
    public static function isAllInitialized()
    {
        return Jieba::isInitialized() &&
               Finalseg::isInitialized() &&
               JiebaAnalyse::isInitialized() &&
               Posseg::isInitialized();
    }// end function isAllInitialized

    /**
     * Clear all caches without destroying classes
     *
     * This method clears caches to free memory but keeps the classes
     * initialized. Useful for reducing memory usage during long-running
     * processes without requiring re-initialization.
     *
     * @return void
     */
    public static function clearAllCaches()
    {
        // Clear Jieba cache if initialized
        if (Jieba::isInitialized()) {
            Jieba::clearCache();
        }
        
        // Force garbage collection
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }// end function clearAllCaches
}// end of class JiebaMemory
