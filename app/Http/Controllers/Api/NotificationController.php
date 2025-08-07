<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * @OA\Tag(
 *     name="Notifications",
 *     description="API endpoints pour la gestion des notifications"
 * )
 */
class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->middleware('auth:sanctum');
        $this->notificationService = $notificationService;
    }

    /**
     * @OA\Get(
     *     path="/api/notifications",
     *     tags={"Notifications"},
     *     summary="Liste des notifications utilisateur",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Nombre de notifications",
     *         @OA\Schema(type="integer", default=20)
     *     ),
     *     @OA\Parameter(
     *         name="unread_only",
     *         in="query",
     *         description="Notifications non lues seulement",
     *         @OA\Schema(type="boolean", default=false)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Liste des notifications",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="notifications", type="array", @OA\Items(type="object")),
     *                 @OA\Property(property="stats", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $limit = min($request->get('limit', 20), 50);
        $unreadOnly = $request->boolean('unread_only');

        $query = $user->notifications()
            ->with('related')
            ->orderBy('created_at', 'desc');

        if ($unreadOnly) {
            $query->unread();
        }

        $notifications = $query->limit($limit)->get();
        $stats = $this->notificationService->getNotificationStats($user);

        return $this->sendResponse([
            'notifications' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'data' => $notification->data,
                    'is_read' => $notification->is_read,
                    'created_at' => $notification->created_at,
                    'read_at' => $notification->read_at,
                    'related_type' => $notification->related_type,
                    'related_id' => $notification->related_id,
                ];
            }),
            'stats' => $stats
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/notifications/{id}/read",
     *     tags={"Notifications"},
     *     summary="Marquer une notification comme lue",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la notification",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Notification marquée comme lue")
     * )
     */
    public function markAsRead($id)
    {
        $user = auth()->user();
        
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        return $this->sendResponse([
            'message' => 'Notification marquée comme lue',
            'notification' => $notification
        ]);
    }

    /**
     * @OA\Post(
     *     path="/api/notifications/mark-all-read",
     *     tags={"Notifications"},
     *     summary="Marquer toutes les notifications comme lues",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Toutes les notifications marquées comme lues")
     * )
     */
    public function markAllAsRead()
    {
        $user = auth()->user();
        $count = $this->notificationService->markAllAsRead($user);

        return $this->sendResponse([
            'message' => "Toutes les notifications ont été marquées comme lues",
            'count' => $count
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/api/notifications/{id}",
     *     tags={"Notifications"},
     *     summary="Supprimer une notification",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID de la notification",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Notification supprimée")
     * )
     */
    public function destroy($id)
    {
        $user = auth()->user();
        
        $notification = $user->notifications()->findOrFail($id);
        $notification->delete();

        return $this->sendResponse([
            'message' => 'Notification supprimée avec succès'
        ]);
    }

    /**
     * @OA\Get(
     *     path="/api/notifications/stats",
     *     tags={"Notifications"},
     *     summary="Statistiques des notifications",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(response=200, description="Statistiques des notifications")
     * )
     */
    public function stats()
    {
        $user = auth()->user();
        $stats = $this->notificationService->getNotificationStats($user);

        return $this->sendResponse($stats);
    }

    /**
     * @OA\Get(
     *     path="/api/notifications/unread-count",
     *     tags={"Notifications"},
     *     summary="Nombre de notifications non lues",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Nombre de notifications non lues",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="unread_count", type="integer")
     *             )
     *         )
     *     )
     * )
     */
    public function unreadCount()
    {
        $user = auth()->user();
        $count = $user->notifications()->unread()->count();

        return $this->sendResponse([
            'unread_count' => $count
        ]);
    }
}