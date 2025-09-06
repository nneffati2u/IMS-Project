<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
| Ce fichier te permet :
|  - de déclarer des petites commandes Artisan "inline" (ex. inspire)
|  - de planifier l’exécution de commandes avec le scheduler (Schedule)
*/

/**
 * Exemple par défaut (optionnel) :
 * php artisan inspire
 */
Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Affiche une citation inspirante');

/**
 * PLANIFICATION OFFICIELLE DU SUJET
 * Vérifie les seuils et envoie les e-mails d’alerte au plus tard chaque minute.
 * - withoutOverlapping : évite deux exécutions en parallèle si une minute prend du temps
 * - onOneServer : utile si tu déploies sur plusieurs serveurs (sans effet en local)
 */
Schedule::command('stock:check-thresholds')
    ->everyMinute()
    ->withoutOverlapping()
    ->onOneServer();
