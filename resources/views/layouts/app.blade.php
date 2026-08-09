@php
    $user = auth()->user();
    $isTeacher = $user && $user->isTeacher();
    $homeRoute = $isTeacher ? route('teacher.dashboard') : route('student.dashboard');
    $unread = $user ? $user->notifications()->unread()->count() : 0;
    $recentNotifs = $user ? $user->notifications()->latest()->limit(3)->get() : collect();
@endphp
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'ModulUS')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="{{ asset('assets/css/style.css') }}" rel="stylesheet">
    <script>(function(){var t=localStorage.getItem('modulus-theme')||'light';document.documentElement.setAttribute('data-theme',t);})();</script>
</head>
<body>
<div id="app-header">
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container-fluid px-3">
            <button class="btn btn-light d-lg-none me-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
                <i class="bi bi-list"></i>
            </button>
            <a class="navbar-brand" href="{{ $homeRoute }}">Modul<span class="brand-dot">US</span></a>
            <div class="d-flex align-items-center gap-2 ms-auto">
                {{-- Переключатель темы --}}
                <button class="theme-toggle" onclick="toggleTheme()" title="Сменить тему"></button>
                {{-- Уведомления --}}
                <div class="dropdown">
                    <button class="icon-btn position-relative" data-bs-toggle="dropdown" title="Уведомления" aria-label="Уведомления">
                        <i class="bi bi-bell"></i>
                        @if($unread > 0)<span class="notif-dot"></span>@endif
                    </button>
                    <div class="dropdown-menu dropdown-menu-end p-0" style="width:min(360px, calc(100vw - 1.5rem));max-width:min(360px, calc(100vw - 1.5rem));">
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom" style="border-color:var(--border) !important;">
                            <strong>Уведомления</strong>
                            <a href="{{ route('notifications.index') }}" class="small">Все</a>
                        </div>
                        @forelse($recentNotifs as $n)
                            <a href="{{ route('notifications.index') }}" class="notif-item d-block text-decoration-none {{ $n->is_read ? '' : 'unread' }}" style="color:var(--text);">
                                <div class="d-flex gap-2">
                                    <span class="fs-5">{{ $n->icon }}</span>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold small">{{ $n->title }}</div>
                                        <div class="small" style="color:var(--text-muted);">{!! $n->text !!}</div>
                                        <div style="color:var(--text-soft);font-size:.72rem;">{{ $n->created_at->diffForHumans() }}</div>
                                    </div>
                                </div>
                            </a>
                        @empty
                            <div class="p-3 small" style="color:var(--text-muted);">Нет уведомлений</div>
                        @endforelse
                    </div>
                </div>
                {{-- Профиль --}}
                <div class="dropdown">
                    <button class="btn d-flex align-items-center gap-2 p-1" data-bs-toggle="dropdown">
                        <span class="avatar">{{ $user?->initials() }}</span>
                        <span class="text-start d-none d-md-block lh-1">
                            <span class="d-block fw-semibold user-name" style="font-size:.9rem;">{{ $user?->name }}</span>
                            <span class="badge {{ $isTeacher ? 'badge-role-teacher' : 'badge-role-student' }}">{{ $isTeacher ? 'Преподаватель' : 'Студент' }}</span>
                        </span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ $homeRoute }}">🏠 Главная</a></li>
                        <li><a class="dropdown-item" href="{{ route('notifications.index') }}"><i class="bi bi-bell me-1"></i> Уведомления</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger">🚪 Выйти</button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>
</div>

<div class="container-fluid">
    <div class="row g-0">
        <div class="col-lg-2 col-md-3">
            {{-- Десктопный сайдбар --}}
            <aside class="sidebar p-2 d-none d-lg-block">
                <ul class="nav flex-column">
                    @yield('sidebar')
                </ul>
            </aside>
            {{-- Мобильный offcanvas --}}
            <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title">Меню</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
                </div>
                <div class="offcanvas-body p-0">
                    <ul class="nav flex-column">
                        @yield('sidebar')
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-lg-10 col-md-9 p-3 p-md-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="{{ asset('assets/js/app.js') }}"></script>
<script>
    // polling уведомлений (раз в 60 сек)
    setInterval(function(){
        fetch('{{ route("notifications.api.unread") }}')
            .then(r => r.json())
            .then(d => {
                document.querySelectorAll('.notif-dot').forEach(el => {
                    el.style.display = d.count > 0 ? '' : 'none';
                });
            }).catch(()=>{});
    }, 60000);
    initPage();
    @yield('scripts')
</script>
</body>
</html>
