<?php

namespace App\View\Components;

use Illuminate\View\Component;

class ZoomImage extends Component
{
    public $path;
    public $alt;
    public $width;
    public $height;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(
        $path = '',
        $alt = '',
        $width = '',
        $height = '',
    )
    {
        $this->path   = $path;
        $this->alt    = $alt;
        $this->width  = $width;
        $this->height = $height;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|\Closure|string
     */
    public function render()
    {
        return view('components.zoom-image');
    }
}
