<?php

namespace App\Filament\Pages;

use App\Models\Airline;
use App\Models\Airport;
use App\Models\AirportRoute;
use App\Models\Currency;
use App\Models\Passenger;
use App\Models\Supplier;
use App\Models\Ticket;
use App\Services\Tickets\TicketParser;
use DB;
use Filament\Actions\Action;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use File;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class UploadTicket extends Page
{
    protected string $view = 'filament.pages.upload-ticket';


    public $defaultAction = 'onboarding';
    protected ?string $subheading = 'Custom Page Subheading';


    protected static string|\BackedEnum|null $navigationIcon = Heroicon::CloudArrowUp;




    /*    public function onboardingAction(): Action
        {
    //        return Action::make('onboarding')
    //            ->modalHeading('Welcome')
    //            ->visible(fn (): bool => ! auth()->user()->isOnBoarded())
    //            ;
        }*/


    public ?array $data = [];
    public $text_file;


    public function mount(): void
    {
        // $this->form->fill($this->getRecord()?->attributesToArray());
        $this->text_file = null;
        $this->form->fill(['text_file' => null]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([

                    FileUpload::make('text_file')
                        ->label('Upload TXT File')
                        ->required()
                        ->previewable(false)
                        ->moveFiles()
                        ->directory('tickets') // مسار الحفظ داخل disk
                        ->disk('public') // أو local إن أردت

                ])
                    ->livewireSubmitHandler('save')
                    ->footer([

                        Actions::make([
                            Action::make('save')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ])
//            ->record($this->getRecord())
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        if (empty($data['text_file'])) {

            Notification::make()
                ->danger()
                ->title('من فضلك اختر ملفاً')
                ->send();

            return;
        }

        if (isset($data['text_file'])) {
            // 2) مسار الملف على القرص
            $disk = Storage::disk('public');
            $relativePath = $data['text_file'];                   // مثال: uploads/tickets/abc.zip
            $absPath      = $disk->path($relativePath);
            $ext          = strtolower(pathinfo($absPath, PATHINFO_EXTENSION));

            $parser = new TicketParser();

            try {

                if ($ext === 'zip') {
                    $zip = new ZipArchive();
                    if ($zip->open($absPath) !== true) {
                        Notification::make()
                            ->danger()
                            ->title( 'تعذّر فتح ملف ZIP')
                            ->send();
                        return;
                    }
                    for ($i = 0; $i < $zip->numFiles; $i++) {
                        $stat = $zip->statIndex($i);
                        if (!$stat) continue;
                        $name = $stat['name'];
                        // تجاهل الأدلة
                        if (str_ends_with($name, '/')) continue;

                        // إقرأ المحتوى كنص
                        $content = $zip->getFromIndex($i);
                        if ($content === false) continue;

                        // لو فيه احتمال ترميز غير UTF-8:
                        if (!mb_detect_encoding($content, 'UTF-8', true)) {
                            $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1256,ISO-8859-6,ASCII,UTF-8');
                        }
                        $dto = $parser->parse($content);
                        $this->storeTicketDto($dto, basename($name));
                    }
                    $zip->close();
                } else {
                    if (!is_file($absPath)) {
                        Notification::make()
                            ->danger()
                            ->title(  'الملف غير موجود على القرص')
                            ->send();
                        return;
                    }

                    $content = file_get_contents($absPath);
                    if (!mb_detect_encoding($content, 'UTF-8', true)) {
                        $content = mb_convert_encoding($content, 'UTF-8', 'Windows-1256,ISO-8859-6,ASCII,UTF-8');
                    }
                    $dto = $parser->parse($content);
                    $this->storeTicketDto($dto, basename($absPath));
                }

                /*

                حدث خطأ أثناء الاستيراد: SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'symbol' cannot be null
                (Connection: mysql, SQL: insert into `currencies`
                 (`symbol`, `name`, `updated_at`, `created_at`) values (?, {"en":null}, 2025-09-30 07:16:27, 2025-09-30 07:16:27))



                */
                Notification::make()
                    ->success()
                    ->title('تم استيراد التذكرة/التذاكر بنجاح')
                    ->send();

            } catch (\Throwable $e) {
                report($e);
                Notification::make()
                    ->danger()
                    ->title('حدث خطأ أثناء الاستيراد: ' . $e->getMessage())
                    ->send();
            }

        }

    }



    protected function storeTicketDto(\App\Services\Tickets\DTOs\TicketDTO $dto, ?string $sourceFileName = null): void
    {
        /*
        // مثال تبسيطي للتأكد من عدم التكرار:
        $ticket = \App\Models\Ticket::where('ticket_number_full', $dto->ticketNumber)->first();

        if ($ticket) {
            // لو تحب تحديث/استبدال:
            // $ticket->delete(); أو $ticket->update([...])
            $ticket->delete();
        }

*/

        DB::transaction(function() use ($dto) {
            $ticket = Ticket::create([
                'gds' => $dto->gds,
                'airline_name' => $dto->airlineName,
                'validating_carrier_code' => $dto->validatingCarrier,
                'ticket_number_full' => $dto->ticketNumber,
                'ticket_number_prefix' => $dto->ticketNumberPrefix,
                'ticket_number_core' => $dto->ticketNumberCore,
                'pnr' => $dto->pnr ?? ($dto->meta['pnr'] ?? null),
                'issue_date' => $dto->issueDate,
                'booking_date' => $dto->bookingDate,
                'ticket_type' => $dto->ticketType,
                'ticket_type_code' => $dto->ticketTypeCode,
                'trip_type' => $dto->type,
                'is_domestic_flight' => $dto->isDomesticFlight,
                'itinerary_string' => $dto->itineraryString,
                'fare_basis_out' => $dto->fareBasisOut,
                'fare_basis_in' => $dto->fareBasisIn,
                'branch_code' => $dto->branchCode,
                'office_id' => $dto->officeId,
                'created_by_user' => $dto->createdByUser,
                'supplier_id' =>$dto->supplier ?  optional(Supplier::firstOrCreate(['name'=>$dto->supplier], ['tax_number'=>null]))->id  : null ,
                'currency_id' => $dto->price?->baseCurrency ?
                    optional(Currency::firstOrCreate(['symbol'=> $dto->price->baseCurrency] ,['name'=> $dto->price->baseCurrency])->first())->id : null,
                'cost_base_amount' => $dto->price->baseAmount,
                'cost_tax_amount' => collect($dto->price->taxes)->sum(fn($t)=> (float)$t['amount']),
                'cost_total_amount' => $dto->price->totalAmount,
                // profit/discount/extra_tax يضيفهم الأدمن لاحقًا أو ندعهم null
            ]);
            // passengers
            foreach ($dto->passengers as $p) {
                $passenger = Passenger::firstOrCreate(
                    [
                        'first_name' => $p->fullName ,
                        'last_name' => $p->lastName ,
                        'phone'=>$p->phone ,
                    ],
                    [
                        'title'=>$p->title,
                        'first_name'=>$p->firstName, 'last_name'=>$p->lastName,
                        'email'=>$p->email, 'phone'=>$p->phone,

                    ]
                );
                $ticket->passengers()->attach($passenger->id, [
                    'ticket_number_full' => $ticket->ticket_number_full,
                    'ticket_number_prefix' => $ticket->ticket_number_prefix,
                    'ticket_number_core' => $ticket->ticket_number_core,
                ]);
            }


            // segments + routes + airports
            foreach ($dto->segments as $idx => $s) {

                $o = Airport::firstOrCreate(['iata'=>$s->origin], ['name'=>$s->originName, 'country_code'=>$s->originCountry]);
                $d = Airport::firstOrCreate(['iata'=>$s->destination], ['name'=>$s->destinationName, 'country_code'=>$s->destCountry]);
                $route = AirportRoute::firstOrCreate(
                    ['origin_airport_id'=>$o->id, 'destination_airport_id'=>$d->id],
                    ['display_name'=> "{$o->iata} - {$d->iata}"]
                );

//                    $carrierAirline = $s->carrier ? Airline::firstOrCreate(['code'=>$s->carrier], ['name'=>$s->carrier]) : null;


                $carrierAirline = null;
                if (!empty($s->carrier)) {


                    $op = Airline::firstOrCreate(
                        ['iata_code' => strtoupper($s->carrier)],
                        ['name' => strtoupper($s->carrier)]
                    );
                    if (!empty($dto->ticketNumberPrefix) && empty($op->iata_prefix)) {
                        // مش شرط نفس الـ prefix؛ الأفضل تتركها كما هي، إلا لو كنت متأكد
                    }


//                        $carrierAirline = Airline::firstOrCreate(
//                            ['code' => strtoupper($s->carrier)],
//                            ['name' => strtoupper($s->carrier), 'is_internal' => false]
//                        );
                }
//

                $ticket->segments()->create([
                    'segment_index' => $s->index ?? ($idx+1),
                    'origin_airport_id' => $o->id,
                    'destination_airport_id' => $d->id,
                    'route_id' => $route->id,
                    'carrier_airline_id' => $op?->id,
                    'carrier_code' => $op->iata_code,

                    'flight_number' => $s->flightNumber,
                    'booking_class' => $s->bookingClass,
                    'status' => $s->status,
                    'equipment' => $s->equipment,
                    'baggage' => $s->baggage,
                    'meal' => $s->meal,
                    'departure_at' => $s->departureDateTime,
                    'arrival_at' => $s->arrivalDateTime,
                    'origin_country' => $s->originCountry,
                    'destination_country' => $s->destCountry,
                    'fare_basis' => $s->fareBasis ?? null,
                ]);
            }
            if (!empty($dto->price->taxes)) {
                $ticket->update(['price_taxes_breakdown' => $dto->price->taxes]);
            }
            foreach ($dto->price->taxes as $t) {
                $ticket->taxes()->create([
                    'amount'=>$t['amount'],
                    'code'=>$t['code'],
                    'currency_id' =>$t['currency'] ?  optional(Currency::firstOrCreate(['symbol'=> $t['currency']] ,['name'=> $t['currency']])->first())->id : null,
                ]) ;
            }

            if ($dto->validatingCarrier) {
                $airline = Airline::firstOrCreate(
                    ['iata_code' => strtoupper($dto->validatingCarrier)],
                    ['name' => $dto->airlineName ?: strtoupper($dto->validatingCarrier)]
                );
                // لو عندك prefix من التذكرة، حدّثه لو كان ناقص
                if (!empty($dto->ticketNumberPrefix) && empty($airline->iata_prefix)) {
                    $airline->iata_prefix = $dto->ticketNumberPrefix; // سيصفّر تلقائيًا
                    $airline->save();
                }
                $ticket->airline_id = $airline->id;
                $ticket->save();
            }



        });








    }




    public function getRecord()
    {
//        return WebsitePage::query()
//            ->where('is_homepage', true)
//            ->first();
    }


}
