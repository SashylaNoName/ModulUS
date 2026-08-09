<?php
namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AuthController extends Controller
{
    /** Форма входа */
    public function showLogin()
    {
        return view('auth.login');
    }

    /** Вход */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);
        $credentials['role'] = $request->input('role', 'teacher');

        if (Auth::attempt(['email' => $credentials['email'], 'password' => $credentials['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();
            return redirect()->intended(
                $user->isTeacher() ? route('teacher.dashboard') : route('student.dashboard')
            );
        }
        return back()->withErrors(['email' => 'Неверный email или пароль.'])->withInput();
    }

    /** Форма регистрации */
    public function showRegister()
    {
        return view('auth.register');
    }

    /** Регистрация */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name'           => ['required', 'string', 'max:150'],
            'email'          => ['required', 'email', 'unique:users,email'],
            'password'       => ['required', 'string', 'min:6', 'confirmed'],
            'role'           => ['required', Rule::in(['teacher', 'student'])],
            'department'     => ['nullable', 'string', 'max:150'],
            'student_group'  => ['nullable', 'string', 'max:30'],
            'invite_code'    => ['nullable', 'string', 'max:32'],
        ]);

        $user = User::create([
            'name'        => $data['name'],
            'email'       => $data['email'],
            'password'    => $data['password'],
            'role'        => $data['role'],
            'department'  => $data['role'] === 'teacher' ? ($data['department'] ?? null) : null,
            'invite_code' => $data['role'] === 'student' ? ($data['invite_code'] ?? null) : null,
        ]);

        // если студент указал группу при регистрации — привяжем его к ней
        if ($user->isStudent() && !empty($data['student_group'])) {
            $group = Group::where('name', $data['student_group'])->orWhere('invite_token', $data['student_group'])->first();
            if ($group) { $group->students()->syncWithoutDetaching([$user->id]); }
        }

        Auth::login($user);
        return redirect($user->isTeacher() ? route('teacher.dashboard') : route('student.dashboard'));
    }

    /** Выход */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
