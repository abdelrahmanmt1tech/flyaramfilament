<?php

namespace App\Filament\Pages;

use App\Models\AccountStatement;
use App\Models\Airline;
use App\Models\Airport;
use App\Models\AirportRoute;
use App\Models\Branch;
use App\Models\Currency;
use App\Models\Passenger;
use App\Models\Supplier;
use App\Models\Ticket;
use App\Models\User;
use App\Services\BspTicketMapper;
use App\Services\TabulaExtractor;
use App\Services\Tickets\TicketParser;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Storage;
use Spatie\PdfToText\Pdf;
use UnitEnum;







class TicketMatching extends Page
{
    protected string $view = 'filament.pages.ticket-matching';




    protected static string | UnitEnum | null $navigationGroup = "رفع وتضمين";
    public $defaultAction = 'onboarding';

    public function getTitle(): string
    {
        return __('dashboard.sidebar.ticket-matching');
    }

    public static function getNavigationLabel(): string
    {
        return __('dashboard.sidebar.ticket-matching');
    }

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

                    DatePicker::make('start_date') ->label('من تاريخ') ->required(),
                    DatePicker::make('end_date') ->label('الى تاريخ') ->required(),
                    FileUpload::make('pdf_file')
                        ->label("يجب رفع ملف PDF")
                        ->required()
                        ->previewable(false)
                        ->previewable(false)
                        ->directory('pdf_files')
                        ->disk('public')

                ])
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label(__('dashboard.save'))
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
        config()->set("app.debug" , true);


        if (
            empty($data['pdf_file']) ||
            empty($data['start_date']) ||
            empty($data['end_date'])

        ) {
            Notification::make()
                ->danger()
                ->title("بيانات غير مكتمله")
                ->body('فضلاً ارفع ملف PDF وحدد فترة التاريخ.')

                ->send();
            return;
        }




        // مسار نسبي مثل: 'pdf_files/abc.pdf'
        $relativePath = $data['pdf_file'];

        // تأكد أنك على نفس الـ disk المعرّف في FileUpload
        $disk = Storage::disk('public');

        if (! $disk->exists($relativePath)) {
            Notification::make()
                ->danger()
                ->title('ملف غير موجود')
                ->body("تعذر العثور على الملف: {$relativePath}")
                ->send();
            return;
        }

        // احصل على المسار المطلق الحقيقي على السيرفر
        $absPath = $disk->path($relativePath);

        try {

            $pages = implode(',', [
                2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28,29,
                32,33,34,35,36,37,38,39,40,41,42,43,44,45,46,47,48,49,50,51,52,53,54,55,56,
                57,58,59,60,61,62,63,69,70,73,74
            ]);

            $absPath = $disk->path($relativePath);

            $result = \App\Services\BspReportProcessor::process($absPath);

            dd($result);

// lattice + guess + صفحات TKTT
            $tables  = \App\Services\TabulaExtractor::extractTablesRobust($absPath);
            $rows    = \App\Services\TabulaExtractor::tablesToRows($tables);
dd($rows[0]);
            $tickets = \App\Services\BspTicketMapper::mapRowsToTickets($rows);


                // 4) طابق/حدّث نظامك
                $matched = 0; $unmatched = 0;
                foreach ($tickets as $r) {
                    $full = ($r['airline'] ?? '') . ($r['ticket_no'] ?? '');

                    if (! $full) { $unmatched++; continue; }

                    $match = \App\Models\Ticket::query()
                        ->where('full_ticket_no', $full)
                        ->when($r['issue_date'], fn($q) => $q->whereDate('issue_date', $r['issue_date']))
                        ->first();

                    if ($match) {
                        $match->update([
                            'fare'          => $r['fare'],
                            'transaction'   => $r['txn'],
                            'stat'          => $r['stat'],
                            'std_comm'      => $r['std_comm'],
                            'tax_on_comm'   => $r['tax_on_comm'],
                            'tax_yq'        => $r['taxes']['YQ'] ?? null,
                            'tax_yr'        => $r['taxes']['YR'] ?? null,
                            'tax_total'     => $r['taxes']['total'] ?? null,
                            'fop'           => $r['fop'],
                        ]);
                        $matched++;
                    } else {
                        \App\Models\UnmatchedBspRow::create(array_merge($r, [
                            'full_ticket_no' => $full,
                            'source_pdf'     => $relativePath,
                        ]));
                        $unmatched++;
                    }
                }

                Notification::make()->success()
                    ->title('تم استخراج ومطابقة التذاكر')
                    ->body("Matched: {$matched} — Unmatched: {$unmatched}")
                    ->send();




//            $text = Pdf::getText($absPath);
            Notification::make()
                ->success()
                ->title('تم استخراج النص')
                ->body('تم استخراج نص الـ PDF بنجاح، يمكنك استكمال المعالجة.')
                ->send();

            // ... أكمل منطقك هنا (مطابقة التذاكر الخ)
        } catch (\Throwable $e) {
            Notification::make()
                ->danger()
                ->title('فشل استخراج النص')
                ->body($e->getMessage())
                ->send();
        }







    }





}







