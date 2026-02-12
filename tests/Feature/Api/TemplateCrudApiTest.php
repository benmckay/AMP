<?php

namespace Tests\Feature\Api;

use App\Models\AccessRequest;
use App\Models\Department;
use App\Models\Template;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TemplateCrudApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        Role::findOrCreate('ict_admin', 'web');
        $this->user = User::factory()->create();
        $this->user->assignRole('ict_admin');
        $this->actingAs($this->user, 'sanctum');
    }

    public function test_index_returns_only_active_templates(): void
    {
        $department = $this->createDepartment();
        $activeTemplate = $this->createTemplate($department, ['is_active' => true]);
        $this->createTemplate($department, ['is_active' => false]);

        $response = $this->getJson('/api/templates');

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.id', $activeTemplate->id);
    }

    public function test_store_creates_template_and_returns_201(): void
    {
        $department = $this->createDepartment();

        $payload = [
            'mnemonic' => 'DOC-NEW',
            'name' => 'Doctor Access',
            'department_id' => $department->id,
            'category' => 'clinical',
            'description' => 'Clinical access profile',
            'ehr_access_level' => 'advanced',
            'ehr_module_access' => ['orders', 'results'],
            'ehr_permissions' => ['view', 'edit'],
            'requires_cos_approval' => true,
        ];

        $response = $this->postJson('/api/templates', $payload);

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.mnemonic', 'DOC-NEW')
            ->assertJsonPath('data.department_id', $department->id);

        $this->assertDatabaseHas('templates', [
            'mnemonic' => 'DOC-NEW',
            'name' => 'Doctor Access',
            'department_id' => $department->id,
            'created_by' => $this->user->id,
        ]);
    }

    public function test_store_returns_422_for_invalid_payload(): void
    {
        $response = $this->postJson('/api/templates', []);

        $response
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonValidationErrors(['mnemonic', 'name', 'department_id']);
    }

    public function test_update_changes_fields_and_increments_version(): void
    {
        $department = $this->createDepartment();
        $template = $this->createTemplate($department, ['version' => 1]);

        $response = $this->putJson("/api/templates/{$template->id}", [
            'name' => 'Updated Template Name',
            'is_active' => false,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Updated Template Name')
            ->assertJsonPath('data.is_active', false);

        $template->refresh();

        $this->assertSame(2, $template->version);
        $this->assertSame('Updated Template Name', $template->name);
        $this->assertFalse($template->is_active);
    }

    public function test_destroy_soft_deletes_template_when_not_in_use(): void
    {
        $department = $this->createDepartment();
        $template = $this->createTemplate($department);

        $response = $this->deleteJson("/api/templates/{$template->id}");

        $response
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Template deleted successfully');

        $this->assertSoftDeleted('templates', ['id' => $template->id]);
    }

    public function test_destroy_returns_422_when_template_is_in_use(): void
    {
        $department = $this->createDepartment();
        $template = $this->createTemplate($department);

        AccessRequest::create([
            'requester_id' => $this->user->id,
            'requester_department_id' => $department->id,
            'template_id' => $template->id,
            'department_id' => $department->id,
            'system_id' => 1,
            'request_type' => 'new_access',
            'status' => 'pending',
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'justification' => 'Need access for onboarding',
            'priority' => 'normal',
        ]);

        $response = $this->deleteJson("/api/templates/{$template->id}");

        $response
            ->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Cannot delete template that is in use by access requests');
    }

    private function createDepartment(array $overrides = []): Department
    {
        return Department::create(array_merge([
            'code' => 'DPT-' . fake()->unique()->numerify('###'),
            'name' => fake()->company(),
            'description' => fake()->sentence(),
            'is_active' => true,
        ], $overrides));
    }

    private function createTemplate(Department $department, array $overrides = []): Template
    {
        return Template::create(array_merge([
            'mnemonic' => 'TMP-' . fake()->unique()->numerify('####'),
            'name' => fake()->jobTitle(),
            'department_id' => $department->id,
            'category' => 'general',
            'description' => fake()->sentence(),
            'ehr_access_level' => 'standard',
            'ehr_module_access' => [],
            'ehr_permissions' => [],
            'is_active' => true,
            'requires_cos_approval' => false,
            'created_by' => $this->user->id,
            'version' => 1,
        ], $overrides));
    }
}
