<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Contracts\View\View;

class IndexController
{
    public function __invoke() : View
    {
        $author_styles = \App\Enums\AuthorStyle::options();

        return view('index', compact([
            'author_styles',
        ]));
    }
}
