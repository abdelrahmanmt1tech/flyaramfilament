<?php

namespace App\Filament\Resources\Reservations\Schemas;

use Filament\Schemas\Schema;
use App\Models\Branch;
use App\Models\Client;
use App\Models\Currency;
use App\Models\Franchise;
use App\Models\Passenger;
use App\Models\Supplier;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Illuminate\Database\Eloquent\Model;

class ReservationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            // القسم الأساسي (بيانات الحجز الرئيسية)
            Section::make('المعلومات الأساسية')->schema([
                TextInput::make('reservation_number')
                    ->label('رقم الحجز')
                    ->numeric()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->disabled()
                    ->dehydrated()
                    ->default(fn() => 'RSV' . date('Ymd') . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT)),

                // ربط مع تذكرة (اختياري)
                Select::make('ticket_id')
                    ->label('التذكرة')
                    ->relationship('ticket', 'ticket_number_core')
                    ->searchable()
                    ->preload()
                    ->native(false),

                // المسافر على مستوى الحجز
                Select::make('passenger_id')
                    ->label('المسافر')
                    ->options(Passenger::pluck('first_name', 'id')->toArray())
                    ->searchable()
                    ->native(false),
            ])
                ->columnSpanFull()
                ->columns(3),

            // حقول الفندق/السيارة أصبحت داخل العناصر (Repeater)


            // قسم العلاقات المورفية (الجهة)
            Section::make('العلاقات')->schema([



                // مورف الجهة (related)
                Select::make('related_type')
                    ->label('نوع الجهة')
                    ->options([
                        Client::class => 'عميل',
                        Supplier::class => 'مورد',
                        Branch::class => 'فرع',
                        Franchise::class => 'فرانشايز',
                    ])
                    ->searchable()
                    ->native(false)
                    ->live()
                    ->required()
                    ->afterStateUpdated(fn($set) => $set('related_id', null)),

                Select::make('related_id')
                    ->label('الجهة')
                    ->options(function (callable $get) {
                        $type = $get('related_type');
                        if (!$type) {
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
                    ->required()
                    ->disabled(fn(callable $get) => !$get('related_type')),

            ])
                ->columnSpanFull()
                ->columns(2),

            // عناصر الحجز (Repeater مرتبط بجدول reservation_items)
                Repeater::make('items')
                    ->label('إضافة عنصر')
                    ->relationship('items')
                    ->grid(1)
                    ->collapsible()
                    ->addActionLabel('إضافة عنصر')
                    ->schema([
                        Section::make('بيانات العنصر الأساسية')->schema([
                            Select::make('reservation_type')
                                ->label('نوع الحجز')
                                ->options([
                                    'hotel' => 'فندق',
                                    'car' => 'سيارة',
                                    'tourism' => 'سياحة',
                                ])
                                ->required()
                                ->live()
                                ->native(false),

                            DatePicker::make('date')
                                ->label('تاريخ الحجز')
                                ->default(now()),

                            TextInput::make('agent_name')
                                ->label('اسم الوكيل'),

                            TextInput::make('destination')
                                ->label('الوجهة'),
                        ])->columns(2),


                        // حقول الفندق (تظهر عند اختيار فندق)
                        Section::make('معلومات الفندق')
                            ->visible(fn(callable $get) => $get('reservation_type') === 'hotel')
                            ->schema([
                                TextInput::make('hotel_name')->label('اسم الفندق')->required(),
                                TextInput::make('confirmation_number')->label('رقم التأكيد'),
                                TextInput::make('room_type')->label('نوع الغرفة'),
                                TextInput::make('destination_type')->label('نوع الوجهة'),
                                Grid::make(3)->schema([
                                    TextInput::make('room_count')->label('عدد الغرف')->numeric()->minValue(1),
                                    TextInput::make('nights_count')->label('عدد الليالي')->numeric()->minValue(1),
                                    TextInput::make('room_price')->label('سعر الغرفة')->numeric()->minValue(0)->suffix('SAR'),
                                ]),
                                Grid::make(3)->schema([
                                    DatePicker::make('arrival_date')->label('تاريخ الوصول')->required(),
                                    DatePicker::make('departure_date')->label('تاريخ المغادرة')->required(),
                                    TextInput::make('total_amount')->label('المبلغ الإجمالي')->numeric()->minValue(0)->suffix('SAR'),
                                ]),
                            ])->columns(2),


                        Section::make('المعلومات')
                            ->visible(fn(callable $get) => $get('reservation_type') === 'car' || $get('reservation_type') === 'tourism')
                            ->schema([
                                TextInput::make('service_type')->label('نوع الخدمة')->required(),
                                TextInput::make('document')->label('المستند'),
                                Grid::make(2)->schema([
                                    TextInput::make('count')->label('العدد')->numeric()->minValue(1),
                                    TextInput::make('unit_price')->label('سعر الوحدة')->numeric()->minValue(0)->suffix('SAR'),
                                ]),
                                Grid::make(2)->schema([
                                    DatePicker::make('from_date')->label('من تاريخ')->required(),
                                    DatePicker::make('to_date')->label('إلى تاريخ')->required(),
                                ]),
                                TextInput::make('service_details')->label('تفاصيل الخدمة'),
                                TextInput::make('additional_info')->label('معلومات إضافية'),
                            ])->columns(2),


                        Section::make('الجهات ذات الصلة')->schema([
                            Select::make('supplier_id')
                                ->label('المورد')
                                ->relationship('supplier', 'name')
                                ->searchable()
                                ->preload()
                                ->native(false),

                            Select::make('currency_id')
                                ->label('العملة')
                                ->relationship('currency', 'symbol')
                                ->searchable()
                                ->preload()
                                ->native(false)
                                ->required(),

                            Select::make('safes_bank_id')
                                ->label('الخزنة / البنك')
                                ->relationship('safesBank', 'name')
                                ->searchable()
                                ->preload()
                                ->native(false),
                        ])->columns(3),

                        Section::make('معلومات مالية')->schema([
                            Grid::make(3)->schema([
                                TextInput::make('purchase_amount')->label('سعر الشراء')->numeric()->minValue(0)->suffix('SAR'),
                                TextInput::make('sale_amount')->label('سعر البيع')->numeric()->minValue(0)->suffix('SAR'),
                                TextInput::make('commission_amount')->label('العمولة')->numeric()->minValue(0)->suffix('SAR'),
                            ]),
                            Grid::make(2)->schema([
                                TextInput::make('cash_payment')->label('الدفع نقداً')->numeric()->minValue(0)->suffix('SAR'),
                                TextInput::make('visa_payment')->label('الدفع بفيزا')->numeric()->minValue(0)->suffix('SAR'),
                            ]),
                            TextInput::make('account_number')->label('رقم الحساب'),
                        ])->columns(1),


                        // ملاحظات عامة على العنصر
                        Section::make('ملاحظات')->schema([
                            TextInput::make('special_requests')->label('طلبات خاصة'),
                            TextInput::make('additions')->label('إضافات'),
                            TextInput::make('notes')->label('ملاحظات'),
                            TextInput::make('added_value')->label('القيمة المضافة'),
                        ])->columns(2),
                    ])->columnSpanFull(),
        ]);
    }

    private static function getHotelFields(): array
    {
        return [
            TextInput::make('hotel_name')
                ->label('اسم الفندق')
                ->required(),

            TextInput::make('confirmation_number')
                ->label('رقم التأكيد'),

            TextInput::make('room_type')
                ->label('نوع الغرفة'),

            TextInput::make('destination_type')
                ->label('نوع الوجهة'),

            Grid::make(3)->schema([
                TextInput::make('room_count')
                    ->label('عدد الغرف')
                    ->numeric()
                    ->minValue(1),

                TextInput::make('nights_count')
                    ->label('عدد الليالي')
                    ->numeric()
                    ->minValue(1),

                TextInput::make('room_price')
                    ->label('سعر الغرفة')
                    ->numeric()
                    ->minValue(0)
                    ->suffix('SAR'),
            ]),

            Grid::make(3)->schema([
                DatePicker::make('arrival_date')
                    ->label('تاريخ الوصول')
                    ->required(),

                DatePicker::make('departure_date')
                    ->label('تاريخ المغادرة')
                    ->required(),

                TextInput::make('total_amount')
                    ->label('المبلغ الإجمالي')
                    ->numeric()
                    ->minValue(0)
                    ->suffix('SAR'),
            ]),
        ];
    }

    private static function getCarFields(): array
    {
        return [
            TextInput::make('service_type')
                ->label('نوع الخدمة')
                ->required(),

            TextInput::make('document')
                ->label('المستند'),

            Grid::make(2)->schema([
                TextInput::make('count')
                    ->label('العدد')
                    ->numeric()
                    ->minValue(1),

                TextInput::make('unit_price')
                    ->label('سعر الوحدة')
                    ->numeric()
                    ->minValue(0)
                    ->suffix('SAR'),
            ]),

            Grid::make(2)->schema([
                DatePicker::make('from_date')
                    ->label('من تاريخ')
                    ->required(),

                DatePicker::make('to_date')
                    ->label('إلى تاريخ')
                    ->required(),
            ]),

            Textarea::make('service_details')
                ->label('تفاصيل الخدمة')
                ->rows(3),
            // ->columnSpanFull(),

            Textarea::make('additional_info')
                ->label('معلومات إضافية')
                ->rows(3),
            // ->columnSpanFull(),
        ];
    }
}
