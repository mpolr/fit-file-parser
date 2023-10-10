<?php

namespace Fit;

use Zend_Io_Writer;

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
 * Class FitWriter
 * Class to write a .FIT file.
 *
 * @example examples/create_data.php How to use this class
 * @uses Zend_Io_Writer Binary file parser
 */
class Writer extends Core
{
    /**
     * @var Zend_Io_Writer
     */
    protected $writer;
    protected $local_msg_types = array();


    /**
     * Create a .fit file and write data to it.
     * @param Data $data
     * @param string $filepath
     * @return string The filepath of the file that was created
     * @throws Exception
     * @throws \Zend_Io_Exception
     */
    public function writeData(Data $data, $filepath = false)
    {
        if ($filepath === false) {
            $filepath = tempnam('/tmp', 'fit');
        }
        if (false === $filepath) {
            Exception::create(1001);
        }
        if (false === ($file = @fopen($filepath, 'wb'))) {
            Exception::create(1001);
        }
        $this->writer = new Zend_Io_Writer($file);
        try {
            $this
                ->writeFileHeader()
                ->writeTheRecords($data)
                ->writeFileClosure($filepath)
            ;
        } catch (\Exception $e) {
            $this->writer->close();
            unlink($filepath);
            throw $e;
        }
        $this->writer->close();
        return $filepath;
    }

    protected function FitCRC_Get16($crc, $byte): int
    {
        $crc_table =[0x0000, 0xCC01, 0xD801, 0x1400, 0xF001, 0x3C00, 0x2800, 0xE401,0xA001, 0x6C00, 0x7800, 0xB401, 0x5000, 0x9C01, 0x8801, 0x4400];
        // compute checksum of lower four bits of byte
        $tmp = $crc_table[$crc & 0xF];
        $crc = ($crc >> 4) & 0x0FFF;
        $crc = $crc ^ $tmp ^ $crc_table[$byte & 0xF];
        // now compute checksum of upper four bits of byte
        $tmp = $crc_table[$crc & 0xF];
        $crc = ($crc >> 4) & 0x0FFF;
        $crc = $crc ^ $tmp ^ $crc_table[($byte >> 4) & 0xF];
        return $crc;
    }

    /**
     * @throws \Zend_Io_Exception
     */
    protected function writeFileClosure($filepath): Writer
    {
        $crc=0;
        //mark current writing position
        $offset = $this->writer->getOffset();
        //move to header and write the size of the data
        $this->writer->setOffset(4); //GO TO 4th POSITION TO WRITE IN DATA SIZE
        $data_size = $this->writer->getSize() - 12; //WAS MINUS 12
        $this->writer->writeUInt32LE($data_size);	// Does not include file header or crc.  Little endian format.
            //BEGIN CALCULATE CRC
            $handle = @fopen($filepath, "r");
            if ($handle) {
                while (!feof($handle)) {
                    $hex = bin2hex(fread ($handle , 1 ));
                    $byte=hexdec('0x'.$hex);
                    if ($hex){
                        $crc = $this->FitCRC_Get16($crc, $byte);
                    }
                }
                fclose($handle);
            }
            //END CALCULATE CRC
        //write 2 byte crc check at end of file
        $this->writer->setOffset($offset);  //MOVE TO END OF FILE
        $this->writer->writeUInt16LE($crc); //PUT IN CRC
        return $this;
    }

    /**
     * Writes the Fit file header to the file.
     * @return Writer
     * @throws \Zend_Io_Exception
     */
    protected function writeFileHeader(): Writer
    {
        $this->writer->writeUInt8(12);                                      // FIT_FILE_HDR_SIZE (size of this structure)
        $this->writer->writeUInt8($this->profile->protocol_version());      // FIT_PROTOCOL_VERSION
        $this->writer->writeUInt16LE($this->profile->profile_version());    // FIT_PROFILE_VERSION
        $this->writer->writeUInt32LE(0);                                    // Does not include file header or crc.  Little endian format.
        $this->writer->writeString8('.FIT');                                // ".FIT"
        return $this;
    }

