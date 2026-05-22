<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Admin Dashboard</h2>
                <p class="text-sm text-gray-500">Apartments, residents, and watchmen overview.</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">

            {{-- Stat Cards --}}
            <section class="grid gap-4 md:grid-cols-3">
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-medium text-gray-500">Apartments</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $apartments->count() }}</div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-medium text-gray-500">Residents</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $residents->count() }}</div>
                </div>
                <div class="rounded-lg border border-gray-200 bg-white p-5 shadow-sm">
                    <div class="text-sm font-medium text-gray-500">Watchmen</div>
                    <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $watchmen->count() }}</div>
                </div>
            </section>

            {{-- Apartments with Owner Details --}}
            <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">Apartment Ownership</h3>
                    <p class="mt-0.5 text-sm text-gray-500">Each apartment and the residents registered to it.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                            <tr>
                                <th class="px-6 py-3">Apartment</th>
                                <th class="px-6 py-3">Owner / Resident</th>
                                <th class="px-6 py-3">Email</th>
                                <th class="px-6 py-3">Phone</th>
                                <th class="px-6 py-3 text-center">Total Residents</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($apartments as $apartment)
                                @if ($apartment->residents->isNotEmpty())
                                    @foreach ($apartment->residents as $i => $resident)
                                        <tr>
                                            @if ($i === 0)
                                                <td class="px-6 py-4 font-semibold text-gray-900 align-top" rowspan="{{ $apartment->residents->count() }}">
                                                    {{ $apartment->label }}
                                                </td>
                                            @endif
                                            <td class="px-6 py-4 text-gray-900">{{ $resident->name }}</td>
                                            <td class="px-6 py-4 text-gray-600">{{ $resident->email }}</td>
                                            <td class="px-6 py-4 text-gray-600">{{ $resident->phone_number ?? '-' }}</td>
                                            @if ($i === 0)
                                                <td class="px-6 py-4 text-center text-gray-600 align-top" rowspan="{{ $apartment->residents->count() }}">
                                                    {{ $apartment->residents_count }}
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td class="px-6 py-4 font-semibold text-gray-900">{{ $apartment->label }}</td>
                                        <td colspan="3" class="px-6 py-4 text-gray-400 italic">No residents assigned</td>
                                        <td class="px-6 py-4 text-center text-gray-600">0</td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">No apartments registered yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

            {{-- Watchmen --}}
            <section class="overflow-hidden rounded-lg border border-gray-200 bg-white shadow-sm">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">Watchmen</h3>
                    <p class="mt-0.5 text-sm text-gray-500">All registered watchman accounts.</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-semibold uppercase text-gray-500">
                            <tr>
                                <th class="px-6 py-3">#</th>
                                <th class="px-6 py-3">Name</th>
                                <th class="px-6 py-3">Email</th>
                                <th class="px-6 py-3">Phone</th>
                                <th class="px-6 py-3">Joined</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            @forelse ($watchmen as $index => $watchman)
                                <tr>
                                    <td class="px-6 py-4 text-gray-500">{{ $index + 1 }}</td>
                                    <td class="px-6 py-4 font-medium text-gray-900">{{ $watchman->name }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $watchman->email }}</td>
                                    <td class="px-6 py-4 text-gray-600">{{ $watchman->phone_number ?? '-' }}</td>
                                    <td class="px-6 py-4 text-gray-500">{{ $watchman->created_at->format('d M Y') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">No watchmen registered yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>

        </div>
    </div>
</x-app-layout>
