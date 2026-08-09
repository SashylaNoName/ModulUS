@extends('layouts.app', ['sidebarActive' => 'groups'])
@section('title', 'Мои группы — ModulUS')

@section('sidebar')@include('teacher._sidebar')@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4" data-reveal>
    <div>
        <h3 class="fw-bold mb-1">Мои группы</h3>
        <p class="mb-0" style="color:var(--text-muted);">Создавайте и управляйте учебными группами</p>
    </div>
    <a href="{{ route('teacher.groups.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Создать группу</a>
</div>

<form method="GET" class="card mb-4" data-reveal>
    <div class="card-body">
        <div class="row g-2 align-items-end">
            <div class="col-md-4 col-12">
                <label class="form-label small fw-semibold">Предмет</label>
                <select name="subject" class="form-select">
                    <option value="">Все предметы</option>
                    @foreach($subjects as $s)
                        <option value="{{ $s->id }}" @if(request('subject')==$s->id) selected @endif>{{ $s->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label small fw-semibold">Уровень</label>
                <select name="level" class="form-select">
                    <option value="">Все</option>
                    <option value="Бакалавриат" @if(request('level')==='Бакалавриат') selected @endif>Бакалавриат</option>
                    <option value="Магистратура" @if(request('level')==='Магистратура') selected @endif>Магистратура</option>
                </select>
            </div>
            <div class="col-md-3 col-6">
                <label class="form-label small fw-semibold">Поиск</label>
                <input type="text" name="q" class="form-control" value="{{ request('q') }}" placeholder="Название...">
            </div>
            <div class="col-md-2 col-12">
                <button class="btn btn-outline-primary w-100"><i class="bi bi-funnel"></i> Применить</button>
            </div>
        </div>
    </div>
</form>

<div class="row g-3">
    @forelse($groups as $g)
        <div class="col-xl-4 col-lg-6 col-md-6" data-reveal data-delay="{{ ($loop->index%3)+1 }}">
            <div class="group-card" onclick="location.href='{{ route('teacher.gradebook.show', $g) }}'">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge {{ $g->level==='Магистратура'?'badge-role-teacher':'badge-role-student' }}">{{ $g->level }}</span>
                    <span class="badge badge-group"><i class="bi bi-calendar me-1"></i>{{ $g->year }}</span>
                </div>
                <div class="group-name">{{ $g->name }}</div>
                <div class="group-meta small mb-3">{{ $g->subject?->name }}</div>
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <span class="group-meta small"><i class="bi bi-people me-1"></i>{{ $g->students_count }} студ.</span>
                </div>
                <div class="d-flex gap-2" onclick="event.stopPropagation()">
                    <a href="{{ route('teacher.groups.edit', $g) }}" class="btn btn-light btn-sm flex-grow-1"><i class="bi bi-pencil me-1"></i> Редактировать</a>
                    <a href="{{ route('teacher.members.index', $g) }}" class="btn btn-light btn-sm" title="Студенты"><i class="bi bi-people"></i></a>
                    <form method="POST" action="{{ route('teacher.groups.destroy', $g) }}" onsubmit="return confirm('Удалить группу {{ $g->name }}?')">
                        @csrf @method('DELETE')
                        <button class="btn btn-light btn-sm text-danger" title="Удалить"><i class="bi bi-trash"></i></button>
                    </form>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12 empty-state">
            <div class="empty-icon"><i class="bi bi-inbox"></i></div>
            <h5>Группы не найдены</h5>
            <p>Измените фильтры или создайте новую группу.</p>
        </div>
    @endforelse
</div>
@endsection
