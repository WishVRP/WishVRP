Hallo {{ $o->name }},

danke für deine Anmeldung zum Nachschärfen im Rahmen unserer lebenslangen Schliff-Garantie. Deine Vorgangsnummer: {{ $o->number }}

@if ($o->shipping_method === 'versand')
SO SCHICKST DU DEIN MESSER EIN
1. Klinge sicher verpacken: Schneide mit fester Pappe umwickeln und gut verkleben, dann in einen stabilen Karton.
2. Einen Zettel mit der Vorgangsnummer {{ $o->number }} beilegen.
3. Senden an:
   {{ $c['name'] }}
   {{ $c['owner'] }}
   {{ $c['address']['street'] }}
   {{ $c['address']['postal_code'] }} {{ $c['address']['locality'] }}
   Deutschland
4. Wir melden uns, sobald das Messer bei uns angekommen ist, und schicken es nach dem Nachschliff an deine Anschrift zurück:
   {{ $o->street }}, {{ $o->postal_code }} {{ $o->city }}{{ $o->country && $o->country !== 'Deutschland' ? ', '.$o->country : '' }}

Das Nachschärfen ist kostenlos. Die Kosten für Hin- und Rückversand trägst du; die Rückversandkosten stimmen wir vorher mit dir ab. Bei Sendungen aus dem Ausland bitte vorher die Einfuhr- und Versandbestimmungen für Messer prüfen.
@else
SO GIBST DU DEIN MESSER AB
Bring dein Messer in die Manufaktur:
{{ $c['name'] }}, {{ $c['address']['street'] }}, {{ $c['address']['postal_code'] }} {{ $c['address']['locality'] }} ({{ $c['address']['district'] }})
Ruf am besten vorher kurz an ({{ $c['phone']['display'] }}), damit jemand da ist. Wir melden uns, sobald du es wieder abholen kannst.

Das Nachschärfen ist kostenlos.
@endif

Gilt für Messer aus unserer Werkstatt – gekauft oder im Schmiedekurs selbst geschmiedet. Reparaturen von Schäden sind nicht enthalten; die besprechen wir vorher mit dir. Alle Bedingungen: {{ rtrim(config('app.url'), '/') }}/garantie/

Fragen? {{ $c['phone']['display'] }} · {{ $c['email'] }}

{{ $c['owner'] }}
{{ $c['name'] }}
