<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'role' => ['nullable', 'in:admin,watchman,resident'],
            'apartment_block' => ['nullable', 'string', 'max:50'],
            'apartment_number' => ['nullable', 'string', 'max:50'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $role = $request->role ?? 'resident';
        $apartment = null;

        if ($role === 'resident' && $request->filled(['apartment_block', 'apartment_number'])) {
            $apartment = Apartment::firstOrCreate([
                'block' => strtoupper(trim($request->apartment_block)),
                'number' => strtoupper(trim($request->apartment_number)),
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email ?: 'user-'.uniqid().'@gatepass.local',
            'phone_number' => $request->phone_number,
            'role' => $role,
            'apartment_id' => $apartment?->id,
            'password' => Hash::make($request->password),
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
