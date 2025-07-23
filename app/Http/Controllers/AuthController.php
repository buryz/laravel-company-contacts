<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Wyświetl formularz logowania
     */
    public function showLoginForm(): View
    {
        return view('auth.contact-login');
    }

    /**
     * Obsłuż próbę logowania
     */
    public function login(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Pole email jest wymagane.',
            'email.email' => 'Podaj prawidłowy adres email.',
            'password.required' => 'Pole hasło jest wymagane.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }

        $credentials = $request->only('email', 'password');
        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            
            return redirect()->intended(route('contacts.index'))
                ->with('success', 'Zostałeś pomyślnie zalogowany.');
        }

        return back()
            ->withErrors(['email' => 'Nieprawidłowe dane logowania.'])
            ->withInput($request->only('email'));
    }

    /**
     * Wyloguj użytkownika
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('contacts.index')
            ->with('success', 'Zostałeś pomyślnie wylogowany.');
    }

    /**
     * Wyświetl formularz rejestracji
     */
    public function showRegisterForm(): View
    {
        return view('auth.contact-register');
    }

    /**
     * Obsłuż rejestrację nowego użytkownika
     */
    public function register(Request $request): RedirectResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [
            'name.required' => 'Pole nazwa jest wymagane.',
            'name.max' => 'Nazwa nie może być dłuższa niż 255 znaków.',
            'email.required' => 'Pole email jest wymagane.',
            'email.email' => 'Podaj prawidłowy adres email.',
            'email.unique' => 'Ten adres email jest już zajęty.',
            'password.required' => 'Pole hasło jest wymagane.',
            'password.min' => 'Hasło musi mieć co najmniej 8 znaków.',
            'password.confirmed' => 'Potwierdzenie hasła nie pasuje.',
        ]);

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput($request->only('name', 'email'));
        }

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        Auth::login($user);

        return redirect()->route('contacts.index')
            ->with('success', 'Konto zostało utworzone pomyślnie.');
    }
}