<?php

use Illuminate\Support\Facades\Route;
use EhsanNosair\Chativel\Http\Controllers\Api\ConversationController;
use EhsanNosair\Chativel\Http\Middlware\SetLocale;

Route::middleware(['api', 'auth:sanctum', SetLocale::class])
    ->prefix('api/chativel')
    ->group(function () {
        Route::get('/broadcast-my-status', [ConversationController::class, 'broadcastMyStatus']);
        Route::get('/conversations', [ConversationController::class, 'myConversations']);
        Route::get('/conversations/{id}', [ConversationController::class, 'getConversation']);
        Route::get('/conversations/{id}/other-activity-status', [ConversationController::class, 'getOtherActivityStatus']);
        Route::get('/conversations/{id}/messages', [ConversationController::class, 'conversationMessages']);
        Route::get('/conversations/{id}/messages/{mid}', [ConversationController::class, 'getMessage']);
        Route::post('/conversations/{id}/send-message', [ConversationController::class, 'sendMessage']);
        Route::get('/chatables/search', [ConversationController::class, 'chatablesSearch']);
        Route::get('/chatables/conversation-with', [ConversationController::class, 'getConversationWith']);
    });
