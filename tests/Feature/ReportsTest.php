<?php

namespace Tests\Feature;

use App\Models\Farm;
use App\Models\HealthRecord;
use App\Models\Permission;
use App\Models\Role;
use App\Models\Swine;
use App\Models\SwineMovement;
use App\Models\User;
use App\Models\WeightRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_filter_summary_by_farm_and_date(): void
    {
        $this->actingAs($this->userWithReportPermission());

        $farm = Farm::create(['farm_code' => 'F-001', 'name' => 'North Farm']);
        $otherFarm = Farm::create(['farm_code' => 'F-002', 'name' => 'South Farm']);
        $swine = $this->createSwine($farm, 'TAG-001');
        $this->createSwine($otherFarm, 'TAG-002');

        HealthRecord::create([
            'swine_id' => $swine->id,
            'record_date' => '2026-09-10',
            'record_type' => 'Checkup',
            'health_status' => 'healthy',
        ]);
        HealthRecord::create([
            'swine_id' => $swine->id,
            'record_date' => '2026-08-10',
            'record_type' => 'Checkup',
            'health_status' => 'sick',
        ]);
        WeightRecord::create([
            'swine_id' => $swine->id,
            'record_date' => '2026-09-11',
            'weight' => 42.5,
        ]);
        SwineMovement::create([
            'swine_id' => $swine->id,
            'movement_date' => '2026-09-12 10:00:00',
            'reason' => 'Transfer',
        ]);

        $this->get(route('reports.index', [
            'farm_id' => $farm->id,
            'start_date' => '2026-09-01',
            'end_date' => '2026-09-20',
        ]))
            ->assertOk()
            ->assertViewHas('totalSwine', 1)
            ->assertViewHas('activeSwine', 1)
            ->assertViewHas('totalHealthRecords', 1)
            ->assertViewHas('totalMovements', 1)
            ->assertViewHas('averageWeight', 42.5)
            ->assertSee('North Farm')
            ->assertDontSee('TAG-002');
    }

    public function test_reports_export_downloads_csv(): void
    {
        $this->actingAs($this->userWithReportPermission());
        $farm = Farm::create(['farm_code' => 'F-003', 'name' => 'Export Farm']);
        $swine = $this->createSwine($farm, 'TAG-CSV');
        $swine->update(['name' => 'Private Swine Name']);

        HealthRecord::create([
            'swine_id' => $swine->id,
            'record_date' => '2026-09-10',
            'record_type' => 'Checkup',
            'health_status' => 'healthy',
            'symptoms' => 'Health export marker',
        ]);
        HealthRecord::create([
            'swine_id' => $swine->id,
            'record_date' => '2026-08-10',
            'record_type' => 'Checkup',
            'health_status' => 'healthy',
            'symptoms' => 'Outside selected range',
        ]);
        SwineMovement::create([
            'swine_id' => $swine->id,
            'movement_date' => '2026-09-12 10:00:00',
            'reason' => 'Movement export marker',
        ]);
        WeightRecord::create([
            'swine_id' => $swine->id,
            'record_date' => '2026-09-13',
            'weight' => 45.25,
        ]);

        $exports = [
            'swine' => 'TAG-CSV',
            'health' => 'Health export marker',
            'movements' => 'Movement export marker',
            'weights' => '45.25',
        ];

        foreach ($exports as $type => $expectedContent) {
            $response = $this->get(route('reports.export', [
                'type' => $type,
                'farm_id' => $farm->id,
                'start_date' => '2026-09-01',
                'end_date' => '2026-09-20',
            ]))
                ->assertOk()
                ->assertDownload();

            $content = $response->streamedContent();
            $this->assertStringContainsString($expectedContent, $content);
            $this->assertStringNotContainsString('Private Swine Name', $content);

            if ($type === 'health') {
                $this->assertStringNotContainsString('Outside selected range', $content);
            }
        }
    }

    private function userWithReportPermission(): User
    {
        $role = Role::create(['name' => 'Report Viewer', 'slug' => 'report-viewer']);
        $permission = Permission::create(['name' => 'View Reports', 'slug' => 'view-reports']);
        $role->permissions()->attach($permission);

        return User::factory()->create(['role_id' => $role->id]);
    }

    private function createSwine(Farm $farm, string $tagNumber): Swine
    {
        return Swine::create([
            'farm_id' => $farm->id,
            'tag_number' => $tagNumber,
            'sex' => 'female',
            'status' => 'active',
        ]);
    }
}
