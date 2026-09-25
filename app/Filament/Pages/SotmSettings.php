<?php

namespace App\Filament\Pages;

use App\Models\Department;
use App\Models\Setting;
use App\Models\SotmPj;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SotmSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $title = 'SOTM Settings';

    protected static string $view = 'filament.pages.sotm-settings';

    // ---- form tambah PJ ----
    public ?array $pjData = [];

    public ?array $scheduleData = [];

    public $pjList;

    public function mount(): void
    {
        $this->pjList = SotmPj::with(['department', 'user'])->get();

        $this->pjForm->fill([
            'department_id' => null,
            'user_id' => null,
        ]);

        $this->scheduleForm->fill([
            'sotm_start_day' => Setting::getVal('sotm_start_day', 25),
            'sotm_end_day' => Setting::getVal('sotm_end_day', 5),
        ]);
    }

    // ---- FORM: Tambah PJ ----
    public function pjForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Penanggung Jawab')
                    ->description('Tambahkan PJ yang bisa melihat hasil SOTM. Bisa lebih dari satu.')
                    ->schema([
                        Forms\Components\Select::make('department_id')
                            ->label('Department')
                            ->options(Department::pluck('name', 'id'))
                            ->searchable()
                            ->live()
                            ->required(),

                        Forms\Components\Select::make('user_id')
                            ->label('Akun PJ')
                            ->options(function (Forms\Get $get) {
                                $deptId = $get('department_id');
                                if (! $deptId) {
                                    return User::pluck('name', 'id');
                                }

                                return User::where('department_id', $deptId)->pluck('name', 'id');
                            })
                            ->searchable()
                            ->required(),
                    ])->columns(2),
            ])
            ->statePath('pjData');
    }

    // ---- FORM: Jadwal ----
    public function scheduleForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Jadwal Pengisian SOTM')
                    ->description('Tentukan tanggal buka dan tutup pengisian penilaian setiap bulannya.')
                    ->schema([
                        Forms\Components\TextInput::make('sotm_start_day')
                            ->label('Tanggal Buka')
                            ->numeric()
                            ->minValue(1)->maxValue(31)
                            ->required(),

                        Forms\Components\TextInput::make('sotm_end_day')
                            ->label('Tanggal Tutup (Bulan Berikutnya)')
                            ->numeric()
                            ->minValue(1)->maxValue(31)
                            ->required(),
                    ])->columns(2),
            ])
            ->statePath('scheduleData');
    }

    protected function getForms(): array
    {
        return ['pjForm', 'scheduleForm'];
    }

    // ---- AKSI: Tambah PJ ----
    public function tambahPj(): void
    {
        $data = $this->pjForm->getState();

        // Cek duplikat
        $exists = SotmPj::where('department_id', $data['department_id'])
            ->where('user_id', $data['user_id'])
            ->exists();

        if ($exists) {
            Notification::make()
                ->warning()
                ->title('PJ ini sudah terdaftar untuk divisi tersebut.')
                ->send();

            return;
        }

        SotmPj::create($data);

        $this->pjList = SotmPj::with(['department', 'user'])->get();

        $this->pjForm->fill(['department_id' => null, 'user_id' => null]);

        Notification::make()
            ->success()
            ->title('Penanggung Jawab berhasil ditambahkan.')
            ->send();
    }

    // ---- AKSI: Hapus PJ ----
    public function hapusPj(int $id): void
    {
        SotmPj::findOrFail($id)->delete();
        $this->pjList = SotmPj::with(['department', 'user'])->get();

        Notification::make()
            ->success()
            ->title('PJ berhasil dihapus.')
            ->send();
    }

    // ---- AKSI: Simpan Jadwal ----
    public function saveSchedule(): void
    {
        $data = $this->scheduleForm->getState();

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => 'integer']
            );
        }

        Notification::make()
            ->success()
            ->title('Jadwal SOTM berhasil disimpan.')
            ->send();
    }
}
