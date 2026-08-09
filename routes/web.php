<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ColumnController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ExcelController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\GradebookController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\GroupMemberController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\StudentGradeController;
use App\Http\Controllers\StudentSubjectController;
use App\Http\Controllers\TeacherDashboardController;
use Illuminate\Support\Facades\Route;

/* ===== Гостевые ===== */
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/login',    [AuthController::class, 'showLogin'])->middleware('guest')->name('login');
Route::post('/login',   [AuthController::class, 'login'])->middleware('guest');
Route::get('/register', [AuthController::class, 'showRegister'])->middleware('guest')->name('register');
Route::post('/register',[AuthController::class, 'register'])->middleware('guest');
Route::get('/join/{token}', [InviteController::class, 'join'])->name('invite.join');

/* ===== Авторизованные ===== */
Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    /* Уведомления (общие) */
    Route::get('/notifications',           [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/mark-read', [NotificationController::class, 'markAllRead'])->name('notifications.markRead');
    Route::get('/api/notifications/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.api.unread');

    /* Комментарии к оценке (общий: препод и студент) */
    Route::post('/grades/{grade}/comments', [CommentController::class, 'store'])->name('comments.store');

    /* ===== Преподаватель ===== */
    Route::middleware('role:teacher')->prefix('teacher')->name('teacher.')->group(function () {
        Route::get('/dashboard', [TeacherDashboardController::class, 'index'])->name('dashboard');

        // Группы CRUD
        Route::get('/groups',          [GroupController::class, 'index'])->name('groups.index');
        Route::get('/groups/create',   [GroupController::class, 'create'])->name('groups.create');
        Route::post('/groups',         [GroupController::class, 'store'])->name('groups.store');
        Route::get('/groups/{group}/edit',    [GroupController::class, 'edit'])->name('groups.edit');
        Route::put('/groups/{group}',         [GroupController::class, 'update'])->name('groups.update');
        Route::delete('/groups/{group}',      [GroupController::class, 'destroy'])->name('groups.destroy');

        // Студенты группы
        Route::get('/groups/{group}/members', [GroupMemberController::class, 'index'])->name('members.index');
        Route::post('/groups/{group}/members',[GroupMemberController::class, 'store'])->name('members.store');
        Route::delete('/groups/{group}/members/{student}', [GroupMemberController::class, 'destroy'])->name('members.destroy');

        // Журнал
        Route::get('/groups/{group}/gradebook', [GradebookController::class, 'show'])->name('gradebook.show');

        // Столбцы
        Route::post('/groups/{group}/columns',          [ColumnController::class, 'store'])->name('columns.store');
        Route::patch('/columns/{column}/visibility',    [ColumnController::class, 'toggleVisibility'])->name('columns.visibility');
        Route::delete('/columns/{column}',              [ColumnController::class, 'destroy'])->name('columns.destroy');

        // Оценки (AJAX)
        Route::put('/groups/{group}/grades', [GradeController::class, 'update'])->name('grades.update');

        // Excel импорт/экспорт
        Route::get('/groups/{group}/export',   [ExcelController::class, 'export'])->name('excel.export');
        Route::post('/groups/{group}/import-students', [ExcelController::class, 'importStudents'])->name('excel.importStudents');
        Route::post('/groups/{group}/import-grades',   [ExcelController::class, 'importGrades'])->name('excel.importGrades');
    });

    /* ===== Студент ===== */
    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {
        Route::get('/dashboard',  [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('/subjects',   [StudentSubjectController::class, 'index'])->name('subjects.index');
        Route::get('/subjects/{group}', [StudentSubjectController::class, 'show'])->name('subject.show');
        Route::get('/grades',     [StudentGradeController::class, 'index'])->name('grades.index');
    });
});
