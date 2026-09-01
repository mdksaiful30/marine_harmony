<?php

use App\Http\Controllers\ApprovalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\InvestmentController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('tyro-login.login');
Route::get('/login-alias', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/login-submit', [AuthController::class, 'login'])->name('tyro-login.login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
Route::post('/auth-logout', [AuthController::class, 'logout'])->name('tyro-login.logout');
// Protected Application Routes
Route::middleware(['auth'])->group(function () {
    Route::match(['get', 'post'], '/switch-member/{id}', [AuthController::class, 'switchMember'])->name('auth.switch-member');

    Route::get('/', [DashboardController::class, 'index'])->name('home');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/dashboard/reconcile', [DashboardController::class, 'updateActualBalance'])->name('dashboard.reconcile');

    // Deposits
    Route::get('/deposits', [DepositController::class, 'index'])->name('deposits.index');
    Route::post('/deposits', [DepositController::class, 'store'])->name('deposits.store');
    Route::delete('/deposits/{id}', [DepositController::class, 'destroy'])->name('deposits.destroy');

    // Income
    Route::get('/income', [IncomeController::class, 'index'])->name('income.index');
    Route::post('/income', [IncomeController::class, 'store'])->name('income.store');
    Route::delete('/income/{id}', [IncomeController::class, 'destroy'])->name('income.destroy');

    // Expenses / Expenditure
    Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
    Route::delete('/expenses/{id}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

    // Investments
    Route::get('/investments', [InvestmentController::class, 'index'])->name('investments.index');
    Route::post('/investments', [InvestmentController::class, 'store'])->name('investments.store');
    Route::delete('/investments/{id}', [InvestmentController::class, 'destroy'])->name('investments.destroy');

    // Approval Queue (Admin only)
    Route::get('/approval', [ApprovalController::class, 'index'])->name('approval.index');
    Route::post('/approval/decide', [ApprovalController::class, 'decide'])->name('approval.decide');

    // Members Directory & Individual Profiles
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    Route::get('/members/{id}', [MemberController::class, 'show'])->name('members.show');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');

    // API endpoints for dynamic UI
    Route::get('/api/members/{member}/months', [DepositController::class, 'getMemberMonths'])->name('api.members.months');
});

Route::view('dashboard/admin', 'dashboard.admin')->middleware(['auth', 'tyro-dashboard.admin'])->name('dashboard.admin');

Route::view('dashboard/user', 'dashboard.user')->middleware(['auth'])->name('dashboard.user');

Route::view('dashboard/all-members', 'dashboard.all-members')->middleware(['auth'])->name('dashboard.all-members');
