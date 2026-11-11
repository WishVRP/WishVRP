<?php

namespace App\Support;

use App\Mail\NewsletterMail;
use App\Models\DiscountCode;
use App\Models\NewsletterDelivery;
use App\Models\NewsletterIssue;
use App\Models\NewsletterSubscriber;
use App\Models\XmasEntry;
use Illuminate\Support\Facades\Mail;

/** Versand an alle bestätigten, nicht abgemeldeten Empfänger – wiederaufnehmbar, mit Protokoll je Empfänger. */
final class NewsletterSender
{
    /** Automatische Wochen-Ausgabe anlegen (höchstens eine je Kalenderwoche). */
    public static function createWeekly(): ?NewsletterIssue
    {
        $week = now()->format('o-\WW');
        if (NewsletterIssue::query()->where('week', $week)->exists()) {
            return null;
        }
        $n = config('ulbrich.newsletter');
        $lastDiscount = NewsletterIssue::query()->whereNotNull('discount_percent')->whereNotNull('sent_at')->latest('sent_at')->value('sent_at');
        $discount = (! $lastDiscount || $lastDiscount->lt(now()->subDays($n['discount_every_days']))) ? min($n['discount_percent'], $n['max_percent']) : null;
        $blocks = NewsletterContent::weekly($discount);

        return NewsletterIssue::create([
            'kind' => 'auto', 'week' => $week, 'status' => 'geplant', 'scheduled_at' => now(),
            'subject' => NewsletterContent::subject($discount), 'preheader' => NewsletterContent::preheader($blocks), 'blocks' => $blocks,
            'discount_percent' => $discount, 'discount_valid_days' => $discount ? $n['discount_valid_days'] : null,
        ]);
    }

    /** Fällige Ausgaben versenden. @return int Anzahl versendeter Ausgaben */
    public static function sendDue(): int
    {
        $count = 0;
        NewsletterIssue::query()->whereIn('status', ['geplant', 'sendet'])->where('scheduled_at', '<=', now())->orderBy('scheduled_at')
            ->each(function (NewsletterIssue $issue) use (&$count) {
                self::send($issue);
                $count++;
            });

        return $count;
    }

    public static function send(NewsletterIssue $issue): void
    {
        $issue->update(['status' => 'sendet']);
        $percent = $issue->discount();
        $validUntil = $percent ? now()->addDays($issue->discount_valid_days ?: 30)->endOfDay() : null;

        NewsletterSubscriber::query()->active()->orderBy('id')->chunkById(100, function ($subs) use ($issue, $percent, $validUntil) {
            foreach ($subs as $sub) {
                if (NewsletterDelivery::query()->where('newsletter_issue_id', $issue->id)->where('newsletter_subscriber_id', $sub->id)->exists()) {
                    continue;   // schon versendet (Wiederaufnahme nach Abbruch)
                }
                $code = $percent ? DiscountCode::create([
                    'code' => DiscountCode::newCode(), 'percent' => $percent, 'email' => $sub->email, 'email_key' => XmasEntry::emailKey($sub->email),
                    'source' => 'newsletter', 'newsletter_issue_id' => $issue->id, 'valid_until' => $validUntil,
                ]) : null;
                try {
                    Mail::to($sub->email)->send(new NewsletterMail($issue, $sub, $code));
                    $status = 'versendet';
                    $error = null;
                } catch (\Throwable $e) {
                    report($e);
                    $status = 'fehler';
                    $error = mb_substr($e->getMessage(), 0, 250);
                }
                NewsletterDelivery::create([
                    'newsletter_issue_id' => $issue->id, 'newsletter_subscriber_id' => $sub->id, 'email' => $sub->email,
                    'status' => $status, 'error' => $error, 'discount_code_id' => $code?->id, 'sent_at' => now(),
                ]);
            }
        });

        $issue->update([
            'status' => 'versendet', 'sent_at' => now(),
            'recipients_count' => $issue->deliveries()->where('status', 'versendet')->count(),
            'failed_count' => $issue->deliveries()->where('status', 'fehler')->count(),
        ]);
    }

    /** Testmail (z. B. an das eigene Konto) – ohne Protokoll, Beispielcode statt echtem Code. */
    public static function sendTest(NewsletterIssue $issue, string $email): void
    {
        $sub = new NewsletterSubscriber(['email' => $email, 'unsubscribe_token' => str_repeat('x', 48)]);
        $sub->confirmed_at = now();
        $code = $issue->discount() ? new DiscountCode(['code' => 'NEWS-BEISPIEL', 'percent' => $issue->discount(), 'valid_until' => now()->addDays($issue->discount_valid_days ?: 30)]) : null;
        Mail::to($email)->send((new NewsletterMail($issue, $sub, $code))->subject('[Test] '.$issue->subject));
    }
}
