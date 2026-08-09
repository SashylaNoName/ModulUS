@extends('layouts.app', ['sidebarActive' => 'groups'])
@section('title', $group ? 'Редактировать группу' : 'Создать группу')

@section('sidebar')@include('teacher._sidebar')@endsection

@section('content')
<div class="d-flex align-items-center mb-4" data-reveal>
    <a href="{{ route('teacher.groups.index') }}" class="btn btn-light me-2"><i class="bi bi-arrow-left"></i></a>
    <h3 class="fw-bold mb-0">{{ $group ? 'Редактировать группу' : 'Создать группу' }}</h3>
</div>

<div class="row g-4">
    <div class="col-lg-8" data-reveal="left">
        <div class="card">
            <div class="card-body p-4">
                <form method="POST" class="group-form" action="{{ $group ? route('teacher.groups.update', $group) : route('teacher.groups.store') }}">
                    @csrf @if($group) @method('PUT') @endif
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Название группы <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="g-name" class="form-control" placeholder="ПИб-231"
                                   value="{{ old('name', $group?->name) }}" oninput="parseGroupName()" required>
                            <div class="form-text">Формат: <b>спец.</b> + <b>б/м</b> + <b>год</b> + <b>номер</b>. б — бакалавриат, м — магистратура.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Предмет <span class="text-danger">*</span></label>
                            <select name="subject_id" class="form-select" required>
                                @foreach($subjects as $s)
                                    <option value="{{ $s->id }}" @if((string)old('subject_id', $group?->subject_id)===(string)$s->id) selected @endif>{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-4"><label class="form-label small" style="color:var(--text-muted);">Уровень</label><div class="fw-bold text-gradient" id="g-level">@if($group){{ $group->level }}@else—@endif</div></div>
                        <div class="col-md-4"><label class="form-label small" style="color:var(--text-muted);">Год поступления</label><div class="fw-bold text-gradient" id="g-year">@if($group){{ $group->year }}@else—@endif</div></div>
                        <div class="col-md-4"><label class="form-label small" style="color:var(--text-muted);">Номер группы</label><div class="fw-bold text-gradient" id="g-number">@if($group){{ $group->number }}@else—@endif</div></div>
                    </div>

                    @if(!$group)
                    <hr class="divider-gradient my-4">
                    <h6 class="fw-bold">Добавить студентов вручную (необязательно)</h6>
                    <label class="form-label small">Список (ФИО, по одному на строку)</label>
                    <textarea name="students_manual" class="form-control" rows="3" placeholder="Алексеев Артём Дмитриевич&#10;Борисова Анна Сергеевна"></textarea>
                    @endif

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary px-4">Сохранить</button>
                        <a href="{{ route('teacher.groups.index') }}" class="btn btn-light">Отмена</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4" data-reveal="right">
        <div class="glass p-4">
            <h6 class="fw-bold"><i class="bi bi-info-circle text-gradient"></i> Как читать название</h6>
            <ul class="small mb-0 ps-3" style="color:var(--text-muted);">
                <li><b>ПИ</b> — специальность</li>
                <li><b>б / м</b> — бакалавриат / магистратура</li>
                <li><b>23</b> — год поступления (2023)</li>
                <li><b>1 / 2</b> — номер группы</li>
            </ul>
            <div class="mt-3 p-2 rounded" style="background:var(--grad-brand-soft);">
                <small><b>Пример:</b> ПИм-231 → магистратура, набор 2023, 1-я группа.</small>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
function parseGroupName(){
    var raw=document.getElementById('g-name').value.trim();
    var m=raw.match(/^([A-Za-zА-Яа-я]+)([бмБМ])-?(\d{2})(\d)$/);
    if(!m){['g-level','g-year','g-number'].forEach(function(id){document.getElementById(id).textContent='—';});return;}
    document.getElementById('g-level').textContent=m[2].toLowerCase()==='м'?'Магистратура':'Бакалавриат';
    document.getElementById('g-year').textContent='20'+m[3];
    document.getElementById('g-number').textContent=m[4];
}
@endsection
