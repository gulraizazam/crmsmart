<?php

use App\Http\Controllers\Api\WhatsAppInboxController;
use Illuminate\Support\Facades\Route;

Route::prefix('whatsapp')->name('whatsapp.')->middleware('permission:whatsapp_manage')->group(function () {
    Route::get('status', [WhatsAppInboxController::class, 'status'])->name('status');
    Route::get('conversations', [WhatsAppInboxController::class, 'conversations'])->name('conversations');
    Route::post('conversations/start', [WhatsAppInboxController::class, 'start'])->name('start');
    Route::get('conversations/{id}/messages', [WhatsAppInboxController::class, 'messages'])->name('messages');
    Route::post('conversations/{id}/messages', [WhatsAppInboxController::class, 'send'])->name('send');
    Route::get('patients', [WhatsAppInboxController::class, 'patients'])->name('patients');
    Route::get('templates', [WhatsAppInboxController::class, 'templates'])->name('templates');
    Route::get('settings', [WhatsAppInboxController::class, 'showSettings'])->name('settings.show')->middleware('permission:whatsapp_settings');
    Route::post('settings', [WhatsAppInboxController::class, 'saveSettings'])->name('settings.save')->middleware('permission:whatsapp_settings');
});
