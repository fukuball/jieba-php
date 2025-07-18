<?php

/**
 * Jieba.php
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

use Fukuball\Tebru\MultiArray;

define("MIN_FLOAT", -3.14e+100);

/**
 * Jieba
 *
 * @category PHP
 * @package  /src/class/
 * @author   Fukuball Lin <fukuball@gmail.com>
 * @license  MIT Licence
 * @link     https://github.com/fukuball/jieba-php
 */
class Jieba
{
    public static $total = 0.0;
    public static $trie = array();
    public static $FREQ = array();
    public static $original_freq = array();
    public static $min_freq = 0.0;
    public static $route = array();
    public static $dictname;
    public static $user_dictname = array();
    public static $cjk_all = false;
    public static $dag_cache = array();
    public static $enable_logging = true;
    public static $is_initialized = false;
    public static $word_pos = array();
    const MAX_CACHE_SIZE = 52428800; // 50MB in bytes

    /**
     * Static method init
     *
     * @param array $options # other options
     *
     * @return void
     */
    public static function init($options = array())
    {
        $defaults = array(
            'mode' => 'default',
            'dict' => 'normal',
            'cjk' => 'chinese'
        );

        $options = array_merge($defaults, $options);

        if ($options['mode'] == 'test') {
            echo "Building Trie...\n";
        }

        if ($options['dict'] == 'small') {
            $f_name = "dict.small.txt";
            self::$dictname = "dict.small.txt";
        } elseif ($options['dict'] == 'big') {
            $f_name = "dict.big.txt";
            self::$dictname = "dict.big.txt";
        } else {
            $f_name = "dict.txt";
            self::$dictname = "dict.txt";
        }

        if ($options['cjk'] == 'all') {
            self::$cjk_all = true;
        } else {
            self::$cjk_all = false;
        }

        $t1 = microtime(true);
        self::$dag_cache = array();
        self::$trie = Jieba::genTrie(dirname(dirname(__FILE__)) . "/dict/" . $f_name);
        self::__calcFreq();

        if ($options['mode'] == 'test') {
            echo "loading model cost " . (microtime(true) - $t1) . " seconds.\n";
            echo "Trie has been built succesfully.\n";
        }

        self::$is_initialized = true;
    } // end function init

    /**
     * Static method destroy - Free all memory used by the class
     *
     * This method clears all static variables that contain large data structures
     * to free memory. After calling this method, init() must be called again
     * before using any other methods.
     *
     * @return void
     */
    public static function destroy()
    {
        // Clear all large data structures
        self::$trie = null;
        self::$FREQ = array();
        self::$original_freq = array();
        self::$dag_cache = array();
        self::$route = array();
        self::$user_dictname = array();
        self::$word_pos = array();

        // Reset numeric values
        self::$total = 0.0;
        self::$min_freq = 0.0;

        // Reset flags
        self::$dictname = null;
        self::$cjk_all = false;
        self::$is_initialized = false;

        // Force garbage collection
        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    } // end function destroy

    /**
     * Static method isInitialized - Check if the class has been initialized
     *
     * @return bool True if initialized, false otherwise
     */
    public static function isInitialized()
    {
        return self::$is_initialized;
    } // end function isInitialized

    /**
     * Static method requireInitialization - Throws exception if not initialized
     *
     * @return void
     * @throws \Exception if not initialized
     */
    private static function requireInitialization()
    {
        if (!self::$is_initialized) {
            throw new \Exception("Jieba class not initialized. Please call Jieba::init() first.");
        }
    } // end function requireInitialization

    /**
     * Static method logError - configurable logging mechanism
     *
     * @param string $message # error message to log
     *
     * @return void
     */
    private static function logError($message)
    {
        if (self::$enable_logging) {
            error_log($message);
        }
    } // end function logError

    /**
     * Static method setLogging - enable or disable logging
     *
     * @param boolean $enabled # enable logging or not
     *
     * @return void
     */
    public static function setLogging($enabled)
    {
        self::$enable_logging = (bool) $enabled;
    } // end function setLogging

