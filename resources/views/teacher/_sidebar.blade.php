@php
    $items = [
        ['dashboard','🏠','Главная', route('teacher.dashboard')],
        ['groups','👥','Мои группы', route('teacher.groups.index')],
        ['subjects','📚','Предметы', route('teacher.groups.index').'#subjects'],
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
