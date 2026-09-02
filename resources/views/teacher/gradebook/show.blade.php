@extends('layouts.app', ['sidebarActive' => 'groups'])
@section('title', 'Журнал — ' . $group->name)

@section('sidebar')@include('teacher._sidebar')@endsection

@section('content')
@php
    $columns = $group->columns()->orderBy('sort_order')->get();
    $students = $group->students;
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3" data-reveal>
    <div class="d-flex align-items-center">
        <a href="{{ route('teacher.groups.index') }}" class="btn btn-light me-2"><i class="bi bi-arrow-left"></i></a>
        <div><h3 class="fw-bold mb-0">{{ $group->name }}</h3>
        <span class="small" style="color:var(--text-muted);">{{ $group->subject?->name }} · {{ $group->level }} · {{ $students->count() }} студ.</span></div>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#visModal"><i class="bi bi-eye"></i> Видимость</button>
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#sumModal"><i class="bi bi-calculator"></i> Суммирование</button>
        <a class="btn btn-outline-primary" href="{{ route('teacher.excel.export', $group) }}"><i class="bi bi-download"></i> Экспорт</a>
        <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importGradesModal"><i class="bi bi-upload"></i> Импорт</button>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addColModal"><i class="bi bi-plus-lg"></i> Столбец</button>
    </div>
</div>

<div class="card" data-reveal>
    <div class="card-body p-0">
        <div class="table-responsive" style="max-height:70vh;">
            <table class="table table-bordered grade-table mb-0 align-middle" id="grade-table">
                <thead>
                    <tr>
                        <th class="sticky-column" style="min-width:220px;left:0;z-index:3;">Студент</th>
                        @foreach($columns as $c)
                            @php $cls = $c->type==='module'?'col-module':($c->type==='total'?'col-total':''); @endphp
                            <th class="{{ $cls }}">
                                <div class="d-flex align-items-center gap-1">
                                    <span>{{ $c->title }}</span>
                                    @if($c->hidden)<i class="bi bi-eye-slash" style="color:var(--text-soft);font-size:.75rem;"></i>@endif
                                    @if($c->type==='intermediate')
                                        <form method="POST" action="{{ route('teacher.columns.destroy', $c) }}" class="d-inline" onsubmit="return confirm('Удалить столбец?')">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-sm btn-link p-0" style="color:var(--text-muted);" title="Удалить"><i class="bi bi-x"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $s)
                        <tr>
                            <td class="sticky-column" style="min-width:220px;left:0;">
                                <div class="d-flex align-items-center gap-2"><span class="avatar avatar-sm">{{ $s->initials() }}</span><span class="fw-semibold">{{ $s->name }}</span></div>
                            </td>
                            @foreach($columns as $c)
                                @php
                                    $cellKey = $s->id.'_'.$c->id;
                                    $grade = $grades[$cellKey] ?? null;
                                    $hasThread = $grade && $grade->comments->isNotEmpty();
                                    $editable = in_array($c->type, ['module','total']) ? 'readonly' : '';
                                @endphp
                                <td class="cell-grade">
                                    <div class="d-flex align-items-center justify-content-center">
                                        <input class="cell-input {{ $hasThread?'has-comment':'' }}" value="{{ $grade?->value ?? '' }}" {{ $editable }}
                                               data-student="{{ $s->id }}" data-column="{{ $c->id }}"
                                               onchange="saveGrade(this)" onfocus="this.select()">
                                        @php $commentTarget = $grade?->id @endphp
                                        @if($grade)
                                            @if($hasThread)
                                                <button class="btn btn-sm btn-link p-0 ms-1" style="color:var(--warning);" data-bs-toggle="modal" data-bs-target="#chatModal" data-grade-id="{{ $grade->id }}" data-context="{{ $s->name }} · {{ $c->title }}"><i class="bi bi-chat-dots-fill"></i></button>
                                            @else
                                                <button class="btn btn-sm btn-link p-0 ms-1" style="color:var(--text-soft);" data-bs-toggle="modal" data-bs-target="#chatModal" data-grade-id="{{ $grade->id }}" data-context="{{ $s->name }} · {{ $c->title }}"><i class="bi bi-chat"></i></button>
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Видимость --}}
<div class="modal fade" id="visModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="bi bi-eye text-gradient"></i> Видимость столбцов</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <p class="small" style="color:var(--text-muted);">Снимите галочку, чтобы скрыть столбец от студентов.</p>
        @foreach($columns as $c)
            <div class="d-flex align-items-center justify-content-between py-1">
                <span>{{ $c->title }} @if($c->hidden)<i class="bi bi-eye-slash text-muted small"></i>@endif</span>
                <form method="POST" action="{{ route('teacher.columns.visibility', $c) }}">@csrf @method('PATCH')
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" onchange="this.form.submit()" {{ $c->hidden ? '' : 'checked' }}>
                    </div>
                </form>
            </div>
        @endforeach
    </div>
