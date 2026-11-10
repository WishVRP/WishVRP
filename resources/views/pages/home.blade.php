{{-- Startseite: 15 Abschnitte, je eine Datei in pages/home/ --}}
<x-layouts.site :meta="$meta" :schema="$schema" current="/">
    @include('pages.home.01-hero')
    @include('pages.home.02-intro')
    @include('pages.home.03-experience')
    @include('pages.home.04-finder')
    @include('pages.home.05-dates')
    @include('pages.home.06-knives')
    @include('pages.home.07-edition')
    @include('pages.home.07b-gallery')
    @include('pages.home.08-custom')
    @include('pages.home.09-gifts')
    @include('pages.home.10-process')
    @include('pages.home.11-maker')
    @include('pages.home.12-teams')
    @include('pages.home.13-voices')
    @include('pages.home.14-guides')
    @include('pages.home.15-contact')
</x-layouts.site>
