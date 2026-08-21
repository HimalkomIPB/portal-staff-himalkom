<?php

use App\Models\User;
use App\Models\Department;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('dashboard utama merender tanpa duplikasi', function () {
    $dept = Department::create(['name' => 'Test Department', 'slug' => 'test-department', 'description' => 'Test', 'abbreviation' => 'TEST']);
    $user = User::factory()->create(['department_id' => $dept->id]);

    $response = $this->actingAs($user)->get('/dashboard/' . $dept->slug);
    $response->assertOk();

    $html = $response->getContent();
    expect(substr_count($html, '<!DOCTYPE html>'))->toBe(1)
        ->and(substr_count($html, '<aside'))->toBe(1);
});

test('halaman work programs merender tanpa duplikasi', function () {
    $dept = Department::create(['name' => 'Test Department', 'slug' => 'test-department', 'description' => 'Test', 'abbreviation' => 'TEST']);
    $user = User::factory()->create(['department_id' => $dept->id]);

    Permission::firstOrCreate(['name' => 'work-program.view', 'guard_name' => 'web']);
    $user->givePermissionTo('work-program.view');

    $response = $this->actingAs($user)->get('/dashboard/' . $dept->slug . '/workprograms');
    $response->assertOk();

    $html = $response->getContent();
    expect(substr_count($html, '<!DOCTYPE html>'))->toBe(1)
        ->and(substr_count($html, '<aside'))->toBe(1);
});

test('halaman archives merender tanpa duplikasi', function () {
    $user = User::factory()->create();
    Permission::firstOrCreate(['name' => 'archive.view', 'guard_name' => 'web']);
    $user->givePermissionTo('archive.view');

    $response = $this->actingAs($user)->get('/dashboard/archive/departments');
    $response->assertOk();

    $html = $response->getContent();
    expect(substr_count($html, '<!DOCTYPE html>'))->toBe(1)
        ->and(substr_count($html, '<aside'))->toBe(1);
});

test('halaman mod-view merender tanpa duplikasi', function () {
    $user = User::factory()->create();
    Permission::firstOrCreate(['name' => 'archive.view-all', 'guard_name' => 'web']);
    $user->givePermissionTo('archive.view-all');

    $response = $this->actingAs($user)->get('/dashboard/mod-view/departments');
    $response->assertOk();

    $html = $response->getContent();
    expect(substr_count($html, '<!DOCTYPE html>'))->toBe(1)
        ->and(substr_count($html, '<aside'))->toBe(1);
});

test('halaman notifications merender tanpa duplikasi', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/dashboard/notifications');
    $response->assertOk();

    $html = $response->getContent();
    expect(substr_count($html, '<!DOCTYPE html>'))->toBe(1)
        ->and(substr_count($html, '<aside'))->toBe(1);
});

test('halaman profile merender tanpa duplikasi', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/profile');
    $response->assertOk();

    $html = $response->getContent();
    expect(substr_count($html, '<!DOCTYPE html>'))->toBe(1)
        ->and(substr_count($html, '<aside'))->toBe(1);
});

test('halaman performance merender tanpa duplikasi', function () {
    $user = User::factory()->create();
    Permission::firstOrCreate(['name' => 'performance.view', 'guard_name' => 'web']);
    $user->givePermissionTo('performance.view');

    $response = $this->actingAs($user)->get('/dashboard/performance');
    $response->assertOk();

    $html = $response->getContent();
    expect(substr_count($html, '<!DOCTYPE html>'))->toBe(1)
        ->and(substr_count($html, '<aside'))->toBe(1);
});
