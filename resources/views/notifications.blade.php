@extends('layouts.app', ['sidebarActive' => 'notif'])
@section('title', 'Уведомления — ModulUS')

@section('sidebar')@include(auth()->user()->isTeacher() ? 'teacher._sidebar' : 'student._sidebar')@endsection

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4" data-reveal>
    <div><h3 class="fw-bold mb-1">Уведомления</h3>
    <p class="mb-0" style="color:var(--text-muted);">{{ auth()->user()->isTeacher() ? 'Студенты и ответы' : 'Новые оценки и комментарии' }}</p></div>
    <form method="POST" action="{{ route('notifications.markRead') }}">@csrf
        <button class="btn btn-outline-secondary btn-sm"><i class="bi bi-check2-all"></i> Прочитать все</button>
    </form>
</div>

<div class="btn-group mb-3" role="group" data-reveal>
    <a href="{{ route('notifications.index') }}" class="btn btn-outline-primary btn-sm {{ request('filter')!=='unread'?'active':'' }}">Все</a>
    <a href="{{ route('notifications.index', ['filter'=>'unread']) }}" class="btn btn-outline-primary btn-sm {{ request('filter')==='unread'?'active':'' }}">Непрочитанные</a>
</div>

<div class="card" data-reveal>
    <div class="card-body p-0">
        @forelse($items as $n)
            <div class="notif-item d-flex gap-3 {{ $n->is_read?'':'unread' }}">
                <span class="fs-4">{{ $n->icon }}</span>
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between">
                        <strong>{{ $n->title }} @if(!$n->is_read)<span class="pulse ms-1"></span>@endif</strong>
                        <span class="small" style="color:var(--text-soft);">{{ $n->created_at->diffForHumans() }}</span>
                    </div>
                    <div class="small mt-1" style="color:var(--text-muted);">{!! $n->text !!}</div>
                </div>
            </div>
        @empty
            <div class="empty-state"><div class="empty-icon"><i class="bi bi-bell-slash"></i></div><p class="mb-0">Нет уведомлений</p></div>
        @endforelse
    </div>
</div>
@endsection
