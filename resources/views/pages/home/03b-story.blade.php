{{-- Erlebnis-Geschichte: drei Bilder im Kopf, beim Scrollen nacheinander – endet bei der Buchung --}}
@php $chapters = [
    ['no' => '01', 'title' => 'Die Glut.', 'text' => 'Der Stahl liegt in der Esse, bis er leuchtet. Du siehst, wann er bereit ist.', 'foto' => 'damast-detail'],
    ['no' => '02', 'title' => 'Der erste Schlag.', 'text' => 'Am Amboss formst du die Klinge mit dem Hammer – Schlag für Schlag, mit deinen eigenen Händen.', 'foto' => 'punze-detail'],
    ['no' => '03', 'title' => 'Dein Messer.', 'text' => 'Am Ende hältst du ein Messer in der Hand, das es nur einmal gibt. Weil du es gemacht hast.', 'foto' => 'klappmesser-stehend'],
]; @endphp
<section class="story is-dark" aria-labelledby="story-title" data-story>
    <div class="container story__grid">
        <div class="story__visual" aria-hidden="true">
            @foreach ($chapters as $i => $ch)
                <x-ui.media class="story__img {{ $i === 0 ? 'is-active' : '' }}" :foto="$ch['foto']" format="portrait" decorative data-story-img="{{ $i }}" sizes="(min-width: 60rem) 40vw, 90vw" />
            @endforeach
            <div class="story__progress"><span data-story-bar></span></div>
        </div>
        <div class="story__chapters">
            <h2 id="story-title" class="visually-hidden">So fühlt sich ein Schmiedekurs an</h2>
            @foreach ($chapters as $i => $ch)
                <article class="story__chapter" data-story-step="{{ $i }}">
                    <span class="story__no">{{ $ch['no'] }}</span>
                    <h3 class="story__title">{{ $ch['title'] }}</h3>
                    <p class="story__text">{{ $ch['text'] }}</p>
                </article>
            @endforeach
            <div class="story__cta">
                <p class="story__cta-title">Bereit für deinen ersten Schlag?</p>
                <div class="btn-row">
                    <x-ui.button href="/schmiedekurse/buchen/" variant="straw" size="lg">Schmiedekurs buchen</x-ui.button>
                    <x-ui.button href="/geschenke/schmiedekurs-gutschein/" variant="secondary" size="lg">Als Gutschein verschenken</x-ui.button>
                </div>
            </div>
        </div>
    </div>
</section>
