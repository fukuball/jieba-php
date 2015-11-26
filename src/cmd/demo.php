#!/usr/bin/php
<?php
/**
 * demo.php
 *
 * PHP version 5
 *
 * @category PHP
 * @package  /src/cmd/
 * @author   Fukuball Lin <fukuball@gmail.com>
 * @license  MIT Licence
 * @version  GIT: <fukuball/jieba-php>
 * @link     https://github.com/fukuball/jieba-php
 */
ini_set('memory_limit', '600M');

require_once dirname(dirname(__FILE__))."/vendor/multi-array/MultiArray.php";
require_once dirname(dirname(__FILE__))."/vendor/multi-array/Factory/MultiArrayFactory.php";
require_once dirname(dirname(__FILE__))."/class/Jieba.php";
require_once dirname(dirname(__FILE__))."/class/Finalseg.php";
use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
Jieba::init(array('mode'=>'test','dict'=>'samll'));
Finalseg::init();

$seg_list = Jieba::cut("怜香惜玉也得要看对象啊！");
var_dump($seg_list);

echo "Full Mode: \n";
$seg_list = Jieba::cut("我来到北京清华大学", true);
var_dump($seg_list);

echo "Default Mode: \n";
$seg_list = Jieba::cut("我来到北京清华大学", false);
var_dump($seg_list);

$seg_list = Jieba::cut("他来到了网易杭研大厦");
var_dump($seg_list);
?>