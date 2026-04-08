<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class CentralLayout extends Component
{
    public function __construct(
        public string $title = '',
        public string $breadcrumb = '',
    ) {}

    public function render(): View
    {
        return view('layouts.central');
    }
}
