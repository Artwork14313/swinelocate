<?php

namespace App\Http\Controllers;

use App\Models\SwineSale;
use Illuminate\View\View;

class SaleHistoryController extends Controller
{
    public function index(): View
    {
        $sales = SwineSale::query()
            ->with(['swine.farm', 'recordedBy'])
            ->latest('sold_at')
            ->latest()
            ->paginate(15);

        return view('sale-history.index', compact('sales'));
    }
}
