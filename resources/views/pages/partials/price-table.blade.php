{{-- Preisübersicht aller Kurse aus der Datenbank (für Ratgeber Kosten) --}}
<h2 id="preise">Alle Kurspreise im Überblick</h2>
<div class="table-scroll" tabindex="0" role="region" aria-label="Kurspreise">
    <table class="compare compare--compact">
        <thead><tr><th scope="col">Kurs</th><th scope="col">Dauer</th><th scope="col">Projekt</th><th scope="col">Preis p. P.</th></tr></thead>
        <tbody>
            @foreach ($courses as $co)
                <tr class="course--{{ $co->category }}"><th scope="row"><a href="{{ $co->url() }}">{{ $co->name }}</a></th><td>{{ $co->duration_label }}</td><td>{{ $co->project }}</td><td><x-ui.price :amount="$co->price_from" :from="true" /></td></tr>
            @endforeach
        </tbody>
    </table>
</div>
<p class="tech muted"><x-ui.tax-note /></p>
