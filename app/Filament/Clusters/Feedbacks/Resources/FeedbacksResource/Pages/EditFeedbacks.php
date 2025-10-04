<?php

namespace App\Filament\Clusters\Feedbacks\Resources\FeedbacksResource\Pages;

use App\Filament\Clusters\Feedbacks\Resources\FeedbacksResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFeedbacks extends EditRecord
{
    protected static string $resource = FeedbacksResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
