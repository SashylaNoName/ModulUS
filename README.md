# ModulUS — система учёта модульных работ студентов

Веб-приложение на **Laravel 11 + Bootstrap 5**: электронный журнал баллов по
предметам с настраиваемыми столбцами, общением преподаватель↔студент и
уведомлениями.

## Возможности

- 🔐 **Регистрация и вход** с выбором роли (преподаватель / студент).
- 👩‍🏫 **Кабинет преподавателя:**
  - CRUD групп, парсинг названия «ПИб-231» (б/м, год, номер).
  - Журнал оценок: обязательные столбцы 1/2/3 модуль, Итоги*, Экзамен,
    Пересдача, Комиссия, Оценка (балл), Оценка + промежуточные.
  - Скрытие столбцов от студентов, сохранение оценок (AJAX) + уведомление.
  - Импорт/экспорт Excel (maatwebsite/excel), добавление студентов
    вручную / по ссылке-приглашению / импортом.
  - Чат к каждой оценке с фото и файлами.
- 🎓 **Кабинет студента:** предметы, горизонтальная таблица баллов
  (только нескрытые столбцы), диалоги с преподавателем.
- 🔔 **Уведомления** (polling): студенту о баллах, преподавателю о вступлении.

## Запуск (локально)

### 1. Окружение
- PHP ≥ 8.2, Composer
- MySQL (достаточно MySQL из OpenServer)

### 2. База данных
В OpenServer создайте пустую БД `modulus` (utf8mb4_unicode_ci).
Либо настройте свою в `.env`.

### 3. Установка
```bash
composer install
cp .env.example .env       # или отредактируйте существующий .env
php artisan key:generate
```

### 4. `.env` — подключение к MySQL OpenServer
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=modulus
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Миграции + тестовые данные
```bash
php artisan migrate --seed
php artisan storage:link
```

### 6. Запуск
```bash
php artisan serve
```
Откройте http://localhost:8000

## Тестовые аккаунты (после seed)

| Роль          | Email                     | Пароль    |
|---------------|---------------------------|-----------|
| Преподаватель | ivanova@university.ru     | password  |
| Студент       | alexeev@stud.ru           | password  |

## Структура

```
app/
├── Http/Controllers/   Auth, Teacher*, Student*, Group, Gradebook,
│                       Column, Grade, Comment, Notification, Invite, Excel
├── Models/             User, Subject, Group, Column, Grade, Comment, Notification
├── Exports/            GradebookExport
└── Imports/            StudentsImport, GradesImport
database/migrations/    8 таблиц
database/seeders/       DatabaseSeeder (тестовые данные)
resources/views/        layouts/, auth/, teacher/, student/, home, notifications
public/assets/          css/style.css, js/app.js (Bootstrap + дизайн-система)
routes/web.php          все роуты
```

## Стек

- Laravel 11 (PHP 8.2)
- MySQL 8
- Bootstrap 5 (CDN), Blade
- maatwebsite/excel для импорта/экспорта

## Лицензия

Учебный проект.
