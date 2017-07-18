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
    public function isJson($data)
    {
        // To speed things up, we check the first character, which must be { or [
        if ($this->hasInvalidFirstCharacter($data) == true) {
            return false;
        }

        // Then we validate the rest (needs PHP >= 5.3). This isn't perfect, but good enough.
        // See comments in: https://stackoverflow.com/a/6041773/1488445
        if ($this->jsonDecodeProbeReturnsErrors($data) == true) {
            return false;
        }

        // No errors came up, so it should be valid
        return true;
    }

    /**
     * Check does the input begin with invalid characters. JSONs are allowed to start with numbers
     * but they are not expected in our use case, so we keep them as invalid.
     * @param string $string Is the input we check
     * @return boolean Result of the check
     */
    public function hasInvalidFirstCharacter($string)
    {
        $firstChar = substr($string, 0, 1);
        $valid = array('{', '[');
        return in_array($firstChar, $valid) == false;
    }

    /**
     * Check does the json_decode() process return any errors. This covers most of them, but I have
     * left out two cases (JSON_ERROR_RECURSION and JSON_ERROR_INF_OR_NAN) because the target webhost
     * has a version of PHP that does not support the cases. (PHP 5.4.40 < required PHP 5.5)
     * @param string $data Is the data to check
     * @return boolean Whether any errors appeared
     */
    public function jsonDecodeProbeReturnsErrors($data)
    {
        json_decode($data);
        switch (json_last_error()) {
            case JSON_ERROR_NONE:
                $error = ''; // JSON is valid // No error has occurred
                break;
            case JSON_ERROR_DEPTH:
                $error = 'The maximum stack depth has been exceeded.';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error = 'Invalid or malformed JSON.';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error = 'Control character error, possibly incorrectly encoded.';
                break;
            case JSON_ERROR_SYNTAX:
                $error = 'Syntax error, malformed JSON.';
                break;
            // PHP >= 5.3.3
            case JSON_ERROR_UTF8:
                $error = 'Malformed UTF-8 characters, possibly incorrectly encoded.';
                break;
            case JSON_ERROR_UNSUPPORTED_TYPE:
                $error = 'A value of a type that cannot be encoded was given.';
                break;
            default:
                $error = 'Unknown JSON error occured.';
                break;
        }
        return $error !== '';
    }
}
