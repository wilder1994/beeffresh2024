<?php

declare(strict_types=1);

namespace App\Http\Controllers\Notifications;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Repositories\Notifications\NotificationRepository;
use App\Services\Notifications\NotificationPreferenceService;
use App\Support\NotificationActionUrl;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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

        $scope = (string) $request->query('scope', 'unread');

        $items = $scope === 'all'
            ? $this->repository->recentForUser($user, 8)
            : $this->repository->unreadForUser($user);

        return response()->json([
            'unread_count' => $this->repository->unreadCount($user),
            'notifications' => $items->map(fn (Notification $n): array => $this->serializeNotification($n)),
        ]);
    }

    public function history(Request $request): JsonResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $perPage = min(50, max(5, (int) $request->query('per_page', 15)));
        $paginator = $this->repository->paginateForUser($user, $perPage);

        return response()->json([
            'unread_count' => $this->repository->unreadCount($user),
            'notifications' => $paginator->getCollection()
                ->map(fn (Notification $n): array => $this->serializeNotification($n)),
            'meta' => $this->paginationMeta($paginator),
        ]);
    }

    public function markRead(Request $request, Notification $notification): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $notification);

        $this->repository->markAsRead($notification);

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'unread_count' => $this->repository->unreadCount($request->user()),
            ]);
        }

        return back();
    }

    public function markAllRead(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        abort_unless($user !== null, 403);

        $count = $this->repository->markAllAsRead($user);

        if ($request->expectsJson()) {
            return response()->json([
                'ok' => true,
                'marked' => $count,
                'unread_count' => 0,
            ]);
        }

        return redirect()
            ->route('notifications.index')
            ->with('status', 'Notificaciones marcadas como leídas.');
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeNotification(Notification $notification): array
    {
        return [
            'id' => $notification->id,
            'type' => $notification->type->value,
            'type_label' => $notification->type->label(),
            'title' => $notification->title,
            'body' => $notification->body,
            'read' => ! $notification->isUnread(),
            'read_url' => route('notifications.read', $notification),
            'action_url' => NotificationActionUrl::normalize($notification->payload['action_url'] ?? null),
            'created_at' => $notification->created_at?->toIso8601String(),
            'created_human' => $notification->created_at?->diffForHumans(short: true),
        ];
    }

    /**
     * @return array<string, int|bool|null>
     */
    private function paginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
            'has_more' => $paginator->hasMorePages(),
        ];
    }
}
