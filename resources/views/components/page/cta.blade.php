@props(['title' => 'Fragen? Ruf einfach an.', 'text' => null])
@php
    $c = config('ulbrich');
    $en = request()->is('en', 'en/*');
@endphp
<x-page.related />
<section class="page-cta is-dark textured" aria-label="{{ $en ? 'Contact' : 'Kontakt' }}">
    <div class="container page-cta__inner">
        <div>
            <h2 class="page-cta__title">{{ $title }}</h2>
            <p class="page-cta__text">{{ $text ?? ($en ? $c['owner'].' answers your questions about knives, custom work and shipping personally – by email or phone.' : $c['owner'].' beantwortet deine Fragen zu Kursen, Messern und Gutscheinen persönlich.') }}</p>
        </div>
        <div class="btn-row">
            <x-ui.button href="tel:{{ $c['phone']['e164'] }}" variant="straw" size="lg" :arrow="false">{{ $c['phone']['display'] }}</x-ui.button>
            @if ($en)
                <x-ui.button href="mailto:{{ $c['email'] }}" variant="secondary" size="lg">{{ $c['email'] }}</x-ui.button>
            @else
                <x-ui.button href="/schmiedekurse/buchen/" variant="secondary" size="lg">Kurstermin finden</x-ui.button>
            @endif
        </div>
    </div>
</section>
