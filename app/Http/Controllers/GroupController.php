<?php
namespace App\Http\Controllers;

use App\Models\Group;
use App\Models\Subject;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    /** Список групп преподавателя (с фильтрами) */
    public function index(Request $request)
    {
        $query = auth()->user()->groups()->with('subject');

        if ($request->filled('subject')) $query->where('subject_id', $request->subject);
        if ($request->filled('level'))   $query->where('level', $request->level);
        if ($request->filled('q'))       $query->where('name', 'like', '%'.$request->q.'%');

        $groups   = $query->latest()->get();
        $subjects = auth()->user()->subjects;

        return view('teacher.groups.index', compact('groups','subjects'));
    }

    /** Форма создания */
    public function create()
    {
        $subjects = auth()->user()->subjects;
        return view('teacher.groups.form', ['group' => null, 'subjects' => $subjects]);
    }

    /** Сохранение новой группы */
    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:30', 'regex:/^[A-Za-zА-Яа-я]+[бмБМ]-\d{3}$/'],
            'subject_id' => ['required', 'exists:subjects,id'],
        ], [
            'name.regex' => 'Формат названия: специальность + б/м + год(2 цифры) + номер(1 цифра). Пример: ПИб-231.',
        ]);
        $parsed = Group::parseName($data['name']);
        $group = Group::create([
            'name'       => $data['name'],
            'subject_id' => $data['subject_id'],
            'user_id'    => auth()->id(),
            'level'      => $parsed['level'],
            'year'       => $parsed['year'],
            'number'     => $parsed['number'],
        ]);

        // стандартный набор столбцов
        $this->createDefaultColumns($group);

        // добавление студентов вручную (если указаны)
        if ($request->filled('students_manual')) {
            $this->addStudentsFromText($group, $request->students_manual);
        }

        return redirect()->route('teacher.groups.index')->with('success', 'Группа создана.');
    }

    /** Форма редактирования */
    public function edit(Group $group)
    {
        $this->authorizeGroup($group);
        $subjects = auth()->user()->subjects;
        return view('teacher.groups.form', ['group' => $group, 'subjects' => $subjects]);
    }

    /** Обновление */
    public function update(Request $request, Group $group)
    {
        $this->authorizeGroup($group);
        $data = $request->validate([
            'name'       => ['required', 'string', 'max:30', 'regex:/^[A-Za-zА-Яа-я]+[бмБМ]-\d{3}$/'],
            'subject_id' => ['required', 'exists:subjects,id'],
        ], [
            'name.regex' => 'Формат названия: специальность + б/м + год(2 цифры) + номер(1 цифра). Пример: ПИб-231.',
        ]);
        $parsed = Group::parseName($data['name']);
        $group->update(array_merge($data, $parsed));
        return redirect()->route('teacher.groups.index')->with('success', 'Группа обновлена.');
    }

    /** Удаление */
    public function destroy(Group $group)
    {
        $this->authorizeGroup($group);
        $group->delete();
        return redirect()->route('teacher.groups.index')->with('success', 'Группа удалена.');
    }

    /* ===== Хелперы ===== */
    private function authorizeGroup(Group $group): void
    {
        if ($group->user_id !== auth()->id()) abort(403);
    }

    private function createDefaultColumns(Group $group): void
    {
        $defaults = [
            ['1 модуль','module'], ['2 модуль','module'], ['3 модуль','module'],
            ['Итоги*','total'], ['Экзамен','exam'],
            ['Пересдача','retake'], ['Комиссия','commission'],
            ['Оценка (балл)','score'], ['Оценка','grade'],
        ];
        $order = 0;
        foreach ($defaults as [$title,$type]) {
            $group->columns()->create([
                'title' => $title, 'type' => $type,
                'hidden' => in_array($type, ['retake','commission']),
                'sort_order' => $order++,
            ]);
        }
    }

    private function addStudentsFromText(Group $group, string $text): void
    {
        foreach (preg_split('/\r\n|\r|\n/', trim($text)) as $line) {
            $line = trim($line);
            if ($line === '') continue;
            $name = $line;
            // если строка похожа на email — пропустим, нужна ФИО (упрощённо)
            $email = 'student_'.uniqid().'@stud.local';
            $student = \App\Models\User::create([
                'name' => $name, 'email' => $email, 'password' => 'password', 'role' => 'student',
            ]);
            $group->students()->syncWithoutDetaching([$student->id]);
        }
    }
}
