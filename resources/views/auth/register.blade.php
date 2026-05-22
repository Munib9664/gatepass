<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" autocomplete="username" />
            <p class="mt-1 text-xs text-gray-500">Optional. Add one if this user should log in later with email.</p>
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="phone_number" :value="__('Phone Number')" />
            <x-text-input id="phone_number" class="block mt-1 w-full" type="text" name="phone_number" :value="old('phone_number')" autocomplete="tel" />
            <p class="mt-1 text-xs text-gray-500">For residents, this is used by the watchman if approval times out.</p>
            <x-input-error :messages="$errors->get('phone_number')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="role" :value="__('Role')" />
            <select id="role" name="role" class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" required>
                <option value="resident" @selected(old('role', 'resident') === 'resident')>Resident</option>
                <option value="watchman" @selected(old('role') === 'watchman')>Watchman</option>
                <option value="admin" @selected(old('role') === 'admin')>Admin</option>
            </select>
            <x-input-error :messages="$errors->get('role')" class="mt-2" />
        </div>

        <div id="apartment-fields" class="grid grid-cols-2 gap-3 rounded-md border border-gray-200 bg-gray-50 p-3">
            <div>
                <x-input-label for="apartment_block" :value="__('Block')" />
                <x-text-input id="apartment_block" class="block mt-1 w-full uppercase" type="text" name="apartment_block" :value="old('apartment_block')" autocomplete="address-line1" />
                <x-input-error :messages="$errors->get('apartment_block')" class="mt-2" />
            </div>
            <div>
                <x-input-label for="apartment_number" :value="__('Apartment No.')" />
                <x-text-input id="apartment_number" class="block mt-1 w-full uppercase" type="text" name="apartment_number" :value="old('apartment_number')" autocomplete="address-line2" />
                <x-input-error :messages="$errors->get('apartment_number')" class="mt-2" />
            </div>
        </div>

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end pt-2">
            <a class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2" href="{{ route('login') }}">
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>

    <script>
        const roleSelect = document.getElementById('role');
        const apartmentFields = document.getElementById('apartment-fields');
        const blockInput = document.getElementById('apartment_block');
        const numberInput = document.getElementById('apartment_number');

        function syncApartmentFields() {
            const isResident = roleSelect.value === 'resident';
            apartmentFields.classList.toggle('hidden', !isResident);
            blockInput.required = isResident;
            numberInput.required = isResident;
        }

        roleSelect.addEventListener('change', syncApartmentFields);
        syncApartmentFields();
    </script>
</x-guest-layout>