    /**
     * Writes a data-record to the file.
     * @throws \Zend_Io_Exception
     */
    protected function writeMessageData(array $msg_def, array $message_data): Writer
    {
        $local_msg_type = $this->getLocalMsgType($msg_def['global_msg_number']);
        $this->writeRecordHeaderByte($local_msg_type, false);
        //en nu de velddata wegschrijven
        foreach ($msg_def['fields'] as $field_def) {
            if (isset($message_data[$field_def[Field::NAME]])) {
                $val = $message_data[$field_def[Field::NAME]];
            } else {
                $val = null;
            }
            if (is_numeric($val) && $field_def[Field::FACTOR] > 0) {
                $val /= $field_def[Field::FACTOR];
            }
            $big_endian = $msg_def['architecture'] === 1;
            switch ($field_def[Field::TYPE_NUMBER]) {
                case Core::STRING:
                    $this->writer->writeString8((string)$val, $field_def[Field::SIZE]);
                    break;
                case Core::SINT8:
                    $this->writer->writeInt8($val);
                    break;
                case Core::ENUM:
                case Core::UINT8Z:
                case Core::UINT8:
                    $this->writer->writeUInt8($val);
                    break;
                case Core::SINT16:
                    $big_endian ? $this->writer->writeInt16BE($val) : $this->writer->writeInt16LE($val);
                    break;
                case Core::UINT16Z:
                case Core::UINT16:
                    $big_endian ? $this->writer->writeUInt16BE($val) : $this->writer->writeUInt16LE($val);
                    break;
                case Core::SINT32:
                    $big_endian ? $this->writer->writeInt32BE($val) : $this->writer->writeInt32LE($val);
                    break;
                case Core::UINT32Z:
                case Core::UINT32:
                    $big_endian ? $this->writer->writeUInt32BE($val) : $this->writer->writeUInt32LE($val);
                    break;
                case Core::FLOAT32:
                    $big_endian ? $this->writer->writeFloatBE($val) : $this->writer->writeFloatLE($val);
                    break;
                case Core::FLOAT64:
                    $big_endian ? $this->writer->writeInt64BE($val) : $this->writer->writeInt64LE($val);
                    break;
                case Core::BYTE:
                default:
                    $this->writer->write($val, $field_def[Field::SIZE]);
            }
        }
        return $this;
    }

    /**
     * Writes a definition record to file, when necessary (not already written).
     * @param array $msg_def The definition of the message.
     * @throws \Zend_Io_Exception
     */
    protected function writeMessageDefinition(array $msg_def): Writer
    {
        $local_msg_type = $this->getLocalMsgType($msg_def['global_msg_number'], true);
        if ($local_msg_type !== false) {
            $this->writeRecordHeaderByte($local_msg_type, true);

            //write definition record fields
            $this->writer->writeUInt8(0); //reserved
            //architecture
            $this->writer->writeUInt8($msg_def['architecture']); //Architecture Type 0: Definition and Data Messages are Little Endian 1: Definition and Data Message are Big Endian
            $big_endian = $msg_def['architecture'] === 1;
            //global_msg_number
            if ($big_endian) {
                $this->writer->writeUInt16BE($msg_def['global_msg_number']); //0:65535 – Unique to each message *Endianness of this 2 Byte value is defined in the Architecture byte
            } else {
                $this->writer->writeUInt16LE($msg_def['global_msg_number']); //0:65535 – Unique to each message *Endianness of this 2 Byte value is defined in the Architecture byte
            }
            //no_of_fields
            $this->writer->writeUInt8(count($msg_def['fields'])); //Number of fields in the Data Message


            foreach ($msg_def['fields'] as $field_def) {
                $this->writer->writeUInt8($field_def[Field::DEF_NUMBER]);
                $this->writer->writeUInt8($field_def[Field::SIZE]);
                $this->writer->writeUInt8(self::bartoint(array_merge(
                    array_slice(self::inttobar($field_def[Field::TYPE_NUMBER]), 0, 5),
                    array(
                        false,//reserved
                        false,//reserved
                        self::$base_types[$field_def[Field::TYPE_NUMBER]]['endian_ability'],//endian_ability: 0 - for single byte data 1 - if base type has endianness (i.e. base type is 2 or more bytes)
                    )
                )));
            }
        }
        return $this;
    }

