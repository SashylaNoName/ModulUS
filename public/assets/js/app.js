/* =========================================================
   Тестовые данные (заглушки) и вспомогательные функции
   В будущем всё это будет приходить из Laravel API.
   ========================================================= */

// Текущий «пользователь» — имитация сессии.
// role: 'teacher' | 'student'
const CURRENT_USER = {
    role: 'teacher', // меняется после «входа»
    name: 'Иванова Мария Петровна',
    email: 'ivanova@university.ru',
    avatar: 'ИМ',
};

// ----- Предметы -----
const SUBJECTS = [
    { id: 1, name: 'Программирование' },
    { id: 2, name: 'Высшая математика' },
    { id: 3, name: 'Базы данных' },
    { id: 4, name: 'Английский язык' },
    { id: 5, name: 'Физика' },
];

// ----- Группы -----
// Формат названия: СПЕЦ + (б|м) + ГОД + НОМЕР  → ПИб-231, ПИм-231, ПИб-232 ...
const GROUPS = [
    {
        id: 1, name: 'ПИб-231', subjectId: 1,
        students: 24, level: 'Бакалавриат', year: 2023, number: 1,
        inviteLink: 'https://modulus.ru/join/abc123xyz',
    },
    {
        id: 2, name: 'ПИб-232', subjectId: 1,
        students: 22, level: 'Бакалавриат', year: 2023, number: 2,
        inviteLink: 'https://modulus.ru/join/def456uvw',
    },
    {
        id: 3, name: 'ПИм-231', subjectId: 3,
        students: 12, level: 'Магистратура', year: 2023, number: 1,
        inviteLink: 'https://modulus.ru/join/ghi789rst',
    },
    {
        id: 4, name: 'ПИб-221', subjectId: 2,
        students: 28, level: 'Бакалавриат', year: 2022, number: 1,
        inviteLink: 'https://modulus.ru/join/jkl012qrs',
    },
];

// ----- Студенты в группе -----
const STUDENTS = [
    { id: 1, name: 'Алексеев Артём Дмитриевич', email: 'alexeev@stud.ru', group: 1, avatar: 'АА' },
    { id: 2, name: 'Борисова Анна Сергеевна',   email: 'borisova@stud.ru', group: 1, avatar: 'БА' },
    { id: 3, name: 'Волков Иван Игоревич',      email: 'volkov@stud.ru', group: 1, avatar: 'ВИ' },
    { id: 4, name: 'Григорьева Ольга Павловна', email: 'grigoreva@stud.ru', group: 1, avatar: 'ГО' },
    { id: 5, name: 'Дмитриев Сергей Андреевич', email: 'dmitriev@stud.ru', group: 1, avatar: 'ДС' },
    { id: 6, name: 'Егорова Екатерина Максимовна', email: 'egorova@stud.ru', group: 1, avatar: 'ЕЕ' },
    { id: 7, name: 'Жуков Дмитрий Романович',   email: 'zhukov@stud.ru', group: 1, avatar: 'ЖД' },
    { id: 8, name: 'Зайцева Мария Алексеевна',  email: 'zaytseva@stud.ru', group: 1, avatar: 'ЗМ' },
];

// ----- Структура столбцов таблицы -----
// type: 'intermediate' (промежуточный) | 'module' (1/2/3 модуль)
//        | 'total' (Итоги) | 'exam' (Экзамен) | 'retake' (Пересдача)
//        | 'commission' (Комиссия) | 'score' (Оценка балл) | 'grade' (Оценка)
// position: куда относится — 'before1' (до 1 модуля), 'before2', 'before3'
// sumInto: в какой модуль суммируется (1|2|3|null), null = не суммируется
// hidden: true → столбец скрыт от студентов (виден только преподавателю)
const COLUMN_STRUCTURE = [
    // обязательные модули
    { id: 'm1',  title: '1 модуль',      type: 'module',      position: null, sumInto: null, hidden: false },
    { id: 'm2',  title: '2 модуль',      type: 'module',      position: null, sumInto: null, hidden: false },
    { id: 'm3',  title: '3 модуль',      type: 'module',      position: null, sumInto: null, hidden: false },

    // итоги и экзамены
    { id: 't1',  title: 'Итоги*',        type: 'total',       position: null, sumInto: null, hidden: false },
    { id: 'ex',  title: 'Экзамен',       type: 'exam',        position: null, sumInto: null, hidden: false },
    { id: 'rt',  title: 'Пересдача',     type: 'retake',      position: null, sumInto: null, hidden: true  },
    { id: 'cm',  title: 'Комиссия',      type: 'commission',  position: null, sumInto: null, hidden: true  },

    // финальные оценки
    { id: 'sc',  title: 'Оценка (балл)', type: 'score',       position: null, sumInto: null, hidden: false },
    { id: 'gr',  title: 'Оценка',        type: 'grade',       position: null, sumInto: null, hidden: false },
];

