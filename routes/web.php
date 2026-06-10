<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProspectController;
use App\Http\Controllers\LoaDocumentController;
use App\Http\Controllers\DocumentChecklistController;
use App\Http\Controllers\SingleSupportAssignmentController;
use App\Http\Controllers\SingleSupportConflictController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\BranchController;
use App\Http\Controllers\Admin\EntityController;
use App\Http\Controllers\Admin\EntityGroupController;
use Illuminate\Support\Facades\Route;

// Auth
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Authenticated routes
Route::middleware('auth')->group(function () {

    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Prospects
    Route::resource('prospects', ProspectController::class);
    Route::post('prospects/{prospect}/submit', [ProspectController::class, 'submit'])->name('prospects.submit');
    Route::post('prospects/{prospect}/trigger-ai', [ProspectController::class, 'triggerAi'])->name('prospects.trigger-ai');
    Route::post('prospects/{prospect}/approve', [ProspectController::class, 'approve'])->name('prospects.approve');
    Route::post('prospects/{prospect}/reject', [ProspectController::class, 'reject'])->name('prospects.reject');
    Route::post('prospects/{prospect}/request-clarification', [ProspectController::class, 'requestClarification'])->name('prospects.request-clarification');
    Route::post('prospects/{prospect}/respond-clarification', [ProspectController::class, 'respondClarification'])->name('prospects.respond-clarification');

    // LOA Documents
    Route::post('prospects/{prospect}/loa', [LoaDocumentController::class, 'store'])->name('loa.store');
    Route::post('loa/{loa}/check', [LoaDocumentController::class, 'check'])->name('loa.check');
    Route::post('loa/{loa}/review', [LoaDocumentController::class, 'review'])->name('loa.review');

    // Document Checklist
    Route::post('prospects/{prospect}/checklist/generate', [DocumentChecklistController::class, 'generate'])->name('checklist.generate');
    Route::patch('checklist/{checklist}', [DocumentChecklistController::class, 'update'])->name('checklist.update');

    // Single Support Assignments
    Route::get('assignments', [SingleSupportAssignmentController::class, 'index'])->name('assignments.index');
    Route::get('assignments/{assignment}', [SingleSupportAssignmentController::class, 'show'])->name('assignments.show');
    Route::post('prospects/{prospect}/create-assignment', [SingleSupportAssignmentController::class, 'store'])->name('assignments.store');
    Route::patch('assignments/{assignment}/revoke', [SingleSupportAssignmentController::class, 'revoke'])->name('assignments.revoke');

    // Conflicts
    Route::get('conflicts', [SingleSupportConflictController::class, 'index'])->name('conflicts.index');
    Route::get('conflicts/{conflict}', [SingleSupportConflictController::class, 'show'])->name('conflicts.show');
    Route::post('conflicts/{conflict}/resolve', [SingleSupportConflictController::class, 'resolve'])->name('conflicts.resolve');

    // Admin routes
    Route::middleware('role:admin,supervisor')->prefix('admin')->name('admin.')->group(function () {
        Route::resource('users', UserController::class);
        Route::resource('branches', BranchController::class);
        Route::resource('entities', EntityController::class);
        Route::resource('entity-groups', EntityGroupController::class);
    });

});
