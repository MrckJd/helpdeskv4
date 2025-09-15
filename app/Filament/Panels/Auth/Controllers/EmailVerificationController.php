<?php

namespace App\Filament\Panels\Auth\Controllers;

use App\Http\Middleware\Authenticate;
use Illuminate\Routing\Controllers\HasMiddleware;

class EmailVerificationController extends \Filament\Auth\Http\Controllers\EmailVerificationController implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            Authenticate::class,
        ];
    }
}
