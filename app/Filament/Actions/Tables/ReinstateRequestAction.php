<?php

namespace App\Filament\Actions\Tables;

use App\Enums\ActionStatus;
use App\Enums\RequestClass;
use App\Filament\Actions\Concerns\Notifications\CanNotifyUsers;
use App\Filament\Forms\FileAttachment;
use App\Models\Request;
use Exception;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;

class ReinstateRequestAction extends Action
{
    use CanNotifyUsers;

    protected static ?ActionStatus $requestAction = ActionStatus::REINSTATED;

    protected function setUp(): void
    {
        parent::setUp();

        $this->name('reinstate-request');

        $this->label('Reinstate');

        $this->icon(static::$requestAction->getIcon());

        $this->modalIcon(static::$requestAction->getIcon());

        $this->slideOver();

        $this->modalHeading('Reinstate Request');

        $this->modalDescription('Reinstating a request removes its suspension status and immediately resumes the process.');

        $this->modalWidth(MaxWidth::ExtraLarge);

        $this->closeModalByClickingAway(false);

        $this->modalSubmitActionLabel('Confirm');

        $this->successNotificationTitle('Request reinstated');

        $this->failureNotificationTitle('Failed to reinstate request');

        $this->form([
            MarkdownEditor::make('remarks')
                ->label('Reason')
                ->required()
                ->helperText('Please provide a reason for reinstating this request.'),
            FileAttachment::make(),
        ]);

        $this->action(function (Request $request, array $data) {
            if ($request->class !== RequestClass::TICKET || $request->action->status === static::$requestAction) {
                return;
            }

            try {
                $this->beginDatabaseTransaction();

                $action = $request->actions()->create([
                    'status' => static::$requestAction,
                    'user_id' => Auth::id(),
                    'remarks' => $data['remarks'],
                ]);

                if (count($data['files']) > 0) {
                    $action->attachment()->create([
                        'files' => $data['files'],
                        'paths' => $data['paths'],
                    ]);
                }

                $this->commitDatabaseTransaction();

                $this->sendSuccessNotification();

                $this->notifyUsers();
            } catch (Exception) {
                $this->rollbackDatabaseTransaction();

                $this->sendFailureNotification();
            }
        });

        $this->visible(function (Request $request) {
            if ($request->action->status->finalized()) {
                return false;
            }

            return in_array($request->action->status, [ActionStatus::SUSPENDED]) && match ($request->class) {
                RequestClass::TICKET => $request->assignees->contains(Auth::user()) || Auth::user()->admin,
                default => false,
            };
        });
    }
}
