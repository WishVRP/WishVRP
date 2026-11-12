{{-- Rechtstext als Abschnitt einer Inhaltsseite (z. B. Versand & Zahlung) --}}
@php [$legalSlug, $legalTitle] = $page['legal_section']; @endphp
<section class="legal-inline mt-6" id="bedingungen" aria-labelledby="bedingungen-title">
    <h2 id="bedingungen-title">{{ $legalTitle }}</h2>
    <div class="prose">{!! \App\Support\LegalText::html($legalSlug) !!}</div>
</section>
