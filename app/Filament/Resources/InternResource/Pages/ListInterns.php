<?php

namespace App\Filament\Resources\InternResource\Pages;

use App\Filament\Resources\InternResource;
use App\Services\InternSpreadsheetService;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListInterns extends ListRecords
{
    protected static string $resource = InternResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('downloadTemplate')
                ->label('Download Template')
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->action(fn (InternSpreadsheetService $service) => $service->downloadTemplate()),

            Actions\Action::make('importInterns')
                ->label('Import Excel / CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('success')
                ->modalHeading('Import Data Intern')
                ->modalDescription('Upload file Excel (.xlsx) atau CSV (.csv) sesuai template. Lihat sheet "Referensi" pada template untuk daftar nama Departemen dan Divisi yang valid.')
                ->form([
                    FileUpload::make('file')
                        ->label('File Excel / CSV')
                        ->disk('local')
                        ->directory('temp-intern-imports')
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'text/csv',
                            'text/plain',
                            'application/csv',
                        ])
                        ->required(),
                ])
                ->action(function (array $data, InternSpreadsheetService $service): void {
                    $disk = Storage::disk('local');
                    $relativePath = $data['file'];
                    $fullPath = $disk->path($relativePath);

                    try {
                        $result = $service->importFromFile($fullPath);
                    } finally {
                        $disk->delete($relativePath);
                    }

                    $totalSuccess = $result['created'] + $result['updated'];

                    if ($totalSuccess > 0) {
                        Notification::make()
                            ->success()
                            ->title("Berhasil mengimpor {$totalSuccess} intern")
                            ->body("{$result['created']} data baru ditambahkan, {$result['updated']} data diperbarui.")
                            ->send();
                    }

                    if (! empty($result['errors'])) {
                        $errorPreview = implode("\n", array_slice($result['errors'], 0, 5));
                        $extra = count($result['errors']) > 5
                            ? "\n...dan ".(count($result['errors']) - 5).' baris lainnya.'
                            : '';

                        Notification::make()
                            ->warning()
                            ->title(count($result['errors']).' baris dilewati karena tidak valid')
                            ->body($errorPreview.$extra)
                            ->persistent()
                            ->send();
                    }

                    if ($totalSuccess === 0 && empty($result['errors'])) {
                        Notification::make()
                            ->warning()
                            ->title('Tidak ada data yang diimpor')
                            ->body('Pastikan file berisi data intern di bawah baris header.')
                            ->send();
                    }
                }),

            Actions\CreateAction::make()
                ->label('Tambah Manual'),
        ];
    }
}
