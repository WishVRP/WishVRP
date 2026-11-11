{{-- Newsletter: Bestätigen bzw. Abmelden (Knopf statt automatischer Aktion beim Öffnen des Links) --}}
@php
    $confirm = $page['newsletter_action'] === 'confirm';
    $token = (string) request('t');
    $sub = $token ? \App\Models\NewsletterSubscriber::query()->where($confirm ? 'confirm_token' : 'unsubscribe_token', $token)->first() : null;
    $done = session('newsletter');
@endphp
<x-layouts.site :meta="$meta" :schema="$schema" :current="$path">
    <x-page.hero :meta="$meta" :title="$confirm ? 'Newsletter bestätigen' : 'Newsletter abmelden'" kicker="Newsletter" tone="straw" />
    <section class="block">
        <div class="container prose">
            @if ($done === 'confirmed')
                <p class="lead">Danke! Deine Anmeldung ist bestätigt. Du bekommst ab jetzt unseren Newsletter – abmelden kannst du dich jederzeit über den Link in jeder E-Mail.</p>
            @elseif ($done === 'unsubscribed')
                <p class="lead">Du bist abgemeldet und bekommst keinen Newsletter mehr von uns.</p>
            @elseif ($done === 'invalid')
                <p class="lead">Dieser Link ist ungültig oder abgelaufen.</p>
            @elseif ($sub)
                <p class="lead">{{ $confirm ? 'Möchtest du den Newsletter der '.$c['name'].' an '.$sub->email.' erhalten?' : 'Möchtest du '.$sub->email.' vom Newsletter abmelden?' }}</p>
                <form method="post" action="{{ $confirm ? '/newsletter/bestaetigen/' : '/newsletter/abmelden/' }}">
                    @csrf
                    <input type="hidden" name="t" value="{{ $token }}">
                    <button type="submit" class="btn btn--primary btn--lg">{{ $confirm ? 'Ja, Newsletter bestätigen' : 'Ja, abmelden' }}</button>
                </form>
            @else
                <p class="lead">Öffne den Link aus deiner E-Mail, um {{ $confirm ? 'deine Anmeldung zu bestätigen' : 'dich abzumelden' }}.</p>
            @endif
            <p class="mt-6">Weiter zu den <a href="/geschenke/weihnachten/">Weihnachtsgeschenken</a>, zur <a href="/klappmesser/">Edition 100</a> oder zu den <a href="/schmiedekurse/">Schmiedekursen</a>.</p>
        </div>
    </section>
    <x-page.cta />
</x-layouts.site>
