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

// Nach dem Kurs: am Abend des letzten Kurstags Danke + Bitte um Google-Bewertung.
Artisan::command('orders:course-reviews', function () {
    $this->info(\App\Support\OrderAutomation::courseReviews().' Bewertungsanfrage(n) nach Kurs gesendet.');
})->purpose('Bewertungsanfragen nach Kurstagen senden');
\Illuminate\Support\Facades\Schedule::command('orders:course-reviews')->dailyAt('19:00')->timezone('Europe/Berlin')->withoutOverlapping();

// DHL-Pakete automatisch auf „Zugestellt“ setzen (nur mit DHL_API_KEY), dann „Angekommen“-Mail mit Bewertungslink.
Artisan::command('orders:track-deliveries', function () {
    $this->info(\App\Support\OrderAutomation::trackDeliveries().' Sendung(en) als zugestellt erkannt.');
})->purpose('DHL-Sendungsstatus prüfen');
\Illuminate\Support\Facades\Schedule::command('orders:track-deliveries')->everyTwoHours()->between('8:00', '21:00')->timezone('Europe/Berlin')->withoutOverlapping();

// Alle Benachrichtigungen einmal mit Beispieldaten an eine Adresse schicken (z. B. nach dem Einrichten des Mailservers).
Artisan::command('ulbrich:mail-test {email}', function (string $email) {
    $course = \App\Models\Course::query()->first();
    $mk = function (array $a) {
        $o = new \App\Models\Order($a + ['name' => 'Test Kundin', 'email' => 'test@example.org', 'payment_method' => 'ueberweisung', 'review_consent' => true]);
        $o->number = 'U'.now()->year.'-TEST';
        $o->manage_token = str_repeat('t', 48);
        $o->created_at = now();

        return $o;
    };
    $knife = $mk(['type' => 'messer', 'item' => 'Damast-Kochmesser (Beispiel)', 'quantity' => 1, 'amount' => 289, 'shipping_amount' => 7.99, 'shipping_method' => 'versand',
        'street' => 'Musterweg 1', 'postal_code' => '12345', 'city' => 'Musterstadt', 'country' => 'Deutschland', 'country_code' => 'DE',
        'tracking_carrier' => 'dhl', 'tracking_number' => '00340434161094000000', 'production_time' => 'ca. 3–4 Wochen']);
    $kurs = $mk(['type' => 'kurs', 'course_id' => $course?->id, 'participants' => 2, 'amount' => 600, 'course_start' => now()->addDays(30), 'course_end' => now()->addDays(30)]);
    $sent = 0;
    foreach (['eingang', 'bezahlt', 'in_arbeit', 'versendet', 'abholbereit', 'zugestellt'] as $k) {
        \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\CustomerUpdateMail($knife, $k));
        $sent++;
    }
    foreach (['eingang', 'bezahlt', 'umgebucht', 'kurs'] as $k) {
        \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\CustomerUpdateMail($kurs, $k));
        $sent++;
    }
    $app = new \App\Models\MarketApplication(['code' => 'KHM-TEST00', 'season' => \App\Support\Market::season(), 'stand' => 4, 'name' => 'Test Ausstellerin', 'email' => $email, 'category' => 'Keramik', 'description' => 'Beispiel', 'amount' => 90, 'tables' => 1]);
    foreach (['eingang', 'angenommen', 'bezahlt', 'abgelehnt'] as $k) {
        \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\MarketMail($app, $k));
        $sent++;
    }
    $w = new \App\Models\Withdrawal(['name' => 'Test Kundin', 'email' => $email, 'order_number' => 'U'.now()->year.'-TEST', 'decision_note' => 'Beispieltext']);
    $w->created_at = now();
    foreach (['eingang', 'angenommen', 'abgelehnt'] as $k) {
        \Illuminate\Support\Facades\Mail::to($email)->send(new \App\Mail\WithdrawalMail($w, $k));
        $sent++;
    }
    $this->info("{$sent} Test-E-Mails an {$email} geschickt (Mailer: ".config('mail.default').').');
})->purpose('Alle Kunden-, Markt- und Widerrufs-E-Mails mit Beispieldaten an eine Adresse schicken');
