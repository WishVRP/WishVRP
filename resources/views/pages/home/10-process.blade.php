@php
    // Allgemeiner Ablauf beim Schmieden eines Messers (Handwerkswissen, keine betriebsspezifischen Angaben).
    $steps = [
        ['icon' => 'steel', 'title' => 'Stahl', 'text' => 'Ausgangspunkt ist ein Stück Stahl – beim Damast werden Lagen verschiedener Stähle zusammengeführt.'],
        ['icon' => 'fire', 'title' => 'Esse', 'text' => 'Der Stahl wird in der Esse auf Schmiedetemperatur gebracht.'],
        ['icon' => 'hammer', 'title' => 'Schmieden', 'text' => 'Am Amboss entsteht mit Hammerschlägen die Form der Klinge.'],
        ['icon' => 'drop', 'title' => 'Härten', 'text' => 'Erhitzen und schnelles Abkühlen machen den Stahl hart.'],
        ['icon' => 'temper', 'title' => 'Anlassen', 'text' => 'Kontrolliertes Erwärmen nimmt Sprödigkeit – an den Anlassfarben lässt sich die Temperatur ablesen.', 'temper' => true],
        ['icon' => 'blade', 'title' => 'Schliff & Griff', 'text' => 'Schleifen, Griff anbringen, schärfen: Aus der Klinge wird ein Messer.'],
    ];
@endphp
<section class="block is-dark textured process" aria-labelledby="process-title">
    <div class="container">
        <div class="block-head">
            <div>
                <x-ui.kicker color="straw">Handwerk</x-ui.kicker>
                <h2 id="process-title" class="h-section">Vom Stahl zum Messer</h2>
            </div>
            <p class="block-head__intro">Sechs Schritte, die ein geschmiedetes Messer durchläuft – von der Esse bis zur scharfen Schneide.</p>
        </div>
        <ol class="process__track" role="list">
            @foreach ($steps as $i => $step)
                <li class="process__step reveal {{ $i ? 'reveal-delay-'.min($i, 3) : '' }} @if(!empty($step['temper'])) process__step--temper @endif">
                    <span class="process__num">Schritt {{ $i + 1 }} von {{ count($steps) }}</span>
                    <x-ui.icon :name="$step['icon']" class="process__icon" />
                    <h3>{{ $step['title'] }}</h3>
                    <p>{{ $step['text'] }}</p>
                    @if (!empty($step['temper']))<div class="process__mini-temper" aria-hidden="true"></div>@endif
                </li>
            @endforeach
        </ol>
        <p class="process__note">Allgemeiner Ablauf beim Schmieden. Was du in welchem Kurs selbst machst, steht auf der jeweiligen Kursseite.</p>
    </div>
</section>