    /**
     * Static method validateCachePath - validate cache file path against directory traversal
     *
     * @param string $f_name     # dictionary file name
     * @param string $cache_file # cache file path
     *
     * @return void
     * @throws \Exception if path is invalid
     */
    private static function validateCachePath($f_name, $cache_file)
    {
        // Get real paths to prevent directory traversal attacks
        $dict_dir = dirname($f_name);
        $cache_dir = dirname($cache_file);

        // Get real paths, handling non-existent directories
        $real_dict_path = is_dir($dict_dir) ? realpath($dict_dir) : false;
        $real_cache_path = is_dir($cache_dir) ? realpath($cache_dir) : false;

        // If directories don't exist, compare the normalized paths
        if ($real_dict_path === false || $real_cache_path === false) {
            $normalized_dict_path = rtrim(str_replace('\\', '/', $dict_dir), '/');
            $normalized_cache_path = rtrim(str_replace('\\', '/', $cache_dir), '/');

            if ($normalized_dict_path !== $normalized_cache_path) {
                throw new \Exception("Invalid cache file path: directory mismatch");
            }
        } else {
            // Compare real paths if directories exist
            if ($real_dict_path !== $real_cache_path) {
                throw new \Exception("Invalid cache file path: directory traversal detected");
            }
        }

        // Additional check: ensure cache filename is just dictionary filename + .cache
        $expected_cache_name = basename($f_name) . '.cache';
        $actual_cache_name = basename($cache_file);

        if ($expected_cache_name !== $actual_cache_name) {
            throw new \Exception("Invalid cache file name");
        }
    } // end function validateCachePath

    /**
     * Static method processNormalDictionary - extract normal dictionary processing
     *
     * @param string $f_name # dictionary file name
     *
     * @return MultiArray self::$trie
     */
    private static function processNormalDictionary($f_name)
    {
        // Process dictionary file normally if no cache or cache is invalid
        $content = fopen($f_name, "r");
        if ($content === false) {
            throw new \Exception("Unable to open dictionary file: " . $f_name);
        }

        while (($line = fgets($content)) !== false) {
            $explode_line = explode(" ", trim($line));

            // Skip malformed lines
            if (count($explode_line) < 2) {
                continue;
            }

            $word = $explode_line[0];
            $freq = $explode_line[1];
            $tag = isset($explode_line[2]) ? $explode_line[2] : '';
            $freq = (float) $freq;

            // Update frequency data
            if (isset(self::$original_freq[$word])) {
                self::$total -= self::$original_freq[$word];
            }
            self::$original_freq[$word] = $freq;
            self::$total += $freq;

            // Store POS tag information if available
            if (!empty($tag)) {
                self::$word_pos[$word] = $tag;
            }
        }
        fclose($content);

        // Create cache file to improve future loading performance
        $cache_file = $f_name . '.cache';

        // Validate cache file path against directory traversal attacks
        self::validateCachePath($f_name, $cache_file);

        try {
            // Validate cache directory permissions
            $cache_dir = dirname($cache_file);
            if (!is_dir($cache_dir) || !is_writable($cache_dir)) {
                throw new \Exception("Cache directory not writable: " . $cache_dir);
            }

            $cache_data = array(
                'original_freq' => self::$original_freq,
                'total' => self::$total,
                'word_pos' => self::$word_pos
            );

            $json_data = json_encode($cache_data);
            if ($json_data === false) {
                throw new \JsonException("Failed to encode cache data to JSON");
            }

            // Write with explicit permissions and file locking
            if (file_put_contents($cache_file, $json_data, LOCK_EX) === false) {
                throw new \Exception("Failed to write cache file");
            }

            // Set explicit file permissions (readable by owner and group)
            chmod($cache_file, 0644);
        } catch (\JsonException $e) {
            // JSON encode failures
            self::logError("Cache file JSON encoding failed: " . $e->getMessage());
        } catch (\Exception $e) {
            // Other cache creation failures should not stop normal operation
            self::logError("Cache file creation failed: " . $e->getMessage());
        }

        return self::$trie;
    } // end function processNormalDictionary

