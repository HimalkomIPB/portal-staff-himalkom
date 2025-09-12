<?php

namespace App\Filament\Pages;

use Log;
use Filament\Forms;
use App\Models\User;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification as FilamentNotification;
use Illuminate\Support\Facades\Notification as LaravelNotification;
use App\Notifications\GlobalAnnouncement as GlobalAnnouncementNotification;

class GlobalAnnouncement extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-megaphone';
    protected static string $view = 'filament.pages.global-announcement';
    protected static ?string $title = 'Global Announcement';

    public ?string $titleInput = null;
    public ?string $messageInput = null;

    public function mount(): void
    {
        $this->form->fill();
    }

    protected function getFormSchema(): array
    {
        return [
            Forms\Components\TextInput::make('titleInput')
                ->label('Title')
                ->required()
                ->maxLength(100)
                ->live(),

            Forms\Components\Textarea::make('messageInput')
                ->label('Message')
                ->required()
                ->maxLength(500)
                ->live(),
        ];
    }


    public function form(Form $form): Form
    {
        return $form
            ->schema($this->getFormSchema())
            ->statePath(null);
    }

    public function send(): void
    {
        $title = $this->titleInput;
        $message = $this->messageInput;

        User::chunk(100, function ($users) use ($title, $message) {
            foreach ($users as $user) {
                try {
                    $user->notify(new GlobalAnnouncementNotification($title, $message));
                } catch (\Exception $e) {
                    Log::error("Failed to send notification to user ID {$user->id}: " . $e->getMessage());
                }
            }
        });



        FilamentNotification::make()
            ->title('Announcement Sent')
            ->success()
            ->send();

        $this->titleInput = null;
        $this->messageInput = null;
    }
}
