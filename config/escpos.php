<?php

return [
    'connection' => env('ESCPOS_CONNECTION', 'none'),
    'printer_name' => env('ESCPOS_PRINTER_NAME'),
    'printer_host' => env('ESCPOS_PRINTER_HOST'),
    'printer_port' => (int) env('ESCPOS_PRINTER_PORT', 9100),
    'store_name' => env('STORE_NAME', 'KomikStore'),
    'store_address' => env('STORE_ADDRESS', ''),
    'store_phone' => env('STORE_PHONE', ''),
];
