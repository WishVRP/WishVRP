<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Statistik nach 13 Monaten löschen (PageView, SiteEvent).
\Illuminate\Support\Facades\Schedule::command('model:prune')->daily();

// Newsletter: fällige Ausgaben alle 5 Minuten versenden; Wochen-Ausgabe automatisch anlegen.
Artisan::command('newsletter:send-due', function () {
    $this->info(\App\Support\NewsletterSender::sendDue().' Ausgabe(n) versendet.');
})->purpose('Fällige Newsletter-Ausgaben versenden');

Artisan::command('newsletter:weekly', function () {
    if (! config('ulbrich.newsletter.auto_weekly')) {
        return $this->info('Automatischer Wochen-Newsletter ist ausgeschaltet.');
    }
    if (! \App\Models\NewsletterSubscriber::query()->active()->exists()) {
        return $this->info('Keine bestätigten Empfänger – keine Ausgabe angelegt.');
    }
    $issue = \App\Support\NewsletterSender::createWeekly();
    $this->info($issue ? "Ausgabe „{$issue->subject}“ angelegt." : 'Diese Woche gibt es schon eine Ausgabe.');
})->purpose('Automatische Wochen-Ausgabe anlegen');

\Illuminate\Support\Facades\Schedule::command('newsletter:weekly')
    ->weeklyOn((int) config('ulbrich.newsletter.weekday') % 7, config('ulbrich.newsletter.time'))->timezone('Europe/Berlin');
\Illuminate\Support\Facades\Schedule::command('newsletter:send-due')->everyFiveMinutes()->withoutOverlapping();
