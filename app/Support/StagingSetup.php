<?php

namespace App\Support;

use Illuminate\Support\Facades\Artisan;

/**
 * Hosting ohne SSH (z. B. IONOS Webhosting per FTP): Datenbank anlegen bzw. auf den neuesten Stand bringen.
 * Aktiv in der Testumgebung (APP_ENV=staging) und in Produktion mit AUTO_SETUP=true.
 * Läuft nur, wenn sich die Migrationsdateien geändert haben (Merker in storage/app/.migrated) oder die
 * Datenbank leer ist. Bestehende Daten (Admin-Konten, Bestellungen) bleiben erhalten – Migrationen ergänzen nur.
 * Funktioniert mit SQLite (Datei) und MySQL/MariaDB (z. B. IONOS-Datenbank).
 */
class StagingSetup
{
    public static function run(): void
    {
        if (! config('ulbrich_admin.auto_setup') || app()->runningInConsole()) {
            return;
        }
        $hash = md5(implode('|', array_map('basename', glob(database_path('migrations/*.php')) ?: [])));
        $marker = storage_path('app/.migrated');
        $db = config('database.default') === 'sqlite' ? config('database.connections.sqlite.database') : null;
        $dbEmpty = $db ? (! is_file($db) || filesize($db) === 0)   // SQLite: fehlt oder wurde durch eine leere Datei ersetzt
            : ! self::hasMigrationsTable();                         // MySQL: noch keine Tabellen
        if (! $dbEmpty && is_file($marker) && trim((string) file_get_contents($marker)) === $hash) {
            return;
        }
        $lock = fopen(storage_path('app/.migrate.lock'), 'c');
        if (! $lock || ! flock($lock, LOCK_EX | LOCK_NB)) {
            return;   // läuft gerade in einer anderen Anfrage
        }
        try {
            if ($db && ! is_file($db)) {
                touch($db);
            }
            Artisan::call('migrate', ['--force' => true]);
            if (\App\Models\Course::query()->doesntExist()) {
                Artisan::call('db:seed', ['--class' => \Database\Seeders\CourseSeeder::class, '--force' => true]);
            }
        } catch (\Throwable $e) {
            report($e);   // Seite läuft weiter; Fehler steht in storage/logs/laravel.log
        } finally {
            file_put_contents($marker, $hash);   // kein erneuter Versuch bei jeder Anfrage
            flock($lock, LOCK_UN);
            fclose($lock);
        }
    }

    private static function hasMigrationsTable(): bool
    {
        try {
            return \Illuminate\Support\Facades\Schema::hasTable('migrations');
        } catch (\Throwable) {
            return true;   // Datenbank nicht erreichbar: nichts versuchen, Fehler zeigt die Seite selbst
        }
    }
}
