<?php

namespace Apps\Utils;

class Arrays
{
    /**
     * Convert an array of arrays into a flat array with just the values
     * @param mixed[] $arr The array that we will flatten
     * @param string $name Is the key to look for in the array
     * @return mixed[] $new The flat array
     */
    public function flattenArray($array, $name)
    {
        foreach ($array as $k => $v) {
            $new[$k] = $v[$name];
        }

        return $new;
    }

    /**
     * Convert a flat array to an int array. Non-integer values will be omitted. If there are no
     * ints in the array, we return an empty array.
     * @return int[] $intArray Which is a flat array of integers
     */
    public function toIntArray($array)
    {
        $intArray = array();

        foreach ($array as $item) {
            if (is_numeric($item)) {
                $intArray[] = (int)$item;
            }
        }

        return $intArray;
    }

    /**
     * Go through the given (flat) array and check if it contains only integers
     * @param array $array is the array to check
     * @return boolean
     */
    public function arrayContainsNonIntegers($array)
    {
        $nonIntegersCount = 0;
        foreach ($array as $item) {
            if (is_numeric($item) == false) {
                $nonIntegersCount++;
            }
        }

        return $nonIntegersCount > 0;
    }
}
