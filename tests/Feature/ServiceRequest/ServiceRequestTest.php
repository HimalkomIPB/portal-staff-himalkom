<?php

namespace Tests\Feature\ServiceRequest;

use App\Models\Department;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ServiceRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Seed departments manually for the test
        $this->deptCreative = Department::create([
            'name' => 'Creative', 
            'slug' => 'creative',
            'abbreviation' => 'Cr',
            'description' => 'Creative Department'
        ]);
        
        $this->deptRnT = Department::create([
            'name' => 'Research and Technology', 
            'slug' => 'research-and-technology',
            'abbreviation' => 'RnT',
            'description' => 'RnT Department'
        ]);
        
        $this->deptOther = Department::create([
            'name' => 'Education', 
            'slug' => 'education',
            'abbreviation' => 'Edu',
            'description' => 'Education Department'
        ]);

        $this->creativeUser = User::factory()->create(['department_id' => $this->deptCreative->id]);
        $this->rntUser = User::factory()->create(['department_id' => $this->deptRnT->id]);
        $this->requesterUser = User::factory()->create(['department_id' => $this->deptOther->id]);
    }

    public function test_user_can_create_service_request()
    {
        $response = $this->actingAs($this->requesterUser)->post(route('dashboard.services.store'), [
            'type' => 'copm',
            'title' => 'Test Poster',
            'description' => 'Tolong buatkan poster acara XYZ',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('service_requests', [
            'requester_id' => $this->requesterUser->id,
            'department_id' => $this->deptOther->id,
            'type' => 'copm',
            'title' => 'Test Poster',
            'status' => 'pending',
        ]);
    }

    public function test_creative_user_can_manage_copm()
    {
        $service = ServiceRequest::create([
            'requester_id' => $this->requesterUser->id,
            'department_id' => $this->deptOther->id,
            'type' => 'copm',
            'title' => 'Test',
            'description' => 'Test desc',
            'status' => 'pending',
        ]);

        // Creative user can see it
        $response = $this->actingAs($this->creativeUser)->get(route('dashboard.services.show', $service));
        $response->assertOk();

        // Creative user can update status
        $response = $this->actingAs($this->creativeUser)->patch(route('dashboard.services.status.update', $service), [
            'status' => 'accepted'
        ]);
        $response->assertSessionHas('success');
        
        $this->assertEquals('accepted', $service->fresh()->status);
    }

    public function test_creative_user_cannot_manage_komnews()
    {
        $service = ServiceRequest::create([
            'requester_id' => $this->requesterUser->id,
            'department_id' => $this->deptOther->id,
            'type' => 'komnews',
            'title' => 'Test',
            'description' => 'Test desc',
            'status' => 'pending',
        ]);

        // Creative user cannot manage komnews
        $response = $this->actingAs($this->creativeUser)->patch(route('dashboard.services.status.update', $service), [
            'status' => 'accepted'
        ]);
        
        $response->assertForbidden();
    }
    
    public function test_rnt_user_can_manage_komnews()
    {
        $service = ServiceRequest::create([
            'requester_id' => $this->requesterUser->id,
            'department_id' => $this->deptOther->id,
            'type' => 'komnews',
            'title' => 'Test',
            'description' => 'Test desc',
            'status' => 'pending',
        ]);

        // RnT user can manage komnews
        $response = $this->actingAs($this->rntUser)->patch(route('dashboard.services.status.update', $service), [
            'status' => 'accepted'
        ]);
        
        $response->assertSessionHas('success');
    }
}
