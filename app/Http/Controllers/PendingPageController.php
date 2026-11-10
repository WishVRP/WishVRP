<?php

namespace App\Http\Controllers;

use App\Support\PageMeta;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/** Platzhalter für noch nicht migrierte Bestandsseiten – nur außerhalb der Produktion. */
class PendingPageController extends Controller
{
    public function __invoke(Request $request): View
    {
        $path = $request->route()->defaults['pagePath'];
        $page = config('pages')[$path];

        $meta = new PageMeta(
            title: $page['title'],
            description: 'Diese Seite wird im Relaunch noch umgesetzt.',
            path: $path,
            index: false,
            breadcrumb: [['name' => 'Startseite', 'path' => '/'], ['name' => $page['title'], 'path' => $path]],
        );

        return view('pages.pending', compact('meta', 'page', 'path'));
    }
}
