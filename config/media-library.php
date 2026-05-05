<?php

return [
    /*
     * The disk where media are stored.
     */
    'disk_name' => env('MEDIA_DISK', 'public'),

    /*
     * The maximum file size of an item in bytes.
     */
    'max_file_size' => 1024 * 1024 * 10, // 10MB

    /*
     * The queue connections that will be used to generate thumbnails.
     */
    'queue_connection_names' => [
        'default' => env('QUEUE_CONNECTION', 'default'),
    ],

    /*
     * These are the options that will be used when generating thumbnails.
     */
    'image_generator' => [
        'driver' => env('IMAGE_GENERATOR_DRIVER', 'gd'),
    ],

    /*
     * These are the options that will be used when generating thumbnails.
     */
    'responsive_images' => [
        /*
         * The width breakpoints that will be used to generate responsive images.
         */
        'width_breakpoints' => [100, 200, 300, 400, 500, 600, 700, 800, 900, 1000, 1100, 1200],
    ],

    /*
     * These are the options that will be used when generating thumbnails.
     */
    'path_generator' => [
        'driver' => env('MEDIA_PATH_GENERATOR_DRIVER', 'default'),
    ],

    /*
     * These are the options that will be used when generating thumbnails.
     */
    'url_generator' => [
        'driver' => env('MEDIA_URL_GENERATOR_DRIVER', 'default'),
    ],

    /*
     * These are the options that will be used when generating thumbnails.
     */
    'file_namer' => [
        'driver' => env('MEDIA_FILE_NAMER_DRIVER', 'default'),
    ],

    /*
     * These are the options that will be used when generating thumbnails.
     */
    'image_manipulator' => [
        'driver' => env('MEDIA_IMAGE_MANIPULATOR_DRIVER', 'gd'),
    ],

    /*
     * These are the options that will be used when generating thumbnails.
     */
    'file_system' => [
        'driver' => env('MEDIA_FILE_SYSTEM_DRIVER', 'default'),
    ],

    /*
     * These are the options that will be used when generating thumbnails.
     */
    'collections' => [
        'default' => [
            'disk' => 'public',
            'conversions' => [
                'thumb' => [
                    'width' => 200,
                    'height' => 200,
                ],
            ],
        ],
    ],
];
