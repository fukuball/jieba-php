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
 * @version  Release: <0.16>
 * @link     https://github.com/fukuball/jieba-php
 */
class JiebaAnalyse
{

    public static $idf_freq = array();
    public static $max_idf = 0;
    public static $median_idf = 0;
    public static $stop_words= [
        "the", "of", "is", "and", "to", "in", "that", "we",
        "for", "an", "are", "by", "be", "as", "on", "with",
        "can", "if", "from", "which", "you", "it", "this",
        "then", "at", "have", "all", "not", "one", "has",
        "or", "that"
    ];

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
            'mode'=>'default',
            'dict'=>'normal'
        );

        $options = array_merge($defaults, $options);

        if ($options['dict']=='big') {
            $f_name = "idf.big.txt";
        } else {
            $f_name = "idf.txt";
        }
        $content = fopen(dirname(dirname(__FILE__))."/dict/idf.txt", "r");

        while (($line = fgets($content)) !== false) {
            $explode_line = explode(" ", trim($line));
            $word = $explode_line[0];
            $freq = $explode_line[1];
            $freq = (float) $freq;
            self::$idf_freq[$word] = $freq;
        }
        fclose($content);

        asort(self::$idf_freq);
        $keys = array_keys(self::$idf_freq);
        $middle_key = $keys[count(self::$idf_freq)/2];
        self::$max_idf = max(self::$idf_freq);
        self::$median_idf = self::$idf_freq[$middle_key];
    }// end function init

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

        while (($line = fgets($content)) !== false) {
            $stop_word = strtolower(trim($line));
            if (! in_array($stop_word, self::$stop_words)) {
                self::$stop_words[] = $stop_word;
            }
        }
        fclose($content);
    }

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

        $defaults = array(
            'mode'=>'default'
        );

        $options = array_merge($defaults, $options);

        $tags = array();

        if (isset($options['allowPOS']) && is_array($options['allowPOS']) && !empty($options['allowPOS'])) {
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

        $freq = array();
        $total = 0.0;

        foreach ($words as $w) {
            $w = trim($w);
            if (mb_strlen($w, 'UTF-8')<2) {
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
            $freq[$k] = $v/$total;
        }

        $tf_idf_list = array();

        foreach ($freq as $k => $v) {
            if (isset(self::$idf_freq[$k])) {
                $idf_freq = self::$idf_freq[$k];
            } else {
                $idf_freq = self::$median_idf;
            }
            $tf_idf_list[$k] = $v * $idf_freq;
        }

        arsort($tf_idf_list);

        $tags = array_slice($tf_idf_list, 0, $top_k, true);

        return $tags;

    }// end function extractTags
}// end of class JiebaAnalyse
