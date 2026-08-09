@extends('layouts.app', ['sidebarActive' => 'dashboard'])
@section('title', 'Главная — ModulUS (Студент)')

@section('sidebar')@include('student._sidebar')@endsection

@section('content')
<div data-reveal>
    <h3 class="fw-bold mb-1">Здравствуйте, {{ auth()->user()->name }}! 👋</h3>
    <p style="color:var(--text-muted);">Ваши предметы и последние события</p>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3 col-6" data-reveal data-delay="1"><div class="glass p-3 text-center h-100">
        <div class="fs-3 fw-bold text-gradient">{{ $groups->count() }}</div><small style="color:var(--text-muted);">Предметов</small></div></div>
    <div class="col-md-3 col-6" data-reveal data-delay="2"><div class="glass p-3 text-center h-100">
        <div class="fs-3 fw-bold text-gradient">{{ $unread }}</div><small style="color:var(--text-muted);">Уведомлений</small></div></div>
</div>

<h5 class="fw-bold mb-3" data-reveal>Мои предметы</h5>
<div class="row g-3 mb-4">
    @forelse($groups as $g)
        <div class="col-md-6 col-lg-4" data-reveal data-delay="{{ ($loop->index%3)+1 }}">
            <a href="{{ route('student.subject.show', $g) }}" class="card feature-card h-100 text-decoration-none" style="color:var(--text);">
                <div class="feature-icon soft" style="width:44px;height:44px;font-size:1.1rem;">📚</div>
                <h6 class="fw-bold mb-1">{{ $g->subject?->name }}</h6>
                <div class="small" style="color:var(--text-muted);">Группа {{ $g->name }} · {{ $g->teacher?->name }}</div>
            </a>
        </div>
    @empty
        <div class="col-12 empty-state"><div class="empty-icon"><i class="bi bi-inbox"></i></div><p class="mb-0">Вас ещё не добавили в группу. Используйте ссылку-приглашение от преподавателя.</p></div>
    @endforelse
</div>

@if($recent->isNotEmpty())
<div class="card" data-reveal>
    <div class="card-header bg-transparent" style="border-color:var(--border);"><strong>Последние события</strong></div>
    <div class="list-group list-group-flush">
        @foreach($recent as $n)
            <div class="list-group-item d-flex gap-3 align-items-center">
                <span class="fs-4">{{ $n->icon }}</span>
                <div class="flex-grow-1"><div class="small">{!! $n->text !!}</div></div>
                <span style="color:var(--text-soft);font-size:.72rem;">{{ $n->created_at->diffForHumans() }}</span>
            </div>
        @endforeach
    </div>
</div>
@endif
@endsection
