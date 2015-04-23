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

    /**
     * Static method __cut_all
     *
     * @param string $sentence # input sentence
     * @param array  $options  # other options
     *
     * @return void
     */
    public static function __cut_all($sentence, $options=array())
    {

        echo "$sentence \n";
        echo "__cut_all \n";

    }// end function __cut_all

    /**
     * Static method __cut_DAG
     *
     * @param string $sentence # input sentence
     * @param array  $options  # other options
     *
     * @return void
     */
    public static function __cut_DAG($sentence, $options=array())
    {

        echo "$sentence \n";
        echo "__cut_DAG \n";

    }// end function __cut_DAG

    /**
     * Static method cut
     *
     * @param string  $sentence     # input sentence
     * @param boolean $cut_all      # cut_all or not
     * @param array   $options      # other options
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

            if ($cut_all) {
                $words = Jieba::__cut_all($blk);
            } else {
                $words = Jieba::__cut_DAG($blk);
            }

        }// end foreach ($blocks as $blk)

        return $seg_list;

    }// end function cut


}// end of class Jieba
?>