<button type="button" class="market-stand market-stand--{{ $st['type'] }} market-stand--n{{ $st['number'] }} is-{{ $s }}" data-stand="{{ $st['number'] }}"
    data-price="{{ $st['price'] }}" data-label="{{ $st['label'] }}" data-size="{{ $st['size'] }}" data-side="{{ $side ?? 'frei zugänglich' }}"
    @disabled(! in_array($s, ['frei', 'mein'], true) || ! $open) aria-label="Stand {{ $st['number'] }}, {{ $st['label'] }} {{ $st['size'] }}, {{ $stateLabel[$s] }}">
    <span class="market-stand__no">{{ $st['number'] }}</span>
    <span class="market-stand__size">{{ $st['size'] }}</span>
    <span class="market-stand__state">{{ $stateLabel[$s] }}</span>
</button>
