<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LeadSourceResource\Pages;
use App\Models\LeadSource;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class LeadSourceResource extends Resource
{
    protected static ?string $model = LeadSource::class;

    protected static ?string $slug = 'lead-sources';

    protected static ?int $navigationSort = 110;

    protected static ?string $navigationLabel = "مصادر العملاء المحتملين";
    protected static ?string $pluralModelLabel = "مصادر العملاء المحتملين";
    protected static ?string $modelLabel = 'مصدر عميل محتمل';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::Funnel;

    public static function getNavigationLabel(): string
    {
        return __('dashboard.sidebar.lead_sources');
    }

       public static function canViewAny(): bool
    {
        return Auth::user()->can('lead_sources.view');
    }

    public static function canCreate(): bool
    {
        return Auth::user()->can('lead_sources.create');
    }

    public static function canEdit($record): bool
    {
        return Auth::user()->can('lead_sources.update');
    }

    public static function canDelete($record): bool
    {
        return Auth::user()->can('lead_sources.delete');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name.ar')
                    ->label(__('dashboard.fields.name_ar'))->suffix("ar")
                    ->required(),

                TextInput::make('name.en')
                    ->label(__('dashboard.fields.name_en'))->suffix("en")
                    ->required(),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label(__('dashboard.fields.name')),
            ])
            ->filters([
                TrashedFilter::make(),
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
            'index' => Pages\ListLeadSources::route('/'),
//            'create' => Pages\CreateLeadSource::route('/create'),
//            'edit' => Pages\EditLeadSource::route('/{record}/edit'),
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
