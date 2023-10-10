<?php

use Fit\Core;

return [
    'type'      => \Fit\FileType::segment,
    'messages'  => [
        [
            'name'              => 'file_id',
            'architecture'      => 0,
            'global_msg_number' => 0,
            'fields'            => [
                //      name,   field_def_number, factor, unit, base_type_number, size
                ['type',               0,      1,      '',     Core::ENUM     ,1],
                ['manufacturer',       1,      1,      '',     Core::UINT16   ,2],
                ['product',            2,      1,      '',     Core::UINT16   ,2],
                ['serial_number',      3,      1,      '',     Core::UINT32Z  ,4],
                ['time_created',       4,      1,      '',     Core::TIME     ,4],
                ['number',             5,      1,      '',     Core::UINT16   ,2],
            ],
        ],
        [
            'name'              => 'file_creator',
            'architecture'      => 0,
            'global_msg_number' => 49,
            'fields'        => [
                ['software_version',   0,      0.01,   '',     \Fit\Core::UINT16   ,2],
                ['hardware_version',   1,      1,      '',     \Fit\Core::UINT8    ,1],
            ],
        ],
        [
            'name'              => 'segment_id',
            'architecture'      => 0,
            'global_msg_number' => 148,
            'fields'            => [
                //      name,   field_def_number, factor, unit, base_type_number, size
                ['name',                0,      1,      '',     Core::STRING   ,64],
                ['uuid',                1,      1,      '',     Core::STRING   ,64],
                ['sport',               2,      1,      '',     Core::ENUM     ,1],
                ['enabled',             3,      1,      '',     Core::ENUM     ,1],
                ['selection_type',      8,      1,      '',     Core::ENUM     ,1],
            ],
        ],
        [
            'name'              => 'segment_leaderboard_entry',
            'architecture'      => 0,
            'global_msg_number' => 149,
            'fields'            => [
                //      name,   field_def_number, factor, unit, base_type_number, size
                ['type',                1,      1,      '',     Core::UINT16   ,2],
                ['segment_time',        4,      0.001,  's',    Core::UINT32   ,4],
                ['message_index',       254,    1,      '',     Core::UINT16,   2],
            ],
        ],
        [
            'name'              => 'segment_lap',
            'architecture'      => 0,
            'global_msg_number' => 142,
            'fields'            => [
                //      name,   field_def_number, factor, unit, base_type_number, size
                ['uuid',                65,     1,      '',                Core::STRING,   64],
                ['total_distance',      9,      0.00001,'km',              Core::UINT32,   4],
                ['total_ascent',        21,     1,      'm',               Core::UINT16,   2],
                ['swc_lat',             27,     1,      'semicircles',     Core::SINT32,   4],
                ['swc_long',            28,     1,      'semicircles',     Core::SINT32,   4],
                ['nec_lat',             25,     1,      'semicircles',     Core::SINT32,   4],
                ['nec_long',            26,     1,      'semicircles',     Core::SINT32,   4],
                ['message_index',       254,    1,      '',                Core::UINT16,   2],
                ['start_position_lat',  3,      1,      'semicircles',     Core::SINT32,   4],
                ['start_position_long', 4,      1,      'semicircles',     Core::SINT32,   4],
                ['end_position_lat',    5,      1,      'semicircles',     Core::SINT32,   4],
                ['end_position_long',   6,      1,      'semicircles',     Core::SINT32,   4],
            ],
        ],
        [
            'name'              => 'segment_point',
            'architecture'      => 0,
            'global_msg_number' => 150,
            'fields'            => [
                //      name,   field_def_number, factor, unit, base_type_number, size
                ['position_lat',        1,      1,      'semicircles',     Core::SINT32,   4],
                ['position_long',       2,      1,      'semicircles',     Core::SINT32,   4],
                ['distance',            3,      0.00001,   'km',    Core::UINT32   ,4],
                ['altitude',            4,      0.002,      'm',     Core::UINT16,   2],
                ['leader_time',         5,      0.001,      's',     Core::UINT32,   4],
                ['message_index',       254,    1,      '',     Core::UINT16,   2],
            ],
        ],
    ],
];