// ----- Оценки студентов -----
// ключ = studentId + '_' + columnId, значение = { value, commentId }
const GRADES = {
    // Алексеев Артём (id 1)
    '1_m1': { value: '23', comment: 1 },
    '1_m2': { value: '25', comment: null },
    '1_m3': { value: '20', comment: null },
    '1_t1': { value: '68', comment: null },
    '1_ex': { value: '24', comment: null },
    '1_rt': { value: '',   comment: null },
    '1_cm': { value: '',   comment: null },
    '1_sc': { value: '92', comment: null },
    '1_gr': { value: 'отл', comment: null },

    // Борисова Анна (id 2)
    '2_m1': { value: '20', comment: null },
    '2_m2': { value: '18', comment: null },
    '2_m3': { value: '22', comment: null },
    '2_t1': { value: '60', comment: null },
    '2_ex': { value: '18', comment: null },
    '2_rt': { value: '',   comment: null },
    '2_cm': { value: '',   comment: null },
    '2_sc': { value: '78', comment: null },
    '2_gr': { value: 'хор', comment: null },

    // Волков Иван (id 3)
    '3_m1': { value: '25', comment: null },
    '3_m2': { value: '26', comment: null },
    '3_m3': { value: '27', comment: null },
    '3_t1': { value: '78', comment: null },
    '3_ex': { value: '17', comment: null },
    '3_rt': { value: '',   comment: null },
    '3_cm': { value: '',   comment: null },
    '3_sc': { value: '95', comment: null },
    '3_gr': { value: 'отл', comment: null },

    // Григорьева Ольга (id 4)
    '4_m1': { value: '12', comment: 2 },
    '4_m2': { value: '10', comment: null },
    '4_m3': { value: '11', comment: null },
    '4_t1': { value: '33', comment: null },
    '4_ex': { value: '15', comment: null },
    '4_rt': { value: '22', comment: null },
    '4_cm': { value: '',   comment: null },
    '4_sc': { value: '55', comment: null },
    '4_gr': { value: 'удовл', comment: null },
};

// ----- Комментарии (диалог преподаватель ↔ студент) -----
// Сообщение может содержать: text, image (url фото), file (имя файла)
const COMMENTS = [
    {
        id: 1, cellKey: '1_m1',
        thread: [
            { author: 'teacher', name: 'Иванова М. П.', text: 'Отличная работа по первому модулю! Разобрался с рекурсией лучше всех.', time: '12 окт, 10:30' },
            { author: 'student', name: 'Алексеев Артём', text: 'Спасибо! Прикрепляю решение доп. задачи.', time: '12 окт, 14:15', image: 'https://placehold.co/300x200/2563eb/ffffff?text=%D0%A0%D0%B5%D1%88%D0%B5%D0%BD%D0%B8%D0%B5' },
            { author: 'teacher', name: 'Иванова М. П.', text: 'Методичка по теме — во вложении.', time: '12 окт, 15:02', file: 'metodichka_rekursia.pdf' },
        ],
    },
    {
        id: 2, cellKey: '4_m1',
        thread: [
            { author: 'teacher', name: 'Иванова М. П.', text: 'Много ошибок в коде. Подойдите на консультацию.', time: '11 окт, 16:20' },
            { author: 'student', name: 'Григорьева Ольга', text: 'Подойду в четверг на 2-й паре.', time: '11 окт, 18:45' },
        ],
    },
];

// ----- Уведомления -----
const NOTIFICATIONS_TEACHER = [
    { id: 1, type: 'join', icon: '👤', title: 'Новый студент в группе', text: '<b>Кузнецов Павел</b> присоединился к группе <b>ПИб-231</b> по ссылке-приглашению.', time: '5 мин назад', unread: true },
    { id: 2, type: 'reply', icon: '💬', title: 'Ответ студента', text: '<b>Григорьева Ольга</b> ответила на ваш комментарий по <b>ЛР 1</b>.', time: '1 час назад', unread: true },
    { id: 3, type: 'join', icon: '👤', title: 'Новый студент в группе', text: '<b>Смирнова Дарья</b> присоединилась к группе <b>ПИм-231</b>.', time: 'вчера', unread: false },
];

