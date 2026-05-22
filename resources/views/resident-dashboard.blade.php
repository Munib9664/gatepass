@php
    $approvedCount = $visitors->where('status', 'approved')->count();
    $exitedCount = $visitors->where('status', 'exited')->count();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Resident Dashboard</h2>
                <p class="text-sm text-gray-500">Apartment {{ auth()->user()->apartment?->label ?? 'not assigned' }}</p>
            </div>
            <span class="inline-flex w-fit rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold uppercase text-emerald-700 ring-1 ring-emerald-200">Resident</span>
        </div>
    </x-slot>

    <div class="bg-gray-50 py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
            <div id="toast" class="hidden rounded-lg border px-4 py-3 text-sm font-semibold"></div>

            @unless (auth()->user()->apartment_id)
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-900">
                    Your account is not linked to an apartment, so approvals cannot appear here yet.
                </div>
            @endunless

            <section class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-medium text-gray-500">Pending Requests</div>
                    <div id="pending-count" class="mt-2 text-3xl font-semibold text-gray-950">{{ $pendingVisitors->count() }}</div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-medium text-gray-500">Approved</div>
                    <div id="approved-count" class="mt-2 text-3xl font-semibold text-gray-950">{{ $approvedCount }}</div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-medium text-gray-500">Exited</div>
                    <div id="exited-count" class="mt-2 text-3xl font-semibold text-gray-950">{{ $exitedCount }}</div>
                </div>
            </section>

            <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-950">Approve Visitors</h3>
                        <p class="text-sm text-gray-500">New requests for your apartment appear here.</p>
                    </div>
                </div>

                <div id="pending-list" class="grid gap-4 p-6 md:grid-cols-2 xl:grid-cols-3">
                    @forelse ($pendingVisitors as $visitor)
                        @php
                            $deadline = $visitor->created_at->copy()->addMinute();
                            $expired = $deadline->isPast();
                        @endphp
                        <article id="visitor-card-{{ $visitor->id }}" data-deadline="{{ $deadline->toIso8601String() }}" class="rounded-lg border border-gray-200 bg-white p-4 shadow-sm transition hover:border-gray-300">
                            @if ($visitor->photo_path)
                                <img src="{{ asset('storage/'.$visitor->photo_path) }}" alt="{{ $visitor->visitor_name }}" class="h-44 w-full rounded-md object-cover">
                            @else
                                <div class="flex h-44 w-full items-center justify-center rounded-md bg-gray-100 text-sm font-semibold text-gray-500">No Photo</div>
                            @endif

                            <div class="mt-4">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <h4 class="font-semibold text-gray-950">{{ $visitor->visitor_name }}</h4>
                                        <p class="text-sm text-gray-500">{{ $visitor->phone_number }}</p>
                                    </div>
                                    <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Pending</span>
                                </div>
                                <div class="mt-3 rounded-md border px-3 py-2 text-sm {{ $expired ? 'border-red-200 bg-red-50 text-red-700' : 'border-amber-200 bg-amber-50 text-amber-800' }}">
                                    <span class="font-semibold">Approval window:</span>
                                    <span data-countdown>{{ $expired ? 'Expired' : '01:00' }}</span>
                                </div>
                                <p class="mt-3 rounded-md bg-gray-50 px-3 py-2 text-sm text-gray-700">{{ $visitor->reason }}</p>
                            </div>

                            <div class="mt-4 grid grid-cols-2 gap-2">
                                <form method="POST" action="{{ route('resident.visitors.approve', $visitor) }}" data-ajax-action="resident" data-status="approved">
                                    @csrf
                                    @method('PATCH')
                                    <button @disabled($expired) class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-70">
                                        <span class="loader hidden h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                                        <span class="label">Approve</span>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('resident.visitors.reject', $visitor) }}" data-ajax-action="resident" data-status="rejected">
                                    @csrf
                                    @method('PATCH')
                                    <button @disabled($expired) class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-70">
                                        <span class="loader hidden h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                                        <span class="label">Reject</span>
                                    </button>
                                </form>
                            </div>
                            <p data-expired-note class="{{ $expired ? '' : 'hidden' }} mt-3 text-xs font-semibold text-red-600">Time expired. Watchman will call the apartment owner.</p>
                        </article>
                    @empty
                        <div id="empty-pending" class="col-span-full rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-10 text-center text-sm text-gray-500">No pending visitor requests.</div>
                    @endforelse
                </div>
            </section>

            <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-950">Visitor History</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Visitor</th>
                                <th class="px-6 py-3">Reason</th>
                                <th class="px-6 py-3">Status</th>
                                <th class="px-6 py-3">Entry</th>
                                <th class="px-6 py-3">Exit</th>
                            </tr>
                        </thead>
                        <tbody id="history-body" class="divide-y divide-gray-100 bg-white">
                            @forelse ($history as $visitor)
                                <tr>
                                    <td class="px-6 py-4 font-medium text-gray-950">{{ $visitor->visitor_name }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $visitor->reason }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ ucfirst($visitor->status) }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $visitor->entry_time?->format('d M, h:i A') ?? '-' }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $visitor->exit_time?->format('d M, h:i A') ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr id="empty-history">
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">No history yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    </div>

    <script>
        const token = document.querySelector('meta[name="csrf-token"]').content;
        const residentVisitorsUrl = @json(route('resident.visitors.index'));
        const approveRouteTemplate = @json(route('resident.visitors.approve', ['visitor' => '__ID__']));
        const rejectRouteTemplate = @json(route('resident.visitors.reject', ['visitor' => '__ID__']));
        const notifiedVisitors = new Set([...document.querySelectorAll('[id^="visitor-card-"]')].map((card) => card.id.replace('visitor-card-', '')));
        const visitorDeadlines = new Map([...document.querySelectorAll('[id^="visitor-card-"]')].map((card) => [card.id.replace('visitor-card-', ''), card.dataset.deadline]));

        function escapeHtml(value) {
            return String(value ?? '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;',
            }[char]));
        }

        function showToast(message, ok = true) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            toast.className = `rounded-lg border px-4 py-3 text-sm font-semibold ${ok ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-red-200 bg-red-50 text-red-800'}`;
            setTimeout(() => toast.classList.add('hidden'), 2500);
        }

        function numberValue(id) {
            return Number(document.getElementById(id).textContent);
        }

        function setLoading(form, loading) {
            const button = form.querySelector('button');
            form.querySelector('.loader').classList.toggle('hidden', !loading);
            button.disabled = loading;
        }

        function visitorCardHtml(visitor, highlight = false) {
            const expired = new Date(visitor.deadline_at).getTime() <= Date.now();
            const approveUrl = approveRouteTemplate.replace('__ID__', visitor.id);
            const rejectUrl = rejectRouteTemplate.replace('__ID__', visitor.id);
            const photo = visitor.photo_url
                ? `<img src="${escapeHtml(visitor.photo_url)}" alt="${escapeHtml(visitor.name)}" class="h-44 w-full rounded-md object-cover">`
                : '<div class="flex h-44 w-full items-center justify-center rounded-md bg-gray-100 text-sm font-semibold text-gray-500">No Photo</div>';

            return `
                <article id="visitor-card-${visitor.id}" data-deadline="${escapeHtml(visitor.deadline_at)}" class="rounded-lg border ${highlight ? 'border-emerald-300 ring-2 ring-emerald-100' : 'border-gray-200'} bg-white p-4 shadow-sm transition hover:border-gray-300">
                    ${photo}
                    <div class="mt-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h4 class="font-semibold text-gray-950">${escapeHtml(visitor.name)}</h4>
                                <p class="text-sm text-gray-500">${escapeHtml(visitor.phone)}</p>
                            </div>
                            <span class="rounded-full bg-amber-100 px-2.5 py-1 text-xs font-semibold text-amber-800">Pending</span>
                        </div>
                        <div class="mt-3 rounded-md border px-3 py-2 text-sm ${expired ? 'border-red-200 bg-red-50 text-red-700' : 'border-amber-200 bg-amber-50 text-amber-800'}">
                            <span class="font-semibold">Approval window:</span>
                            <span data-countdown>${expired ? 'Expired' : '01:00'}</span>
                        </div>
                        <p class="mt-3 rounded-md bg-gray-50 px-3 py-2 text-sm text-gray-700">${escapeHtml(visitor.reason)}</p>
                    </div>
                    <div class="mt-4 grid grid-cols-2 gap-2">
                        <form method="POST" action="${approveUrl}" data-ajax-action="resident" data-status="approved">
                            <input type="hidden" name="_token" value="${token}">
                            <input type="hidden" name="_method" value="PATCH">
                            <button ${expired ? 'disabled' : ''} class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-emerald-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-700 disabled:cursor-not-allowed disabled:opacity-70">
                                <span class="loader hidden h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                                <span class="label">Approve</span>
                            </button>
                        </form>
                        <form method="POST" action="${rejectUrl}" data-ajax-action="resident" data-status="rejected">
                            <input type="hidden" name="_token" value="${token}">
                            <input type="hidden" name="_method" value="PATCH">
                            <button ${expired ? 'disabled' : ''} class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-700 disabled:cursor-not-allowed disabled:opacity-70">
                                <span class="loader hidden h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                                <span class="label">Reject</span>
                            </button>
                        </form>
                    </div>
                    <p data-expired-note class="${expired ? '' : 'hidden'} mt-3 text-xs font-semibold text-red-600">Time expired. Watchman will call the apartment owner.</p>
                </article>
            `;
        }

        function addHistoryRow(visitor) {
            document.getElementById('empty-history')?.remove();
            document.getElementById('history-body').insertAdjacentHTML('afterbegin', `
                <tr>
                    <td class="px-6 py-4 font-medium text-gray-950">${escapeHtml(visitor.name)}</td>
                    <td class="px-6 py-4 text-gray-600">${escapeHtml(visitor.reason)}</td>
                    <td class="px-6 py-4 text-gray-600">${escapeHtml(visitor.status.charAt(0).toUpperCase() + visitor.status.slice(1))}</td>
                    <td class="px-6 py-4 text-gray-600">${escapeHtml(visitor.entry_time ?? '-')}</td>
                    <td class="px-6 py-4 text-gray-600">${escapeHtml(visitor.exit_time ?? '-')}</td>
                </tr>
            `);
        }

        function updateResidentCountdowns() {
            document.querySelectorAll('[data-deadline]').forEach((card) => {
                const deadline = new Date(card.dataset.deadline).getTime();
                const remaining = Math.max(0, Math.ceil((deadline - Date.now()) / 1000));
                const countdown = card.querySelector('[data-countdown]');
                const note = card.querySelector('[data-expired-note]');

                if (remaining <= 0) {
                    countdown.textContent = 'Expired';
                    note?.classList.remove('hidden');
                    card.querySelectorAll('button').forEach((button) => {
                        button.disabled = true;
                    });
                    countdown.closest('div').className = 'mt-3 rounded-md border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700';
                    return;
                }

                countdown.textContent = `00:${String(remaining).padStart(2, '0')}`;
            });
        }

        updateResidentCountdowns();
        setInterval(updateResidentCountdowns, 1000);

        async function submitResidentAction(form) {
            setLoading(form, true);

            try {
                const response = await fetch(form.action, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                });

                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Action failed.');

                document.getElementById(`visitor-card-${data.visitor.id}`)?.remove();
                document.getElementById('pending-count').textContent = Math.max(numberValue('pending-count') - 1, 0);
                if (data.visitor.status === 'approved') {
                    document.getElementById('approved-count').textContent = numberValue('approved-count') + 1;
                }
                addHistoryRow(data.visitor);

                if (!document.querySelector('[id^="visitor-card-"]')) {
                    document.getElementById('pending-list').innerHTML = '<div id="empty-pending" class="col-span-full rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-10 text-center text-sm text-gray-500">No pending visitor requests.</div>';
                }

                showToast(data.message);
            } catch (error) {
                showToast(error.message, false);
                setLoading(form, false);
            }
        }

        document.addEventListener('submit', (event) => {
            const form = event.target.closest('[data-ajax-action="resident"]');
            if (!form) return;
            event.preventDefault();
            submitResidentAction(form);
        });

        async function refreshResidentNotifications() {
            try {
                const response = await fetch(residentVisitorsUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': token,
                    },
                });
                const data = await response.json();
                if (!response.ok) throw new Error(data.message || 'Could not refresh requests.');

                const pending = data.visitors.filter((visitor) => visitor.status === 'pending');
                document.getElementById('pending-count').textContent = pending.length;

                pending.slice().reverse().forEach((visitor) => {
                    const existingCard = document.getElementById(`visitor-card-${visitor.id}`);
                    const previousDeadline = visitorDeadlines.get(String(visitor.id));

                    if (existingCard && previousDeadline !== visitor.deadline_at) {
                        existingCard.outerHTML = visitorCardHtml(visitor, true);
                        visitorDeadlines.set(String(visitor.id), visitor.deadline_at);
                        showToast(`Request sent again: ${visitor.name}`);
                        return;
                    }

                    if (existingCard) return;

                    document.getElementById('empty-pending')?.remove();
                    const isNewNotification = !notifiedVisitors.has(String(visitor.id));
                    document.getElementById('pending-list').insertAdjacentHTML('afterbegin', visitorCardHtml(visitor, isNewNotification));
                    notifiedVisitors.add(String(visitor.id));
                    visitorDeadlines.set(String(visitor.id), visitor.deadline_at);

                    if (isNewNotification) {
                        showToast(`New visitor request: ${visitor.name}`);
                    }
                });

                updateResidentCountdowns();
            } catch (error) {
                console.warn(error.message);
            }
        }

        setInterval(refreshResidentNotifications, 5000);

        /*
        document.querySelectorAll('[data-ajax-action="resident"]').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                setLoading(form, true);

                try {
                    const response = await fetch(form.action, {
                        method: 'PATCH',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': token,
                        },
                    });

                    const data = await response.json();
                    if (!response.ok) throw new Error(data.message || 'Action failed.');

                    document.getElementById(`visitor-card-${data.visitor.id}`)?.remove();
                    document.getElementById('pending-count').textContent = Math.max(numberValue('pending-count') - 1, 0);
                    if (data.visitor.status === 'approved') {
                        document.getElementById('approved-count').textContent = numberValue('approved-count') + 1;
                    }
                    addHistoryRow(data.visitor);

                    if (!document.querySelector('[id^="visitor-card-"]')) {
                        document.getElementById('pending-list').innerHTML = '<div id="empty-pending" class="col-span-full rounded-lg border border-dashed border-gray-300 bg-gray-50 px-4 py-10 text-center text-sm text-gray-500">No pending visitor requests.</div>';
                    }

                    showToast(data.message);
                } catch (error) {
                    showToast(error.message, false);
                    setLoading(form, false);
                }
            });
        });
        */
    </script>
</x-app-layout>
