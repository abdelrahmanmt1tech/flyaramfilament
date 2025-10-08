<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Filament\SharedForms\ContactInfoForm;
use App\Models\Supplier;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SupplierResource extends Resource
{
    protected static ?string $model = Supplier::class;

    protected static ?string $slug = 'suppliers';

    protected static ?int $navigationSort = 45;

    protected static ?string $navigationLabel = "الموردون";
    protected static ?string $pluralModelLabel = "الموردون";
    protected static ?string $modelLabel = 'مورد';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Truck;

    public static function getNavigationLabel(): string
    {
        return __('dashboard.sidebar.suppliers');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('dashboard.fields.basic_info'))
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->label(__('dashboard.fields.name'))
                            ->columnSpanFull(),


                        TextInput::make('tax_number')
                            ->required()
                            ->label(__('dashboard.fields.tax_number'))
                            ->columnSpanFull(),
                    ]),

                Section::make(__('dashboard.fields.contact_info'))
                    ->schema([
                        Repeater::make('contactInfos')
                            ->relationship('contactInfos')
                            ->schema(ContactInfoForm::make())
                            ->label(__('dashboard.fields.contact_infos'))
                            ->reorderable()
                            ->collapsible()
                            ->columns(2)
                            ->defaultItems(0)
                            ->addActionLabel(__('dashboard.fields.add_contact_info')),
                    ])
                    ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('dashboard.fields.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tax_number')
                    ->label(__('dashboard.fields.tax_number'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('contactInfos.phone')
                    ->label(__('dashboard.fields.phone'))
                    ->getStateUsing(fn ($record) => $record->contactInfos()->first()?->phone ?? '-')
                    ->sortable(false)
                    ->searchable(false),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('statement')
                ->label('كشف الحساب')
                ->icon('heroicon-o-document-text')
                ->color('success')
                ->url(fn($record) => url("/admin/account-statement-page") . '?' . http_build_query([
                    'tableFilters' => [
                        'date_filter' => [
                            'date_range' => 'current_month',
                        ],
                        'account_filter' => [
                            'statementable_type' => get_class($record), // عشان يجيب الكلاس الحقيقي للموديل
                            'statementable_id'   => $record->id,
                        ],
                    ],
                ]))

                ->openUrlInNewTab(),
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
            'index' => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit' => Pages\EditSupplier::route('/{record}/edit'),
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
        return ['name'];
    }
}
