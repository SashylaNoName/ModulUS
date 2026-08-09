@extends('layouts.app', ['sidebarActive' => 'subjects'])
@section('title', 'Мои предметы — ModulUS')

@section('sidebar')@include('student._sidebar')@endsection

@section('content')
<div data-reveal>
    <h3 class="fw-bold mb-1">Мои предметы</h3>
    <p style="color:var(--text-muted);" class="mb-4">Нажмите, чтобы увидеть оценки</p>
</div>

<div class="row g-3">
    @forelse($groups as $g)
        @php
            $grade = $g->grades()->where('user_id', auth()->id())->whereHas('column', fn($q)=>$q->where('type','grade'))->first();
            $gradeVal = $grade?->value;
            $badge = $gradeVal==='отл' ? 'badge-role-student' : ($gradeVal==='удовл' ? 'badge-role-teacher' : 'badge-group');
        @endphp
        <div class="col-md-6 col-lg-4" data-reveal data-delay="{{ ($loop->index%3)+1 }}">
            <a href="{{ route('student.subject.show', $g) }}" class="card feature-card h-100 text-decoration-none" style="color:var(--text);">
                <div class="d-flex justify-content-between align-items-start">
                    <div class="feature-icon soft mb-2" style="width:44px;height:44px;font-size:1.1rem;">📚</div>
                    @if($gradeVal)<span class="badge {{ $badge }}">{{ $gradeVal }}</span>@endif
                </div>
                <h6 class="fw-bold mb-1">{{ $g->subject?->name }}</h6>
                <div class="small" style="color:var(--text-muted);">Группа {{ $g->name }} · {{ $g->teacher?->name }}</div>
            </a>
        </div>
    @empty
        <div class="col-12 empty-state"><div class="empty-icon"><i class="bi bi-inbox"></i></div><p class="mb-0">Вас ещё не добавили ни в одну группу.</p></div>
    @endforelse
</div>
@endsection