    /**
     * Static method __calcFreq
     *
     * @param void
     *
     * @return void
     */
    public static function __calcFreq()
    {
        foreach (self::$original_freq as $key => $value) {
            self::$FREQ[$key] = log($value / self::$total);
        }
        self::$min_freq = min(self::$FREQ);
    } // end function __calcFreq

    /**
     * Static method calc
     *
     * @param string $sentence # input sentence
     * @param array  $DAG      # DAG
     * @param array  $options  # other options
     *
     * @return array self::$route
     */
    public static function calc($sentence, $DAG, $options = array())
    {
        $N = mb_strlen($sentence, 'UTF-8');
        self::$route = array();
        self::$route[$N] = array($N => 0.0);
        for ($i = ($N - 1); $i >= 0; $i--) {
            $candidates = array();
            foreach ($DAG[$i] as $x) {
                $w_c = mb_substr($sentence, $i, (($x + 1) - $i), 'UTF-8');
                $previous_freq = current(self::$route[$x + 1]);
                if (isset(self::$FREQ[$w_c])) {
                    $current_freq = (float) $previous_freq + self::$FREQ[$w_c];
                } else {
                    $current_freq = (float) $previous_freq + self::$min_freq;
                }
                $candidates[$x] = $current_freq;
            }
            arsort($candidates);
            $max_prob = reset($candidates);
            $max_key = key($candidates);
            self::$route[$i] = array($max_key => $max_prob);
        }

        return self::$route;
    } // end function calc

    /**
     * Static method genTrie
     *
     * @param string $f_name  # input f_name
     * @param array  $options # other options
     *
     * @return array self::$trie
     */
    public static function genTrie($f_name, $options = array())
    {
        $defaults = array(
            'mode' => 'default'
        );

        $options = array_merge($defaults, $options);

        $trie_content = file_get_contents($f_name . '.json');
        if ($trie_content === false) {
            throw new \Exception("Failed to read trie file: " . $f_name . '.json');
        }
        self::$trie = new MultiArray($trie_content);

        // Check if cache file exists and is valid for performance optimization
        $cache_file = $f_name . '.cache';

        // Validate cache file path against directory traversal attacks
        self::validateCachePath($f_name, $cache_file);

        if (file_exists($cache_file)) {
            // Check if cache is newer than dictionary file
            $dict_mtime = filemtime($f_name);
            $cache_mtime = filemtime($cache_file);

            if ($cache_mtime >= $dict_mtime) {
                // Load cached frequency data to avoid re-processing dictionary
                try {
                    // Check cache file size before loading to prevent memory issues
                    $cache_size = filesize($cache_file);
                    if ($cache_size === false) {
                        throw new \Exception("Unable to get cache file size");
                    }

                    if ($cache_size > self::MAX_CACHE_SIZE) {
                        self::logError(
                            "Cache file too large ($cache_size bytes), regenerating"
                        );
                        @unlink($cache_file);
                        // Fall back to normal processing
                        return self::processNormalDictionary($f_name);
                    }

                    $cache_content = file_get_contents($cache_file);
                    if ($cache_content === false) {
                        throw new \Exception("Unable to read cache file content");
                    }

                    $cache_data = json_decode($cache_content, true);

                    // Verify cache data integrity
                    if ($cache_data !== null &&
                        isset($cache_data['original_freq']) &&
                        isset($cache_data['total']) &&
                        is_array($cache_data['original_freq']) &&
                        is_numeric($cache_data['total'])
                    ) {
                        self::$original_freq = $cache_data['original_freq'];
                        self::$total = (float) $cache_data['total'];

                        // Load word_pos data if available (for backward compatibility with old cache files)
                        if (isset($cache_data['word_pos']) && is_array($cache_data['word_pos'])) {
                            self::$word_pos = $cache_data['word_pos'];
                        }

                        return self::$trie;
                    }
                } catch (\JsonException $e) {
                    // JSON decode/encode failures
                    self::logError(
                        "Cache file JSON parsing failed, falling back to normal processing: " . $e->getMessage()
                    );
                } catch (\Exception $e) {
                    // Other unexpected errors
                    self::logError(
                        "Cache file reading failed, falling back to normal processing: " . $e->getMessage()
                    );
                }
            } else {
                // Cache is outdated, remove it to force regeneration
                @unlink($cache_file);
            }
        }

        // Process dictionary file normally if no cache or cache is invalid
        return self::processNormalDictionary($f_name);
    } // end function genTrie

