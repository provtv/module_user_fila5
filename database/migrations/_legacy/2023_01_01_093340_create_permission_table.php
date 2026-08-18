<?php

declare(strict_types=1);

use Modules\Xot\Database\Migrations\XotBaseMigration;

return new class extends XotBaseMigration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // `config()` restituisce mixed: i tipi si restringono a runtime con guard che
        // servono davvero (una config assente qui è un errore di deploy, non un caso
        // di tipo da annotare), non con `@var` inline.
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $teams = config('permission.teams');

        if (! \is_array($tableNames) || [] === $tableNames) {
            throw new Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        if (! \is_array($columnNames)) {
            throw new Exception('Error: config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        if ($teams && empty($columnNames['team_foreign_key'] ?? null)) {
            throw new Exception('Error: team_foreign_key on config/permission.php not loaded. Run [php artisan config:clear] and try again.');
        }

        $cacheStore = config('permission.cache.store');
        $cacheKey = config('permission.cache.key');

        try {
            // Verifica se l'applicazione è completamente inizializzata
            if (\is_string($cacheKey) && app()->bound('cache')) {
                $store = \is_string($cacheStore) && 'default' !== $cacheStore ? $cacheStore : null;
                app('cache')->store($store)->forget($cacheKey);
            }
        } catch (Exception $e) {
            // Silently ignore cache errors during package discovery
            // echo $e->getMessage();
        }
    }

    /* -- is in xotbasemigration
     * public function down(): void
     * {
     * $tableNames = config('permission.table_names');
     *
     * if (empty($tableNames)) {
     * throw new Exception('Error: config/permission.php not found and defaults could not be merged. Please publish the package configuration before proceeding, or drop the tables manually.');
     * }
     *
     * Schema::drop($tableNames['role_has_permissions']);
     * Schema::drop($tableNames['model_has_roles']);
     * Schema::drop($tableNames['model_has_permissions']);
     * Schema::drop($tableNames['roles']);
     * Schema::drop($tableNames['permissions']);
     * }
     */
};
