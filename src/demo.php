#!/usr/bin/php
<?php
/**
 * demo.php
 *
 * PHP version 5
 *
 * @category PHP
 * @package  /
 * @author   Fukuball Lin <fukuball@gmail.com>
 * @license  MIT Licence
 * @version  GIT: <fukuball/iloveck101>
 * @link     https://github.com/fukuball/iloveck101
 */
ini_set('memory_limit', '1024M');

require_once dirname(__FILE__)."/vendor/multi-array/MultiArray.php";
require_once dirname(__FILE__)."/vendor/multi-array/Factory/MultiArrayFactory.php";
require_once dirname(__FILE__)."/class/Jieba.php";
require_once dirname(__FILE__)."/class/Finalseg.php";
use Fukuball\Jieba;
use Fukuball\Finalseg;
Jieba::init();
Finalseg::init();

$seg_list = Jieba::cut("他来到了网易考察大厦 A test，\n我来到北京清华大学");
var_dump($seg_list);
?>