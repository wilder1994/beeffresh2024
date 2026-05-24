<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\Store\HomePageService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(HomePageService $homePage): View
    {
        return view('welcome', $homePage->data());
    }
}
