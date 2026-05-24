<?php

declare(strict_types=1);

namespace App\Http\Controllers\Notifications;

use App\Enums\Notifications\NotificationChannel;
use App\Http\Controllers\Controller;
use App\Services\Notifications\NotificationPreferenceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationPreferenceController extends Controller
{
    public function __construct(
        private readonly NotificationPreferenceService $preferences,
    ) {}

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $validated = $request->validate([
            'internal_enabled' => ['nullable', 'boolean'],
            'email_enabled' => ['nullable', 'boolean'],
            'push_enabled' => ['nullable', 'boolean'],
        ]);

        if (array_key_exists('internal_enabled', $validated)) {
            $this->preferences->setPreference(
                $user,
                NotificationChannel::Internal,
                (bool) $validated['internal_enabled'],
            );
        }

        if (array_key_exists('email_enabled', $validated)) {
            $this->preferences->setPreference(
                $user,
                NotificationChannel::Email,
                (bool) $validated['email_enabled'],
            );
        }

        if (array_key_exists('push_enabled', $validated)) {
            $this->preferences->setPreference(
                $user,
                NotificationChannel::Push,
                (bool) $validated['push_enabled'],
            );
        }

        return back()->with('status', 'Preferencias de notificación actualizadas.');
    }
}
