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
    } // end function destroyAll

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
    } // end function getInitializationStatus

    /**
     * Get memory usage statistics
     *
     * Returns detailed memory usage information including current usage,
     * peak usage, and cache statistics if available.
     *
     * Note: Memory statistics may not immediately reflect memory freed by
     * destroy() methods due to PHP's garbage collection behavior. The reported
     * memory usage may remain high until PHP's garbage collector runs, even
     * after calling destroyAll() or individual destroy() methods.
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

        // Add cache statistics for all classes
        $stats['cache_stats'] = self::getAllCacheStats();

        return $stats;
    } // end function getMemoryStats

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
    } // end function formatBytes

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
    } // end function initAll

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
    } // end function isAllInitialized

    /**
     * Clear all caches without destroying classes
     *
     * This method clears caches to free memory but keeps the classes
     * initialized. Useful for reducing memory usage during long-running
     * processes without requiring re-initialization.
     *
     * Note: Currently only Jieba class has dynamic caches that benefit from
     * selective clearing. Other classes (Posseg, Finalseg, JiebaAnalyse) store
     * static model data that doesn't change during runtime and should be cleared
     * only via destroy() methods to avoid requiring re-initialization.
     *
     * @return void
     */
    public static function clearAllCaches()
    {
        // Clear Jieba cache if initialized
        // Note: Only Jieba has dynamic caches ($dag_cache, trie cache) that grow
        // during usage and benefit from selective clearing
        if (Jieba::isInitialized()) {
            Jieba::clearCache();
        }

        // Other classes don't have dynamic caches to clear:
        // - Posseg: stores static model data and word-tag mappings
        // - Finalseg: stores static HMM model data
        // - JiebaAnalyse: stores static IDF frequencies and stop words

        // Force garbage collection
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    } // end function clearAllCaches

    /**
     * Get cache statistics from all classes
     *
     * Returns detailed cache statistics for all jieba-php classes,
     * including dynamic caches and static data storage.
     *
     * @return array Array containing cache statistics for all classes
     */
    public static function getAllCacheStats()
    {
        $stats = array();

        // Jieba cache stats (dynamic caches)
        if (Jieba::isInitialized()) {
            $stats['jieba'] = Jieba::getCacheStats();
        } else {
            $stats['jieba'] = array(
                'dag_cache_size' => 0,
                'trie_cache_size' => 0,
                'memory_usage' => 0
            );
        }

        // Posseg cache stats (static model data)
        if (Posseg::isInitialized()) {
            $stats['posseg'] = array(
                'prob_start_size' => count(Posseg::$prob_start),
                'prob_trans_size' => count(Posseg::$prob_trans),
                'prob_emit_size' => count(Posseg::$prob_emit),
                'char_state_size' => count(Posseg::$char_state),
                'word_tag_size' => count(Posseg::$word_tag),
                'pos_tag_readable_size' => count(Posseg::$pos_tag_readable),
                'cache_type' => 'static_model_data'
            );
        } else {
            $stats['posseg'] = array(
                'prob_start_size' => 0,
                'prob_trans_size' => 0,
                'prob_emit_size' => 0,
                'char_state_size' => 0,
                'word_tag_size' => 0,
                'pos_tag_readable_size' => 0,
                'cache_type' => 'static_model_data'
            );
        }

        // Finalseg cache stats (static HMM model data)
        if (Finalseg::isInitialized()) {
            $stats['finalseg'] = array(
                'prob_start_size' => count(Finalseg::$prob_start),
                'prob_trans_size' => count(Finalseg::$prob_trans),
                'prob_emit_size' => count(Finalseg::$prob_emit),
                'cache_type' => 'static_hmm_model'
            );
        } else {
            $stats['finalseg'] = array(
                'prob_start_size' => 0,
                'prob_trans_size' => 0,
                'prob_emit_size' => 0,
                'cache_type' => 'static_hmm_model'
            );
        }

        // JiebaAnalyse cache stats (static IDF data)
        if (JiebaAnalyse::isInitialized()) {
            $stats['jieba_analyse'] = array(
                'idf_freq_size' => count(JiebaAnalyse::$idf_freq),
                'stop_words_size' => count(JiebaAnalyse::$stop_words),
                'cache_type' => 'static_idf_data'
            );
        } else {
            $stats['jieba_analyse'] = array(
                'idf_freq_size' => 0,
                'stop_words_size' => 0,
                'cache_type' => 'static_idf_data'
            );
        }

        return $stats;
    } // end function getAllCacheStats
}// end of class JiebaMemory