    /**
     * Static method loadUserDict
     *
     * @param string $f_name  # input f_name
     * @param array  $options # other options
     *
     * @return array self::$trie
     */
    public static function loadUserDict($f_name, $options = array())
    {
        self::requireInitialization();

        self::$user_dictname[] = $f_name;
        $content = fopen($f_name, "r");
        if ($content === false) {
            throw new \Exception("Failed to open user dictionary file: " . $f_name);
        }

        try {
            while (($line = fgets($content)) !== false) {
                $explode_line = explode(" ", trim($line));
                $word = $explode_line[0];
                $freq = isset($explode_line[1]) ? $explode_line[1] : 1;
                $tag = isset($explode_line[2]) ? $explode_line[2] : null;
                $freq = (float) $freq;
                if (isset(self::$original_freq[$word])) {
                    self::$total -= self::$original_freq[$word];
                }
                self::$original_freq[$word] = $freq;
                self::$total += $freq;
                $l = mb_strlen($word, 'UTF-8');
                $word_c = array();
                for ($i = 0; $i < $l; $i++) {
                    $c = mb_substr($word, $i, 1, 'UTF-8');
                    $word_c[] = $c;
                }
                $word_c_key = implode('.', $word_c);
                self::$trie->set($word_c_key, array("end" => ""));
                
                // Add POS tag if provided and Posseg class is available
                if (!empty($tag) && class_exists('Fukuball\Jieba\Posseg')) {
                    try {
                        Posseg::addWordTag($word, $tag);
                    } catch (\InvalidArgumentException $e) {
                        // Log warning and skip this tag, but continue processing the word
                        error_log(
                            "Warning: Invalid POS tag '$tag' for word '$word' in user dictionary: " . $e->getMessage()
                        );
                        // Don't use continue here - let the word be processed even if tag is invalid
                    }
                }
            }
        } finally {
            fclose($content);
        }
        self::__calcFreq();
        self::$dag_cache = array();

        return self::$trie;
    } // end function loadUserDict

    /**
     * Static method addWord
     *
     * @param string $word
     * @param float  $freq
     * @param string $tag
     * @param array  $options
     *
     * @return array self::$trie
     * @throws \InvalidArgumentException When tag validation fails
     */
    public static function addWord($word, $freq, $tag = '', $options = array())
    {
        self::requireInitialization();

        if (isset(self::$original_freq[$word])) {
            self::$total -= self::$original_freq[$word];
        }
        self::$original_freq[$word] = $freq;
        self::$total += $freq;
        $l = mb_strlen($word, 'UTF-8');
        $word_c = array();
        for ($i = 0; $i < $l; $i++) {
            $c = mb_substr($word, $i, 1, 'UTF-8');
            $word_c[] = $c;
        }
        $word_c_key = implode('.', $word_c);
        self::$trie->set($word_c_key, array("end" => ""));
        
        // Handle POS tag if Posseg class is available
        if (class_exists('Fukuball\Jieba\Posseg')) {
            if (!empty($tag)) {
                try {
                    Posseg::addWordTag($word, $tag);
                } catch (\InvalidArgumentException $e) {
                    // Log the error or handle it as needed
                    // For now, we'll throw it up to let the caller handle it
                    throw $e;
                }
            } else {
                // If no tag provided, remove any existing tag to prevent memory leaks
                Posseg::removeWordTag($word);
            }
        }
        
        self::__calcFreq();
        self::$dag_cache = array();
        return self::$trie;
    }