const NOTIFICATIONS_STUDENT = [
    { id: 1, type: 'grade', icon: '📊', title: 'Поставлена оценка', text: '<b>Иванова М. П.</b> поставила вам оценку <b>«отл»</b> за <b>Итог</b> (Программирование).', time: '10 мин назад', unread: true },
    { id: 2, type: 'comment', icon: '💬', title: 'Новый комментарий', text: '<b>Иванова М. П.</b> оставила комментарий к <b>ЛР 1</b>.', time: '2 часа назад', unread: true },
    { id: 3, type: 'grade', icon: '📊', title: 'Поставлена оценка', text: 'Получен <b>25</b> за <b>2 модуль</b> (Программирование).', time: 'вчера', unread: false },
];

/* =========================================================
   Хелперы + дизайн-система (тема, анимации)
   ========================================================= */

function $(sel, root = document) { return root.querySelector(sel); }
function $all(sel, root = document) { return [...root.querySelectorAll(sel)]; }

function getSubject(id) { return SUBJECTS.find(s => s.id === Number(id)); }
function getGroup(id)   { return GROUPS.find(g => g.id === Number(id)); }
function getCommentByCell(cellKey) { return COMMENTS.find(c => c.cellKey === cellKey); }

/* Рендер одного сообщения чата: текст + (опц.) фото + (опц.) файл */
function renderMessage(m) {
    let body = `<div class="d-flex justify-content-between">
        <strong class="small">${m.name}</strong>
        <span style="color:var(--text-soft);font-size:.7rem;">${m.time||''}</span>
    </div>`;
    if (m.text) body += `<div class="small mt-1">${m.text}</div>`;
    if (m.image) body += `<img src="${m.image}" alt="фото" class="img-fluid rounded mt-2" style="max-height:220px;cursor:pointer;" onclick="window.open('${m.image}','_blank')">`;
    if (m.file)  body += `<a href="#" onclick="return false;" class="d-inline-flex align-items-center gap-2 mt-2 small" style="color:var(--brand-1);">
        <i class="bi bi-paperclip"></i> ${m.file}</a>`;
    return body;
}
/* Рендер всей ветки диалога по cellKey */
function renderThread(cellKey) {
    const t = getCommentByCell(cellKey);
    if (!t) return '<p class="small" style="color:var(--text-muted);">Комментариев пока нет.</p>';
    return '<div class="comment-thread">' + t.thread.map(m =>
        `<div class="comment-bubble ${m.author==='teacher'?'teacher':''}">${renderMessage(m)}</div>`).join('') + '</div>';
}

// Инициалы из ФИО
function initials(full) {
    return full.split(' ').filter(Boolean).slice(0, 2).map(w => w[0]).join('').toUpperCase();
}

/* =========================================================
   Тема: сохраняем выбор в localStorage, применяем до рендера
   ========================================================= */
function getTheme() { return localStorage.getItem('modulus-theme') || 'light'; }
function setTheme(t) {
    localStorage.setItem('modulus-theme', t);
    document.documentElement.setAttribute('data-theme', t);
    document.querySelectorAll('.theme-toggle').forEach(b => {
        b.textContent = t === 'dark' ? '☀️' : '🌙';
        b.title = t === 'dark' ? 'Светлая тема' : 'Тёмная тема';
    });
}
function toggleTheme() {
    setTheme(getTheme() === 'dark' ? 'light' : 'dark');
}

/* Роль текущего пользователя — храним в sessionStorage, чтобы общие страницы
   (уведомления) знали, кто зашёл, и не показывали чужой интерфейс. */
function saveRole(role) { sessionStorage.setItem('modulus-role', role); }
function savedRole() { return sessionStorage.getItem('modulus-role') || 'teacher'; }
// применить как можно раньше — вызывается в <head> см. ниже initThemeEarly()

/* =========================================================
   Кнопка темы (HTML) — вставляется в шапку/лендинг
   ========================================================= */
function themeToggleBtn(extraClass = '') {
    return `<button class="theme-toggle ${extraClass}" onclick="toggleTheme()" title="Сменить тему"></button>`;
}
// заполнить иконкой все существующие кнопки темы на странице
function paintThemeButtons() {
    const t = getTheme();
    document.querySelectorAll('.theme-toggle').forEach(b => {
        b.textContent = t === 'dark' ? '☀️' : '🌙';
    });
}

/* =========================================================
   Общий header для «кабинета» (после входа)
   ========================================================= */