    /**
     * The local message type is a coupler for definition to data records. The
     * definition needs to be written before a datarecord is, but it only needs
     * to be written once.
     *
     * @return int The local message type or false when the request if for a
     * definition record.
     */
    protected function getLocalMsgType(int $global_msg_no, bool $for_definition = false)
    {
        $local_msg_type = array_search($global_msg_no, $this->local_msg_types);
        if ($local_msg_type === false) {
            $local_msg_type = count($this->local_msg_types);
            $this->local_msg_types[] = $global_msg_no;
            return $local_msg_type;
        }
        return $for_definition ? false : $local_msg_type;
    }

    /**
     * @throws \Zend_Io_Exception
     */
    protected function writeRecordHeaderByte(int $local_msg_type, bool $is_definition = false, bool $is_normal_header = true): Writer
    {
        //write definition record
        //write definition record header byte
        $header_bits = array_merge(
            array_slice(self::inttobar($local_msg_type), 0, 4),
            array(
                false,  //reserved
                false,  //reserved
                $is_definition,   // Message Type, 1: Definition Message, 0: Data Message
                false === $is_normal_header,  //Normal Header
            )
        );
        $this->writer->writeUInt8(self::bartoint($header_bits));
        return $this;
    }

    /**
     * Writes the data to the file.
     * @param Data $data The data that needs to be written to the file.
     * @throws \Zend_Io_Exception
     * @throws Exception
     */
    protected function writeTheRecords(Data $data): Writer
    {
        foreach ($data->getData() as $file_type => $messages) {
            $file_type_definition = $this->profile->findFileTypeByType($file_type);
            if ($file_type_definition !== null) {
                //we schrijven eerst de file_id message weg
                $file_id_written = false;
                foreach ($messages as $k => $msg) {
                    if ($msg['name'] === 'file_id') {
                        $message_def = $this->profile->findFieldDefByName(
                            $file_type_definition,
                            'file_id'
                        );
                        if ($message_def !== null) {
                            $file_id_written = true;
                            $this->writeMessageDefinition($message_def);
                            $this->writeMessageData($message_def, $msg['data'][0]);
                            unset($messages[$k]);
                            break;
                        }
                    }
                }
                if ($file_id_written === false) {
                    //we always need a file_id
                    Exception::create(
                        1002,
                        implode(PHP_EOL, array(
                            'Missing file_id in message data. Every file type needs one file_id message.',
                            'Example: ',
                            '<code>',
                            '$data = new \Fit\Data;',
                            '$data->setFile(\Fit\FileType::activity);',
                            '$data->add(\'file_id\', array(',
                            '		\'type\'			=> \Fit\FileType::activity,',
                            '		\'manufacturer\'	=> \Fit\Manufacturer::development,',
                            '		\'product\'			=> 0,',
                            '		\'serial_number\'	=> 0,',
                            '		\'time_created\'	=> time() - mktime(0,0,0,12,31,1989),',
                            '	))',
                            '</code>'
                        ))
                    );
                }

                foreach ($messages as $msg) {
                    $message_def = $this->profile->findFieldDefByName(
                        $file_type_definition,
                        $msg['name']
                    );
                    if ($message_def !== null) {
                        foreach ($msg['data'] as $records) {
                            $this->writeMessageDefinition($message_def);
                            $this->writeMessageData($message_def, $records);
                        }
                    }
                }
            }
        }
        return $this;
    }
}
