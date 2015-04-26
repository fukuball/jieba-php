<?php
/**
 * Jieba.php
 *
 * PHP version 5
 *
 * @category PHP
 * @package  /class/
 * @author   Fukuball Lin <fukuball@gmail.com>
 * @license  MIT Licence
 * @version  GIT: <fukuball/iloveck101>
 * @link     https://github.com/fukuball/iloveck101
 */

/**
 * Jieba
 *
 * @category PHP
 * @package  /class/
 * @author   Fukuball Lin <fukuball@gmail.com>
 * @license  MIT Licence
 * @version  Release: <0.0.1>
 * @link     https://github.com/fukuball/iloveck101
 */
class Jieba
{

    public static $total = 0.0;
    public static $trie = array();
    public static $FREQ = array();
    public static $min_freq;

    /**
     * Static method init
     *
     * @param array $options # other options
     *
     * @return void
     */
    public static function init($options=array())
    {

        echo "Building Trie...\n";

        $t1 = microtime(true);
        self::$trie = Jieba::gen_trie(dirname(dirname(__FILE__))."/dict/dict2.txt");
        foreach (self::$FREQ as $key=>$value) {
            self::$FREQ[$key] = ($value/self::$total);
        }
        self::$min_freq = min(self::$FREQ);

        echo "loading model cost ".(microtime(true) - $t1)." seconds.\n";
        echo "Trie has been built succesfully.\n";

    }// end function init

    /**
     * Static method gen_trie
     *
     * @param string $f_name  # input f_name
     * @param array  $options # other options
     *
     * @return array self::$trie
     */
    public static function gen_trie($f_name, $options=array())
    {

        $defaults = array(
            'mode'=>'default'
        );

        $options = array_merge($defaults, $options);

        $content = fopen($f_name, "r");

        while (($line = fgets($content)) !== false) {
            $explode_line = explode(" ", trim($line));
            $word = $explode_line[0];
            $freq = $explode_line[1];
            $freq = (float) $freq;
            self::$FREQ[$word] = $freq;
            self::$total += $freq;
            $l = mb_strlen($word, 'UTF-8');

            $word_c = array();
            for ($i=0; $i<$l; $i++) {
                $c = mb_substr($word, $i, 1, 'UTF-8');
                if ($i==0) {
                    if (!isset(self::$trie[$c])) {
                        self::$trie[$c] = array();
                    }
                } else {
                    $eval_trie_string = 'self::$trie[';
                    for ($j=0; $j<count($word_c); $j++) {
                        $eval_trie_string = $eval_trie_string.'$word_c['.$j.']][';
                    }
                    $eval_trie_string = substr($eval_trie_string, 0, -1);
                    eval('$key_not_exist = !isset('.$eval_trie_string.'[$c]);');
                    if ($key_not_exist) {
                        eval($eval_trie_string.'[$c] = array();');
                    }

                }
                array_push($word_c, $c);
            }
        }

        fclose($content);

        return self::$trie;

    }// end function gen_trie

    /**
     * Static method __cut_all
     *
     * @param string $sentence # input sentence
     * @param array  $options  # other options
     *
     * @return array $words
     */
    public static function __cut_all($sentence, $options=array())
    {

        $defaults = array(
            'mode'=>'default'
        );

        $options = array_merge($defaults, $options);

        $words = array();

        echo "$sentence \n";
        echo "__cut_all \n";

        return $words;

    }// end function __cut_all

    /**
     * Static method __cut_DAG
     *
     * @param string $sentence # input sentence
     * @param array  $options  # other options
     *
     * @return array $words
     */
    public static function __cut_DAG($sentence, $options=array())
    {

        $defaults = array(
            'mode'=>'default'
        );

        $options = array_merge($defaults, $options);

        $words = array();

        $N = mb_strlen($sentence, 'UTF-8');
        $i = 0;
        $j = 0;
        $p = self::$trie;
        $DAG = array();

        echo "$sentence \n";
        echo "__cut_DAG \n";

        return $words;

    }// end function __cut_DAG

    /**
     * Static method cut
     *
     * @param string  $sentence # input sentence
     * @param boolean $cut_all  # cut_all or not
     * @param array   $options  # other options
     *
     * @return array $seg_list
     */
    public static function cut($sentence, $cut_all=false, $options=array())
    {

        $defaults = array(
            'mode'=>'default'
        );

        $options = array_merge($defaults, $options);

        $seg_list = array();

        $re_han_pattern = '([\x{4E00}-\x{9FA5}]+)';
        $re_skip_pattern = '([a-zA-Z0-9+#\n]+)';
        preg_match_all('/('.$re_han_pattern.'|'.$re_skip_pattern.')/u', $sentence, $matches, PREG_PATTERN_ORDER);
        $blocks = $matches[0];

        foreach ($blocks as $blk) {

            if (preg_match('/'.$re_han_pattern.'/u', $blk)) {

                if ($cut_all) {
                    $words = Jieba::__cut_all($blk);
                } else {
                    $words = Jieba::__cut_DAG($blk);
                }

                foreach ($words as $word) {
                    array_push($seg_list, $word);
                }

            } else {

                array_push($seg_list, $blk);

            }// end else (preg_match('/'.$re_han_pattern.'/u', $blk))


        }// end foreach ($blocks as $blk)

        return $seg_list;

    }// end function cut


}// end of class Jieba

Jieba::init();
?>