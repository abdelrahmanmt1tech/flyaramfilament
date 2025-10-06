<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Session;

class NewUsers extends Page implements HasTable,HasForms
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
                    ->form([
                        TextInput::make('name')
                            ->label('اسم المستخدم')
                            ->required(),

                        TextInput::make('email')
                            ->label('البريد الإلكتروني')
                            ->email()
                            ->required(),

                        TextInput::make('iata_code')
                            ->label('كود IATA')
                            ->default(fn($record)=>$record['iata_code'])
                            ->readonly(),

                        TextInput::make('password')
                            ->label('كلمة المرور')
                            ->password()
                            ->required(),
                    ])
//                    ->mountUsing(function (ComponentContainer $form, $record) {
//                        // تعبئة الكود تلقائياً
//                        $form->fill([
//                            'iata_code' => $record['iata_code'] ?? $record,
//                        ]);
//                    })
                    ->action(function (array $data, $record, $livewire) {
                        // إنشاء المستخدم
                        User::create([
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'password' => bcrypt($data['password']),
                            'iata_code' => $data['iata_code'],
                        ]);

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
