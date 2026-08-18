<?php

declare(strict_types=1);

return [
    'navigation' => [
        'name' => 'Utente Tenant',
        'plural' => 'Utenti Tenant',
        'label' => 'Utenti Tenant',
        'group' => [
            'name' => 'Tenants',
            'description' => 'Gestione degli utenti associati ai tenant',
        ],
        'sort' => 87,
        'icon' => 'heroicon-o-building-office',
    ],
    'label' => 'Tenant User',
    'plural_label' => 'Tenant User (Plurale)',
    'fields' => [
        'id' => [
            'label' => 'Identificativo',
            'tooltip' => 'Identificativo univoco del record',
            'helper_text' => '',
            'description' => '',
        ],
        'created_at' => [
            'label' => 'Data Creazione',
            'tooltip' => '',
            'helper_text' => '',
            'description' => '',
        ],
        'updated_at' => [
            'label' => 'Ultima Modifica',
            'tooltip' => '',
            'helper_text' => '',
            'description' => '',
        ],
        'tenant_id' => [
            'label' => 'tenant_id',
            'placeholder' => 'tenant_id',
            'helper_text' => 'tenant_id',
            'description' => 'tenant_id',
        ],
        'user_id' => [
            'label' => 'user_id',
            'placeholder' => 'user_id',
            'helper_text' => 'user_id',
            'description' => 'user_id',
        ],
        'role' => [
            'label' => 'role',
            'placeholder' => 'role',
            'helper_text' => 'role',
            'description' => 'role',
        ],
    ],
    'actions' => [
        'create' => [
            'label' => 'Crea Tenant User',
        ],
        'edit' => [
            'label' => 'Modifica Tenant User',
        ],
        'delete' => [
            'label' => 'Elimina Tenant User',
        ],
    ],
    'sections' => [
        'Tenant User Information' => [
            'label' => 'Tenant User Information',
            'heading' => 'Tenant User Information',
        ],
    ],
];
