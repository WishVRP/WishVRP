{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">
<channel>
<title>{{ config('ulbrich.name') }} – Shop</title>
<link>{{ \App\Support\PageMeta::absolute('/shop/') }}</link>
<description>Handgefertigte Messer aus der {{ config('ulbrich.name') }}</description>
@foreach ($items as $i)
<item>
<g:id>{{ $i['id'] }}</g:id>
<g:title>{{ $i['title'] }}</g:title>
<g:description>{{ $i['description'] }}</g:description>
<g:link>{{ $i['link'] }}</g:link>
<g:image_link>{{ $i['image'] }}</g:image_link>
@foreach ($i['extra'] as $u)
<g:additional_image_link>{{ $u }}</g:additional_image_link>
@endforeach
<g:price>{{ $i['price'] }}</g:price>
<g:availability>{{ $i['availability'] }}</g:availability>
<g:condition>new</g:condition>
<g:brand>{{ config('ulbrich.name') }}</g:brand>
<g:identifier_exists>no</g:identifier_exists>
<g:product_type>{{ $i['type'] }}</g:product_type>
<g:shipping><g:country>DE</g:country><g:service>Standard</g:service><g:price>{{ $shipping }}</g:price></g:shipping>
</item>
@endforeach
</channel>
</rss>
