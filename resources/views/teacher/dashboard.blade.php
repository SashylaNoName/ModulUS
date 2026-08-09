@extends('layouts.app', ['sidebarActive' => 'dashboard'])
@section('title', 'Главная — ModulUS (Преподаватель)')

@section('sidebar')@include('teacher._sidebar')@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4" data-reveal>
    <div>
        <h3 class="fw-bold mb-1">Здравствуйте, {{ auth()->user()->name }}! 👋</h3>
        <p class="mb-0" style="color:var(--text-muted);">Сводка по вашим предметам и группам</p>
    </div>
    <a href="{{ route('teacher.groups.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Создать группу</a>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6" data-reveal data-delay="1">
        <div class="glass p-3 d-flex align-items-center gap-3 h-100">
            <span class="feature-icon soft mb-0" style="width:48px;height:48px;font-size:1.2rem;">📚</span>
            <div><div class="fs-3 fw-bold lh-1 text-gradient">{{ $subjects->count() }}</div><small style="color:var(--text-muted);">Предметов</small></div>
        </div>
    </div>
    <div class="col-md-3 col-6" data-reveal data-delay="2">
        <div class="glass p-3 d-flex align-items-center gap-3 h-100">
            <span class="feature-icon soft mb-0" style="width:48px;height:48px;font-size:1.2rem;">👥</span>
            <div><div class="fs-3 fw-bold lh-1 text-gradient">{{ $groups->count() }}</div><small style="color:var(--text-muted);">Групп</small></div>
        </div>
    </div>
    <div class="col-md-3 col-6" data-reveal data-delay="3">
        <div class="glass p-3 d-flex align-items-center gap-3 h-100">
            <span class="feature-icon soft mb-0" style="width:48px;height:48px;font-size:1.2rem;">🎓</span>
            <div><div class="fs-3 fw-bold lh-1 text-gradient">{{ $studentsCount }}</div><small style="color:var(--text-muted);">Студентов</small></div>
        </div>
    </div>
    <div class="col-md-3 col-6" data-reveal data-delay="4">
        <div class="glass p-3 d-flex align-items-center gap-3 h-100">
            <span class="feature-icon soft mb-0" style="width:48px;height:48px;font-size:1.2rem;">🔔</span>
            <div><div class="fs-3 fw-bold lh-1 text-gradient">{{ $unreadCount }}</div><small style="color:var(--text-muted);">Новых уведомлений</small></div>
        </div>
    </div>
</div>

<div class="card" data-reveal>
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center" style="border-color:var(--border);">
        <strong>Мои группы</strong>
        <a href="{{ route('teacher.groups.index') }}" class="small">Все группы →</a>
    </div>
    <div class="card-body p-0">
        @forelse($groups as $g)
            <div class="group-card" onclick="location.href='{{ route('teacher.gradebook.show', $g) }}'">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="badge {{ $g->level==='Магистратура' ? 'badge-role-teacher' : 'badge-role-student' }}">{{ $g->level }}</span>
                    <span class="badge badge-group">{{ $g->year }}</span>
                </div>
                <div class="group-name mt-2">{{ $g->name }}</div>
                <div class="group-meta small">{{ $g->subject?->name }}</div>
                <div class="d-flex align-items-center justify-content-between mt-2">
                    <span class="group-meta small"><i class="bi bi-people me-1"></i>{{ $g->students_count }} студ.</span>
                </div>
                <div class="d-flex gap-2 mt-2" onclick="event.stopPropagation()">
                    <a href="{{ route('teacher.groups.edit', $g) }}" class="btn btn-light btn-sm flex-grow-1"><i class="bi bi-pencil me-1"></i> Редактировать</a>
                    <a href="{{ route('teacher.members.index', $g) }}" class="btn btn-light btn-sm" title="Студенты"><i class="bi bi-people"></i></a>
                </div>
            </div>
        @empty
            <div class="empty-state"><div class="empty-icon"><i class="bi bi-inbox"></i></div><p class="mb-0">Групп пока нет. Создайте первую.</p></div>
        @endforelse
    </div>
</div>
@endsection
