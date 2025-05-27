<?php


return [
    'slug' => 'chativel',

    'navigation_label' => 'ChatiVel',

    'navigation_icon' => 'heroicon-o-chat-bubble-left-right',

    'deafult_search_column' => 'name',

    'deafult_display_column' => 'name',

    'chatables' => [
        \App\Models\User::class,
    ],

    'timezone' => 'Asia/Damascus',

    'languages' => ['en', 'ar'],
    'default_language' => 'ar',

    'max_message_length' => 1000,

    'attachments_store_directory' => 'attachments',
    
    'max_attachment_size' => 10000,
    'min_attachment_size' => 1,
    
    'max_attachments_count' => 10,
    'min_attachments_count' => 0,
    
    'image_editor' => true,

    'allowed_mime_types' => [
        'image/png',
        'image/jpeg',
        'image/jpg',
        'image/gif',

        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'text/csv',
        'text/plain',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
    ],
];
