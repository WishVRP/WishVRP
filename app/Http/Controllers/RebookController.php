<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Order;
use App\Support\CourseCalendar;
use App\Support\CustomerMail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

/** Kurstermin umbuchen über den persönlichen Link: 2× kostenlos, bis 48 Stunden vor Kursbeginn, Preis bleibt (Preisbindung). */
class RebookController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            't' => ['required', 'string', 'size:48'],
            'start' => ['nullable', 'date_format:Y-m-d', 'required_without:start_custom'],
            'start_custom' => ['nullable', 'date_format:Y-m-d'],
        ], ['start.required_without' => 'Bitte wähle einen neuen Kurstag.']);
        $data['start'] = ($data['start_custom'] ?? null) ?: $data['start'];
        $order = Order::query()->where('manage_token', $data['t'])->with('course')->firstOrFail();
        $back = '/buchung/umbuchen/?t='.$order->manage_token;

        if ($reason = $order->rebookBlocker()) {
            return redirect($back)->withErrors(['start' => $reason]);
        }
        $start = Carbon::parse($data['start'])->startOfDay();
        if ($start->isSameDay($order->course_start)) {
            return redirect($back)->withErrors(['start' => 'Das ist schon dein aktueller Termin.']);
        }
        if (! $order->course || ! CourseCalendar::available($order->course, $start, $order->id)) {
            return redirect($back)->withErrors(['start' => 'Dieser Tag ist leider nicht frei oder liegt außerhalb des Buchungsfensters. Bitte wähle einen anderen Tag.'])->withInput();
        }

        $old = $order->course_start->copy();
        $days = CourseCalendar::days($order->course);
        $order->forceFill([
            'original_course_start' => $order->original_course_start ?? $old,
            'course_start' => $start, 'course_end' => $start->copy()->addDays($days - 1),
            'rebook_count' => $order->rebook_count + 1, 'rebooked_at' => now(),
            // Preis bleibt unverändert (Preisbindung): amount wird bewusst nicht neu berechnet
        ])->save();
        Note::query()->create(['order_id' => $order->id, 'body' => 'Online umgebucht: '.$old->format('d.m.Y').' → '.$start->format('d.m.Y').' ('.$order->rebook_count.'. kostenlose Umbuchung)', 'done_at' => now()]);
        CustomerMail::send($order, 'umgebucht');

        return redirect($back)->with('rebooked', true);
    }
}
