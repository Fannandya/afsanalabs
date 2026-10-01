<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateMessageStatusRequest;
use App\Models\Consultation;
use App\Models\ContactMessage;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    protected function paginated(string $model, Request $request): array
    {
        $limit = min(max((int) $request->integer('limit', 20), 1), 100);
        $paginator = $model::query()->orderByDesc('created_at')->orderByDesc('id')->paginate($limit);

        return ['data' => $paginator->items(), 'page' => $paginator->currentPage(), 'limit' => $paginator->perPage()];
    }

    public function contactIndex(Request $request)
    {
        return response()->json($this->paginated(ContactMessage::class, $request));
    }

    public function consultationIndex(Request $request)
    {
        return response()->json($this->paginated(Consultation::class, $request));
    }

    public function contactStatus(UpdateMessageStatusRequest $request, string $id)
    {
        $row = ContactMessage::findOrFail($id);
        $row->update(['status' => $request->validated()['status']]);

        return response()->json(['ok' => true]);
    }

    public function consultationStatus(UpdateMessageStatusRequest $request, string $id)
    {
        $row = Consultation::findOrFail($id);
        $row->update(['status' => $request->validated()['status']]);

        return response()->json(['ok' => true]);
    }
}
