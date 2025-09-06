<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Planification des tâches.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('stock:check-thresholds')
            ->everyMinute()          // exécute la commande chaque minute
            ->withoutOverlapping()   // évite qu’une nouvelle exécution démarre si la précédente n’a pas fini
            ->onOneServer();         // en cluster : une seule machine exécute la tâche (voir explications ci-dessous)
            // ->evenInMaintenanceMode(); // (optionnel) exécuter même en maintenance
            // ->runInBackground();        // (optionnel) ne bloque pas le scheduler si long
    }

    /**
     * Fuseau horaire du scheduler (s’applique à toutes les tâches).
     * Utile pour la logique de “toutes les minutes/heures” avec l’heure légale FR.
     */
    protected function scheduleTimezone(): ?\DateTimeZone
    {
        return new \DateTimeZone('Europe/Paris');
    }

    /**
     * Enregistrement des commandes Artisan.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
