<?php

use App\Http\Controllers\AttachmentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('test', fn (Request $request) => $request->expectsJson() ? response()->json('Hello World') : 'Hello World');

Route::get('attachments/{attachment}/{name}', AttachmentController::class)->name('file.attachment')->where('name', '.*');
