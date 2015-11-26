<?php
/**
 * File MultiArrayFactory.php
 */

namespace Fukuball\Tebru\Factory;

use Fukuball\Tebru\MultiArray;

/**
 * Class MultiArrayFactory
 *
 * @author Nate Brunette <n@tebru.net>
 */
class MultiArrayFactory
{
    public function make($jsonOrArray, $delimiter = '.')
    {
        return new MultiArray($jsonOrArray, $delimiter);
    }
}
