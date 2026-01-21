<?php

declare(strict_types=1);

return [
    'navigation' => [
        'label' => 'Authentication Logs',
        'group' => 'Authentication',
        'icon' => 'heroicon-o-shield-check',
        'sort' => 5,
    ],
    'actions' => [
        'reorderRecords' => [
            'tooltip' => 'reorderRecords',
            'icon' => 'reorderRecords',
            'label' => 'reorderRecords',
        ],
        'edit' => [
            'label' => 'edit',
            'icon' => 'edit',
            'tooltip' => 'edit',
        ],
        'delete' => [
            'label' => 'delete',
            'icon' => 'delete',
            'tooltip' => 'delete',
        ],
        'detach' => [
            'label' => 'detach',
            'icon' => 'detach',
            'tooltip' => 'detach',
        ],
        'attach' => [
            'label' => 'attach',
            'icon' => 'attach',
            'tooltip' => 'attach',
        ],
        'create' => [
            'label' => 'create',
            'icon' => 'create',
            'tooltip' => 'create',
        ],
        'applyFilters' => [
            'label' => 'applyFilters',
            'icon' => 'applyFilters',
            'tooltip' => 'applyFilters',
        ],
        'openFilters' => [
            'label' => 'openFilters',
            'icon' => 'openFilters',
            'tooltip' => 'openFilters',
        ],
        'resetFilters' => [
            'label' => 'resetFilters',
            'icon' => 'resetFilters',
            'tooltip' => 'resetFilters',
        ],
        'applyTableColumnManager' => [
            'label' => 'applyTableColumnManager',
            'icon' => 'applyTableColumnManager',
            'tooltip' => 'applyTableColumnManager',
        ],
        'openColumnManager' => [
            'label' => 'openColumnManager',
            'icon' => 'openColumnManager',
            'tooltip' => 'openColumnManager',
        ],
    ],
    'label' => '',
    'plural_label' => '',
    'fields' => [
        'ip_address' => [
            'label' => 'ip_address',
        ],
        'user_agent' => [
            'label' => 'user_agent',
        ],
        'login_successful' => [
            'label' => 'login_successful',
        ],
        'login_at' => [
            'label' => 'login_at',
        ],
        'logout_at' => [
            'label' => 'logout_at',
        ],
        'location' => [
            'label' => 'location',
        ],
        'created_at' => [
            'label' => 'created_at',
        ],
    ],
];
