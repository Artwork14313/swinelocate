<?php

namespace App\Http\Controllers;

use App\Models\Farm;
use App\Models\HealthRecord;
use App\Models\Swine;
use App\Models\SwineMovement;
use App\Models\WeightRecord;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportsController extends Controller
{
    public function index(Request $request): View
    {
        [$farmId, $startDate, $endDate] = $this->filters($request);

        $swines = Swine::query()->when($farmId, fn ($query) => $query->where('farm_id', $farmId));
        $swineIds = (clone $swines)->select('id');

        $populationByStatus = (clone $swines)
            ->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->orderByDesc('total')
            ->get();

        $healthRecords = HealthRecord::query()
            ->whereIn('swine_id', $swineIds)
            ->whereBetween('record_date', [$startDate, $endDate]);

        $healthByStatus = (clone $healthRecords)
            ->select('health_status', DB::raw('COUNT(*) as total'))
            ->groupBy('health_status')
            ->orderByDesc('total')
            ->get();

        $movementRecords = SwineMovement::query()
            ->whereIn('swine_id', $swineIds)
            ->whereBetween('movement_date', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ]);

        $weightRecords = WeightRecord::query()
            ->whereIn('swine_id', $swineIds)
            ->whereBetween('record_date', [$startDate, $endDate]);

        $recentHealthRecords = (clone $healthRecords)
            ->with('swine')
            ->orderByDesc('record_date')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        $recentMovements = (clone $movementRecords)
            ->with(['swine', 'fromLocation', 'toLocation'])
            ->orderByDesc('movement_date')
            ->orderByDesc('id')
            ->take(8)
            ->get();

        return view('reports.index', [
            'farms' => Farm::query()->orderBy('name')->get(),
            'selectedFarmId' => $farmId,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalSwine' => (clone $swines)->count(),
            'activeSwine' => (clone $swines)->where('status', 'active')->count(),
            'totalHealthRecords' => (clone $healthRecords)->count(),
            'totalMovements' => (clone $movementRecords)->count(),
            'averageWeight' => (clone $weightRecords)->avg('weight'),
            'populationByStatus' => $populationByStatus,
            'healthByStatus' => $healthByStatus,
            'recentHealthRecords' => $recentHealthRecords,
            'recentMovements' => $recentMovements,
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        [$farmId, $startDate, $endDate] = $this->filters($request);
        $type = $request->validate([
            'type' => ['nullable', 'in:swine,health,movements,weights'],
        ])['type'] ?? 'swine';

        return match ($type) {
            'health' => $this->exportHealthRecords($farmId, $startDate, $endDate),
            'movements' => $this->exportMovements($farmId, $startDate, $endDate),
            'weights' => $this->exportWeightRecords($farmId, $startDate, $endDate),
            default => $this->exportSwine($farmId, $startDate, $endDate),
        };
    }

    private function exportSwine(int|string|null $farmId, string $startDate, string $endDate): StreamedResponse
    {
        $swines = Swine::query()
            ->with(['farm', 'farmer'])
            ->when($farmId, fn ($query) => $query->where('farm_id', $farmId))
            ->with([
                'healthRecords' => fn ($query) => $query
                    ->whereBetween('record_date', [$startDate, $endDate])
                    ->orderByDesc('record_date')
                    ->orderByDesc('id'),
                'weightRecords' => fn ($query) => $query
                    ->whereBetween('record_date', [$startDate, $endDate])
                    ->orderByDesc('record_date')
                    ->orderByDesc('id'),
            ])
            ->orderBy('tag_number');

        return $this->downloadCsv('swine-report', [
            'Tag Number', 'Farmer', 'Farm', 'Breed', 'Sex', 'Status',
            'Latest Health Date', 'Latest Health Status', 'Latest Weight Date', 'Latest Weight (kg)',
        ], function ($output) use ($swines): void {
            $swines->chunk(500, function ($records) use ($output): void {
                foreach ($records as $swine) {
                    $latestHealth = $swine->healthRecords->first();
                    $latestWeight = $swine->weightRecords->first();
                    $this->writeCsvRow($output, [
                        $swine->tag_number,
                        $swine->farmer?->name,
                        $swine->farm?->name,
                        $swine->breed,
                        $swine->sex,
                        $swine->status,
                        $latestHealth?->record_date?->toDateString(),
                        $latestHealth?->health_status,
                        $latestWeight?->record_date?->toDateString(),
                        $latestWeight?->weight,
                    ]);
                }
            });
        });
    }

    private function exportHealthRecords(int|string|null $farmId, string $startDate, string $endDate): StreamedResponse
    {
        $records = HealthRecord::query()
            ->with('swine.farm')
            ->whereHas('swine', fn ($query) => $query->when($farmId, fn ($query) => $query->where('farm_id', $farmId)))
            ->whereBetween('record_date', [$startDate, $endDate])
            ->orderBy('record_date')
            ->orderBy('id');

        return $this->downloadCsv('health-report', [
            'Record Date', 'Tag Number', 'Farm', 'Record Type', 'Health Status', 'Vaccine',
            'Dose', 'Batch Number', 'Next Due Date', 'Symptoms', 'Diagnosis', 'Treatment',
            'Observations', 'Veterinary Assessment', 'Notes',
        ], function ($output) use ($records): void {
            $records->chunk(500, function ($batch) use ($output): void {
                foreach ($batch as $record) {
                    $this->writeCsvRow($output, [
                        $record->record_date?->toDateString(),
                        $record->swine?->tag_number,
                        $record->swine?->farm?->name,
                        $record->record_type,
                        $record->health_status,
                        $record->vaccine_name,
                        $record->dose,
                        $record->batch_number,
                        $record->next_due_date?->toDateString(),
                        $record->symptoms,
                        $record->diagnosis,
                        $record->treatment,
                        $record->observations,
                        $record->veterinary_assessment,
                        $record->notes,
                    ]);
                }
            });
        });
    }

    private function exportMovements(int|string|null $farmId, string $startDate, string $endDate): StreamedResponse
    {
        $records = SwineMovement::query()
            ->with(['swine.farm', 'fromLocation', 'toLocation'])
            ->whereHas('swine', fn ($query) => $query->when($farmId, fn ($query) => $query->where('farm_id', $farmId)))
            ->whereBetween('movement_date', [
                Carbon::parse($startDate)->startOfDay(),
                Carbon::parse($endDate)->endOfDay(),
            ])
            ->orderBy('movement_date')
            ->orderBy('id');

        return $this->downloadCsv('movement-report', [
            'Movement Date', 'Tag Number', 'Farm', 'From Location', 'To Location', 'Reason', 'Notes',
        ], function ($output) use ($records): void {
            $records->chunk(500, function ($batch) use ($output): void {
                foreach ($batch as $record) {
                    $this->writeCsvRow($output, [
                        $record->movement_date?->toDateTimeString(),
                        $record->swine?->tag_number,
                        $record->swine?->farm?->name,
                        $record->fromLocation?->name,
                        $record->toLocation?->name,
                        $record->reason,
                        $record->notes,
                    ]);
                }
            });
        });
    }

    private function exportWeightRecords(int|string|null $farmId, string $startDate, string $endDate): StreamedResponse
    {
        $records = WeightRecord::query()
            ->with('swine.farm')
            ->whereHas('swine', fn ($query) => $query->when($farmId, fn ($query) => $query->where('farm_id', $farmId)))
            ->whereBetween('record_date', [$startDate, $endDate])
            ->orderBy('record_date')
            ->orderBy('id');

        return $this->downloadCsv('weight-report', [
            'Record Date', 'Tag Number', 'Farm', 'Weight (kg)', 'Notes',
        ], function ($output) use ($records): void {
            $records->chunk(500, function ($batch) use ($output): void {
                foreach ($batch as $record) {
                    $this->writeCsvRow($output, [
                        $record->record_date?->toDateString(),
                        $record->swine?->tag_number,
                        $record->swine?->farm?->name,
                        $record->weight,
                        $record->notes,
                    ]);
                }
            });
        });
    }

    private function downloadCsv(string $reportName, array $headers, Closure $writeRows): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $writeRows): void {
            $output = fopen('php://output', 'w');
            $this->writeCsvRow($output, $headers);
            $writeRows($output);
            fclose($output);
        }, $reportName.'-'.now()->format('Y-m-d').'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function writeCsvRow($output, array $values): void
    {
        foreach ($values as &$value) {
            if (is_string($value) && preg_match('/^[\x00-\x20]*[=+\-@]/', $value) === 1) {
                $value = "'".$value;
            }
        }

        fputcsv($output, $values);
    }

    private function filters(Request $request): array
    {
        $validated = $request->validate([
            'farm_id' => ['nullable', 'integer', 'exists:farms,id'],
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
        ]);

        $startDate = $validated['start_date'] ?? now()->subDays(29)->toDateString();
        $endDate = $validated['end_date'] ?? now()->toDateString();

        if (Carbon::parse($startDate)->gt(Carbon::parse($endDate))) {
            throw ValidationException::withMessages([
                'start_date' => 'The start date must be before or equal to the end date.',
            ]);
        }

        return [
            $validated['farm_id'] ?? null,
            $startDate,
            $endDate,
        ];
    }
}
