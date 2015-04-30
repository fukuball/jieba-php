<?php
/**
 * cn_to_zh.php
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

require_once dirname(dirname(__FILE__))."/vendor/zhconverter/Zhconverter.php";
use Seta0909\Zhconverter;

$content = fopen(dirname(dirname(__FILE__))."/dict/dict.txt", "r");
$zh_content = '';

while (($line = fgets($content)) !== false) {

    $zh_line = Zhconverter::translate($line,'TW');
    echo $zh_line;
    $zh_content = $zh_content.$zh_line;

}

file_put_contents(dirname(dirname(__FILE__))."/dict/dict.zh.txt", $zh_content);
?>