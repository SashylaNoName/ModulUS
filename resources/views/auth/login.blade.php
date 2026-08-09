@extends('layouts.guest')
@section('title', 'Вход — ModulUS')

@section('content')
<nav class="navbar sticky-top">
  <div class="container">
    <a class="navbar-brand" href="{{ route('home') }}">Modul<span class="brand-dot">US</span></a>
    <div class="d-flex align-items-center gap-2">
      <button class="theme-toggle" onclick="toggleTheme()" title="Сменить тему"></button>
      <a href="{{ route('register') }}" class="btn btn-outline-primary btn-sm d-none d-sm-inline-block">Регистрация</a>
    </div>
  </div>
</nav>

<div class="container" style="min-height:calc(100vh - 70px);display:flex;align-items:center;padding:2rem 0;">
  <div class="glass w-100 mx-auto" style="max-width:460px;padding:2.5rem;" data-reveal="scale">
    <h3 class="fw-bold text-center mb-1">С возвращением 👋</h3>
    <p style="color:var(--text-muted);" class="text-center mb-4">Войдите в свой аккаунт ModulUS</p>

    @if ($errors->any())
      <div class="alert alert-danger small py-2">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
      @csrf
      <label class="form-label fw-semibold">Я захожу как:</label>
      <div class="row g-2 mb-4">
        <div class="col-6">
          <label class="role-pick d-block text-center">
            <input type="radio" name="role" value="teacher" class="d-none" checked>
            <div class="fs-3">👩‍🏫</div>
            <div class="fw-semibold">Преподаватель</div>
            <small style="color:var(--text-muted);">Журнал, группы</small>
          </label>
        </div>
        <div class="col-6">
          <label class="role-pick d-block text-center">
            <input type="radio" name="role" value="student" class="d-none">
            <div class="fs-3">🎓</div>
            <div class="fw-semibold">Студент</div>
            <small style="color:var(--text-muted);">Оценки, предметы</small>
          </label>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Email</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
          <input type="email" name="email" class="form-control" placeholder="you@university.ru" value="{{ old('email') }}" required>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label">Пароль</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" name="password" id="pass" class="form-control" placeholder="••••••••" required>
          <button class="btn btn-outline-secondary" type="button" onclick="togglePass(this)"><i class="bi bi-eye"></i></button>
        </div>
      </div>
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="form-check">
          <input class="form-check-input" type="checkbox" name="remember" id="remember" checked>
          <label class="form-check-label small" for="remember">Запомнить меня</label>
        </div>
      </div>
      <button type="submit" class="btn btn-primary w-100 py-2">Войти</button>
    </form>

    <hr class="my-4">
    <div class="text-center small" style="color:var(--text-muted);">
      Нет аккаунта? <a href="{{ route('register') }}">Зарегистрироваться</a>
    </div>
  </div>
</div>
@endsection

@section('scripts')
function togglePass(btn){
  const i=document.getElementById('pass'),ic=btn.querySelector('i');
  if(i.type==='password'){i.type='text';ic.className='bi bi-eye-slash';}else{i.type='password';ic.className='bi bi-eye';}
}
@endsection
