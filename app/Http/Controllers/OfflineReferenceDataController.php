<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\Farmer;
use App\Models\FarmLocation;
use App\Models\Swine;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfflineReferenceDataController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $user = $request->user();
        $canRegisterSwine = $user->hasPermission('register-swine');
        $canManageSwine = $user->hasPermission('manage-swine');

        $canUseFarmRecords = $canRegisterSwine
            || $user->hasPermission('manage-movements');
        $canUseLocations = $canRegisterSwine || $user->hasPermission('manage-movements');
        $canUseSwineLookup = $canManageSwine
            || $canRegisterSwine
            || $user->hasPermission('manage-health')
            || $user->hasPermission('record-weight')
            || $user->hasPermission('manage-movements');

        return response()
            ->json([
                'farms' => $canUseFarmRecords
                    ? Farm::query()
                        ->where('status', 'active')
                        ->orderBy('name')
                        ->get(['id', 'farm_code', 'name'])
                    : [],
                'farmers' => $canUseFarmRecords
                    ? Farmer::query()
                        ->whereHas('farm', fn ($query) => $query->where('status', 'active'))
                        ->with('farm:id,name')
                        ->orderBy('name')
                        ->get(['id', 'farm_id', 'name'])
                        ->map(fn (Farmer $farmer) => [
                            'id' => $farmer->id,
                            'farm_id' => $farmer->farm_id,
                            'name' => $farmer->name,
                            'farm_name' => $farmer->farm?->name,
                        ])
                    : [],
                'locations' => $canUseLocations
                    ? FarmLocation::query()
                        ->where('status', 'active')
                        ->orderBy('name')
                        ->get(['id', 'farm_id', 'location_code', 'name'])
                    : [],
                'swine' => $canUseSwineLookup
                    ? Swine::query()
                        ->where('status', 'active')
                        ->orderBy('tag_number')
                        ->get(['id', 'tag_number', 'name', 'farm_id'])
                    : [],
                'synced_at' => now()->toIso8601String(),
            ])
            ->header('Cache-Control', 'private, no-store');
    }
}
