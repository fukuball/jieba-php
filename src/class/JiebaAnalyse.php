<?php

/**
 * JiebaAnalyse.php
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
 * JiebaAnalyse
 *
 * @category PHP
 * @package  /src/class/
 * @author   Fukuball Lin <fukuball@gmail.com>
 * @license  MIT Licence
 * @link     https://github.com/fukuball/jieba-php
 */
class JiebaAnalyse
{

    public static $idf_freq = array();
    public static $max_idf = 0;
    public static $median_idf = 0;
    public static $stop_words = [
        "the",
        "of",
        "is",
        "and",
        "to",
        "in",
        "that",
        "we",
        "for",
        "an",
        "are",
        "by",
        "be",
        "as",
        "on",
        "with",
        "can",
        "if",
        "from",
        "which",
        "you",
        "it",
        "this",
        "then",
        "at",
        "have",
        "all",
        "not",
        "one",
        "has",
        "or",
        "that"
    ];
    public static $is_initialized = false;

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
            'dict' => 'normal'
        );

        $options = array_merge($defaults, (array)$options);

        if ($options['dict'] == 'big') {
            $f_name = "idf.big.txt";
        } else {
            $f_name = "idf.txt";
        }
        $content = fopen(dirname(dirname(__FILE__)) . "/dict/" . $f_name, "r");

        if ($content === false) {
            throw new \Exception("Failed to open IDF dictionary file: " . $f_name);
        }

        try {
            while (($line = fgets($content)) !== false) {
                $explode_line = explode(" ", trim($line));
                $word = $explode_line[0];
                $freq = $explode_line[1];
                $freq = (float) $freq;
                self::$idf_freq[$word] = $freq;
            }
        } finally {
            fclose($content);
        }

        asort(self::$idf_freq);
        $keys = array_keys(self::$idf_freq);
        $middle_key = $keys[count(self::$idf_freq) / 2];
        self::$max_idf = max(self::$idf_freq);
        self::$median_idf = self::$idf_freq[$middle_key];

        self::$is_initialized = true;
    } // end function init

    /**
     * Static method destroy - Free all memory used by the class
     *
     * This method clears all static variables that contain IDF data
     * to free memory. After calling this method, init() must be called again
     * before using any other methods.
     *
     * @return void
     */
    public static function destroy()
    {
        // Clear all IDF data
        self::$idf_freq = array();

        // Reset numeric values
        self::$max_idf = 0;
        self::$median_idf = 0;

        // Reset stop words to default
        self::$stop_words = [
            "the",
            "of",
            "is",
            "and",
            "to",
            "in",
            "that",
            "we",
            "for",
            "an",
            "are",
            "by",
            "be",
            "as",
            "on",
            "with",
            "can",
            "if",
            "from",
            "which",
            "you",
            "it",
            "this",
            "then",
            "at",
            "have",
            "all",
            "not",
            "one",
            "has",
            "or",
            "that"
        ];

        // Reset initialization flag
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
            throw new \Exception("JiebaAnalyse class not initialized. Please call JiebaAnalyse::init() first.");
        }
    } // end function requireInitialization

    /**
     * Static method setStopWords
     *
     * @param string  $stop_words_path
     * @param array   $options
     *
     * @return array $tags
     */
    public static function setStopWords($stop_words_path, $options = array())
    {
        $content = fopen($stop_words_path, "r");

        if ($content === false) {
            throw new \Exception("Failed to open stop words file: " . $stop_words_path);
        }

        try {
            while (($line = fgets($content)) !== false) {
                $stop_word = strtolower(trim($line));
                if (! in_array($stop_word, self::$stop_words)) {
                    self::$stop_words[] = $stop_word;
                }
            }
        } finally {
            fclose($content);
        }
    }

    /**
     * Static method calculateTF - Calculate Term Frequency
     *
     * @param array $words # array of words
     *
     * @return array $tf_values # array of word => tf_value
     */
    public static function calculateTF($words)
    {
        $freq = array();
        $total = 0.0;

        foreach ($words as $w) {
            $w = trim($w);
            if (mb_strlen($w, 'UTF-8') < 2) {
                continue;
            }

            if (in_array(strtolower($w), self::$stop_words)) {
                continue;
            }
            if (isset($freq[$w])) {
                $freq[$w] = $freq[$w] + 1.0;
            } else {
                $freq[$w] = 0.0 + 1.0;
            }
            $total = $total + 1.0;
        }

        foreach ($freq as $k => $v) {
            $freq[$k] = $v / $total;
        }

        return $freq;
    } // end function calculateTF

    /**
     * Static method calculateTFIDF - Calculate TF-IDF values with detailed info
     *
     * @param array $tf_values # TF values array
     * @param bool  $detailed  # return detailed info including tf, idf, tfidf
     *
     * @return array $tfidf_values
     */
    public static function calculateTFIDF($tf_values, $detailed = false)
    {
        self::requireInitialization();

        $result = array();

        foreach ($tf_values as $word => $tf) {
            if (isset(self::$idf_freq[$word])) {
                $idf = self::$idf_freq[$word];
            } else {
                $idf = self::$median_idf;
            }
            $tfidf = $tf * $idf;

            if ($detailed) {
                $result[$word] = array(
                    'tf' => $tf,
                    'idf' => $idf,
                    'tfidf' => $tfidf
                );
            } else {
                $result[$word] = $tfidf;
            }
        }

        return $result;
    } // end function calculateTFIDF

    /**
     * Static method extractTags
     *
     * @param string  $content  # input content
     * @param int     $top_k    # top_k
     * @param array   $options  # other options
     *
     * @return array $tags
     */
    public static function extractTags($content, $top_k = 20, $options = array())
    {
        self::requireInitialization();

        $defaults = array(
            'mode' => 'default'
        );

        $options = array_merge($defaults, (array)$options);

        $tags = array();

        if (isset($options['allowPOS']) && is_array($options['allowPOS']) && !empty($options['allowPOS'])) {
            // Initialize Posseg if not already initialized
            if (empty(Posseg::$prob_start)) {
                Posseg::init();
            }

            $wordsPos = Posseg::cut($content);

            $words = array();
            foreach ($wordsPos as $key => $word) {
                if (in_array($word['tag'], $options['allowPOS'])) {
                    $words[] = $word['word'];
                }
            }
        } else {
            $words = Jieba::cut($content);
        }

        // Use modularized TF-IDF calculation
        $tf_values = self::calculateTF($words);
        $tf_idf_list = self::calculateTFIDF($tf_values);

        arsort($tf_idf_list);

        $tags = array_slice($tf_idf_list, 0, $top_k, true);

        return $tags;
    } // end function extractTags
}// end of class JiebaAnalyse
