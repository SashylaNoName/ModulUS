<?php

namespace Database\Seeders;

use App\Models\Column;
use App\Models\Comment;
use App\Models\Grade;
use App\Models\Group;
use App\Models\Notification;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ===== Преподаватель =====
        $teacher = User::create([
            'name'       => 'Иванова Мария Петровна',
            'email'      => 'ivanova@university.ru',
            'password'   => 'password',
            'role'       => 'teacher',
            'department' => 'Информационных технологий',
        ]);

        // ===== Предметы =====
        $subjNames = ['Программирование', 'Высшая математика', 'Базы данных', 'Английский язык', 'Физика'];
        $subjects = [];
        foreach ($subjNames as $name) {
            $subjects[] = Subject::create(['name' => $name, 'user_id' => $teacher->id]);
        }

        // ===== Группа ПИб-231 (Программирование) =====
        $group = Group::create([
            'name'       => 'ПИб-231',
            'subject_id' => $subjects[0]->id,
            'user_id'    => $teacher->id,
            'level'      => 'Бакалавриат',
            'year'       => 2023,
            'number'     => 1,
            'invite_token' => 'abc123xyz',
        ]);

        // ===== Стандартные столбцы =====
        $colDefs = [
            ['1 модуль','module',false], ['2 модуль','module',false], ['3 модуль','module',false],
            ['Итоги*','total',false], ['Экзамен','exam',false],
            ['Пересдача','retake',true], ['Комиссия','commission',true],
            ['Оценка (балл)','score',false], ['Оценка','grade',false],
        ];
        $columns = [];
        foreach ($colDefs as $i => [$title,$type,$hidden]) {
            $columns[explode(' ',$title)[0]] = Column::create([
                'group_id' => $group->id, 'title' => $title, 'type' => $type,
                'hidden' => $hidden, 'sort_order' => $i,
            ]);
        }

        // ===== Студенты =====
        $studentsData = [
            ['Алексеев Артём Дмитриевич', 'alexeev@stud.ru'],
            ['Борисова Анна Сергеевна', 'borisova@stud.ru'],
            ['Волков Иван Игоревич', 'volkov@stud.ru'],
            ['Григорьева Ольга Павловна', 'grigoreva@stud.ru'],
            ['Дмитриев Сергей Андреевич', 'dmitriev@stud.ru'],
            ['Егорова Екатерина Максимовна', 'egorova@stud.ru'],
            ['Жуков Дмитрий Романович', 'zhukov@stud.ru'],
            ['Зайцева Мария Алексеевна', 'zaytseva@stud.ru'],
        ];
        $students = [];
        foreach ($studentsData as [$name,$email]) {
            $s = User::create(['name'=>$name,'email'=>$email,'password'=>'password','role'=>'student']);
            $group->students()->attach($s->id);
            $students[] = $s;
        }

        // ===== Оценки (по 4 студентам, как в прототипе) =====
        $gradesMap = [
            1 => ['23','25','20','68','24','','','92','отл'],
            2 => ['20','18','22','60','18','','','78','хор'],
            3 => ['25','26','27','78','17','','','95','отл'],
            4 => ['12','10','11','33','15','22','','55','удовл'],
        ];
        $colKeys = ['1','2','3','Итоги*','Экзамен','Пересдача','Комиссия','Оценка','Оценка'];
        $colObjects = array_values($columns);
        foreach ($gradesMap as $studentIdx => $values) {
            foreach ($colObjects as $i => $col) {
                Grade::create([
                    'group_id'  => $group->id,
                    'user_id'   => $students[$studentIdx-1]->id,
                    'column_id' => $col->id,
                    'value'     => $values[$i] ?? '',
                ]);
            }
        }

        // ===== Комментарии (диалоги с фото/файлом) =====
        // Алексеев — 1 модуль: с фото
        $g1 = Grade::where('user_id', $students[0]->id)->where('column_id', $columns['1']->id)->first();
        Comment::create(['grade_id'=>$g1->id,'user_id'=>$teacher->id,'text'=>'Отличная работа по первому модулю!']);
        Comment::create(['grade_id'=>$g1->id,'user_id'=>$students[0]->id,'text'=>'Спасибо! Прикрепляю решение доп. задачи.']);
        // Григорьева — 1 модуль
        $g4 = Grade::where('user_id', $students[3]->id)->where('column_id', $columns['1']->id)->first();
        Comment::create(['grade_id'=>$g4->id,'user_id'=>$teacher->id,'text'=>'Много ошибок. Подойдите на консультацию.']);
        Comment::create(['grade_id'=>$g4->id,'user_id'=>$students[3]->id,'text'=>'Подойду в четверг.']);

        // ===== Уведомления (с ссылками на обсуждение) =====
        Notification::create(['user_id'=>$teacher->id,'type'=>'join','icon'=>'👤','title'=>'Новый студент','text'=>'<b>Кузнецов Павел</b> присоединился к <b>ПИб-231</b>.','link'=>'/teacher/groups/1/gradebook']);
        Notification::create(['user_id'=>$teacher->id,'type'=>'reply','icon'=>'💬','title'=>'Ответ студента','text'=>'<b>Григорьева Ольга</b> ответила на комментарий.','link'=>'/teacher/groups/1/gradebook']);
        Notification::create(['user_id'=>$students[0]->id,'type'=>'grade','icon'=>'📊','title'=>'Поставлена оценка','text'=>'Получен балл «92» за «Оценка (балл)» (Программирование).','link'=>'/student/subjects/1']);
        Notification::create(['user_id'=>$students[0]->id,'type'=>'comment','icon'=>'💬','title'=>'Новый комментарий','text'=>'Комментарий преподавателя к 1 модулю.','link'=>'/student/subjects/1']);
    }
}
