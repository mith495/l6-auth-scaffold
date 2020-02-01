<?php

use App\Models\Role;

return [
    'role_structure' => [
        Role::ROLE_ADMIN => [
            'users' => 'c,r,u,d',
            'acl' => 'c,r,u,d',
            'profile' => 'r,u',
            'posts' => 'c,r,u,d,p',
            'posts' => 'c,r,u,d',
        ],
        Role::ROLE_MEMBER => [
            'profile' => 'r,u',
            'services' => 'c,r,u',
            'posts' => 'c,r,u,p',
            'threads' => 'c,r,u,d',
        ],
    ],
    'permissions_map' => [
        'c' => 'create',
        'r' => 'read',
        'u' => 'update',
        'd' => 'delete',
    ]
];
