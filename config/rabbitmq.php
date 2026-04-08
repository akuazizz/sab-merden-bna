<?php

return [

    /*
    |--------------------------------------------------------------------------
    | RabbitMQ Connection
    |--------------------------------------------------------------------------
    */

    'host'     => env('RABBITMQ_HOST', '127.0.0.1'),
    'port'     => env('RABBITMQ_PORT', 5672),
    'user'     => env('RABBITMQ_USER', 'guest'),
    'password' => env('RABBITMQ_PASSWORD', 'guest'),
    'vhost'    => env('RABBITMQ_VHOST', '/'),

    /*
    |--------------------------------------------------------------------------
    | Exchange Definitions
    |
    | Semua exchange di sistem SAB Merden.
    | Tipe 'topic' dipilih agar routing fleksibel (wildcard *.#).
    |--------------------------------------------------------------------------
    */

    'exchanges' => [
        'events'  => 'sab.events',   // domain events umum
        'payment' => 'sab.payment',  // payment gateway callbacks
    ],

    /*
    |--------------------------------------------------------------------------
    | Routing Keys per Domain Event
    |--------------------------------------------------------------------------
    */

    'routing_keys' => [
        'MeteranDibaca'   => 'meteran.dibaca',
        'TagihanDibuat'   => 'tagihan.dibuat',
        'TagihanDivoid'   => 'tagihan.divoid',
        'WargaTerdaftar'  => 'pelanggan.terdaftar',
        'PaymentCallback' => 'payment.callback',
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Bindings (Consumer Side)
    |
    | Nama queue yang akan dibuat dan di-bind ke exchange + routing key.
    |--------------------------------------------------------------------------
    */

    'queues' => [
        'generate-tagihan'    => [
            'exchange'    => 'sab.events',
            'routing_key' => 'meteran.dibaca',
        ],
        'notifikasi-tagihan'  => [
            'exchange'    => 'sab.events',
            'routing_key' => 'tagihan.dibuat',
        ],
        'batalkan-transaksi'  => [
            'exchange'    => 'sab.events',
            'routing_key' => 'tagihan.divoid',
        ],
        'catat-warga'         => [
            'exchange'    => 'sab.events',
            'routing_key' => 'pelanggan.terdaftar',
        ],
        'payment-callback'    => [
            'exchange'    => 'sab.payment',
            'routing_key' => 'payment.callback',
        ],
    ],

];
