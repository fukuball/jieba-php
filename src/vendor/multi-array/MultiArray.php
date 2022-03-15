<?php
/**
 * File MultiArray.php
 */

namespace Fukuball\Tebru;

use ArrayAccess;
use ArrayIterator;
use InvalidArgumentException;
use IteratorAggregate;
use OutOfBoundsException;
use Traversable;

interface JsonSerializable {

    public function jsonSerialize();

}

/**
 * Class MultiArray
 *
 * Attempts to ease access to multidimensional arrays. Created with the intention of
 * accessing json response values.
 *
 * @author Nate Brunette <n@tebru.net>
 */
class MultiArray implements IteratorAggregate, JsonSerializable, ArrayAccess
{
    const EXCEPTION_KEY_MISSING = 'Could not find key in array.';

    /**
     * How keys will be delimited
     *
     * @var string $keyDelimiter
     */
    private $keyDelimiter;

    /**
     * Stores array object was created with
     * @var array $storage
     */
    public $storage = array();

    /**
     * A cache of keys that have been verified and values
     *
     * @var array $cache
     */
    public $cache = array();

    /**
     * Constructor
     *
     * @param array|string $jsonOrArray An array or string. If a string is provided, attempts
     *     to json_decode() it into an associative array.
     * @param string $keyDelimiter How array key access will be delimited
     * @throws InvalidArgumentException
     */
    public function __construct($jsonOrArray, $keyDelimiter = '.')
    {
        if (is_string($jsonOrArray)) {
            $jsonOrArray = json_decode($jsonOrArray, true);

            if (null === $jsonOrArray) {
                throw new InvalidArgumentException('Could not decode json string into array.');
            }
        }

        if (!is_array($jsonOrArray)) {
            throw new InvalidArgumentException('Expected array or string, got ' . gettype($jsonOrArray));
        }

        $this->storage = $jsonOrArray;
        $this->keyDelimiter = $keyDelimiter;
    }

    /**
     * Determine if a key exists
     *
     * example:
     *     $jsonObject->exists('key1.key2');
     *
     * @param string $keyString
     * @return bool
     */
    public function exists($keyString)
    {
        if (true === $this->inCache($keyString)) {
            return true;
        }

        try {
            $this->get($keyString);
        } catch (OutOfBoundsException $e) {
            return false;
        }

        return true;
    }

    /**
     * Attempt to get a value by key
     *
     * example:
     *     $jsonObject->get('key1.key2');
     *
     * @param $keyString
     * @return mixed
     * @throws OutOfBoundsException If the key does not exist
     */
    public function get($keyString)
    {
        if (true === $this->inCache($keyString)) {
            return $this->cache[$keyString];
        }

        $keys = $this->getKeys($keyString);
        $value = $this->getValue($keys, $this->storage);
        $this->cache[$keyString] = $value;

        return $value;
    }

    /**
     * Set the value
     *
     * example:
     *     $jsonObject->set('key1.key2', 'value');
     *
     * @param string $keyString
     * @param mixed $value
     * @throws InvalidArgumentException
     */
    public function set($keyString, $value)
    {
        $keys = $this->getKeys($keyString);
        $this->setValue($keys, $this->storage, $value);

        // set or override cache
        $this->cache[$keyString] = $value;
    }

    /**
     * Remove by key
     *
     * example:
     *     $jsonObject->remove('key1.key2');
     *
     * @param string$keyString
     * @throws OutOfBoundsException
     */
    public function remove($keyString)
    {
        if (!$this->exists($keyString)) {
            throw new OutOfBoundsException(self::EXCEPTION_KEY_MISSING);
        }

        $keys = $this->getKeys($keyString);
        $this->unsetValue($keys, $this->storage);

        if ($this->inCache($keyString)) {
            unset($this->cache[$keyString]);
        }
    }

    /**
     * Get keys array
     *
     * @param string $keyString
     * @return array
     */
    private function getKeys($keyString)
    {
        return explode($this->keyDelimiter, $keyString);
    }

    /**
     * Recursive method to get a value
     *
     * Will continue to call method until $keys array is empty, then returns
     * the current value.
     *
     * @param array $keys
     * @param mixed $element
     * @return mixed
     * @throws OutOfBoundsException If the key doesn't exist
     */
    private function getValue(array &$keys, &$element)
    {
        $checkKey = array_shift($keys);

        if (!isset($element[$checkKey])) {
            throw new OutOfBoundsException(self::EXCEPTION_KEY_MISSING);
        }

        if (empty($keys)) {
            return $element[$checkKey];
        }

        return $this->getValue($keys, $element[$checkKey]);
    }

    /**
     * Set the value
     *
     * @param array $keys
     * @param mixed $element
     * @param mixed $value
     * @return mixed
     * @throws InvalidArgumentException If we try to set a key on a non-array
     */
    private function setValue(array &$keys, &$element, &$value)
    {
        $checkKey = array_shift($keys);

        if (empty($keys)) {
            if (!is_array($element)) {
                throw new InvalidArgumentException('Expected array, got ' . gettype($element));
            }

            if (!isset($element[$checkKey])) {
                $element[$checkKey] = $value;
            } else {
                if (!is_array($element[$checkKey])) {
                    $temp_value = $element[$checkKey];
                    $element[$checkKey] = array();
                    array_push($element[$checkKey], $temp_value);
                    array_push($element[$checkKey], $value);
                } else {
                    array_push($element[$checkKey], $value);
                }

            }

            return $element[$checkKey];
        }

        if (!isset($element[$checkKey])) {
            $element[$checkKey] = array();
        }

        if (!is_array($element[$checkKey])) {
            return $this->setValue($keys, $element, $value);
        } else {
            return $this->setValue($keys, $element[$checkKey], $value);
        }

    }

    /**
     * Unset a key
     *
     * @param array $keys
     * @param $element
     * @return null
     * @throws OutOfBoundsException If the key doesn't exist
     */
    private function unsetValue(array &$keys, &$element)
    {
        $checkKey = array_shift($keys);

        if (!isset($element[$checkKey])) {
            throw new OutOfBoundsException(self::EXCEPTION_KEY_MISSING);
        }

        if (empty($keys)) {
            unset($element[$checkKey]);

            return null;
        }

        return $this->unsetValue($keys, $element[$checkKey]);
    }

    /**
     * Check if the key is currently in the cache
     *
     * @param string $keyString
     * @return bool
     */
    private function inCache($keyString)
    {
        return isset($this->cache[$keyString]);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Retrieve an external iterator
     * @link http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     */
    public function getIterator() : Traversable
    {
        return new ArrayIterator($this->storage);
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        return $this->storage;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Whether a offset exists
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset <p>
     * An offset to check for.
     * </p>
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     */
    public function offsetExists($offset) : bool
    {
        return $this->exists($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to retrieve
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset <p>
     * The offset to retrieve.
     * </p>
     * @return mixed Can return all value types.
     */
    public function offsetGet($offset) : mixed
    {
        return $this->get($offset);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to set
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset <p>
     * The offset to assign the value to.
     * </p>
     * @param mixed $value <p>
     * The value to set.
     * </p>
     * @return void
     */
    public function offsetSet($offset, $value) : void
    {
        $this->set($offset, $value);
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset) : void
    {
        $this->remove($offset);
    }
}
