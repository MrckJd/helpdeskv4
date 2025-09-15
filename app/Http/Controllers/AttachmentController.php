<?php

namespace App\Http\Controllers;

use App\Http\Middleware\Authenticate;
use App\Http\Middleware\Verify;
use App\Http\Middleware\Approve;
use App\Http\Middleware\Active;
use App\Http\Middleware\Initialize;
use Illuminate\Routing\Controllers\Middleware;
use Closure;
use App\Models\Attachment;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class AttachmentController extends Controller implements HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     *
     * @return array<int, Middleware|Closure|string>
     */
    public static function middleware(): array
    {
        return [
            Authenticate::class,
            Verify::class,
            Approve::class,
            Active::class,
            Initialize::class,
        ];
    }

    /**
     * Handle the incoming file attachment download request.
     */
    public function __invoke(Attachment $attachment, string $name)
    {
        abort_unless($attachment->paths->contains($name) && Storage::exists($attachment->paths->search($name)), 404);

        $allowed = match ($attachment->attachable_type) {
            'App\Models\Request' => in_array(Auth::user()->organization_id, [$attachment->attachable->organization_id, $attachment->attachable->from_id]) ||
                Auth::id() === $attachment->attachable->user_id,
            'App\Models\Action' => in_array(Auth::user()->organization_id, [$attachment->attachable->request->organization_id, $attachment->attachable->request->from_id]) ||
                Auth::id() === $attachment->attachable->request->user_id,
            'App\Models\Note' => in_array(Auth::user()->organization_id, [$attachment->attachable->notable->organization_id]) ||
                Auth::id() === $attachment->attachable->notable->user_id,
            default => false,
        };

        abort_unless($allowed, 403);

        return Storage::download($attachment->paths->search($name), $name);
    }
}
