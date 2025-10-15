<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\Pages\CreateUser;
use App\Filament\Resources\UserResource\Pages\EditUser;
use App\Filament\Resources\UserResource\Pages\ListUsers;
use App\Models\Branch;
use App\Models\Franchise;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'admins';

    protected static ?int $navigationSort = 10;

    protected static ?string $navigationLabel = 'المسؤولون';
    protected static ?string $pluralModelLabel = 'المسؤولون';
    protected static ?string $modelLabel = 'مسؤول';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::UserCircle;

    public static function getNavigationLabel(): string
    {
        return 'المستخدمون';
    }

      public static function canViewAny(): bool
    {
        return Auth::user()->can('users.view');
    }

    public static function canCreate(): bool
    {
        return Auth::user()->can('users.create');
    }

    public static function canEdit($record): bool
    {
        return Auth::user()->can('users.update');
    }

    public static function canDelete($record): bool
    {
        return Auth::user()->can('users.delete');
    }
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('البيانات الأساسية')
                ->schema([
                    Grid::make(2)->schema([
                        TextInput::make('name')
                            ->label('الاسم')
                            ->required(),

                        TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->required(),

                        Select::make('roles')
                            ->label('الدور')
                            ->relationship('roles', 'name')
                            ->searchable()
                            ->preload()
                            ->native(false),

                        TextInput::make('password')
                            ->label('كلمة المرور')
                            ->password()
                            ->confirmed() // validation confirmed
                            ->dehydrateStateUsing(fn($state) => !empty($state) ? bcrypt($state) : null)
                            ->dehydrated(fn($state) => filled($state)),

                        TextInput::make('password_confirmation')
                            ->label('تأكيد كلمة المرور')
                            ->password()
                            ->dehydrated(false), // مش بيتخزن في الداتا بيز
                    ]),
                ]),

            Section::make('الربط')
                ->schema([
                    Grid::make(2)->schema([
                        Select::make('branches')
                            ->label('الفروع')
                            ->relationship('branches', 'name') // auto selected in edit
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->native(false),

                        Select::make('franchises')
                            ->label('الفرانشايز')
                            ->relationship('franchises', 'name') // auto selected in edit
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->native(false),
                    ]),
                ]),

        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('الاسم')->searchable()->sortable(),
                TextColumn::make('email')->label('البريد الإلكتروني')->searchable(),
                TextColumn::make('roles.name')->label('الدور')->listWithLineBreaks()->limitList(3),
                TextColumn::make('iata_code')->label('IATA'),
                TextColumn::make('branches.name')->label('الفروع')->listWithLineBreaks()->limitList(3),
                TextColumn::make('franchises.name')->label('الفرانشايز')->listWithLineBreaks()->limitList(3),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
                ForceDeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
            // 'new-users' => Pages\NewUsers::route('/new-users'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }
}
