<?php

use App\Http\Controllers\Admin\AdminComplaintController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\DonationReceiptController;
use App\Http\Controllers\Admin\DonationRequestController;
// use App\Http\Controllers\admin\GlassesController as AdminGlassesController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\Donor\DashboardController;
use App\Http\Controllers\Donor\DonorContactRequestController;
use App\Http\Controllers\Donor\GlassesController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Recipient\DashboardController as RecipientDashboardController;
use App\Http\Controllers\Recipient\DeliveryConfirmationController;
use App\Http\Controllers\Recipient\GlassesController as RecipientGlassesController;
use App\Http\Controllers\Recipient\RecipientContactRequestController;
use App\Http\Controllers\Recipient\RecipientDonationsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/phone/verify', function () {
    return view('auth.verify-phone');
})->name('phone.verify.notice');
/*
|--------------------------------------------------------------------------
| Notification Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/notifications', [NotificationController::class, 'index'])
        ->name('notifications.index');

    Route::get('/notifications/{notification}', [NotificationController::class, 'open'])
        ->name('notifications.open');

    Route::post('/notifications/{id}/read', [NotificationController::class, 'markRead'])
        ->name('notifications.read');

    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])
        ->name('notifications.read_all');

});

/*
|--------------------------------------------------------------------------
| Donor Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:donor', 'active', 'phone.verified'])
    ->prefix('donor')
    ->name('donor.')
    ->group(function () {

        // Donor main page
        Route::get('/main-page', [DashboardController::class, 'index'])->name('main_page');

        // Donor glasses CRUD
        Route::resource('glasses', GlassesController::class)
            ->parameters(['glasses' => 'glasses']);

        // Delete additional image
        Route::delete('glasses/{glasses}/images/{image}', [GlassesController::class, 'destroyImage'])
            ->name('glasses.images.destroy');

        // Donor contact system
        Route::get('/glasses/{glasses}/requests', [DonorContactRequestController::class, 'index'])
            ->name('requests.index');

        Route::post('/contact-requests/{request}/accept', [DonorContactRequestController::class, 'accept'])
            ->name('requests.accept');

        Route::post('/contact-requests/{request}/reject', [DonorContactRequestController::class, 'reject'])
            ->name('requests.reject');

        Route::post('/conversations/{conversation}/disconnect', [DonorContactRequestController::class, 'disconnect'])
            ->name('conversations.disconnect');

        Route::post('/glasses/{glasses}/mark-donated', [DonorContactRequestController::class, 'markDonated'])
            ->name('glasses.mark_donated');

        /**
         * ✅ Chats Inbox (Livewire)
         * يفتح صفحة المحادثات العامة
         * ويمكن فتح محادثة محددة عبر:
         * /donor/chats?conversation=123
         */
        Route::get('/chats', [\App\Http\Controllers\ChatController::class, 'index'])
            ->name('chats.index');

        // (اختياري) لو ما عدت تحتاج صفحة محادثة واحدة قديمة، احذف السطرين التاليين لاحقاً
        // Route::get('/conversations/{conversation}', [\App\Http\Controllers\ConversationController::class, 'show'])
        //     ->name('conversations.show');

        // Route::post('/conversations/{conversation}/messages', [\App\Http\Controllers\ConversationController::class, 'storeMessage'])
        //     ->name('conversations.messages.store');

        Route::post('/conversations/{conversation}/mark-donated', [DonorContactRequestController::class, 'markDonated'])
            ->name('conversations.mark_donated');

        Route::get('/receipts', [DonationReceiptController::class, 'index'])->name('receipts.index');
        Route::get('/receipts/{receipt}', [DonationReceiptController::class, 'show'])->name('receipts.show');
        Route::get('/receipts/{receipt}/download', [DonationReceiptController::class, 'download'])->name('receipts.download');
    });

