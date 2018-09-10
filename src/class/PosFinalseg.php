<?php
/**
 * PosFinalseg.php
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
 * PosFinalseg
 *
 * @category PHP
 * @package  /src/class/
 * @author   Fukuball Lin <fukuball@gmail.com>
 * @license  MIT Licence
 * @version  Release: <0.16>
 * @link     https://github.com/fukuball/jieba-php
 */
class PosFinalseg
{
    public static $prob_start = array();
    public static $prob_trans = array();
    public static $prob_emit = array();
    public static $char_state = array();

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
            'mode'=>'default'
        );

        $options = array_merge($defaults, $options);

        self::$prob_start = self::loadModel(dirname(dirname(__FILE__)).'/model/pos/prob_start.json');
        self::$prob_trans = self::loadModel(dirname(dirname(__FILE__)).'/model/pos/prob_trans.json');
        self::$prob_emit = self::loadModel(dirname(dirname(__FILE__)).'/model/pos/prob_emit.json');
        self::$char_state = self::loadModel(dirname(dirname(__FILE__)).'/model/pos/char_state.json');

    }// end function init

    /**
     * Static method loadModel
     *
     * @param string $f_name # input f_name
     * @param array $options # other options
     *
     * @return void
     */
    public static function loadModel($f_name, $options = array())
    {
        $defaults = array(
            'mode'=>'default'
        );

        $options = array_merge($defaults, $options);

        return json_decode(file_get_contents($f_name), true);
    }// end function loadModel

    /**
     * Static method getTopStates
     *
     * @param array $t_state_v # input t_state_v
     * @param int   $top_k     # input top_k
     * @param array $options   # other options
     *
     * @return array $top_states
     */
    public static function getTopStates($t_state_v, $top_k = 4, $options = array())
    {
        arsort($t_state_v);

        $top_states = array_slice($t_state_v, 0, $top_k);

        return $top_states;
    }// end function getTopStates
}