    /**
     * Static method tokenize
     *
     * @param string $sentence
     *
     * @return array
     */
    public static function tokenize($sentence, $options = array("HMM" => true))
    {
        self::requireInitialization();

        $defaults = array(
            'preserve_punctuation' => true,
            'HMM' => true
        );

        $options = array_merge($defaults, $options);

        $seg_list = self::cut($sentence, false, array(
            "HMM" => $options["HMM"],
            "preserve_punctuation" => $options["preserve_punctuation"]
        ));
        $tokenize_list = [];
        $start = 0;
        $end = 0;
        foreach ($seg_list as $seg) {
            $end = $start + mb_strlen($seg, 'UTF-8');
            $tokenize = [
                'word' => $seg,
                'start' => $start,
                'end' => $end
            ];
            $start = $end;
            $tokenize_list[] = $tokenize;
        }
        return $tokenize_list;
    }

    /**
     * Static method __cutAll
     *
     * @param string $sentence # input sentence
     * @param array  $options  # other options
     *
     * @return array $words
     */
    public static function __cutAll($sentence, $options = array())
    {
        $defaults = array(
            'mode' => 'default'
        );

        $options = array_merge($defaults, $options);

        $words = array();

        $DAG = self::getDAG($sentence);
        $old_j = -1;

        foreach ($DAG as $k => $L) {
            if (count($L) == 1 && $k > $old_j) {
                $word = mb_substr($sentence, $k, (($L[0] - $k) + 1), 'UTF-8');
                $words[] = $word;
                $old_j = $L[0];
            } else {
                foreach ($L as $j) {
                    if ($j > $k) {
                        $word = mb_substr($sentence, $k, ($j - $k) + 1, 'UTF-8');
                        $words[] = $word;
                        $old_j = $j;
                    }
                }
            }
        }

        return $words;
    } // end function __cutAll

    /**
     * Static method getDAG
     *
     * @param string $sentence # input sentence
     * @param array  $options  # other options
     *
     * @return array $DAG
     */
    public static function getDAG($sentence, $options = array())
    {
        $defaults = array(
            'mode' => 'default'
        );

        $options = array_merge($defaults, $options);

        $N = mb_strlen($sentence, 'UTF-8');
        $i = 0;
        $j = 0;
        $DAG = array();
        $word_c = array();

        while ($i < $N) {
            $c = mb_substr($sentence, $j, 1, 'UTF-8');
            if (count($word_c) == 0) {
                $next_word_key = $c;
            } else {
                $next_word_key = implode('.', $word_c) . '.' . $c;
            }

            if (isset(self::$dag_cache[$next_word_key])) {
                if (self::$dag_cache[$next_word_key]['exist']) {
                    $word_c[] = $c;
                    if (self::$dag_cache[$next_word_key]['end']) {
                        if (!isset($DAG[$i])) {
                            $DAG[$i] = array();
                        }
                        $DAG[$i][] = $j;
                    }
                    $j += 1;
                    if ($j >= $N) {
                        $word_c = array();
                        $i += 1;
                        $j = $i;
                    }
                } else {
                    $word_c = array();
                    $i += 1;
                    $j = $i;
                }
                continue;
            }

            if (self::$trie->exists($next_word_key)) {
                self::$dag_cache[$next_word_key] = array('exist' => true, 'end' => false);
                $word_c[] = $c;
                $next_word_key_value = self::$trie->get($next_word_key);
                if ($next_word_key_value == array("end" => "")
                    || isset($next_word_key_value["end"])
                    || isset($next_word_key_value[0]["end"])
                ) {
                    self::$dag_cache[$next_word_key]['end'] = true;
                    if (!isset($DAG[$i])) {
                        $DAG[$i] = array();
                    }
                    $DAG[$i][] = $j;
                }
                $j += 1;
                if ($j >= $N) {
                    $word_c = array();
                    $i += 1;
                    $j = $i;
                }
            } else {
                $word_c = array();
                $i += 1;
                $j = $i;
                self::$dag_cache[$next_word_key] = array('exist' => false);
            }
        }

        for ($i = 0; $i < $N; $i++) {
            if (!isset($DAG[$i])) {
                $DAG[$i] = array($i);
            }
        }

        return $DAG;
    } // end function getDAG