</div></div></div>

{{-- Суммирование --}}
@php
    $intermediates = $columns->where('type', 'intermediate');
@endphp
<div class="modal fade" id="sumModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="bi bi-calculator text-gradient"></i> Суммирование в модули</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <p class="small" style="color:var(--text-muted);">
            Включите суммирование модуля — и его балл будет считаться суммой
            промежуточных столбцов, отмеченных ниже. Изменения применяются сразу.
        </p>

        {{-- тумблеры модулей и итога --}}
        <form method="POST" action="{{ route('teacher.groups.summing', $group) }}">@csrf @method('PATCH')
            @foreach([1,2,3] as $n)
                <div class="d-flex align-items-center justify-content-between py-1">
                    <span>Суммировать столбцы в <b>{{ $n }} модуль</b></span>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="sum_m{{ $n }}" value="1"
                               onchange="this.form.submit()" {{ $group->{'sum_m'.$n} ? 'checked' : '' }}>
                    </div>
                </div>
            @endforeach
            <hr>
            <div class="d-flex align-items-center justify-content-between py-1">
                <span>Считать <b>Итоги</b> суммой 1+2+3 модулей</span>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="sum_total" value="1"
                           onchange="this.form.submit()" {{ $group->sum_total ? 'checked' : '' }}>
                </div>
            </div>
        </form>

        {{-- куда суммировать каждый промежуточный столбец --}}
        @if($intermediates->isNotEmpty())
            <hr>
            <h6 class="fw-bold small mb-2">Промежуточные столбцы</h6>
            @foreach($intermediates as $c)
                <div class="d-flex align-items-center justify-content-between py-1">
                    <span>{{ $c->title }}</span>
                    <form method="POST" action="{{ route('teacher.columns.summing', $c) }}">@csrf @method('PATCH')
                        <select name="sum_into" class="form-select form-select-sm" style="width:auto;" onchange="this.form.submit()">
                            <option value="">— не суммировать —</option>
                            @foreach([1,2,3] as $n)
                                <option value="{{ $n }}" {{ (string)$c->sum_into === (string)$n ? 'selected' : '' }}>в {{ $n }} модуль</option>
                            @endforeach
                        </select>
                    </form>
                </div>
            @endforeach
        @endif
    </div>
</div></div></div>

{{-- Добавить столбец --}}
<div class="modal fade" id="addColModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Новый столбец</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('teacher.columns.store', $group) }}">@csrf
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Название</label><input type="text" name="title" class="form-control" required></div>
            <label class="form-label">Размещение</label>
            <select name="position" class="form-select mb-3">
                <option value="before1">Перед 1 модулем</option>
                <option value="before2">Перед 2 модулем</option>
                <option value="before3">Перед 3 модулем</option>
            </select>
            <div class="form-check"><input class="form-check-input" type="checkbox" name="sum" value="1" checked id="sum"><label for="sum" class="form-check-label">Суммировать в модуль</label></div>
            <input type="hidden" name="type" value="text">
        </div>
        <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Отмена</button><button class="btn btn-primary">Добавить</button></div>
    </form>
</div></div></div>

