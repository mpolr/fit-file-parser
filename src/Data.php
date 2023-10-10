<?php

namespace Fit;

require_once __DIR__ . '/Enums.php';
/**
 * @author Karel Wesseling <karel@swc.nl>
 * @version 1.0
 * @copyright (c) 2013, Karel Wesseling
 * @license http://opensource.org/licenses/MIT The MIT License (MIT)
 * @package Fit
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in the
 * Software without restriction, including without limitation the rights to use, copy,
 * modify, merge, publish, distribute, sublicense, and/or sell copies of the Software,
 * and to permit persons to whom the Software is furnished to do so, subject to the
 * following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

/**
 * Class \Fit\Data
 * Helper class to generate well-formed data that can be written by the
 * \Fit\Writer class.
 *
 * @example examples/write_and_read_fit_data.php How to use this class
 * @uses \Zend_Io_Reader Binary file parser
 */
class Data
{
    /**
     * Convert Garmin epoch time to unix timestamp.
     */
    public static function timeToUnix(int $garminEpoch): int
    {
        return $garminEpoch + mktime(0, 0, 0, 12, 31, 1989);
    }

    /**
     * Convert unix time to Garmin epoch timestamp.
     */
    public static function timeToGarminEpoch(int $unixTime): int
    {
        return $unixTime - mktime(0, 0, 0, 12, 31, 1989);
    }

    private $_store = [];

    private $_filetype;

    /**
     * Add a message to the datastore.
     * @throws Exception
     */
    public function add(string $msg_name, array $msg_data): Data
    {
        if ($this->_filetype === null) {
            Exception::create(1005);
        }
        $msg_name   = (string)$msg_name;
        $msg_found  = false;
        $no_msgs    = count($this->_store[$this->_filetype]);
        if ($no_msgs > 0) {
            $last   = $this->_store[$this->_filetype][$no_msgs - 1];
            if ($last['name'] === $msg_name) {
                $this->_store[$this->_filetype][$no_msgs - 1]['data'][] = $msg_data;
                $msg_found = true;
            }
        }
        if (false === $msg_found) {
            $this->_store[$this->_filetype][] = [
                'name' => $msg_name,
                'data' => [
                    $msg_data
                ],
            ];
        }
        return $this;
    }

    public function getData(): array
    {
        return $this->_store;
    }

    /**
     * Set the filetype for the upcoming messages.
     * @return bool True when a known filetype was set, false when not found.
     * @throws Exception
     */
    public function setFile(int $type): bool
    {
        $ref = new \ReflectionClass('\Fit\FileType');
        $constants = $ref->getConstants();
        if (in_array($type, $constants)) {
            $this->_filetype = $type;
            $this->_store[$this->_filetype] = array();
            return true;
        }
        Exception::create(1004);
    }
}