/*
|--------------------------------------------------------------------------
| Recipient Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'role:recipient', 'active', 'phone.verified'])
    ->prefix('recipient')
    ->name('recipient.')
    ->group(function () {

        // Recipient main page (available glasses grid)
        Route::get('/main-page', [RecipientDashboardController::class, 'index'])
            ->name('main_page');

        Route::get('/glasses/{glasses}', [RecipientGlassesController::class, 'show'])
            ->name('glasses.show');

        Route::post('/glasses/{glasses}/contact-request', [RecipientContactRequestController::class, 'store'])
            ->name('contact-requests.store');

        Route::get('/contact-requests', [RecipientContactRequestController::class, 'index'])
            ->name('contact-requests.index');

        // Route::get('/conversations/{conversation}', [\App\Http\Controllers\ConversationController::class, 'show'])
        //     ->name('conversations.show');

        // Route::post('/conversations/{conversation}/messages', [\App\Http\Controllers\ConversationController::class, 'storeMessage'])
        //     ->name('conversations.messages.store');

        Route::get('/chats', [\App\Http\Controllers\ChatController::class, 'index'])
            ->name('chats.index');

        // Route::get('/delivery-confirmations/{confirmation}',
        //     [DeliveryConfirmationController::class, 'show'])
        //     ->name('delivery_confirmations.show');

        // Route::post('/delivery-confirmations/{confirmation}/confirm',
        //     [DeliveryConfirmationController::class, 'confirmReceived'])
        //     ->name('delivery_confirmations.confirm');

        // Route::post('/delivery-confirmations/{confirmation}/deny',
        //     [DeliveryConfirmationController::class, 'denyReceived'])
        //     ->name('delivery_confirmations.deny');

        Route::get('/donations', [RecipientDonationsController::class, 'index'])
            ->name('donations.index');

        Route::get('/delivery-confirmations/{confirmation}', [RecipientDonationsController::class, 'show'])
            ->name('confirmations.show');

        Route::post('/delivery-confirmations/{confirmation}/received', [RecipientDonationsController::class, 'markReceived'])
            ->name('confirmations.received');

        Route::post('/delivery-confirmations/{confirmation}/not-received', [RecipientDonationsController::class, 'markNotReceived'])
            ->name('confirmations.not_received');

        Route::patch(
        '/requests/{request}/withdraw',
        [RecipientContactRequestController::class, 'withdraw']
        )->name('requests.withdraw');

    });

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
*/


