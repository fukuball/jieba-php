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

    public static $trie;
    public static $FREQ;
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
        self::$trie = Jieba::gen_trie();
        self::$FREQ = array();
        self::$min_freq = array();

        echo "loading model cost ".(microtime(true) - $t1)." seconds.\n";
        echo "Trie has been built succesfully.\n";

    }// end function init

    /**
     * Static method gen_trie
     *
     * @param array $options # other options
     *
     * @return void
     */
    public static function gen_trie($options=array())
    {

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
?>