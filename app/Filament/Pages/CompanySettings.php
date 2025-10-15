<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions as SchemaActions;
use Filament\Schemas\Components\Form as SchemaForm;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Auth;
use UnitEnum;

class CompanySettings extends Page
{
    protected static string | UnitEnum | null $navigationGroup = 'الإعدادات';
    protected static ?string $navigationLabel = 'بيانات الشركة';
    protected static ?string $title = 'بيانات الشركة';
    protected static ?int $navigationSort = 100;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::BuildingOffice;

    protected string $view = 'filament.pages.company-settings';

    public ?array $data = [];

    public static function getNavigationGroup(): ?string
    {
        return 'الإعدادات';
    }

    public static function getNavigationSort(): ?int
    {
        return 100;
    }

    public static function canViewAny(): bool
    {
        return Auth::user()->can('company_settings.view');
    }

    public static function canCreate(): bool
    {
        return Auth::user()->can('company_settings.create');
    }

    public function mount(): void
    {
        $this->form->fill($this->getRecord()?->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaForm::make([
                    Section::make('اسم الشركة')
                        ->schema([
                            TextInput::make('company_name_ar')
                                ->label('اسم الشركة (عربي)')
                                ->maxLength(255),
                            TextInput::make('company_name_en')
                                ->label('Company Name (English)')
                                ->maxLength(255),
                        ])
                        ->columns(2),

                    Section::make('العنوان')
                        ->schema([
                            TextInput::make('company_address_ar')
                                ->label('العنوان (عربي)')
                                ->maxLength(500),
                            TextInput::make('company_address_en')
                                ->label('Address (English)')
                                ->maxLength(500),
                        ])
                        ->columns(2),

                    Section::make('البيانات القانونية')
                        ->schema([
                            TextInput::make('tax_number')
                                ->label('الرقم الضريبي')
                                ->maxLength(255),

                            TextInput::make('commercial_register')
                                ->label('رقم السجل التجاري')
                                ->maxLength(255),

                            TextInput::make('tourism_license')
                                ->label('رقم ترخيص السياحة')
                                ->maxLength(255),
                        ])
                        ->columns(2),
                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        SchemaActions::make([
                            Action::make('save')
                                ->submit('save')
                                ->label('حفظ')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        Notification::make()
            ->success()
            ->title('تم حفظ بيانات الشركة بنجاح')
            ->send();
    }

    public function getRecord()
    {
        $keys = [
            'company_name_ar', 'company_name_en',
            'company_address_ar', 'company_address_en',
            'tax_number',
            'commercial_register',
            'tourism_license',
        ];

        $settings = Setting::whereIn('key', $keys)->pluck('value', 'key')->toArray();

        return collect($settings);
    }
}
