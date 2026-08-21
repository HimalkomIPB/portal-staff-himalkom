<?php

/**
 * Properti 5: Indikator Notifikasi Muncul saat Ada Notifikasi Belum Dibaca
 *
 * Memvalidasi: Persyaratan 2.5, 6.4
 *
 * Menggunakan dataset dengan $unreadCount: 0, 1, 5, 99
 * - unreadCount > 0 → output HARUS mengandung `bg-red-500`
 * - unreadCount == 0 → output TIDAK BOLEH mengandung `bg-red-500`
 */

use App\Models\User;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Str;

/**
 * Helper: create $count unread (read_at = null) database notifications for $user.
 */
function createUnreadNotifications(User $user, int $count): void
{
    for ($i = 0; $i < $count; $i++) {
        DatabaseNotification::create([
            'id' => Str::uuid()->toString(),
            'type' => 'App\\Notifications\\TestNotification',
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
            'data' => json_encode(['message' => 'Test notification '.$i]),
            'read_at' => null,
        ]);
    }
}

// ---------------------------------------------------------------------------
// Dataset: [unreadCount, shouldContain]
// ---------------------------------------------------------------------------
dataset('unread counts', [
    'zero unread'         => [0,  false],
    'one unread'          => [1,  true],
    'five unread'         => [5,  true],
    'ninety-nine unread'  => [99, true],
]);

// ---------------------------------------------------------------------------
// Properti 5 — indikator notifikasi merah (sidebar + header bell icon)
// ---------------------------------------------------------------------------

it(
    'shows bg-red-500 indicator when unreadCount > 0, hides it when unreadCount == 0',
    function (int $unreadCount, bool $shouldContain) {
        // Arrange: buat user dan sejumlah notifikasi belum dibaca
        $user = User::factory()->create(['name' => 'Test User']);
        createUnreadNotifications($user, $unreadCount);

        // Act: render sidebar-layout sebagai authenticated user
        $response = $this
            ->actingAs($user)
            ->get(route('profile.edit')); // route yang menggunakan x-sidebar-layout

        $response->assertOk();

        // Assert
        if ($shouldContain) {
            $response->assertSee('bg-red-500', false);
        } else {
            $response->assertDontSee('bg-red-500', false);
        }
    }
)->with('unread counts');
