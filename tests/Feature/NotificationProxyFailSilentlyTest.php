<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

test('notification proxy fails silently when service is unavailable', function () {
    // Simulate service unavailable
    Http::fake([
        '*' => Http::response(null, 503)
    ]);

    $admin = User::factory()->create(['id_rol' => 1]);

    // Attempt to send a notification (should not throw exception)
    $response = $this->actingAs($admin)->postJson('/api/notifications/send', [
        'to' => '59178123456',
        'message' => 'Test message'
    ]);

    // Should return 503 but with graceful message
    $response->assertStatus(503);
    $response->assertJson([
        'success' => false,
        'error' => 'notifications_unavailable',
        'message' => 'Servicio de notificaciones no disponible temporalmente, pero el proceso continuó'
    ]);
});

test('notification proxy handles connection timeout gracefully', function () {
    // Simulate connection timeout
    Http::fake(function () {
        throw new \Illuminate\Http\Client\ConnectionException('Connection timeout');
    });

    $admin = User::factory()->create(['id_rol' => 1]);

    // Should not throw exception, should log error
    Log::shouldReceive('error')
        ->once()
        ->withArgs(function ($message, $context) {
            return str_contains($message, 'Connection failed');
        });

    $response = $this->actingAs($admin)->postJson('/api/notifications/send', [
        'to' => '59178123456',
        'message' => 'Test message'
    ]);

    // Should return graceful error response
    $response->assertStatus(503);
    $response->assertJsonStructure([
        'success',
        'error',
        'message',
        'code',
        '_debug' => ['method', 'url', 'reason', 'timestamp']
    ]);
});

test('notification proxy succeeds when service is available', function () {
    // Simulate successful response
    Http::fake([
        '*' => Http::response([
            'success' => true,
            'messageId' => 'msg_123'
        ], 200)
    ]);

    $admin = User::factory()->create(['id_rol' => 1]);

    $response = $this->actingAs($admin)->postJson('/api/notifications/send', [
        'to' => '59178123456',
        'message' => 'Test message'
    ]);

    $response->assertOk();
    $response->assertJson([
        'success' => true,
        'messageId' => 'msg_123'
    ]);
});

test('notification proxy uses 2 second timeout', function () {
    $startTime = microtime(true);

    // Simulate slow response (will timeout)
    Http::fake(function () {
        sleep(3); // Longer than 2 second timeout
        return Http::response(['success' => true], 200);
    });

    $admin = User::factory()->create(['id_rol' => 1]);

    $response = $this->actingAs($admin)->postJson('/api/notifications/send', [
        'to' => '59178123456',
        'message' => 'Test message'
    ]);

    $endTime = microtime(true);
    $duration = $endTime - $startTime;

    // Should timeout in approximately 2 seconds (with retry = ~2.1s max)
    expect($duration)->toBeLessThan(3);
    
    // Should return graceful error
    $response->assertStatus(503);
});
