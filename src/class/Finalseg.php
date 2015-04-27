<?php
/**
 * Finalseg.php
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
 * Finalseg
 *
 * @category PHP
 * @package  /class/
 * @author   Fukuball Lin <fukuball@gmail.com>
 * @license  MIT Licence
 * @version  Release: <0.0.1>
 * @link     https://github.com/fukuball/iloveck101
 */
class Finalseg
{

    public static $prob_start = array();
    public static $prob_trans = array();
    public static $prob_emit = array();

    /**
     * Static method init
     *
     * @param array $options # other options
     *
     * @return void
     */
    public static function init($options=array())
    {

        $defaults = array(
            'mode'=>'default'
        );

        $options = array_merge($defaults, $options);

        self::$prob_start = self::load_model(dirname(dirname(__FILE__)).'/model/prob_start.json');
        self::$prob_trans = self::load_model(dirname(dirname(__FILE__)).'/model/prob_trans.json');
        self::$prob_emit = self::load_model(dirname(dirname(__FILE__)).'/model/prob_emit.json');

    }// end function init

    /**
     * Static method load_model
     *
     * @param string $f_name # input f_name
     * @param array $options # other options
     *
     * @return void
     */
    public static function load_model($f_name, $options=array())
    {

        $defaults = array(
            'mode'=>'default'
        );

        $options = array_merge($defaults, $options);

        return json_decode(file_get_contents($f_name), true);

    }// end function load_model

    /**
     * Static method __cut
     *
     * @param string $sentence # input sentence
     * @param array  $options  # other options
     *
     * @return array $words
     */
    public static function __cut($sentence, $options=array())
    {

        $defaults = array(
            'mode'=>'default'
        );

        $options = array_merge($defaults, $options);

        $words = array();

        return $words;

    }// end function __cut

}// end of class Finalseg

Finalseg::init();
?>