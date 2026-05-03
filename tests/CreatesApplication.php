<?php

namespace Tests;

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use PDO;
use PDOException;
use Throwable;

trait CreatesApplication
{
    /**
     * Creates the application.
     */
    public function createApplication(): Application
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        if ($app->environment('testing')) {
            $this->ensureIsolatedMysqlTestDatabaseExists($app);
        }

        return $app;
    }

    /**
     * Crea la base de pruebas en MySQL si no existe (solo nombre terminado en _test).
     * Evita que RefreshDatabase / migrate:fresh toque la BD de desarrollo cuando phpunit sobreescribe solo DB_DATABASE.
     */
    private function ensureIsolatedMysqlTestDatabaseExists(Application $app): void
    {
        $connectionName = config('database.default', 'mysql');
        $config = config('database.connections.'.$connectionName);

        if (($config['driver'] ?? '') !== 'mysql') {
            return;
        }

        $database = $config['database'] ?? '';
        if ($database === '' || ! str_ends_with($database, '_test')) {
            return;
        }

        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;charset=utf8mb4',
                $config['host'] ?? '127.0.0.1',
                $config['port'] ?? '3306'
            );
            $pdo = new PDO($dsn, $config['username'] ?? '', $config['password'] ?? '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $pdo->exec(
                'CREATE DATABASE IF NOT EXISTS `'.$database.'` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
            );
        } catch (PDOException|Throwable) {
            // Si no se puede crear (permisos, etc.), el test fallará con un error claro al migrar.
        }
    }
}
