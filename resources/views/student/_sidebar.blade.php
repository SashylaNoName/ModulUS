@php
    $items = [
        ['dashboard','🏠','Главная', route('student.dashboard')],
        ['subjects','📚','Мои предметы', route('student.subjects.index')],
        ['grades','📊','Мои оценки', route('student.grades.index')],
        ['notif','🔔','Уведомления', route('notifications.index')],
    ];
@endphp
@foreach($items as $item)
    <li class="nav-item">
        <a class="nav-link {{ ($sidebarActive ?? '') === $item[0] ? 'active' : '' }}" href="{{ $item[3] }}">
            <span>{{ $item[1] }}</span><span>{{ $item[2] }}</span>
        </a>
    </li>
@endforeach
