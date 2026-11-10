<div class="faq faq--wide">
    @foreach ($faqs as $faq)
        <details>
            <summary>{{ $faq['q'] }}</summary>
            <div class="faq__answer"><p>{{ $faq['a'] }}</p></div>
        </details>
    @endforeach
</div>
