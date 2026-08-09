<?php
namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class GroupMemberController extends Controller
{
    /** Список студентов группы */
    public function index(Group $group)
    {
        $this->authorizeGroup($group);
        $students = $group->students()->orderBy('name')->get();
        return view('teacher.groups.members', compact('group','students'));
    }

    /** Добавить студента вручную */
    public function store(Request $request, Group $group)
    {
        $this->authorizeGroup($group);
        $data = $request->validate([
            'name'  => ['required','string','max:150'],
            'email' => ['required','email','unique:users,email'],
        ]);
        $student = User::create([
            'name' => $data['name'], 'email' => $data['email'],
            'password' => 'password', 'role' => 'student',
        ]);
        $group->students()->syncWithoutDetaching([$student->id]);
        return back()->with('success', 'Студент добавлен.');
    }

    /** Исключить студента из группы */
    public function destroy(Group $group, User $student)
    {
        $this->authorizeGroup($group);
        $group->students()->detach([$student->id]);
        return back()->with('success', 'Студент исключён.');
    }

    private function authorizeGroup(Group $group): void
    {
        if ($group->user_id !== auth()->id()) abort(403);
    }
}
