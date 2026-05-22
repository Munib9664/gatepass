<?php

namespace App\Http\Controllers;

use App\Models\Apartment;
use App\Models\User;
use App\Models\Visitor;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WatchmanController extends Controller
{
    public function dashboard()
    {
        return view('watchman-dashboard', [
            'apartments' => Apartment::orderBy('block')->orderBy('number')->get(),
            'visitors' => Visitor::with(['apartment', 'resident'])->latest()->get(),
        ]);
    }

    public function visitors()
    {
        return response()->json([
            'visitors' => Visitor::with(['apartment', 'resident'])->latest()->get()->map(fn (Visitor $visitor) => $this->visitorPayload($visitor)),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'visitor_name' => ['required', 'string', 'max:255'],
            'phone_number' => ['required', 'string', 'max:20'],
            'apartment_block' => ['required', 'string', 'max:50'],
            'apartment_number' => ['required', 'string', 'max:50'],
            'reason' => ['required', 'string', 'max:255'],
            'visitor_photo' => ['nullable', 'image', 'max:10240'],
        ]);
$apartment = Apartment::where('block', strtoupper(trim($request->apartment_block)))
    ->where('number', strtoupper(trim($request->apartment_number)))
    ->first();

if (!$apartment) {

    return redirect()->back()
        ->withInput()
        ->with('error', 'This apartment is not registered in the system.');

}
        // $apartment = Apartment::firstOrCreate([
        //     'block' => strtoupper(trim($validated['apartment_block'])),
        //     'number' => strtoupper(trim($validated['apartment_number'])),
        // ]);

        $resident = User::where('role', 'resident')
            ->where('apartment_id', $apartment->id)
            ->oldest()
            ->first();

        $photoPath = $request->hasFile('visitor_photo')
            ? $request->file('visitor_photo')->store('visitors', 'public')
            : null;

        $visitor = Visitor::create([
            'visitor_name' => $validated['visitor_name'],
            'phone_number' => $validated['phone_number'],
            'apartment_id' => $apartment->id,
            'resident_id' => $resident?->id,
            'reason' => $validated['reason'],
            'photo_path' => $photoPath,
            'status' => Visitor::STATUS_PENDING,
        ]);

        if ($request->expectsJson()) {
            return $this->visitorResponse($visitor->load(['apartment', 'resident']), 'Visitor request sent to resident.');
        }

        return back()->with('success', 'Visitor request sent to resident.');
    }

    public function markEntry(Request $request, Visitor $visitor)
    {
        abort_unless($visitor->status === Visitor::STATUS_APPROVED, 422);

        $visitor->update([
            'entry_time' => $visitor->entry_time ?? now(),
        ]);

        if ($request->expectsJson()) {
            return $this->visitorResponse($visitor->fresh(['apartment', 'resident']), 'Entry time recorded.');
        }

        return back()->with('success', 'Entry time recorded.');
    }

    public function resend(Request $request, Visitor $visitor)
    {
        abort_unless($visitor->status === Visitor::STATUS_PENDING, 422);
        abort_unless($visitor->created_at->copy()->addMinute()->isPast(), 422);

        $visitor->forceFill([
            'created_at' => now(),
            'updated_at' => now(),
        ])->save();

        if ($request->expectsJson()) {
            return $this->visitorResponse($visitor->fresh(['apartment', 'resident']), 'Request sent again to the resident.');
        }

        return back()->with('success', 'Request sent again to the resident.');
    }

    public function approveByWatchman(Request $request, Visitor $visitor)
    {
        abort_unless($visitor->status === Visitor::STATUS_PENDING, 422);
        abort_unless($visitor->created_at->copy()->addMinute()->isPast(), 422, 'Approval window has not expired yet.');

        $visitor->update([
            'status' => Visitor::STATUS_APPROVED,
        ]);

        if ($request->expectsJson()) {
            return $this->visitorResponse($visitor->fresh(['apartment', 'resident']), 'Entry granted by watchman after owner confirmation.');
        }

        return back()->with('success', 'Entry granted by watchman after owner confirmation.');
    }

    public function rejectByWatchman(Request $request, Visitor $visitor)
    {
        abort_unless($visitor->status === Visitor::STATUS_PENDING, 422);
        abort_unless($visitor->created_at->copy()->addMinute()->isPast(), 422, 'Approval window has not expired yet.');

        $visitor->update([
            'status' => Visitor::STATUS_REJECTED,
        ]);

        if ($request->expectsJson()) {
            return $this->visitorResponse($visitor->fresh(['apartment', 'resident']), 'Visitor rejected by watchman.');
        }

        return back()->with('success', 'Visitor rejected by watchman.');
    }

    public function markExit(Request $request, Visitor $visitor)
    {
        abort_unless(
            $visitor->status === Visitor::STATUS_APPROVED && $visitor->entry_time && ! $visitor->exit_time,
            422
        );

        $visitor->update([
            'status' => Visitor::STATUS_EXITED,
            'exit_time' => now(),
        ]);

        if ($request->expectsJson()) {
            return $this->visitorResponse($visitor->fresh(['apartment', 'resident']), 'Visitor exit marked.');
        }

        return back()->with('success', 'Visitor exit marked.');
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
            'apartment' => $visitor->apartment->label,
            'reason' => $visitor->reason,
            'resident_name' => $visitor->resident?->name,
            'resident_phone' => $visitor->resident?->phone_number,
            'photo_url' => $visitor->photo_path ? asset('storage/'.$visitor->photo_path) : null,
            'status' => $visitor->status,
            'created_at' => $visitor->created_at->toIso8601String(),
            'deadline_at' => $visitor->created_at->copy()->addMinute()->toIso8601String(),
            'entry_time' => $visitor->entry_time?->format('d M, h:i A'),
            'exit_time' => $visitor->exit_time?->format('d M, h:i A'),
        ];
    }
}
