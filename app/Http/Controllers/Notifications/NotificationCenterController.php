<?php

declare(strict_types=1);

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Repositories\Notifications\NotificationRepository;
use App\Services\Notifications\NotificationPreferenceService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationCenterController extends Controller
{
    public function __construct(
        private readonly NotificationRepository $repository,
        private readonly NotificationPreferenceService $preferences,
    ) {}

    public function index(Request $request): View
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        return view('notifications.index', [
            'notifications' => $this->repository->paginateForUser($user),
            'preferences' => $this->preferences->listForUser($user),
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $recent = $this->repository->recentForUser($user, 8);

        return response()->json([
            'unread_count' => $this->repository->unreadCount($user),
            'notifications' => $recent->map(fn (Notification $n): array => [
                'id' => $n->id,
                'type' => $n->type->value,
                'type_label' => $n->type->label(),
                'title' => $n->title,
                'body' => $n->body,
                'read' => ! $n->isUnread(),
                'action_url' => $n->payload['action_url'] ?? null,
                'created_at' => $n->created_at?->toIso8601String(),
                'created_human' => $n->created_at?->diffForHumans(short: true),
            ]),
        ]);
    }

    public function markRead(Request $request, Notification $notification): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $notification);

        $this->repository->markAsRead($notification);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true]);
        }

        return back();
    }

    public function markAllRead(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $count = $this->repository->markAllAsRead($user);

        if ($request->wantsJson()) {
            return response()->json(['ok' => true, 'marked' => $count]);
        }

        return back()->with('status', 'Notificaciones marcadas como leídas.');
    }
}
