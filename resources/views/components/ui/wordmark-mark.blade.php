{{-- Signet der Manufaktur: Monogramm A/Ω (Vektor aus dem gelieferten Logo, public/images/logo-monogramm.svg). --}}
@php
    $svg = file_get_contents(public_path('images/logo-monogramm.svg'));
    $svg = preg_replace('/^<svg /', '<svg class="'.e(trim('wordmark__mark '.$attributes->get('class'))).'" aria-hidden="true" focusable="false" ', $svg, 1);
@endphp
{!! $svg !!}