{{-- Импорт баллов --}}
<div class="modal fade" id="importGradesModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Импорт баллов из Excel</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('teacher.excel.importGrades', $group) }}" enctype="multipart/form-data">@csrf
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Файл Excel</label><input type="file" name="file" class="form-control" accept=".xlsx,.xls" required></div>
            <div class="alert alert-info small mb-0">
                <b>Как готовить файл:</b> первая строка — заголовки. Начало строки — ФИО студента
                (одной колонкой или отдельно «Фамилия» + «Имя»). Дальше — заголовки столбцов журнала:
                знакомые заполнятся, новые (например «к/р 1») создадутся как промежуточные
                на своей позиции. Студентов из файла, которых нет в группе, добавим в неё.
                По завершении покажем отчёт: сколько оценок записано и что пропущено.
            </div>
        </div>
        <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Отмена</button><button class="btn btn-primary">Импортировать</button></div>
    </form>
</div></div></div>

{{-- Чат --}}
<div class="modal fade" id="chatModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="bi bi-chat-dots text-gradient"></i> Диалог по оценке</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="small mb-2" style="color:var(--text-muted);" id="chat-context"></div>
        <div id="chat-thread" class="mb-3" style="max-height:300px;overflow:auto;"></div>
        <div class="border-top pt-3">
            <form method="POST" id="chatForm" enctype="multipart/form-data">@csrf
                <input type="hidden" name="grade_id" id="chat-grade-id">
                <textarea class="form-control mb-2" rows="2" name="text" placeholder="Сообщение..."></textarea>
                <div class="d-flex gap-2">
                    <label class="btn btn-light" title="Прикрепить файл"><i class="bi bi-paperclip"></i><input type="file" name="file" hidden onchange="attachInfo(this)"></label>
                    <label class="btn btn-light"><i class="bi bi-image"></i><input type="file" name="image" accept="image/*" hidden onchange="attachInfo(this)"></label>
                    <button class="btn btn-primary ms-auto"><i class="bi bi-send"></i> Отправить</button>
                </div>
                <div class="attach-info small"></div>
            </form>
        </div>
    </div>
</div></div></div>
@endsection

@section('scripts')
const CHAT = @json($chat);

document.getElementById('chatModal').addEventListener('show.bs.modal', function(e){
    const btn = e.relatedTarget;
    const gradeId = btn.dataset.gradeId;
    document.getElementById('chat-context').textContent = btn.dataset.context;
    document.getElementById('chat-grade-id').value = gradeId;
    document.getElementById('chatForm').action = '{{ url('/grades') }}/' + gradeId + '/comments';
    renderChat(gradeId);
});

function renderChat(gradeId){
    const el = document.getElementById('chat-thread');
    const msgs = CHAT[gradeId];
    if (!msgs || !msgs.length) {
        el.innerHTML = '<p class="small" style="color:var(--text-muted);">Комментариев пока нет.</p>';
        return;
    }
    el.innerHTML = '<div class="comment-thread">' + msgs.map(function(m){
        let b = '<div class="comment-bubble '+(!m.mine?'teacher':'')+'">'
            + '<div class="d-flex justify-content-between"><strong class="small">'+m.name+'</strong>'
            + '<span style="color:var(--text-soft);font-size:.7rem;">'+(m.time||'')+'</span></div>';
        if (m.text)  b += '<div class="small mt-1">'+m.text+'</div>';
        if (m.image) b += '<img src="'+m.image+'" class="img-fluid rounded mt-2" style="max-height:200px;" onclick="window.open(this.src,\'_blank\')">';
        if (m.file)  b += '<a href="'+m.file+'" target="_blank" class="d-inline-flex align-items-center gap-1 mt-2 small"><i class="bi bi-paperclip"></i> '+(m.fname||'файл')+'</a>';
        return b + '</div>';
    }).join('') + '</div>';
    el.scrollTop = el.scrollHeight;
}

// AJAX сохранение оценки
function saveGrade(input){
    fetch('{{ route('teacher.grades.update', $group) }}', {
        method: 'PUT',
        headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}','Content-Type':'application/json','Accept':'application/json'},
        body: JSON.stringify({user_id: input.dataset.student, column_id: input.dataset.column, value: input.value})
    }).then(r=>r.json()).then(function(d){
        toast('Балл сохранён');
        // сразу обновить пересчитанные модули/итог в этой же строке
        if (d && d.cells) {
            Object.keys(d.cells).forEach(function(colId){
                var cell = document.querySelector(
                    '.cell-input[data-student="'+input.dataset.student+'"][data-column="'+colId+'"]');
                if (cell) cell.value = d.cells[colId];
            });
        }
    }).catch(function(){});
}
@endsection
