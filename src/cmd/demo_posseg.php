#!/usr/bin/php
<?php
/**
 * demo_posseg.php
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
require_once dirname(dirname(__FILE__))."/class/Posseg.php";
use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use Fukuball\Jieba\Posseg;
Jieba::init(array('mode'=>'test','dict'=>'big'));
Finalseg::init();
Posseg::init();

$seg_list = Posseg::cut("这是一个伸手不见五指的黑夜。我叫孙悟空，我爱北京，我爱Python和C++。");
var_dump($seg_list);

$seg_list = Posseg::posTagReadable($seg_list);
var_dump($seg_list);

$seg_list = Posseg::cut("這是一個伸手不見五指的黑夜。我叫孫悟空，我愛北京，我愛Python和C++");
var_dump($seg_list);

$seg_list = Posseg::posTagReadable($seg_list);
var_dump($seg_list);

$seg_list = Posseg::cut("林志傑來到了網易杭研大廈", ['HMM' => false]);
var_dump($seg_list);

$seg_list = Posseg::posTagReadable($seg_list);
var_dump($seg_list);
?>