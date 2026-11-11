<div class="faq faq--wide">
    @foreach ($faqs as $faq)
        <details>
            <summary>{{ $faq['q'] }}</summary>
            <div class="faq__answer"><p>{!! \App\Support\Inline::html($faq['a']) !!}</p></div>
        </details>
    @endforeach
</div>
