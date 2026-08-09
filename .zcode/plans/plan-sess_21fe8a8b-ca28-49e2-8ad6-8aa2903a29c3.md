# План: бэкенд Laravel 11 + перенос фронтенда на Blade

## Окружение (готово)
- PHP 8.2.28, Composer 2.10.2, MySQL 8.0 (OpenServer)
- Запуск: `php artisan serve` + MySQL из OpenServer
- БД: создать базу `modulus` в phpMyAdmin (OpenServer), юзер `root` без пароля

## Шаг 0. Создание Laravel-проекта в этой папке
- Бэкап текущего фронтенда во временную папку
- `composer create-project laravel/laravel temp_laravel`
- Перенос Laravel-скелета в корень ZCodeProject
- Установка `maatwebsite/excel`
- `.env`: подключение к MySQL OpenServer (DB_HOST=127.0.0.1, DB_PORT=3306, DB_DATABASE=modulus, DB_USERNAME=root, DB_PASSWORD=)

## Шаг 1. Схема БД (миграции)
Таблицы:
1. **users** (id, name, email, password, role[teacher|student], department, created_at) — добавляется role через отдельную миграцию к стандартной
2. **subjects** (id, name, user_id — кто создал)
3. **groups** (id, name, subject_id, user_id, level, year, number, invite_token)
4. **group_user** (group_id, user_id) — студенты в группах
5. **columns** (id, group_id, title, type[module|total|exam|retake|commission|score|grade|intermediate], position, sum_into, hidden, order)
6. **grades** (id, group_id, user_id, column_id, value)
7. **comments** (id, grade_id, user_id, text, image, file, created_at)
8. **notifications** (id, user_id, type, title, text, read, created_at)

## Шаг 2. Модели + связи
User, Subject, Group, Column, Grade, Comment, Notification — с belongsTo/hasMany, scopes (студенты группы, оценки студента и т.д.)

## Шаг 3. Auth + Middleware
- Регистрация/вход через Laravel built-in (без Breeze, ручные контроллеры)
- `RoleMiddleware` — проверка role, редирект на нужный дашборд
- Гостевые страницы: index (лендинг), login, register, join/{token}

## Шаг 4. Контроллеры + роуты
**Преподаватель:**
- `TeacherDashboardController`, `GroupController` (CRUD), `GroupMemberController`, `GradebookController`, `ColumnController`, `GradeController`, `CommentController`
- CRUD групп, студентов, столбцов, оценок, комментариев
- Парсинг названия группы «ПИб-231» → level/year/number (в валидации/модели)

**Студент:**
- `StudentDashboardController`, `StudentSubjectController`, `StudentGradeController`
- Видит только нескрытые столбцы, свои оценки, свои диалоги

**Общее:**
- `NotificationController`, `ProfileController`
- `InviteController` — присоединение по ссылке-приглашению

**Импорт/экспорт:**
- `ExcelController` — экспорт журнала (.xlsx), импорт студентов/баллов
- Import/Export классы через maatwebsite/excel

## Шаг 5. Перенос фронтенда → Blade
- 14 HTML → `resources/views/**/*.blade.php`
- Сохранить Bootstrap 5 CDN, `assets/css/style.css`, `assets/js/app.js` (хелперы) в `public/`
- layout: `layouts/app.blade.php` (header+sidebar+content), `layouts/guest.blade.php` (лендинг/auth)
- Данные из JS-констант заменить на `$variable` из контроллеров
- Формы → POST-роуты с @csrf
- Чат с фото/файлом: `multipart/form-data`, загрузка в `storage/app/public`

## Шаг 6. Seeders (тестовые данные)
- DatabaseSeeder: 1 преподаватель + 5 студентов, предметы, группы, столбцы, оценки, комментарии, уведомления — данные из текущего `app.js`
- Учётные записи для входа: ivanova@university.ru / password (препод), alexeev@stud.ru / password (студент)

## Шаг 7. Запуск
- `php artisan key:generate`, `php artisan migrate --seed`
- `php artisan storage:link`
- `php artisan serve` → http://localhost:8000

## Итог
Полноценный Laravel-проект с рабочим CRUD, auth по ролям, импортом/экспортом Excel, уведомлениями, чатом с файлами — и тем же фронтендом на Blade.