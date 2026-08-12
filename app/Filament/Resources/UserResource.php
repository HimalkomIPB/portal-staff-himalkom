<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Department;
use App\Models\User;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationLabel = 'Pengguna';

    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Manajemen Pengguna';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Akun')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Lengkap')
                            ->placeholder('Contoh: Budi Utomo')
                            ->required(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true),
                    ]),

                Section::make('Role & Departemen')
                    ->columns(2)
                    ->schema([
                        Select::make('roles')
                            ->label('Role')
                            ->multiple()
                            ->relationship('roles', 'name')
                            ->preload()
                            ->searchable()
                            ->required()
                            ->live()
                            ->columnSpanFull(),
                        Select::make('department_id')
                            ->label('Departemen Utama')
                            ->relationship('department', 'name')
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (callable $set) => $set('sub_division', null))
                            ->helperText('Departemen tempat user ini bertugas sebagai MD/PJS/Sekretaris/dll.'),
                        Select::make('sub_division')
                            ->label('Sub Divisi')
                            ->options(function (\Filament\Forms\Get $get) {
                                $departmentId = $get('department_id');
                                if (!$departmentId) {
                                    return [];
                                }
                                $department = \App\Models\Department::find($departmentId);
                                if (!$department) {
                                    return [];
                                }
                                
                                $mapping = [
                                    'Education' => ['Competition & Community Empowerment', 'Academic & Development'],
                                    'Finance' => ['Operations & Finance', 'Marketing & Technology'],
                                    'Creative' => ['Social Media & Content Creation', 'Multimedia & Visual Design'],
                                    'External' => ['Social Relation', 'Campus & Corporate Outreach'],
                                    'Internal' => ['Internal Harmony', 'Internal Celebration'],
                                    'Research and Technology' => ['Web Innovation', 'Research & Career Development'],
                                    'Talent and Sport' => ['Talent Developement', 'Sport'],
                                ];
                                
                                $subs = $mapping[$department->name] ?? [];
                                return array_combine($subs, $subs);
                            })
                            ->visible(function (\Filament\Forms\Get $get) {
                                $selectedRoles = $get('roles');
                                if (empty($selectedRoles)) {
                                    return false;
                                }
                                $anggotaRoleId = \Spatie\Permission\Models\Role::where('name', 'anggota')->value('id');
                                return in_array('anggota', (array) $selectedRoles) || in_array((string)$anggotaRoleId, array_map('strval', (array) $selectedRoles));
                            })
                            ->helperText('Pilih sub divisi untuk anggota ini.'),
                        Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Hanya 1 MD/PJS yang boleh aktif dalam satu departemen. Jika diaktifkan, MD/PJS lain di departemen yang sama akan otomatis dinonaktifkan.')
                            ->visible(function (\Filament\Forms\Get $get) {
                                $selectedRoles = $get('roles');
                                if (empty($selectedRoles)) {
                                    return false;
                                }
                                $mdRoleId = \Spatie\Permission\Models\Role::where('name', 'managing director')->value('id');
                                $pjsRoleId = \Spatie\Permission\Models\Role::where('name', 'pjs')->value('id');
                                
                                $roles = array_map('strval', (array) $selectedRoles);
                                return in_array('managing director', $roles) || in_array((string)$mdRoleId, $roles)
                                    || in_array('pjs', $roles) || in_array((string)$pjsRoleId, $roles);
                            }),
                        Select::make('scDepartments')
                            ->label('SC untuk Departemen')
                            ->multiple()
                            ->relationship('scDepartments', 'name')
                            ->preload()
                            ->searchable()
                            ->helperText('Isi hanya jika user ini adalah SC (Steering Committee) dari BPH yang mengawasi departemen tertentu.')
                            ->visible(function (\Filament\Forms\Get $get) {
                                $selectedRoles = $get('roles');
                                if (empty($selectedRoles)) {
                                    return false;
                                }
                                $bphRoleId = \Spatie\Permission\Models\Role::where('name', 'bph')->value('id');
                                return in_array('bph', (array) $selectedRoles) || in_array((string)$bphRoleId, array_map('strval', (array) $selectedRoles));
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('department.name')
                    ->label('Departemen Utama')
                    ->placeholder('–')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('sub_division')
                    ->label('Sub Divisi')
                    ->placeholder('–')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('roles.name')
                    ->label('Role')
                    ->badge()
                    ->separator(',')
                    ->placeholder('–'),
                TextColumn::make('scDepartments.name')
                    ->label('SC Departemen')
                    ->badge()
                    ->color('warning')
                    ->separator(',')
                    ->placeholder('–'),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Terakhir Diperbarui')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('roles')
                    ->label('Filter Role')
                    ->relationship('roles', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('department_id')
                    ->label('Filter Departemen')
                    ->relationship('department', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit'   => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
