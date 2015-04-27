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

    /**
     * Static method init
     *
     * @param array $options # other options
     *
     * @return void
     */
    public static function init($options=array())
    {

    }// end function init

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