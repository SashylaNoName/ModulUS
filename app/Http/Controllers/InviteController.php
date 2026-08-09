<?php
namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Notification;
use Illuminate\Http\Request;

class InviteController extends Controller
{
    /** Присоединение к группе по ссылке-приглашению */
    public function join(Request $request, string $token)
    {
        $group = Group::where('invite_token', $token)->firstOrFail();
        $user  = auth()->user();

        if (! $user) {
            // если гость — на страницу регистрации с подсказкой
            return redirect()->route('register')->with('invite_token', $token);
        }

        if ($user->isStudent()) {
            $group->students()->syncWithoutDetaching([$user->id]);

            // уведомить преподавателя о вступлении
            Notification::create([
                'user_id' => $group->user_id,
                'type'    => 'join',
                'icon'    => '👤',
                'title'   => 'Новый студент в группе',
                'text'    => '<b>'.e($user->name).'</b> присоединился к группе <b>'.e($group->name).'</b>.',
                'link'    => route('teacher.gradebook.show', $group, false),
            ]);

            return redirect()->route('student.subject.show', $group)->with('success', 'Вы присоединились к группе.');
        }
        return redirect()->route('teacher.dashboard');
    }
}
