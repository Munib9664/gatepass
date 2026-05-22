@php
    use App\Models\Visitor;

    $pendingVisitors = $visitors->where('status', Visitor::STATUS_PENDING);
    $approvedVisitors = $visitors->where('status', Visitor::STATUS_APPROVED);
    $rejectedVisitors = $visitors->where('status', Visitor::STATUS_REJECTED);
    $exitedVisitors = $visitors->where('status', Visitor::STATUS_EXITED);
    $now = now();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Watchman Dashboard</h2>
                <p class="text-sm text-gray-500">Manage visitor requests, record entry/exit, and monitor visitor history.</p>
            </div>
            <span class="inline-flex w-fit rounded-full bg-sky-50 px-3 py-1 text-xs font-semibold uppercase text-sky-700 ring-1 ring-sky-200">Watchman</span>
        </div>
    </x-slot>

    <div class="bg-gray-50 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <!-- <section class="grid gap-4 md:grid-cols-4">
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-medium text-gray-500">Pending Requests</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $pendingVisitors->count() }}</div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-medium text-gray-500">Approved</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $approvedVisitors->count() }}</div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-medium text-gray-500">Rejected</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $rejectedVisitors->count() }}</div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-medium text-gray-500">Exited</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $exitedVisitors->count() }}</div>
                </div>
            </section> -->

            <section class="grid gap-6 xl:grid-cols-[1.3fr_0.7fr]">
                <div class="space-y-6">
                    <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h3 class="text-lg font-semibold text-gray-900">Pending Visitor Requests</h3>
                            <p class="mt-0.5 text-sm text-gray-500">Review requests and take action when the approval window expires.</p>
                        </div>
                        <div class="divide-y divide-gray-200">
                            @forelse ($pendingVisitors as $visitor)
                                @php
                                    $expired = $visitor->created_at->copy()->addMinute()->isPast();
                                @endphp
                                <article class="rounded-none bg-white px-6 py-6 last:rounded-b-lg">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                                        <div>
                                            <h4 class="text-lg font-semibold text-gray-900">{{ $visitor->visitor_name }}</h4>
                                            <p class="mt-1 text-sm text-gray-500">Apartment: {{ $visitor->apartment->label ?? 'Unknown' }}</p>
                                            <p class="text-sm text-gray-500">Phone: {{ $visitor->phone_number }}</p>
                                            <p class="mt-3 rounded-lg border px-3 py-2 text-sm {{ $expired ? 'border-red-200 bg-red-50 text-red-700' : 'border-amber-200 bg-amber-50 text-amber-800' }}">
                                                {{ $expired ? 'Approval window expired. You may approve, reject, or resend.' : 'Waiting for resident approval. Approval window active for 1 minute.' }}
                                            </p>
                                            <p class="mt-3 text-sm text-gray-600">Reason: {{ $visitor->reason }}</p>
                                            @if ($visitor->resident)

    <div class="mt-3 rounded-2xl border border-slate-200 bg-slate-50 p-4">

        <p class="text-sm font-semibold text-slate-900">
            Resident Details
        </p>

        <div class="mt-2 space-y-1 text-sm text-slate-600">

            <p>
                Name:
                <span class="font-medium">
                    {{ $visitor->resident->name }}
                </span>
            </p>

            <p>
                Phone:
                <span class="font-medium">
                    {{ $visitor->resident->phone_number ?? 'Not Available' }}
                </span>
            </p>

        </div>

        @if ($expired && $visitor->resident->phone_number)

            <a href="tel:{{ $visitor->resident->phone_number }}"
                class="mt-4 inline-flex w-full items-center justify-center rounded-xl bg-sky-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-sky-700">

                📞 Call Resident

            </a>

        @endif

    </div>

