<?php
/**
 * ClassAutoloader.php
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
 * ClassAutoloader
 *
 * @category PHP
 * @package  /class/
 * @author   Fukuball Lin <fukuball@gmail.com>
 * @license  MIT Licence
 * @version  Release: <0.0.1>
 * @link     https://github.com/fukuball/iloveck101
 */
class ClassAutoloader
{

    /**
     * Method __construct
     *
     * @return void
     */
    public function __construct()
    {

        spl_autoload_register(array($this, 'loader'));

    }

    /**
     * Method loader
     *
     * @param string $className # input class namec
     *
     * @return void
     */
    private function loader($className)
    {

        //echo 'Trying to load ', $className, ' via ', __METHOD__, "()\n";
        include_once dirname(__FILE__).'/'.$className . '.php';

    }

}// en of class ClassAutoloader

$autoloader = new ClassAutoloader();
?>