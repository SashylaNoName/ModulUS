@extends('layouts.guest')
@section('title', 'ModulUS — система учёта модульных работ студентов')

@section('content')
<nav class="navbar navbar-expand-lg sticky-top">
  <div class="container">
    <a class="navbar-brand" href="{{ route('home') }}">Modul<span class="brand-dot">US</span></a>
    <div class="d-flex align-items-center gap-2">
      <button class="theme-toggle" onclick="toggleTheme()" title="Сменить тему"></button>
      <a href="{{ route('login') }}" class="btn btn-outline-primary d-none d-sm-inline-block">Войти</a>
      <a href="{{ route('register') }}" class="btn btn-primary">Начать бесплатно</a>
    </div>
  </div>
</nav>

<header class="hero">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-7" data-reveal="left">
        <span class="pill mb-3"><i class="bi bi-stars"></i> Электронный журнал нового поколения</span>
        <h1>Учёт модульных работ <br><span class="text-gradient">без бумаг и Excel</span></h1>
        <p class="lead mt-4">
          Создавайте таблицы с баллами по своим предметам, добавляйте
          неограниченное количество столбцов, комментируйте работы и
          общайтесь со студентами — в одном месте.
        </p>
        <div class="d-flex flex-wrap gap-3 mt-4">
          <a href="{{ route('register') }}" class="btn btn-primary btn-lg"><i class="bi bi-rocket-takeoff"></i> Начать бесплатно</a>
          <a href="#how" class="btn btn-outline-primary btn-lg">Как это работает</a>
        </div>
        <div class="d-flex flex-wrap gap-4 mt-4 small" style="color:var(--text-muted);">
          <span><i class="bi bi-check-circle-fill text-success"></i> Бесплатно для вузов</span>
          <span><i class="bi bi-check-circle-fill text-success"></i> Импорт и экспорт Excel</span>
          <span><i class="bi bi-check-circle-fill text-success"></i> Уведомления</span>
        </div>
      </div>
      <div class="col-lg-5" data-reveal="right">
        <div class="glass p-3">
          <div class="d-flex gap-2 mb-2">
            <span class="badge badge-group">Программирование · ПИб-231</span>
            <span class="badge badge-role-teacher ms-auto">Преподаватель</span>
          </div>
          <div class="table-responsive">
            <table class="table table-sm table-bordered grade-table mb-0">
              <thead><tr>
                <th>Студент</th>
                <th class="col-module">1</th><th>ЛР</th><th class="col-module">2</th><th class="col-total">Итог</th>
              </tr></thead>
              <tbody>
                <tr><td>Алексеев А.</td><td>23</td><td>5</td><td>25</td><td>92</td></tr>
                <tr><td>Борисова А.</td><td>20</td><td>4</td><td>18</td><td>78</td></tr>
                <tr><td>Волков И.</td><td>25</td><td>5</td><td>26</td><td>95</td></tr>
              </tbody>
            </table>
          </div>
          <div class="d-flex flex-wrap gap-2 mt-2 small" style="color:var(--text-muted);font-size:.72rem;">
            <span><b style="color:#dc2626;">0–49</b> неуд.</span>
            <span><b style="color:#d97706;">50–70</b> удовл.</span>
            <span><b style="color:#2563eb;">71–90</b> хор.</span>
            <span><b style="color:#059669;">91–100</b> отл.</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</header>

<section class="py-5">
  <div class="container py-4">
    <div class="text-center mb-5" data-reveal>
      <span class="pill mb-2">Возможности</span>
      <h2 class="fw-bold">Всё для учёта успеваемости <span class="text-gradient">в одном месте</span></h2>
      <p style="color:var(--text-muted);">Гибкость Excel — без головной боли с таблицами</p>
    </div>
    <div class="row g-4">
      @foreach([
        ['bi-table','Гибкие таблицы баллов','Обязательные столбцы <b>1, 2, 3 модуль</b> и <b>Итог</b> — плюс неограниченное число промежуточных столбцов между ними. Значения числовые или текстовые («зачёт», «присут.»).'],
        ['bi-calculator','Автосуммирование модулей','Преподаватель сам решает: суммировать ли промежуточные баллы в модуль, или нет. Гибкая настройка для каждого столбца.'],
        ['bi-file-earmark-arrow-down','Импорт и экспорт Excel','Загружайте списки студентов или баллы из готовой <b>.xlsx</b>-таблицы и выгружайте журнал в один клик. Работает с любыми Excel-файлами.'],
        ['bi-people-fill','Группы и приглашения','Добавляйте студентов вручную, по ссылке-приглашению или импортом из Excel. Фильтруйте группы по предметам и уровню обучения.'],
        ['bi-chat-dots','Общение со студентами','Комментируйте конкретную оценку — студент видит комментарий и может ответить. Прямо в ячейке таблицы, без отдельного мессенджера.'],
        ['bi-bell','Мгновенные уведомления','Студенты узнают о новых оценках, преподаватель — о том, что к группе присоединился новый участник по ссылке.'],
      ] as $i => [$icon,$title,$text])
        <div class="col-md-6 col-lg-4" data-reveal data-delay="{{ ($i%3)+1 }}">
          <div class="card feature-card">
            <div class="feature-icon"><i class="bi {{ $icon }}"></i></div>
            <h5 class="fw-bold">{{ $title }}</h5>
            <p class="mb-0" style="color:var(--text-muted);">{!! $text !!}</p>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>

