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
 * Class \Fit\Core
 * Baseclass for \Fit\Reader and \Fit\Writer that handles the \Fit\ProductProfile
 * and adds some helper function for reading and writing data.
 */
abstract class Core
{
    const ENUM      = 0;
    const SINT8     = 1;
    const UINT8     = 2;
    const SINT16    = 3;
    const UINT16    = 4;
    const SINT32    = 5;
    const UINT32    = 6;
    const STRING    = 7;
    const FLOAT32   = 8;
    const FLOAT64   = 9;
    const UINT8Z    = 10;
    const UINT16Z   = 11;
    const UINT32Z   = 12;
    const BYTE      = 13;
    //aliasses
    const TIME      = 6;
    const POS       = 5;

    protected static $base_types = array(
        self::ENUM      => array('endian_ability' => false, 'name'  => 'enum',      'bytes' => 1,),
        self::SINT8     => array('endian_ability' => false, 'name'  => 'sint8',     'bytes' => 1,),
        self::UINT8     => array('endian_ability' => false, 'name'  => 'uint8',     'bytes' => 1,),
        self::SINT16    => array('endian_ability' => true,  'name'  => 'sint16',    'bytes' => 2,),
        self::UINT16    => array('endian_ability' => true,  'name'  => 'uint16',    'bytes' => 2,),
        self::SINT32    => array('endian_ability' => true,  'name'  => 'sint32',    'bytes' => 4,),
        self::UINT32    => array('endian_ability' => true,  'name'  => 'uint32',    'bytes' => 4,),
        self::STRING    => array('endian_ability' => false, 'name'  => 'string',    'bytes' => 1,),
        self::FLOAT32   => array('endian_ability' => true,  'name'  => 'float32',   'bytes' => 4,),
        self::FLOAT64   => array('endian_ability' => true,  'name'  => 'float64',   'bytes' => 8,),
        self::UINT8Z    => array('endian_ability' => false, 'name'  => 'uint8z',    'bytes' => 1,),
        self::UINT16Z   => array('endian_ability' => true,  'name'  => 'uint16z',   'bytes' => 2,),
        self::UINT32Z   => array('endian_ability' => true,  'name'  => 'uint32z',   'bytes' => 4,),
        self::BYTE      => array('endian_ability' => false, 'name'  => 'byte',      'bytes' => 1,),
    );

    /**
     * The log store.
     * @var \Fit\Log
     */
    public static $log;
    /**
     * Convert an integer to a bool-array (bar)
     * @param int $x
     * @return bool[]
     */
    public static function inttobar(int $x): array
    {
        return array_reverse(
            array_map(
                function ($v) {
                    return (bool)$v;
                },
                str_split(str_pad(decbin($x), 16, '0', STR_PAD_LEFT))
            )
        );
    }

    /**
     * Convert a bool-array to it's integer representation
     * @param bool[] $bs
     * @return int
     */
    public static function bartoint(array $bs): int
    {
        $x = 0;
        for ($i = 0; $i <= sizeOf($bs) - 1; $i++) {
            if ($bs[$i]) {
                $x = $x + pow(2, $i);
            }
        }
        return $x;
    }

    /**
     * When true will output debug information to the browser
     * @var bool
     */
    protected $debug;

    /**
     * @var ProductProfile
     */
    protected $profile;

    public function __construct(bool $debug = false)
    {
        $this->profile  = new ProductProfile();
        $this->debug    = $debug;
    }
}
