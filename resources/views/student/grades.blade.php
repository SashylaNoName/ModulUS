@extends('layouts.app', ['sidebarActive' => 'grades'])
@section('title', 'Мои оценки — ModulUS')

@section('sidebar')@include('student._sidebar')@endsection

@section('content')
<div data-reveal>
    <h3 class="fw-bold mb-1">Мои оценки</h3>
    <p style="color:var(--text-muted);" class="mb-4">Сводная таблица баллов по всем предметам</p>
</div>

<div class="card" data-reveal>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-pretty align-middle mb-0">
                <thead>
                    <tr>
                        <th>Предмет</th>
                        <th class="text-center">1 модуль</th><th class="text-center">2 модуль</th><th class="text-center">3 модуль</th>
                        <th class="text-center">Итоги*</th><th class="text-center">Экзамен</th><th class="text-center">Пересдача</th><th class="text-center">Комиссия</th>
                        <th class="text-center">Оценка (балл)</th><th class="text-center">Оценка</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($groups as $g)
                        @php
                            $val = fn($type) => $g->grades->firstWhere(fn($gr)=>$gr->column && $gr->column->type===$type)?->value;
                            $modules = ['m1'=>null,'m2'=>null,'m3'=>null];
                            foreach($g->grades as $gr){ if($gr->column && $gr->column->type==='module' && !$gr->column->hidden){ $modules['m'.substr($gr->column->title,0,1)] = $gr->value; } }
                            $gradeVal = $val('grade');
                        @endphp
                        <tr>
                            <td><a href="{{ route('student.subject.show', $g) }}" class="fw-semibold">{{ $g->subject?->name }}</a></td>
                            <td class="text-center">{{ $modules['m1'] ?? '—' }}</td>
                            <td class="text-center">{{ $modules['m2'] ?? '—' }}</td>
                            <td class="text-center">{{ $modules['m3'] ?? '—' }}</td>
                            <td class="text-center">{{ $val('total') ?? '—' }}</td>
                            <td class="text-center">{{ $val('exam') ?? '—' }}</td>
                            <td class="text-center">{{ $val('retake') ?? '—' }}</td>
                            <td class="text-center">{{ $val('commission') ?? '—' }}</td>
                            <td class="text-center"><b>{{ $val('score') ?? '—' }}</b></td>
                            <td class="text-center">
                                @if($gradeVal)<span class="badge {{ $gradeVal==='отл'?'badge-role-student':($gradeVal==='удовл'?'badge-role-teacher':'badge-group') }}">{{ $gradeVal }}</span>@else — @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="empty-state">Нет данных</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