@endif
                                        </div>

                                        <div class="grid gap-2 sm:grid-cols-2">
                                            <form method="POST" action="{{ route('watchman.visitors.resend', $visitor) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-md border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50 {{ $expired ? '' : 'opacity-50 pointer-events-none' }}">
                                                    Resend request
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('watchman.visitors.approve', $visitor) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 {{ $expired ? '' : 'opacity-50 pointer-events-none' }}">
                                                    Approve visitor
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('watchman.visitors.reject', $visitor) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="inline-flex w-full items-center justify-center rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 {{ $expired ? '' : 'opacity-50 pointer-events-none' }}">
                                                    Reject visitor
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </article>
                            @empty
                                <div class="px-6 py-10 text-center text-sm text-gray-500">No pending visitor requests.</div>
                            @endforelse
                        </div>
                    </section>

                    <!-- <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h3 class="text-lg font-semibold text-gray-900">Visitor History</h3>
                            <p class="mt-0.5 text-sm text-gray-500">Recent visitor activity for your community.</p>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                                    <tr>
                                        <th class="px-6 py-3">Visitor</th>
                                        <th class="px-6 py-3">Apartment</th>
                                        <th class="px-6 py-3">Status</th>
                                        <th class="px-6 py-3">Entry</th>
                                        <th class="px-6 py-3">Exit</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    @forelse ($visitors as $visitor)
                                        <tr>
                                            <td class="px-6 py-4 font-medium text-gray-900">{{ $visitor->visitor_name }}</td>
                                            <td class="px-6 py-4 text-gray-600">{{ $visitor->apartment->label ?? 'Unknown' }}</td>
                                            <td class="px-6 py-4 text-gray-600">{{ ucfirst($visitor->status) }}</td>
                                            <td class="px-6 py-4 text-gray-600">{{ $visitor->entry_time?->format('d M, h:i A') ?? '-' }}</td>
                                            <td class="px-6 py-4 text-gray-600">{{ $visitor->exit_time?->format('d M, h:i A') ?? '-' }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-6 py-8 text-center text-gray-500">No visitor history yet.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section> -->
                </div>

                <section class="rounded-3xl border border-gray-200 bg-white p-6 shadow-xl ring-1 ring-slate-100">
                    <div class="mb-6">
                        <h3 class="text-xl font-semibold text-gray-900">Register New Visitor</h3>
                        <p class="mt-1 text-sm text-gray-500">Create a new notification for a resident and start the approval flow.</p>
                    </div>

                    <form method="POST" action="{{ route('watchman.visitors.store') }}" enctype="multipart/form-data" class="space-y-5">
                        @csrf

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Visitor name</label>
                            <input name="visitor_name" value="{{ old('visitor_name') }}" class="mt-2 block w-full rounded-2xl border border-gray-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-200" required>
                            @error('visitor_name')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone number</label>
                            <input name="phone_number" value="{{ old('phone_number') }}" class="mt-2 block w-full rounded-2xl border border-gray-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-200" required>
                            @error('phone_number')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Apartment block</label>
                                <input name="apartment_block" value="{{ old('apartment_block') }}" class="mt-2 block w-full rounded-2xl border border-gray-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-200" required>
                                @error('apartment_block')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Apartment number</label>
                                <input name="apartment_number" value="{{ old('apartment_number') }}" class="mt-2 block w-full rounded-2xl border border-gray-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-200" required>
                                @error('apartment_number')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Reason for visit</label>
                            <textarea name="reason" rows="4" class="mt-2 block w-full rounded-2xl border border-gray-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-200" required>{{ old('reason') }}</textarea>
                            @error('reason')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Visitor photo (optional)</label>
                            <input type="file" name="visitor_photo" accept="image/*" class="mt-2 block w-full rounded-2xl border border-gray-300 bg-slate-50 px-4 py-3 text-sm text-slate-900 shadow-sm transition focus:border-sky-500 focus:bg-white focus:outline-none focus:ring-2 focus:ring-sky-200">
                            @error('visitor_photo')<p class="mt-2 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <div class="pt-1">
                            <button type="submit" class="inline-flex w-full items-center justify-center rounded-2xl bg-sky-700 px-5 py-3 text-sm font-semibold text-white shadow-lg shadow-sky-200/30 transition duration-200 hover:bg-sky-800 focus:outline-none focus:ring-2 focus:ring-sky-500 focus:ring-offset-2 focus:ring-offset-white" style="color:black">
                                Send visitor request
                            </button>
                        </div>
                    </form>

                    <div class="mt-6 rounded-3xl border border-slate-200 bg-slate-50 p-5 text-sm text-slate-700">
                        <p class="font-semibold text-slate-900">How it works</p>
                        <ul class="mt-4 space-y-3 pl-5 text-slate-600">
                            <li class="list-disc">Register a visitor and notify the resident.</li>
                            <li class="list-disc">If the resident does not approve within 1 minute, you can approve or reject from this dashboard.</li>
                            <li class="list-disc">After approval, remember to mark entry and exit for the visitor.</li>
                        </ul>
                    </div>
                </section>
            </section>
        </div>
    </div>
</x-app-layout>
