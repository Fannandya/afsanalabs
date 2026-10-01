<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateNotificationPreferencesRequest;
use App\Models\Notification;
use App\Models\NotificationPreference;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Notification::where('user_id', $user->id)->orderByDesc('created_at')->orderByDesc('id');
        if ($request->boolean('unread')) {
            $query->where('is_read', false);
        }

        return response()->json(['data' => $query->limit(100)->get()]);
    }

    public function markRead(Request $request, string $id)
    {
        $user = $request->user();
        $row = Notification::where('user_id', $user->id)->findOrFail($id);
        $row->update(['is_read' => true]);

        return response()->json(['ok' => true]);
    }

    public function showPreferences(Request $request)
    {
        $user = $request->user();

        return response()->json(['data' => NotificationPreference::where('user_id', $user->id)->first()]);
    }

    public function updatePreferences(UpdateNotificationPreferencesRequest $request)
    {
        $user = $request->user();
        $pref = NotificationPreference::firstOrNew(['user_id' => $user->id]);
        $pref->fill($request->validated());
        if (! $pref->exists) {
            $pref->notify_new_order ??= true;
            $pref->notify_contact_message ??= true;
            $pref->notify_consultation ??= true;
            $pref->notify_mockup_request ??= true;
        }
        $pref->save();

        return response()->json(['data' => $pref->fresh()]);
    }
}