<section id="how" class="py-5">
  <div class="container py-4">
    <div class="text-center mb-5" data-reveal>
      <span class="pill mb-2">Просто начать</span>
      <h2 class="fw-bold">Как это работает</h2>
      <p style="color:var(--text-muted);">Отдельно для преподавателей и студентов</p>
    </div>
    <div class="row g-4">
      <div class="col-lg-6" data-reveal="left">
        <div class="glass p-4 h-100">
          <div class="d-flex align-items-center gap-3 mb-4">
            <span class="feature-icon soft mb-0" style="width:50px;height:50px;font-size:1.3rem;">👩‍🏫</span>
            <div><h4 class="fw-bold mb-0">Для преподавателя</h4>
            <small style="color:var(--text-muted);">Создайте журнал за пару минут</small></div>
          </div>
          @foreach(['Зарегистрируйтесь как преподаватель и добавьте предмет.','Создайте группу (например «ПИб-231») и наполните её студентами — вручную, ссылкой или импортом из Excel.','Настройте столбцы: добавьте промежуточные баллы между модулями, решите — суммировать ли их.','Выставляйте баллы и комментируйте работы. Студенты получат уведомления автоматически.'] as $i => $step)
            <div class="d-flex gap-3 align-items-start mb-3">
              <span class="step-num">{{ $i+1 }}</span>
              <div>{!! $step !!}</div>
            </div>
          @endforeach
          <a href="{{ route('register') }}" class="btn btn-primary mt-4 w-100">Создать аккаунт преподавателя</a>
        </div>
      </div>
      <div class="col-lg-6" data-reveal="right">
        <div class="glass p-4 h-100">
          <div class="d-flex align-items-center gap-3 mb-4">
            <span class="feature-icon soft mb-0" style="width:50px;height:50px;font-size:1.3rem;">🎓</span>
            <div><h4 class="fw-bold mb-0">Для студента</h4>
            <small style="color:var(--text-muted);">Всегда в курсе своих оценок</small></div>
          </div>
          @foreach(['Присоединитесь к группе по ссылке-приглашению от преподавателя или зарегистрируйтесь.','Откройте «Мои предметы» — увидите баллы по каждому модулю и итог.','Получайте уведомления о новых оценках и комментариях преподавателя.','Общайтесь с преподавателем: задавайте вопросы по конкретной оценке прямо в диалоге.'] as $i => $step)
            <div class="d-flex gap-3 align-items-start mb-3">
              <span class="step-num">{{ $i+1 }}</span>
              <div>{!! $step !!}</div>
            </div>
          @endforeach
          <a href="{{ route('register') }}" class="btn btn-outline-primary mt-4 w-100">Создать аккаунт студента</a>
        </div>
      </div>
    </div>
  </div>
</section>

<section class="py-5">
  <div class="container">
    <div class="glass p-5 text-center" data-reveal="scale" style="background:var(--grad-brand);color:#fff;">
      <h2 class="fw-bold text-white">Готовы попробовать?</h2>
      <p class="mb-4" style="opacity:.92;">Начните вести электронный журнал уже сегодня — это бесплатно.</p>
      <a href="{{ route('register') }}" class="btn btn-light btn-lg px-4 fw-semibold">Создать аккаунт</a>
      <a href="{{ route('login') }}" class="btn btn-outline-light btn-lg px-4 ms-2">У меня уже есть аккаунт</a>
    </div>
  </div>
</section>

<footer class="py-4 mt-4" style="border-top:1px solid var(--border);">
  <div class="container d-flex flex-wrap justify-content-between align-items-center gap-2">
    <span class="fw-bold">Modul<span class="text-gradient">US</span></span>
    <span class="small" style="color:var(--text-muted);">© {{ date('Y') }} ModulUS — система учёта модульных работ.</span>
  </div>
</footer>
@endsection
