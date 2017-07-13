<?php

namespace VortechAPI\Apps\Utils;

class ArrayUtils
{
    /**
     * Convert an array of arrays into a flat array with just the values
     * @param mixed[] $arr The array that we will flatten
     * @param string $name Is the optional key to look for in the array
     * @return mixed[] $new The flat array
     */
    public function flattenArray($array, $name)
    {
        foreach ($array as $k => $v) {
            $new[$k] = $v[$name];
        }
        $this->array = $new;

        return $new;
    }

    /**
     * Convert a flat array to an int array. Non-integer values will be omitted. If there are no
     * ints in the array, we return an empty array.
     * @return int[] $intArray Which is a flat array of integers
     */
    public function toIntArray()
    {
        $intArray = array();

        if (count($this->array) > 0) {
            $intArray = array_map('intval', $this->array);
        }

        return $intArray;
    }
}