    /**
     * Static method __cutDAG
     *
     * @param string $sentence # input sentence
     * @param array  $options  # other options
     *
     * @return array $words
     */
    public static function __cutDAG($sentence, $options = array())
    {
        $defaults = array(
            'mode' => 'default'
        );

        $options = array_merge($defaults, $options);

        $words = array();

        $N = mb_strlen($sentence, 'UTF-8');
        $DAG = self::getDAG($sentence);

        self::calc($sentence, $DAG);

        $x = 0;
        $buf = '';

        while ($x < $N) {
            $current_route_keys = array_keys(self::$route[$x]);
            $y = $current_route_keys[0] + 1;
            $l_word = mb_substr($sentence, $x, ($y - $x), 'UTF-8');

            if (($y - $x) == 1) {
                $buf = $buf . $l_word;
            } else {
                if (mb_strlen($buf, 'UTF-8') > 0) {
                    if (mb_strlen($buf, 'UTF-8') == 1) {
                        $words[] = $buf;
                        $buf = '';
                    } else {
                        if (! isset(self::$FREQ[$buf])) {
                            $regognized = Finalseg::cut($buf);
                            foreach ($regognized as $key => $word) {
                                $words[] = $word;
                            }
                        } else {
                            $elem_array = preg_split('//u', $buf, -1, PREG_SPLIT_NO_EMPTY);
                            foreach ($elem_array as $word) {
                                $words[] = $word;
                            }
                        }
                        $buf = '';
                    }
                }
                $words[] = $l_word;
            }
            $x = $y;
        }

        if (mb_strlen($buf, 'UTF-8') > 0) {
            if (mb_strlen($buf, 'UTF-8') == 1) {
                $words[] = $buf;
            } else {
                if (! isset(self::$FREQ[$buf])) {
                    $regognized = Finalseg::cut($buf);
                    foreach ($regognized as $key => $word) {
                        $words[] = $word;
                    }
                } else {
                    $elem_array = preg_split('//u', $buf, -1, PREG_SPLIT_NO_EMPTY);
                    foreach ($elem_array as $word) {
                        $words[] = $word;
                    }
                }
            }
        }

        return $words;
    } // end function __cutDAG

    /**
     * Static method __cutDAGNoHMM
     *
     * @param string $sentence # input sentence
     * @param array  $options  # other options
     *
     * @return array $words
     */
    public static function __cutDAGNoHMM($sentence, $options = array())
    {
        $defaults = array(
            'mode' => 'default'
        );

        $options = array_merge($defaults, $options);

        $words = array();

        $N = mb_strlen($sentence, 'UTF-8');
        $DAG = self::getDAG($sentence);

        self::calc($sentence, $DAG);

        $x = 0;
        $buf = '';

        $re_eng_pattern = '[a-zA-Z+#]+';

        while ($x < $N) {
            $current_route_keys = array_keys(self::$route[$x]);
            $y = $current_route_keys[0] + 1;
            $l_word = mb_substr($sentence, $x, ($y - $x), 'UTF-8');

            if (preg_match('/' . $re_eng_pattern . '/u', $l_word)) {
                $buf = $buf . $l_word;
                $x = $y;
            } else {
                if (mb_strlen($buf, 'UTF-8') > 0) {
                    $words[] = $buf;
                    $buf = '';
                }
                $words[] = $l_word;
                $x = $y;
            }
        }

        if (mb_strlen($buf, 'UTF-8') > 0) {
            $words[] = $buf;
            $buf = '';
        }

        return $words;
    } // end function __cutDAGNoHMM

