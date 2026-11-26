<?php

namespace App\Filament\Pages;

use App\Models\Department;
use App\Models\IotmPj;
use App\Models\Setting;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class IotmSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Settings';

    protected static ?string $title = 'IOTM Settings';

    protected static string $view = 'filament.pages.iotm-settings';

    public ?array $pjData = [];

    public ?array $scheduleData = [];

    public $pjList;

    public function mount(): void
    {
        $this->pjList = IotmPj::with(['department', 'user'])->get();

        $this->pjForm->fill([
            'department_id' => null,
            'user_id' => null,
        ]);

        $this->scheduleForm->fill([
            'iotm_start_date' => Setting::getVal('iotm_start_date'),
            'iotm_end_date' => Setting::getVal('iotm_end_date'),
        ]);
    }

    public function pjForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Penanggung Jawab IOTM')
                    ->description('Tambahkan PJ yang bisa melihat hasil & export PDF IOTM. Bisa lebih dari satu.')
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

    public function scheduleForm(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Jadwal Pengisian IOTM')
                    ->description('IOTM hanya diadakan sekali. Tentukan tanggal mulai dan tanggal selesai pengisian (lengkap dengan bulan dan tahun). Setelah tanggal selesai, form otomatis tertutup dan tidak dibuka lagi di bulan berikutnya.')
                    ->schema([
                        Forms\Components\DatePicker::make('iotm_start_date')
                            ->label('Tanggal Mulai')
                            ->native(false)
                            ->displayFormat('d F Y')
                            ->format('Y-m-d')
                            ->required(),

                        Forms\Components\DatePicker::make('iotm_end_date')
                            ->label('Tanggal Selesai')
                            ->native(false)
                            ->displayFormat('d F Y')
                            ->format('Y-m-d')
                            ->required(),
                    ])->columns(2),
            ])
            ->statePath('scheduleData');
    }

    protected function getForms(): array
    {
        return ['pjForm', 'scheduleForm'];
    }

    public function tambahPj(): void
    {
        $data = $this->pjForm->getState();

        $exists = IotmPj::where('department_id', $data['department_id'])
            ->where('user_id', $data['user_id'])
            ->exists();

        if ($exists) {
            Notification::make()
                ->warning()
                ->title('PJ ini sudah terdaftar untuk divisi tersebut.')
                ->send();

            return;
        }

        IotmPj::create($data);

        $this->pjList = IotmPj::with(['department', 'user'])->get();
        $this->pjForm->fill(['department_id' => null, 'user_id' => null]);

        Notification::make()
            ->success()
            ->title('Penanggung Jawab IOTM berhasil ditambahkan.')
            ->send();
    }

    public function hapusPj(int $id): void
    {
        IotmPj::findOrFail($id)->delete();
        $this->pjList = IotmPj::with(['department', 'user'])->get();

        Notification::make()
            ->success()
            ->title('PJ IOTM berhasil dihapus.')
            ->send();
    }

    public function saveSchedule(): void
    {
        $data = $this->scheduleForm->getState();

        if ($data['iotm_end_date'] < $data['iotm_start_date']) {
            Notification::make()
                ->danger()
                ->title('Tanggal selesai tidak boleh sebelum tanggal mulai.')
                ->send();

            return;
        }

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value, 'type' => 'string']
            );
        }

        Notification::make()
            ->success()
            ->title('Jadwal IOTM berhasil disimpan.')
            ->send();
    }
}
