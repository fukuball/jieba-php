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
ini_set('memory_limit', '1024M');

require_once dirname(dirname(__FILE__))."/vendor/multi-array/MultiArray.php";
require_once dirname(dirname(__FILE__))."/vendor/multi-array/Factory/MultiArrayFactory.php";
require_once dirname(dirname(__FILE__))."/class/Jieba.php";
require_once dirname(dirname(__FILE__))."/class/Finalseg.php";
use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
Jieba::init(array('mode'=>'test','dict'=>'big'));
Finalseg::init();

$seg_list = Jieba::cut("怜香惜玉也得要看对象啊！");
var_dump($seg_list);

$seg_list = Jieba::cut("憐香惜玉也得要看對象啊！");
var_dump($seg_list);

echo "Full Mode: \n";
$seg_list = Jieba::cut("我来到北京清华大学", true);
var_dump($seg_list);

echo "Full Mode: \n";
$seg_list = Jieba::cut("我來到北京清華大學", true);
var_dump($seg_list);

echo "Default Mode: \n";
$seg_list = Jieba::cut("我来到北京清华大学", false);
var_dump($seg_list);

echo "Default Mode: \n";
$seg_list = Jieba::cut("我來到北京清華大學", false);
var_dump($seg_list);

$seg_list = Jieba::cut("他来到了网易杭研大厦");
var_dump($seg_list);

$seg_list = Jieba::cut("他來到了網易杭研大廈");
var_dump($seg_list);

$seg_list = Jieba::cutForSearch("小明硕士毕业于中国科学院计算所，后在日本京都大学深造");
var_dump($seg_list);

$seg_list = Jieba::cutForSearch("小明碩士畢業于中國科學院計算所，後在日本京都大學深造");
var_dump($seg_list);
?>