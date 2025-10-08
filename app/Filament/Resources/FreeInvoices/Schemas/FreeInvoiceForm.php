<?php

namespace App\Filament\Resources\FreeInvoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

use App\Models\Client;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\Franchise;

class FreeInvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                // Section: Entity selection
                Section::make('الجهة المرتبطة')
                    ->description('اختر نوع الجهة المرتبطة بالفاتورة')
                    ->schema([
                        Grid::make(2)->schema([
                            Select::make('free_invoiceable_type')
                                ->label('نوع الجهة')
                                ->options([
                                    Client::class => 'عميل',
                                    Supplier::class => 'مورد',
                                    Branch::class => 'فرع',
                                    Franchise::class => 'فرانشايز',
                                    'other' => 'أخرى', 
                                ])
                                ->searchable()
                                ->native(false)
                                ->live()
                                ->required()
                                ->afterStateUpdated(fn($set) => $set('free_invoiceable_id', null)),

                            Select::make('free_invoiceable_id')
                                ->label('الجهة')
                                ->options(function (callable $get) {
                                    $type = $get('free_invoiceable_type');
                                    if (!$type || $type === 'other') {
                                        return [];
                                    }

                                    return match ($type) {
                                        Client::class => Client::pluck('name', 'id')->toArray(),
                                        Supplier::class => Supplier::pluck('name', 'id')->toArray(),
                                        Branch::class => Branch::pluck('name', 'id')->toArray(),
                                        Franchise::class => Franchise::pluck('name', 'id')->toArray(),
                                        default => [],
                                    };
                                })
                                ->searchable()
                                ->native(false)
                                ->placeholder('اختر الجهة أولاً من نوع الجهة')
                                ->required(fn(callable $get) => $get('free_invoiceable_type') !== 'other')
                                ->disabled(fn(callable $get) => !$get('free_invoiceable_type') || $get('free_invoiceable_type') === 'other'),
                        ]),
                    ]),

                // Section: Beneficiary data (only if type = other)
                Section::make('بيانات المستفيد')
                    ->description('أدخل بيانات المستفيد إذا كان نوع الجهة "أخرى"')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('beneficiary_name')
                                ->label('اسم المستفيد')
                                ->required()
                                ->maxLength(255),

                            TextInput::make('beneficiary_tax_number')
                                ->label('رقم الضريبة')
                                ->maxLength(255),

                            TextInput::make('beneficiary_phone')
                                ->label('الهاتف')
                                ->tel()
                                ->maxLength(255),

                            TextInput::make('beneficiary_email')
                                ->label('البريد الإلكتروني')
                                ->email()
                                ->maxLength(255),
                        ]),

                        TextInput::make('beneficiary_address')
                            ->label('العنوان')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->visible(fn(callable $get) => $get('free_invoiceable_type') === 'other'),

                // Section: Items
                Section::make('عناصر الفاتورة')
                    ->schema([
                        Repeater::make('items')
                            ->label('العناصر')
                            ->schema([
                                TextInput::make('name')
                                    ->label('اسم العنصر')
                                    ->required()
                                    ->maxLength(255),

                                TextInput::make('quantity')
                                    ->label('الكمية')
                                    ->numeric()
                                    ->required()
                                    ->minValue(1)
                                    ->default(1),

                                TextInput::make('price')
                                    ->label('السعر')
                                    ->numeric()
                                    ->required()
                                    ->prefix('ر.س')
                                    ->minValue(0),
                            ])
                            ->columns(3)
                            ->defaultItems(1)
                            ->addActionLabel('إضافة عنصر')
                            ->itemLabel(fn(array $state): ?string => $state['name'] ?? null)
                            ->collapsible()
                            ->columnSpanFull(),
                    ]),

                // Section: Dates + total
                Section::make('تفاصيل الفاتورة')
                    ->schema([
                        DatePicker::make('issue_date')
                            ->label('تاريخ الإصدار')
                            ->required()
                            ->default(today()),

                        DatePicker::make('due_date')
                            ->label('تاريخ الاستحقاق')
                            ->required()
                            ->default(today()->addDays(30)),

                        TextInput::make('total')
                            ->label('الإجمالي')
                            ->numeric()
                            ->disabled()
                            ->prefix('ر.س'),
                    ])
                    ->columns(3),
            ]);
    }
}
