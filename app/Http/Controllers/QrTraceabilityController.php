<?php

namespace App\Http\Controllers;

use App\Models\Swine;
use Illuminate\Http\Request;
use Illuminate\View\View;

class QrTraceabilityController extends Controller
{
    /**
     * Display the QR scanner.
     */
    public function scanner(): View
    {
        return view('qr.scanner');
    }

    /**
     * Display traceability records.
     */
    public function index(Request $request): View
    {
        $swine = Swine::query()
            ->with([
                'farm',
                'currentLocation',
            ])
            ->when($request->filled('search'), function ($query) use ($request) {

                $search = trim($request->search);

                $query->where(function ($query) use ($search) {

                    $query->where('tag_number', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");

                });

            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('traceability.index', compact('swine'));
    }
}