function renderHeader() {
    const user = CURRENT_USER;
    const roleBadge = user.role === 'teacher'
        ? '<span class="badge badge-role-teacher">Преподаватель</span>'
        : '<span class="badge badge-role-student">Студент</span>';
    const homeLink = user.role === 'teacher' ? 'teacher-dashboard.html' : 'student-dashboard.html';

    return `
    <nav class="navbar navbar-expand-lg sticky-top">
      <div class="container-fluid px-3">
        <button class="btn btn-light d-lg-none me-2" type="button"
                data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
          <i class="bi bi-list"></i>
        </button>
        <a class="navbar-brand" href="${homeLink}">
          Modul<span class="brand-dot">US</span>
        </a>
        <div class="d-flex align-items-center gap-2 ms-auto">
          ${themeToggleBtn()}
          <!-- Уведомления -->
          <div class="dropdown">
            <button class="icon-btn position-relative" data-bs-toggle="dropdown" title="Уведомления" aria-label="Уведомления">
              <i class="bi bi-bell"></i><span class="notif-dot"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end p-0" style="width:min(360px, calc(100vw - 1.5rem));max-width:min(360px, calc(100vw - 1.5rem));">
              <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                <strong>Уведомления</strong>
                <a href="notifications.html" class="small">Все</a>
              </div>
              <div id="notif-list"></div>
            </div>
          </div>
          <!-- Профиль -->
          <div class="dropdown">
            <button class="btn d-flex align-items-center gap-2 p-1" data-bs-toggle="dropdown">
              <span class="avatar">${user.avatar}</span>
              <span class="text-start d-none d-md-block lh-1">
                <span class="d-block fw-semibold user-name" style="font-size:.9rem;">${user.name}</span>
                ${roleBadge}
              </span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="${homeLink}">🏠 Главная</a></li>
              <li><a class="dropdown-item" href="notifications.html"><i class="bi bi-bell me-1"></i> Уведомления</a></li>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item text-danger" href="index.html">🚪 Выйти</a></li>
            </ul>
          </div>
        </div>
      </div>
    </nav>`;
}

function mountHeader() {
    const el = document.getElementById('app-header');
    if (el) el.innerHTML = renderHeader();
    paintThemeButtons();
    // превью уведомлений
    const list = document.getElementById('notif-list');
    if (list) {
        const items = CURRENT_USER.role === 'teacher' ? NOTIFICATIONS_TEACHER : NOTIFICATIONS_STUDENT;
        list.innerHTML = items.slice(0, 3).map(n => `
          <a href="notifications.html" class="notif-item d-block text-decoration-none ${n.unread ? 'unread' : ''}" style="color:var(--text);">
            <div class="d-flex gap-2">
              <span class="fs-5">${n.icon}</span>
              <div class="flex-grow-1">
                <div class="fw-semibold small">${n.title}</div>
                <div class="small" style="color:var(--text-muted);">${n.text}</div>
                <div style="color:var(--text-soft);font-size:.72rem;">${n.time}</div>
              </div>
            </div>
          </a>`).join('');
    }
}

/* =========================================================
   Сайдбар: десктоп (в сетке) + мобильный offcanvas
   ========================================================= */
function renderTeacherSidebar(active) {
    const items = [
        { id: 'dashboard', icon: '🏠', label: 'Главная',    href: 'teacher-dashboard.html' },
        { id: 'groups',    icon: '👥', label: 'Мои группы', href: 'groups.html' },
        { id: 'subjects',  icon: '📚', label: 'Предметы',   href: 'subjects.html' },
        { id: 'notif',     icon: '🔔', label: 'Уведомления',href: 'notifications.html' },
    ];
    return sidebarHtml(items, active);
}
function renderStudentSidebar(active) {
    const items = [
        { id: 'dashboard', icon: '🏠', label: 'Главная',     href: 'student-dashboard.html' },
        { id: 'subjects',  icon: '📚', label: 'Мои предметы',href: 'student-subjects.html' },
        { id: 'grades',    icon: '📊', label: 'Мои оценки',  href: 'student-grades.html' },
        { id: 'notif',     icon: '🔔', label: 'Уведомления', href: 'notifications.html' },
    ];
    return sidebarHtml(items, active);
}
function sidebarHtml(items, active) {
    const links = items.map(i => `
      <li class="nav-item">
        <a class="nav-link ${active === i.id ? 'active' : ''}" href="${i.href}">
          <span>${i.icon}</span><span>${i.label}</span>
        </a>
      </li>`).join('');

    // десктопный сайдбар
    const desktop = `<aside class="sidebar p-2 d-none d-lg-block">
      <ul class="nav flex-column">${links}</ul>
    </aside>`;

    // мобильный offcanvas (тот же набор пунктов)
    const mobile = `
    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileSidebar">
      <div class="offcanvas-header">
        <h5 class="offcanvas-title">Меню</h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
      </div>
      <div class="offcanvas-body p-0">
        <ul class="nav flex-column">${links}</ul>
      </div>
    </div>`;

    return desktop + mobile;
}

