<?php

namespace App\Http\Controllers;

use App\Models\Visitor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ResidentController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();

        $visitors = Visitor::with('apartment')
            ->when($user->apartment_id, fn ($query) => $query->where('apartment_id', $user->apartment_id))
            ->latest()
            ->get();

        return view('resident-dashboard', [
            'visitors' => $visitors,
            'pendingVisitors' => $visitors->where('status', Visitor::STATUS_PENDING),
            'history' => $visitors->where('status', '!=', Visitor::STATUS_PENDING),
        ]);
    }

    public function visitors()
    {
        $user = auth()->user();

        $visitors = Visitor::with('apartment')
            ->when($user->apartment_id, fn ($query) => $query->where('apartment_id', $user->apartment_id))
            ->latest()
            ->get();

        return response()->json([
            'visitors' => $visitors->map(fn (Visitor $visitor) => $this->visitorPayload($visitor)),
        ]);
    }

    public function approve(Request $request, Visitor $visitor)
    {
        $this->authorizeResidentVisitor($visitor);

        abort_unless($visitor->status === Visitor::STATUS_PENDING, 422);
        abort_if($this->approvalWindowExpired($visitor), 422, 'Approval time expired. Watchman should call the owner.');

        $visitor->update([
            'status' => Visitor::STATUS_APPROVED,
            'resident_id' => auth()->id(),
        ]);

        if ($request->expectsJson()) {
            return $this->visitorResponse($visitor->fresh(['apartment']), 'Visitor approved.');
        }

        return back()->with('success', 'Visitor approved.');
    }

    public function reject(Request $request, Visitor $visitor)
    {
        $this->authorizeResidentVisitor($visitor);

        abort_unless($visitor->status === Visitor::STATUS_PENDING, 422);
        abort_if($this->approvalWindowExpired($visitor), 422, 'Approval time expired. Watchman should call the owner.');

        $visitor->update([
            'status' => Visitor::STATUS_REJECTED,
            'resident_id' => auth()->id(),
        ]);

        if ($request->expectsJson()) {
            return $this->visitorResponse($visitor->fresh(['apartment']), 'Visitor rejected.');
        }

        return back()->with('success', 'Visitor rejected.');
    }

    private function authorizeResidentVisitor(Visitor $visitor): void
    {
        abort_unless(auth()->user()->apartment_id && $visitor->apartment_id === auth()->user()->apartment_id, 403);
    }

    private function approvalWindowExpired(Visitor $visitor): bool
    {
        return $visitor->created_at->copy()->addMinute()->isPast();
    }

    private function visitorResponse(Visitor $visitor, string $message): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'visitor' => $this->visitorPayload($visitor),
        ]);
    }

    private function visitorPayload(Visitor $visitor): array
    {
        return [
            'id' => $visitor->id,
            'name' => $visitor->visitor_name,
            'phone' => $visitor->phone_number,
            'reason' => $visitor->reason,
            'photo_url' => $visitor->photo_path ? asset('storage/'.$visitor->photo_path) : null,
            'status' => $visitor->status,
            'created_at' => $visitor->created_at->toIso8601String(),
            'deadline_at' => $visitor->created_at->copy()->addMinute()->toIso8601String(),
            'entry_time' => $visitor->entry_time?->format('d M, h:i A'),
            'exit_time' => $visitor->exit_time?->format('d M, h:i A'),
        ];
    }
}