    /**
     * Static method cut
     *
     * @param string  $sentence # input sentence
     * @param boolean $cut_all  # cut_all or not
     * @param array   $options  # other options
     *
     * @return array $seg_list
     */
    public static function cut($sentence, $cut_all = false, $options = array("HMM" => true))
    {
        self::requireInitialization();

        $defaults = array(
            'mode' => 'default',
            'preserve_punctuation' => true,
            'HMM' => true,
            'with_pos' => false,
            'with_scores' => false
        );

        $options = array_merge($defaults, $options);

        $seg_list = array();

        $re_han_pattern = '([\x{4E00}-\x{9FA5}]+)';
        $re_han_with_ascii_pattern = '([\x{4E00}-\x{9FA5}a-zA-Z0-9+#&=\._]+)';
        $re_kanjikana_pattern = '([\x{3040}-\x{309F}\x{4E00}-\x{9FA5}]+)';
        $re_katakana_pattern = '([\x{30A0}-\x{30FF}]+)';
        $re_hangul_pattern = '([\x{AC00}-\x{D7AF}]+)';
        $re_ascii_pattern = '([a-zA-Z0-9+#&=\._\r\n]+)';
        $re_skip_pattern = '(\s+)';
        if ($cut_all) {
            $re_skip_pattern = '([a-zA-Z0-9+#&=\._\r\n]+)';
        }
        $re_punctuation_pattern = '([\x{ff5e}\x{ff01}\x{ff08}\x{ff09}\x{300e}' .
            '\x{300c}\x{300d}\x{300f}\x{3001}\x{ff1a}\x{ff1b}' .
            '\x{ff0c}\x{ff1f}\x{3002}]+)';

        if (self::$cjk_all) {
            $filter_pattern = $re_kanjikana_pattern .
                '|' . $re_katakana_pattern .
                '|' . $re_hangul_pattern;
        } else {
            $filter_pattern = $re_han_with_ascii_pattern;
        }

        preg_match_all(
            '/(' . $filter_pattern . '|' . $re_ascii_pattern . '|' . $re_punctuation_pattern . ')/u',
            $sentence,
            $matches,
            PREG_PATTERN_ORDER
        );
        $blocks = $matches[0];

        foreach ($blocks as $blk) {
            if (mb_strlen($blk, 'UTF-8') == 0) {
                continue;
            }

            if (preg_match('/' . $filter_pattern . '/u', $blk)) {
                if ($cut_all) {
                    $words = Jieba::__cutAll($blk);
                } else {
                    if ($options['HMM']) {
                        $words = Jieba::__cutDAG($blk);
                    } else {
                        $words = Jieba::__cutDAGNoHMM($blk);
                    }
                }

                foreach ($words as $word) {
                    $seg_list[] = $word;
                }
            } elseif (preg_match('/' . $re_skip_pattern . '/u', $blk)) {
                preg_match_all(
                    '/(' . $re_skip_pattern . ')/u',
                    $blk,
                    $tmp,
                    PREG_PATTERN_ORDER
                );
                $tmp = $tmp[0];
                foreach ($tmp as $x) {
                    if (preg_match('/' . $re_skip_pattern . '/u', $x)) {
                        if (str_replace(' ', '', $x) != '') {
                            $seg_list[] = $x;
                        }
                    } else {
                        if (!$cut_all) {
                            $xx_array = preg_split('//u', $x, -1, PREG_SPLIT_NO_EMPTY);
                            foreach ($xx_array as $xx) {
                                $seg_list[] = $xx;
                            }
                        } else {
                            $seg_list[] = $x;
                        }
                    }
                }
            } elseif (preg_match('/' . $re_punctuation_pattern . '/u', $blk)) {
                if ($options['preserve_punctuation']) {
                    $seg_list[] = $blk;
                }
            } // end else (preg_match('/'.$re_han_pattern.'/u', $blk))
        } // end foreach ($blocks as $blk)

        // Enhanced return format with POS tags and/or scores if requested
        if ($options['with_pos'] || $options['with_scores']) {
            $enhanced_results = array();

            // Initialize JiebaAnalyse if scores are needed
            if ($options['with_scores']) {
                if (!JiebaAnalyse::isInitialized()) {
                    JiebaAnalyse::init();
                }

                // Calculate TF-IDF scores
                $tf_values = JiebaAnalyse::calculateTF($seg_list);
                $tfidf_values = JiebaAnalyse::calculateTFIDF($tf_values, true);
            }

            foreach ($seg_list as $word) {
                $result = array('word' => $word);

                // Add POS tag if requested
                if ($options['with_pos']) {
                    $result['tag'] = isset(self::$word_pos[$word]) ? self::$word_pos[$word] : 'x';
                }

                // Add TF-IDF scores if requested
                if ($options['with_scores']) {
                    if (isset($tfidf_values[$word])) {
                        $result['tf'] = $tfidf_values[$word]['tf'];
                        $result['tfidf'] = $tfidf_values[$word]['tfidf'];
                    } else {
                        // For words filtered out (stop words, short words), set scores to 0
                        $result['tf'] = 0.0;
                        $result['tfidf'] = 0.0;
                    }
                }

                $enhanced_results[] = $result;
            }

            return $enhanced_results;
        }

        return $seg_list;
    } // end function cut

