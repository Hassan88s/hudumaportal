<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Services\TopSellersService;
use Illuminate\Http\Request;

/**
 * Public "Top 100 Sellers" page.
 * URL: /top-sellers
 */
class TopSellersController extends Controller
{
    public function index(Request $request, TopSellersService $service)
    {
        $period = $request->query('period', 'all');   // month | year | all
        $sellers = $service->getTopSellers(100, $period);

        return view('frontend.top-sellers', compact('sellers', 'period'));
    }
}
