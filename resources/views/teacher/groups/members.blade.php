@extends('layouts.app', ['sidebarActive' => 'groups'])
@section('title', 'Студенты группы — ModulUS')

@section('sidebar')@include('teacher._sidebar')@endsection

@section('content')
<div class="d-flex align-items-center mb-3" data-reveal>
    <a href="{{ route('teacher.groups.index') }}" class="btn btn-light me-2"><i class="bi bi-arrow-left"></i></a>
    <div><h3 class="fw-bold mb-0">Студенты группы {{ $group->name }}</h3>
    <span class="small" style="color:var(--text-muted);">{{ $group->subject?->name }} · {{ $group->level }}</span></div>
</div>

<div class="d-flex flex-wrap gap-2 mb-4" data-reveal>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal"><i class="bi bi-person-plus"></i> Добавить</button>
    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#inviteModal"><i class="bi bi-link-45deg"></i> Ссылка</button>
    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importModal"><i class="bi bi-file-earmark-spreadsheet"></i> Импорт Excel</button>
    <a href="{{ route('teacher.gradebook.show', $group) }}" class="btn btn-light"><i class="bi bi-table"></i> Журнал</a>
</div>

<div class="card" data-reveal>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-pretty align-middle mb-0">
                <thead><tr><th style="width:50px;">#</th><th>Студент</th><th>Email</th><th class="text-end">Действия</th></tr></thead>
                <tbody>
                    @forelse($students as $i => $s)
                        <tr>
                            <td>{{ $i+1 }}</td>
                            <td><div class="d-flex align-items-center gap-2"><span class="avatar avatar-sm">{{ \App\Models\User::find($s->id)?->initials() }}</span><span class="fw-semibold">{{ $s->name }}</span></div></td>
                            <td>{{ $s->email }}</td>
                            <td class="text-end">
                                <form method="POST" action="{{ route('teacher.members.destroy', [$group, $s]) }}" class="d-inline" onsubmit="return confirm('Исключить студента?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-light text-danger" title="Исключить"><i class="bi bi-x-lg"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="empty-state">Студентов пока нет</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Модалка: добавить --}}
<div class="modal fade" id="addModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Добавить студента</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('teacher.members.store', $group) }}">@csrf
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">ФИО</label><input type="text" name="name" class="form-control" required></div>
            <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required></div>
        </div>
        <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Отмена</button><button class="btn btn-primary">Добавить</button></div>
    </form>
</div></div></div>

{{-- Модалка: ссылка --}}
<div class="modal fade" id="inviteModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Ссылка-приглашение</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <p style="color:var(--text-muted);" class="small">Поделитесь ссылкой со студентами.</p>
        <div class="input-group"><input type="text" class="form-control" id="invite-link" value="{{ $group->invite_url }}" readonly>
            <button class="btn btn-outline-primary" onclick="copyInvite()"><i class="bi bi-clipboard"></i></button></div>
    </div>
</div></div></div>

{{-- Модалка: импорт --}}
<div class="modal fade" id="importModal" tabindex="-1"><div class="modal-dialog"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title">Импорт студентов из Excel</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <form method="POST" action="{{ route('teacher.excel.importStudents', $group) }}" enctype="multipart/form-data">@csrf
        <div class="modal-body">
            <div class="mb-3"><label class="form-label">Файл Excel</label><input type="file" name="file" class="form-control" accept=".xlsx,.xls" required></div>
            <div class="alert alert-info small mb-0">Колонка A — ФИО, B — email. Первая строка — заголовок.</div>
        </div>
        <div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Отмена</button><button class="btn btn-primary">Импортировать</button></div>
    </form>
</div></div></div>
@endsection

@section('scripts')
function copyInvite(){var i=document.getElementById('invite-link');i.select();navigator.clipboard&&navigator.clipboard.writeText(i.value);toast('Ссылка скопирована');}
@endsection