Route::middleware(['auth', 'verified', 'role:admin,super_admin', 'active'])
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            Route::get('/dashboard', function () {
                return view('admin.dashboard');
            })->name('dashboard');

            Route::get('/donation-requests', [DonationRequestController::class, 'index'])
                ->name('donation_requests.index');

            Route::get('/donation-requests/{donationRequest}', [DonationRequestController::class, 'show'])
                ->name('donation_requests.show');

            Route::post('/donation-requests/{donationRequest}/approve', [DonationRequestController::class, 'approve'])
                ->name('donation_requests.approve');

            Route::post('/donation-requests/{donationRequest}/reject', [DonationRequestController::class, 'reject'])
                ->name('donation_requests.reject');

            Route::get('/conversations/{conversation}', [ChatController::class, 'show'])
                ->name('conversations.show');

            Route::post('/conversations/{conversation}/toggle', [ChatController::class, 'toggleStatus']
            )->name('conversations.toggle');

            // Users
            Route::get('/users', [\App\Http\Controllers\Admin\UserManagementController::class, 'index'])
                ->name('users.index');

            Route::get('/users/{user}', [\App\Http\Controllers\Admin\UserManagementController::class, 'show'])
                ->name('users.show');

            Route::get('/users/{user}/edit', [\App\Http\Controllers\Admin\UserManagementController::class, 'edit'])
                ->name('users.edit');

            Route::patch('/users/{user}', [\App\Http\Controllers\Admin\UserManagementController::class, 'update'])
                ->name('users.update');

            // Actions
            Route::post('/users/{user}/suspend', [\App\Http\Controllers\Admin\UserManagementController::class, 'suspend'])
                ->name('users.suspend');

            Route::post('/users/{user}/unsuspend', [\App\Http\Controllers\Admin\UserManagementController::class, 'unsuspend'])
                ->name('users.unsuspend');

            Route::post('/users/{user}/role', [\App\Http\Controllers\Admin\UserManagementController::class, 'changeRole'])
                ->name('users.change_role')->middleware('role:super_admin');

            Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserManagementController::class, 'destroy'])
                ->name('users.destroy');

            Route::post('/users/{id}/restore', [\App\Http\Controllers\Admin\UserManagementController::class, 'restore'])
                ->name('users.restore');

            Route::post('/users/{user}/close-open-conversations', [\App\Http\Controllers\Admin\UserManagementController::class, 'closeOpenConversations'])
                ->name('users.close_conversations');

            Route::post('/users/{user}/conversations/open', [\App\Http\Controllers\Admin\UserManagementController::class, 'openClosedConversations'])
                ->name('users.open_conversations');

            Route::get('/glasses', [ \App\Http\Controllers\Admin\GlassesController::class, 'index'])
                ->name('glasses.index');

            Route::get('/glasses/{glasses}', [ \App\Http\Controllers\Admin\GlassesController::class, 'show'])
                ->name('glasses.show');

            Route::get('/legal-pages', [\App\Http\Controllers\Admin\LegalPagesController::class, 'index'])
                ->name('legal.index')->middleware('role:super_admin');

            Route::get('/legal-pages/{page}/edit', [\App\Http\Controllers\Admin\LegalPagesController::class, 'edit'])
                ->name('legal.edit')->middleware('role:super_admin');

            Route::put('/legal-pages/{page}', [\App\Http\Controllers\Admin\LegalPagesController::class, 'update'])
                ->name('legal.update')->middleware('role:super_admin');

            Route::get('/control', [\App\Http\Controllers\Admin\ControlController::class, 'index'])
                ->name('control')->middleware('role:super_admin');

            Route::get('/settings', [SystemSettingsController::class, 'index'])->name('settings.index')->middleware('role:super_admin');
            Route::post('/settings', [SystemSettingsController::class, 'update'])->name('settings.update')->middleware('role:super_admin');

            // Route::post('/system/maintenance/down', [SystemSettingsController::class, 'maintenanceDown'])
            // ->name('system.maintenance.down');

            // Route::post('/system/maintenance/up', [SystemSettingsController::class, 'maintenanceUp'])
            // ->name('system.maintenance.up');

            Route::post('/settings/maintenance/on', [SystemSettingsController::class, 'enableMaintenance'])
                ->name('settings.maintenance.on')->middleware('role:super_admin');

            Route::post('/settings/maintenance/off', [SystemSettingsController::class, 'disableMaintenance'])
                ->name('settings.maintenance.off')->middleware('role:super_admin');

            Route::post('/cache/clear', [SystemSettingsController::class, 'clearCache'])
                ->name('system.cache.clear')->middleware('role:super_admin');

            Route::post('/optimize', [SystemSettingsController::class, 'optimize'])
                ->name('system.optimize')->middleware('role:super_admin');

            Route::get('/complaints', [AdminComplaintController::class, 'index'])->name('complaints.index');
            Route::get('/complaints/{complaint}', [AdminComplaintController::class, 'show'])->name('complaints.show');
            Route::post('/complaints/{complaint}/reply', [AdminComplaintController::class, 'reply'])->name('complaints.reply');
            Route::post('/complaints/{complaint}/set-status', [AdminComplaintController::class, 'setStatus'])->name('complaints.setStatus');
            Route::post('/complaints/{complaint}/close', [AdminComplaintController::class, 'close'])->name('complaints.close');

            Route::get('/receipts/{receipt}', [DonationReceiptController::class, 'show'])->name('receipts.show');
            Route::get('/receipts/{receipt}/download', [DonationReceiptController::class, 'download'])->name('receipts.download');

        });

/*
|--------------------------------------------------------------------------
| Profile Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar.update');
    Route::delete('/profile/avatar', [ProfileController::class, 'destroyAvatar'])->name('profile.avatar.destroy');
});

/*
|--------------------------------------------------------------------------
| Susbended user Routes
|--------------------------------------------------------------------------
*/

Route::get('/suspended', function () {
    return view('auth.suspended');
})->name('suspended');

/*
|--------------------------------------------------------------------------
| complaint Routes
|--------------------------------------------------------------------------
*/
Route::post('/conversations/{conversation}/complaints', [ComplaintController::class, 'store'])->middleware('auth')->name('complaints.store');
Route::get('/complaints/{complaint}', [ComplaintController::class, 'show'])->middleware('auth')->name('complaints.show');
Route::post('/complaints/{complaint}/message', [ComplaintController::class, 'message'])->middleware('auth')->name('complaints.message');
Route::post('/complaints/{complaint}/close', [ComplaintController::class, 'close'])->name('complaints.close');

/*
|--------------------------------------------------------------------------
| Routes
|--------------------------------------------------------------------------
*/

Route::get('/terms', [\App\Http\Controllers\LegalPageController::class, 'show'])
    ->defaults('key', 'terms')
    ->name('terms');

Route::get('/privacy', [\App\Http\Controllers\LegalPageController::class, 'show'])
    ->defaults('key', 'privacy')
    ->name('privacy');
require __DIR__.'/auth.php';
