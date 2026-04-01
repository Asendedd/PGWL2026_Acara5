<?php

namespace App\Http\Controllers;

class PageController extends Controller
{
    public function peta()
    {
        $data = [
            'title' => 'Peta',
        ];

        return view('table', $data);
    }
}
