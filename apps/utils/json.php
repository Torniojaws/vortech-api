<?php

namespace Apps\Utils;

class Json
{
    /**
     * Check that the data input is a valid JSON. Note that JSON spec allows for number strings etc.
     * but I don't expect to use those, so I don't check it. Simply checking for Object or Array will
     * suffice.
     * @param string $data The data to Check
     * @return boolean Is it valid or not
     */
    public function isJson(string $data)
    {
        return (
            $this->hasInvalidFirstCharacter($data) == false
            && $this->jsonDecodeProbeReturnsErrors($data) == false
        );
    }

    /**
     * Check does the input begin with invalid characters. JSONs are allowed to start with numbers
     * but they are not expected in our use case, so we keep them as invalid.
     * @param string $string Is the input we check
     * @return boolean Result of the check
     */
    public function hasInvalidFirstCharacter(string $string)
    {
        $firstChar = substr($string, 0, 1);
        $valid = array('{', '[');
        return in_array($firstChar, $valid) == false;
    }

    /**
     * Check does the json_decode() process return any errors (= returns a non-empty string)
     * @param string $data Is the data to check
     * @return boolean Whether any errors appeared
     */
    public function jsonDecodeProbeReturnsErrors(string $data)
    {
        json_decode($data);
        return json_last_error() !== 0;
    }
}
