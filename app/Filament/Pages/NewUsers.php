<?php

namespace App\Filament\Pages;

use App\Models\Branch;
use App\Models\Franchise;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Grid;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Session;

class NewUsers extends Page implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;


    protected string $view = 'filament.pages.new-users';



    protected static string|null|\UnitEnum $navigationGroup = 'مستخدمين جدد';
    protected static ?string $navigationLabel = 'مستخدمين جدد ';
    protected static ?string $title = 'مستخدمين جدد';
    protected static bool $shouldRegisterNavigation = false;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::Users;




    public array $newUsers = [];

    public function mount(): void
    {
        $this->newUsers = Session::get('NEW_USERS', []);
    }



    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('iata_code')
                    ->label('كود المستخدم')
                    ->sortable(),
            ])
            ->recordActions([
                Action::make('addUser')
                    ->label('إضافة كمستخدم')
                    ->button()
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->schema([
                        TextInput::make('name')
                            ->label('اسم المستخدم')
                            ->required(),

                        TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->required(),

                        TextInput::make('iata_code')
                            ->label('كود IATA')
                            ->default(fn($record) => $record['iata_code'])
                            ->readonly(),

                        TextInput::make('password')
                            ->label('كلمة المرور')
                            ->password()
                            ->required(),
                        TextInput::make('password_confirmation')
                            ->label('تأكيد كلمة المرور')
                            ->password()
                            ->required()
                            ->dehydrated(false),

                        Grid::make(2)->schema([
                            Select::make('branches')
                                ->label('الفروع')
                                ->options(Branch::pluck('name', 'id'))
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->native(false),

                            Select::make('franchises')
                                ->label('الفرانشايز')
                                ->options(Franchise::pluck('name', 'id'))
                                ->multiple()
                                ->searchable()
                                ->preload()
                                ->native(false),
                        ]),
                    ])
                    //    ->mountUsing(function (ComponentContainer $form, $record) {
                    //        // تعبئة الكود تلقائياً
                    //        $form->fill([
                    //            'iata_code' => $record['iata_code'] ?? $record,
                    //        ]);
                    //    })
                    ->action(function (array $data, $record, $livewire) {
                        // إنشاء المستخدم
                        $u=User::create([
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'password' => bcrypt($data['password']),
                            'iata_code' => $data['iata_code'],
                        ]);

                        if (!empty($data['branches'])) {
                            $u->branches()->sync($data['branches']);
                        }
                        
                        if (!empty($data['franchises'])) {
                            $u->franchises()->sync($data['franchises']);
                        }

                        // حذف الكود من الـ session
                        $codes = Session::get('NEW_USERS', []);
                        $codes = array_values(array_filter($codes, fn($c) => $c !== $data['iata_code']));
                        Session::put('NEW_USERS', $codes);

                        // تحديث الجدول
                        $livewire->newUsers = $codes;

                        Notification::make()
                            ->title("تمت إضافة المستخدم {$data['name']} بنجاح ✅")
                            ->success()
                            ->send();
                    }),
            ])
            ->records(fn() => $this->getRecords());
    }

    public function getRecords(): array
    {
        // تحويل الأكواد إلى صفوف جدول
        return collect($this->newUsers)
            ->map(fn($code) => ['iata_code' => $code])
            ->toArray();
    }
}
