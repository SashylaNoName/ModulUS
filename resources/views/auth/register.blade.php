@extends('layouts.guest')
@section('title', 'Регистрация — ModulUS')

@section('content')
<nav class="navbar sticky-top">
  <div class="container">
    <a class="navbar-brand" href="{{ route('home') }}">Modul<span class="brand-dot">US</span></a>
    <div class="d-flex align-items-center gap-2">
      <button class="theme-toggle" onclick="toggleTheme()" title="Сменить тему"></button>
      <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm d-none d-sm-inline-block">Войти</a>
    </div>
  </div>
</nav>

<div class="container" style="padding:2rem 0 3rem;">
  <div class="glass w-100 mx-auto" style="max-width:560px;padding:2.5rem;" data-reveal="scale">
    <h3 class="fw-bold text-center mb-1">Создать аккаунт</h3>
    <p style="color:var(--text-muted);" class="text-center mb-4">Это бесплатно — займёт меньше минуты</p>

    @if ($errors->any())
      <div class="alert alert-danger small py-2">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('register') }}">
      @csrf
      @if($invited ?? false)
        {{-- по ссылке-приглашению — только студент --}}
        <input type="hidden" name="role" value="student">
        <div class="alert alert-info small mb-4">Вы присоединяетесь к группе по приглашению — регистрация доступна только для студентов.</div>
      @else
      <label class="form-label fw-semibold">Я регистрируюсь как:</label>
      <div class="row g-2 mb-4">
        <div class="col-6">
          <label class="role-pick d-block text-center">
            <input type="radio" name="role" value="teacher" class="d-none" checked onchange="switchRole()">
            <div class="fs-3">👩‍🏫</div><div class="fw-semibold">Преподаватель</div>
            <small style="color:var(--text-muted);">Создаю журнал</small>
          </label>
        </div>
        <div class="col-6">
          <label class="role-pick d-block text-center">
            <input type="radio" name="role" value="student" class="d-none" onchange="switchRole()">
            <div class="fs-3">🎓</div><div class="fw-semibold">Студент</div>
            <small style="color:var(--text-muted);">Смотрю оценки</small>
          </label>
        </div>
      </div>
      @endif

      <div id="teacher-fields" @if($invited ?? false) style="display:none" @endif>
        <div class="row g-2">
          <div class="col-md-6 mb-3">
            <label class="form-label">ФИО</label>
            <input type="text" name="name" class="form-control" placeholder="Иванова Мария Петровна" value="{{ old('name') }}" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Кафедра</label>
            <input type="text" name="department" class="form-control" placeholder="Информационных технологий">
          </div>
        </div>
      </div>
      <div id="student-fields" style="display:none;">
        <div class="row g-2">
          <div class="col-md-6 mb-3">
            <label class="form-label">ФИО</label>
            <input type="text" name="name" class="form-control" placeholder="Алексеев Артём Дмитриевич" value="{{ old('name') }}" required>
          </div>
          <div class="col-md-6 mb-3">
            <label class="form-label">Группа</label>
            <input type="text" name="student_group" class="form-control" placeholder="ПИб-231">
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Код приглашения (если есть)</label>
          <input type="text" name="invite_code" class="form-control" placeholder="например, abc123xyz" value="{{ old('invite_code', session('invite_token')) }}">
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Email</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
          <input type="email" name="email" class="form-control" placeholder="you@university.ru" value="{{ old('email') }}" required>
        </div>
      </div>
      <div class="row g-2">
        <div class="col-md-6 mb-3">
          <label class="form-label">Пароль</label>
          <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Повтор пароля</label>
          <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••" required>
        </div>
      </div>

      <div class="form-check mb-4">
        <input class="form-check-input" type="checkbox" id="agree" required>
        <label class="form-check-label small" for="agree">Согласен с условиями и политикой конфиденциальности</label>
      </div>
      <button type="submit" class="btn btn-primary w-100 py-2">Зарегистрироваться</button>
    </form>

    <hr class="my-4">
    <div class="text-center small" style="color:var(--text-muted);">
      Уже есть аккаунт? <a href="{{ route('login') }}">Войти</a>
    </div>
  </div>
</div>
@endsection

@section('scripts')
function switchRole(){
  const t=document.querySelector('input[name=role]:checked').value==='teacher';
  toggleBlock('teacher-fields', t);
  toggleBlock('student-fields', !t);
}
// скрытый блок отключаем целиком: disabled-поля не проходят required-валидацию
// (иначе браузер молча блокирует отправку) и не отправляются на сервер
function toggleBlock(id, show){
  var el=document.getElementById(id);
  el.style.display=show?'':'none';
  el.querySelectorAll('input,select,textarea').forEach(function(inp){ inp.disabled=!show; });
}
// при загрузке — применить к скрытому блоку
@if($invited ?? false)
toggleBlock('teacher-fields', false);
toggleBlock('student-fields', true);
@else
toggleBlock('student-fields', false);
@endif
@endsection
