<?php
return [
    'default' => 'pt210_yichip',
    'profiles' => [
        'pt210_yichip' => [
            'display_name' => 'PT210 / YICHIP POS58',
            'windows_printer_name' => 'YICHIP POS58',
            'protocol' => 'ESC/POS',
            'paper_width_mm' => 58,
            'printable_columns' => 32,
            'encoding' => 'CP437',
            'font_preset' => 'A',
            'density' => 7,
            'supports_qr' => false,
            'supports_barcode' => false,
            'supports_cut' => false,
            'supports_logo' => false,
            'copies' => 1,
            'enabled' => true,
        ],
        'generic_48mm' => [
            'display_name' => 'Generic ESC/POS 48mm', 'windows_printer_name' => 'CHANGE-ME',
            'protocol' => 'ESC/POS', 'paper_width_mm' => 48, 'printable_columns' => 24,
            'encoding' => 'CP437', 'font_preset' => 'A', 'density' => 7,
            'supports_qr' => false, 'supports_barcode' => false, 'supports_cut' => false,
            'supports_logo' => false, 'copies' => 1, 'enabled' => false,
        ],
        'generic_80mm' => [
            'display_name' => 'Generic ESC/POS 80mm', 'windows_printer_name' => 'CHANGE-ME',
            'protocol' => 'ESC/POS', 'paper_width_mm' => 80, 'printable_columns' => 48,
            'encoding' => 'CP437', 'font_preset' => 'A', 'density' => 7,
            'supports_qr' => false, 'supports_barcode' => false, 'supports_cut' => false,
            'supports_logo' => false, 'copies' => 1, 'enabled' => false,
        ],
    ],
];
