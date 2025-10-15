<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoleResource\Pages;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $slug = 'roles';

    protected static ?int $navigationSort = 9;

    protected static ?string $navigationLabel = 'الادوار والصلاحيات';
    protected static ?string $pluralModelLabel = 'الادوار والصلاحيات';
    protected static ?string $modelLabel = 'الدور';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserCircle;

    public static function getNavigationLabel(): string
    {
        return __('dashboard.sidebar.roles');
    }

       public static function canViewAny(): bool
    {
        return Auth::user()->can('roles.view');
    }

    public static function canCreate(): bool
    {
        return Auth::user()->can('roles.create');
    }

    public static function canEdit($record): bool
    {
        return Auth::user()->can('roles.update');
    }

    public static function canDelete($record): bool
    {
        return Auth::user()->can('roles.delete');
    }
    
    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                Group::make()
                    ->schema([
                        Section::make(__('dashboard.fields.role'))
                            ->schema([
                                TextInput::make('name')
                                    ->label(__('dashboard.fields.role_name'))
                                    ->required()
                                    ->maxLength(255),

                            ])->columns(2),

                        Section::make(__('dashboard.fields.permissions'))
                            ->schema([
                                CheckboxList::make('permissions')
                                    ->relationship('permissions', 'name')
                                    ->label(__('dashboard.fields.permissions'))
                                    ->options(
                                       Permission::all()
                                            ->pluck('name', 'id')
                                            ->mapWithKeys(fn($name, $id) => [$id => __("permissions.$name")])
                                    )
                                    ->columns(2)
                                    ->searchable()
                                    ->bulkToggleable()
                                    ->gridDirection('row')
                                    ->helperText(__('dashboard.fields.select_permissions')),
                            ]),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('dashboard.fields.role_name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label(__('dashboard.fields.permissions')),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