    /**
     * Static method cutForSearch
     *
     * @param string  $sentence # input sentence
     * @param array   $options  # other options
     *
     * @return array $seg_list
     */
    public static function cutForSearch($sentence, $options = array("HMM" => true))
    {
        self::requireInitialization();

        $defaults = array(
            'mode' => 'default',
            'preserve_punctuation' => true,
            'HMM' => true
        );

        $options = array_merge($defaults, $options);

        $seg_list = array();

        $cut_seg_list = Jieba::cut($sentence, false, array(
            "HMM" => $options["HMM"],
            "preserve_punctuation" => $options["preserve_punctuation"]
        ));

        foreach ($cut_seg_list as $w) {
            $len = mb_strlen($w, 'UTF-8');

            if ($len > 2) {
                for ($i = 0; $i < ($len - 1); $i++) {
                    $gram2 = mb_substr($w, $i, 2, 'UTF-8');

                    if (isset(self::$FREQ[$gram2])) {
                        $seg_list[] = $gram2;
                    }
                }
            }

            if (mb_strlen($w, 'UTF-8') > 3) {
                for ($i = 0; $i < ($len - 2); $i++) {
                    $gram3 = mb_substr($w, $i, 3, 'UTF-8');

                    if (isset(self::$FREQ[$gram3])) {
                        $seg_list[] = $gram3;
                    }
                }
            }

            $seg_list[] = $w;
        }

        return $seg_list;
    } // end function cutForSearch

    /**
     * Clear all caches to free memory
     *
     * This method clears both the DAG cache and the trie cache to free up memory.
     * Useful when processing multiple large text files to prevent memory issues.
     * Note: This will reset performance optimizations and may impact processing speed
     * for subsequent operations until caches are rebuilt.
     *
     * @return void
     */
    public static function clearCache()
    {
        // Clear DAG cache
        self::$dag_cache = array();

        // Clear trie cache if trie is initialized
        if (self::$trie instanceof \Fukuball\Tebru\MultiArray) {
            self::$trie->cache = array();
        }
    } // end function clearCache

    /**
     * Get cache statistics
     *
     * Returns information about current cache usage for monitoring purposes.
     *
     * @return array Array containing cache statistics
     */
    public static function getCacheStats()
    {
        $stats = array(
            'dag_cache_size' => count(self::$dag_cache),
            'trie_cache_size' => 0,
            'total_memory_usage' => memory_get_usage(),
            'peak_memory_usage' => memory_get_peak_usage()
        );

        if (self::$trie instanceof \Fukuball\Tebru\MultiArray) {
            $stats['trie_cache_size'] = count(self::$trie->cache);
        }

        return $stats;
    } // end function getCacheStats

    /**
     * Clear cache if it exceeds specified size limits
     *
     * This method provides automatic cache management by clearing caches
     * when they exceed the specified size limits.
     *
     * @param int $dag_cache_limit Maximum number of entries in DAG cache (default: 50000)
     * @param int $trie_cache_limit Maximum number of entries in trie cache (default: 50000)
     *
     * @return bool True if cache was cleared, false otherwise
     */
    public static function clearCacheIfNeeded($dag_cache_limit = 50000, $trie_cache_limit = 50000)
    {
        $dag_size = count(self::$dag_cache);
        $trie_size = (self::$trie instanceof \Fukuball\Tebru\MultiArray) ? count(self::$trie->cache) : 0;

        if ($dag_size > $dag_cache_limit || $trie_size > $trie_cache_limit) {
            self::clearCache();
            return true;
        }

        return false;
    } // end function clearCacheIfNeeded
}// end of class Jieba
