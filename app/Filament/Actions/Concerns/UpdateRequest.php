<?php

namespace App\Filament\Actions\Concerns;

use App\Enums\ActionStatus;
use App\Enums\RequestClass;
use App\Models\Request;
use Exception;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\TextInput;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\MaxWidth;
use Illuminate\Support\Facades\Auth;

trait UpdateRequest
{
    protected function bootUpdateRequest(): void
    {
        $this->icon('heroicon-o-pencil-square');

        $this->label('Update');

        $this->slideOver();

        $this->modalHeading('Update request');

        $this->modalFooterActionsAlignment(Alignment::End);

        $this->modalSubmitActionLabel('Update');

        $this->modalCancelActionLabel('Cancel');

        $this->modalWidth(MaxWidth::ExtraLarge);

        $this->successNotificationTitle('Request updated');

        $this->failureNotificationTitle('Failed to update request');

        $this->fillForm(fn (Request $request): array => [
            'subject' => $request->subject,
            'body' => $request->body,
        ]);

        $this->form([
            TextInput::make('subject')
                ->rule('required')
                ->markAsRequired()
                ->extraAttributes([
                    'x-data' => '{}',
                    'x-on:input' => 'event.target.value = event.target.value.charAt(0).toUpperCase() + event.target.value.slice(1)',
                ])
                ->helperText(fn (Request $request) => 'Be clear and concise about '.match ($request->class) {
                    RequestClass::TICKET => 'the issue you are facing.',
                    RequestClass::SUGGESTION => 'the idea or suggestion you would like to share.',
                    RequestClass::INQUIRY => 'the question you have.',
                }),
            MarkdownEditor::make('body')
                ->required()
                ->helperText(fn (Request $request) => 'Provide detailed information about '.match ($request->class) {
                    RequestClass::INQUIRY => 'your question, specifying any necessary context for clarity.',
                    RequestClass::SUGGESTION => 'your idea, explaining its benefits and potential impact.',
                    RequestClass::TICKET => 'the issue, including any steps to reproduce it and relevant details.',
                }),
        ]);

        $this->action(function (Request $request, array $data): void {
            if ($request->body === $data['body'] && $request->subject === $data['subject']) {
                return;
            }

            try {
                $this->beginDatabaseTransaction();

                $old = $request->only(['subject', 'body']);

                $request->update($data);

                $request->actions()->create([
                    'user_id' => Auth::id(),
                    'status' => ActionStatus::UPDATED,
                    'remarks' => "#### From: \n\n#### Subject: \n".$old['subject']."\n\n #### Body: \n".$old['body'],
                ]);

                $this->commitDatabaseTransaction();

                $this->success();
            } catch (Exception) {
                $this->rollbackDatabaseTransaction();

                $this->failure();
            }
        });

        $this->hidden(fn (Request $request): bool => $request->trashed());

        $this->visible(fn (Request $request): bool => is_null($request->action) ?: in_array($request->action?->status, [
            ActionStatus::RECALLED,
            ActionStatus::SUSPENDED,
            ActionStatus::RESTORED,
        ]));
    }
}
