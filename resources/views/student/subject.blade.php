@extends('layouts.app', ['sidebarActive' => 'subjects'])
@section('title', $group->subject?->name)

@section('sidebar')@include('student._sidebar')@endsection

@section('content')
<div class="d-flex align-items-center mb-3" data-reveal>
    <a href="{{ route('student.subjects.index') }}" class="btn btn-light me-2"><i class="bi bi-arrow-left"></i></a>
    <div><h3 class="fw-bold mb-0">{{ $group->subject?->name }}</h3>
    <span class="small" style="color:var(--text-muted);">{{ $group->teacher?->name }} · Группа {{ $group->name }}</span></div>
</div>

<div class="card mb-4" data-reveal>
    <div class="card-header bg-transparent d-flex justify-content-between align-items-center" style="border-color:var(--border);">
        <strong>Мои баллы</strong>
        <span class="small" style="color:var(--text-muted);">Кликните по ячейке, чтобы открыть диалог</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered grade-table mb-0 align-middle">
                <thead>
                    <tr>
                        <th class="sticky-column" style="min-width:140px;left:0;">Вид контроля</th>
                        @foreach($columns as $c)
                            <th class="{{ $c->type==='module'?'col-module':'' }} {{ $c->type==='total'?'col-total':'' }}">{{ $c->title }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="sticky-column fw-semibold" style="min-width:140px;left:0;">Балл</td>
                        @foreach($columns as $c)
                            @php
                                $grade = $grades[$c->id] ?? null;
                                $hasThread = $grade && $grade->comments->isNotEmpty();
                            @endphp
                            <td class="cell-grade cursor-pointer {{ $c->type==='module'?'col-module':'' }} {{ $c->type==='total'?'col-total':'' }}">
                                @if($grade)
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <b>{{ $grade->value ?: '—' }}</b>
                                        @if($hasThread)
                                            <button class="btn btn-sm btn-link p-0" data-bs-toggle="modal" data-bs-target="#chatModal" data-grade-id="{{ $grade->id }}" data-context="{{ $c->title }}"><i class="bi bi-chat-dots-fill" style="color:var(--brand-1);font-size:.8rem;"></i></button>
                                        @else
                                            <button class="btn btn-sm btn-link p-0" data-bs-toggle="modal" data-bs-target="#chatModal" data-grade-id="{{ $grade->id }}" data-context="{{ $c->title }}"><i class="bi bi-chat" style="color:var(--text-soft);font-size:.8rem;"></i></button>
                                        @endif
                                    </div>
                                @else
                                    <span style="color:var(--text-soft);">—</span>
                                @endif
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card" data-reveal>
    <div class="card-header bg-transparent" style="border-color:var(--border);"><strong>Диалоги с преподавателем</strong></div>
    <div class="card-body">
        @php
            $threads = $grades->filter(fn($g)=>$g->comments->isNotEmpty());
        @endphp
        @forelse($threads as $grade)
            <div class="mb-3">
                <div class="small mb-1" style="color:var(--text-muted);">{{ $grade->column->title }}</div>
                <div class="comment-thread">
                    @foreach($grade->comments as $c)
                        <div class="comment-bubble {{ $c->author->role==='teacher'?'teacher':'' }}">
                            <div class="d-flex justify-content-between">
                                <strong class="small">{{ $c->author->name }}</strong>
                                <span style="color:var(--text-soft);font-size:.7rem;">{{ $c->created_at->diffForHumans() }}</span>
                            </div>
                            @if($c->text)<div class="small mt-1">{{ $c->text }}</div>@endif
                            @if($c->image)<img src="{{ asset('storage/'.$c->image) }}" class="img-fluid rounded mt-2" style="max-height:200px;">@endif
                            @if($c->file)<a href="{{ asset('storage/'.$c->file) }}" target="_blank" class="d-inline-flex align-items-center gap-1 mt-2 small" style="color:var(--brand-1);"><i class="bi bi-paperclip"></i> {{ basename($c->file) }}</a>@endif
                        </div>
                    @endforeach
                </div>
                <button class="btn btn-sm btn-outline-primary mt-1" data-bs-toggle="modal" data-bs-target="#chatModal" data-grade-id="{{ $grade->id }}" data-context="{{ $grade->column->title }}"><i class="bi bi-reply"></i> Ответить</button>
            </div>
        @empty
            <div class="empty-state"><div class="empty-icon"><i class="bi bi-chat"></i></div><p class="mb-0">Диалогов пока нет</p></div>
        @endforelse
    </div>
</div>

{{-- Чат --}}
<div class="modal fade" id="chatModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content">
    <div class="modal-header"><h5 class="modal-title"><i class="bi bi-chat-dots text-gradient"></i> Диалог по оценке</h5><button class="btn-close" data-bs-dismiss="modal"></button></div>
    <div class="modal-body">
        <div class="small mb-2" style="color:var(--text-muted);" id="chat-context"></div>
        <div class="mb-3">
            <form method="POST" id="chatForm" enctype="multipart/form-data">@csrf
                <input type="hidden" name="grade_id" id="chat-grade-id">
                <textarea class="form-control mb-2" rows="2" name="text" placeholder="Ваше сообщение..."></textarea>
                <div class="d-flex gap-2">
                    <label class="btn btn-light"><i class="bi bi-paperclip"></i><input type="file" name="file" hidden></label>
                    <label class="btn btn-light"><i class="bi bi-image"></i><input type="file" name="image" accept="image/*" hidden></label>
                    <button class="btn btn-primary ms-auto"><i class="bi bi-send"></i> Отправить</button>
                </div>
            </form>
        </div>
    </div>
</div></div></div>
@endsection

@section('scripts')
document.getElementById('chatModal').addEventListener('show.bs.modal', function(e){
    const btn = e.relatedTarget;
    document.getElementById('chat-context').textContent = 'Столбец: ' + btn.dataset.context;
    document.getElementById('chat-grade-id').value = btn.dataset.gradeId;
    document.getElementById('chatForm').action = '{{ url('/grades') }}/' + btn.dataset.gradeId + '/comments';
});
@endsection
