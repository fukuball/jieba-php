#!/usr/bin/php
<?php
/**
 * demo_user_dict.php
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

$seg_list = Jieba::cut("李小福是创新办主任也是云计算方面的专家");
var_dump($seg_list);

Jieba::loadUserDict(dirname(dirname(__FILE__)).'/dict/user_dict.txt');

$seg_list = Jieba::cut("李小福是创新办主任也是云计算方面的专家");
var_dump($seg_list);

$seg_list = Jieba::cut("easy_install is great");
var_dump($seg_list);

$seg_list = Jieba::cut("python 的正则表达式是好用的");
var_dump($seg_list);
?>