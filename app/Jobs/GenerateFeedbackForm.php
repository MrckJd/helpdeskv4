<?php

namespace App\Jobs;

use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Queue\Queueable;
use Spatie\Browsershot\Browsershot;
use Spatie\LaravelPdf\Enums\Format;
use Spatie\LaravelPdf\Enums\Unit;
use Spatie\LaravelPdf\Facades\Pdf;

class GenerateFeedbackForm implements ShouldQueue
{
    use Queueable;

    private Collection $feedbacks;

    public function __construct(Collection $feedbacks)
    {
        $this->feedbacks = $feedbacks;
    }

    public function handle(): void
    {
        $directory = storage_path('app/public/PDF');
        $filename = $directory.'/feedback__' . now()->format('Y-m-d') . '.pdf';

        Pdf::view('filament.panels.feedback.feedback-form', ['records' => $this->feedbacks,'preview' => false])
            ->margins(10, 10, 10, 10)
            ->paperSize(8.5, 13, Unit::Inch)
            ->withBrowsershot(function (Browsershot $browsershot) {
                return $browsershot
                    ->noSandbox()
                    ->emulateMedia('print')
                    ->portrait()
                    ->timeout(120)
                    ->showBackground();
            })
            ->save($filename);

    }
}
