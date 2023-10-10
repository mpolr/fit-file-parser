<?php

return [
    'type'      => \Fit\FileType::segment,
    'messages'  => [
        [
            'name'              => 'file_id',
            'architecture'      => 0,
            'global_msg_number' => 0,
            'fields'            => [
                //      name,   field_def_number, factor, unit, base_type_number, size
                ['type',               0,      1,      '',     \Fit\Core::ENUM     ,1],
                ['manufacturer',       1,      1,      '',     \Fit\Core::UINT16   ,2],
                ['product',            2,      1,      '',     \Fit\Core::UINT16   ,2],
                ['serial_number',      3,      1,      '',     \Fit\Core::UINT32Z  ,4],
                ['time_created',       4,      1,      '',     \Fit\Core::TIME     ,4],
                ['number',             5,      1,      '',     \Fit\Core::UINT16   ,2],
            ],
        ],
        [
            'name'              => 'segment_id',
            'architecture'      => 0,
            'global_msg_number' => 148,
            'fields'            => [
                //      name,   field_def_number, factor, unit, base_type_number, size
                ['name',                0,      1,      '',     \Fit\Core::STRING   ,4],
                ['uuid',                1,      1,      '',     \Fit\Core::STRING   ,4],
                ['sport',               2,      1,      '',     \Fit\Core::ENUM     ,1],
                ['enabled',             3,      1,      '',     \Fit\Core::ENUM     ,1],
            ],
        ],
        [
            'name'              => 'segment_lap',
            'architecture'      => 0,
            'global_msg_number' => 142,
            'fields'            => [
                //      name,   field_def_number, factor, unit, base_type_number, size
                ['segment_length',      5,      1,      'm',    \Fit\Core::UINT16   ,2],
                ['uuid',                10,     1,      '',     \Fit\Core::STRING   ,4],
                ['total_distance',      11,     0.00001,'km',   \Fit\Core::UINT32   ,4],
                ['total_ascent',        12,     1,      'm',    \Fit\Core::UINT16   ,2],
                ['swc_lat',             13,     1,      'semicircles', \Fit\Core::SINT32, 4],
                ['swc_long',            14,     1,      'semicircles', \Fit\Core::SINT32, 4],
                ['nec_lat',             15,     1,      'semicircles', \Fit\Core::SINT32, 4],
                ['nec_long',            16,     1,      'semicircles', \Fit\Core::SINT32, 4],
                ['message_index',       254,    1,      '',     \Fit\Core::UINT16   ,2],
                ['start_position_lat',  17,     1,      'semicircles', \Fit\Core::SINT32, 4],
                ['start_position_long', 18,     1,      'semicircles', \Fit\Core::SINT32, 4],
                ['end_position_lat',    19,     1,      'semicircles', \Fit\Core::SINT32, 4],
                ['end_position_long',   20,     1,      'semicircles', \Fit\Core::SINT32, 4],
            ],
        ],
    ],
];
