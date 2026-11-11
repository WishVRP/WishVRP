<?php

namespace App\Support;

use Illuminate\Support\Facades\Artisan;

/**
 * Testumgebung ohne SSH: Datenbank anlegen bzw. auf den neuesten Stand bringen.
 * Läuft nur bei APP_ENV=staging und nur, wenn sich die Migrationsdateien geändert haben
 * (Merker in storage/app/.migrated). Bestehende Daten (Admin-Konto, Bestellungen) bleiben erhalten.
 */
class StagingSetup
{
    public static function run(): void
    {
        if (! app()->environment('staging') || app()->runningInConsole()) {
            return;
        }
        $hash = md5(implode('|', array_map('basename', glob(database_path('migrations/*.php')) ?: [])));
        $marker = storage_path('app/.migrated');
        if (is_file($marker) && trim((string) file_get_contents($marker)) === $hash) {
            return;
        }
        $lock = fopen(storage_path('app/.migrate.lock'), 'c');
        if (! $lock || ! flock($lock, LOCK_EX | LOCK_NB)) {
            return;   // läuft gerade in einer anderen Anfrage
        }
        try {
            $db = config('database.connections.sqlite.database');
            if (config('database.default') === 'sqlite' && $db && ! is_file($db)) {
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
}