/* =========================================================
   Карточка статистики (для дашбордов)
   ========================================================= */
function statCard(icon, value, label, delay = 0) {
    return `<div class="col-md-3 col-6" data-reveal data-delay="${delay}">
      <div class="glass p-3 d-flex align-items-center gap-3 h-100">
        <span class="feature-icon soft mb-0" style="width:48px;height:48px;font-size:1.2rem;">${icon}</span>
        <div>
          <div class="fs-3 fw-bold lh-1 text-gradient">${value}</div>
          <small style="color:var(--text-muted);">${label}</small>
        </div>
      </div>
    </div>`;
}

/* =========================================================
   Toast + демо-действия
   ========================================================= */
function toast(msg, type = 'success') {
    const bg = type === 'error'   ? 'linear-gradient(135deg,#ef4444,#b91c1c)'
             : type === 'info'    ? 'linear-gradient(135deg,#6366f1,#8b5cf6)'
             :                       'linear-gradient(135deg,#10b981,#059669)';
    const t = document.createElement('div');
    t.style.cssText = `position:fixed;bottom:24px;right:24px;background:${bg};color:#fff;
        padding:.9rem 1.3rem;border-radius:.8rem;z-index:9999;box-shadow:0 12px 30px rgba(0,0,0,.25);
        font-weight:600;max-width:360px;opacity:0;transform:translateY(20px);
        transition:opacity .3s,transform .3s;`;
    t.textContent = msg;
    document.body.appendChild(t);
    requestAnimationFrame(() => { t.style.opacity = '1'; t.style.transform = 'none'; });
    setTimeout(() => { t.style.opacity = '0'; t.style.transform = 'translateY(20px)'; }, 2600);
    setTimeout(() => t.remove(), 3000);
}
function demoAction(action) {
    toast('Демо-режим: действие «' + action + '» сохранится в Laravel.', 'info');
}

/* =========================================================
   Анимации появления при скролле (data-reveal)
   ========================================================= */
function initReveal() {
    const els = $all('[data-reveal]');
    if (!els.length) return;
    if (!('IntersectionObserver' in window)) {
        els.forEach(e => e.classList.add('in-view'));
        return;
    }
    const io = new IntersectionObserver((entries) => {
        entries.forEach(en => {
            if (en.isIntersecting) {
                en.target.classList.add('in-view');
                io.unobserve(en.target);
            }
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
    els.forEach(e => io.observe(e));
}

/* =========================================================
   Инициализация страницы (вызывается в конце каждой страницы)
   ========================================================= */
function initPage() {
    paintThemeButtons();
    initReveal();
}

/* Показ выбранного вложения (фото-превью или имя файла) + крестик удаления */
function attachInfo(input){
    var box = input.closest('form') && input.closest('form').querySelector('.attach-info');
    if (!box) return;
    box.innerHTML = '';
    if (! (input.files && input.files[0])) return;

    var f = input.files[0];
    var isImg = /^image\//.test(f.type) && input.accept && input.accept.indexOf('image') === 0;

    var wrap = document.createElement('div');
    wrap.style.cssText = 'display:inline-flex;align-items:flex-start;gap:8px;margin-top:6px;';

    var body = document.createElement('div');
    var name = document.createElement('div');
    name.textContent = (isImg ? '🖼 ' : '📎 ') + f.name;
    name.style.cssText = 'color:var(--brand-1);word-break:break-all;';
    body.appendChild(name);
    if (isImg) {
        var img = document.createElement('img');
        img.src = URL.createObjectURL(f);
        img.style.cssText = 'max-height:90px;border-radius:8px;display:block;margin-top:6px;';
        body.appendChild(img);
    }
    wrap.appendChild(body);

    // крестик: убрать вложение
    var x = document.createElement('button');
    x.type = 'button';
    x.innerHTML = '&times;';
    x.title = 'Убрать вложение';
    x.style.cssText = 'border:none;background:var(--surface-solid);color:var(--danger);font-size:1.2rem;line-height:1;padding:2px 8px;border-radius:8px;cursor:pointer;';
    x.onclick = function(){
        input.value = '';          // снять выбор файла
        box.innerHTML = '';
    };
    wrap.appendChild(x);

    box.appendChild(wrap);
}
