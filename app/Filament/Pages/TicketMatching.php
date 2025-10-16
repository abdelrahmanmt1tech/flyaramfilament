<?php

namespace App\Filament\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use UnitEnum;

class TicketMatching extends Page
    implements HasTable, HasForms
{
    use InteractsWithTable;
    use InteractsWithForms;


    protected string $view = 'filament.pages.ticket-matching';


    protected static string|UnitEnum|null $navigationGroup = "رفع وتضمين";
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


    public array $MatchResults = [];

    public static function canAccess(): bool
    {
        return Auth::user()->can('ticket_matching.view');
    }



    public function mount(): void
    {
        // $this->form->fill($this->getRecord()?->attributesToArray());
        $this->text_file = null;
        $this->form->fill(['text_file' => null]);
        $this->MatchResults = session()->get('MatchResults', []) ?? [];
    }


    public function getRecords(): array
    {
        // تحويل الأكواد إلى صفوف جدول
        return collect($this->MatchResults)
            ->toArray();
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([



                TextColumn::make('tic_number')->label("رقم التذكره"),
                IconColumn::make('is_in_db')->label("في النظام")->boolean(),
                IconColumn::make('is_in_pdf')->label("في الPDF")->boolean(),


                ColumnGroup::make('type', [
                    TextColumn::make('tic_attr.type.value')->label("value")
                        ->icon(Heroicon::OutlinedFolderMinus),
                    IconColumn::make('tic_attr.type.same_as_db')->label("same as db")->boolean(),
                    TextColumn::make('tic_attr.type.value_db')->label("value db")->icon(Heroicon::Server),
                ])    ->alignCenter()->wrapHeader(),




                ColumnGroup::make('amount', [
                    TextColumn::make('tic_attr.amount.amount')->label("amount")
                        ->icon(Heroicon::OutlinedCurrencyDollar),
                    IconColumn::make('tic_attr.amount.same_as_db')->label("same as db")->boolean(),
                    TextColumn::make('tic_attr.amount.amount_db')->label("amount db")->icon(Heroicon::Server),
                ])    ->alignCenter()->wrapHeader(),


                ColumnGroup::make('taxes', [
                    TextColumn::make('tic_attr.total_taxes.value')->label("value")
                        ->icon(Heroicon::Tag),
                    IconColumn::make('tic_attr.total_taxes.same_as_db')->label("same as db")->boolean(),
                    TextColumn::make('tic_attr.total_taxes.value_db')->label("value db")->icon(Heroicon::Server),
                ])    ->alignCenter()->wrapHeader(),

                ColumnGroup::make('date', [
                    TextColumn::make('tic_attr.issue_date.value')->label("value")
                        ->icon(Heroicon::Calculator),
                    IconColumn::make('tic_attr.issue_date.same_as_db')->label("same as db")->boolean(),
                    TextColumn::make('tic_attr.issue_date.value_db')->label("value db")->icon(Heroicon::Server),
                ])    ->alignCenter()->wrapHeader(),

            ])




            ->recordActions([
            ])
            ->records(fn() => $this->getRecords());
    }


    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([

       Grid::make()->schema([
           DatePicker::make('start_date')->label('من تاريخ')->required(),
           DatePicker::make('end_date')->label('الى تاريخ')->required(),
       ])->columnSpan(1)->columns(1),
                    FileUpload::make('pdf_file')
                        ->label("يجب رفع ملف PDF")
                        ->required()
                        ->previewable(false)
                        ->previewable(false)
                        ->directory('pdf_files')
                        ->disk('public')

                ])->columns(2)
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
            ->record($this->getRecords())
            ->statePath('data');
    }


    public function save(): void
    {
        $data = $this->form->getState();
        config()->set("app.debug", true);


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

        if (!$disk->exists($relativePath)) {
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

            $start_date = $data['start_date'];
            $end_date = $data['end_date'];


            $absPath = $disk->path($relativePath);

            $result = \App\Services\BspReportProcessor::process($absPath, $start_date, $end_date);


            //   Storage::put('example.txt', json_encode($result, JSON_PRETTY_PRINT));


            session()->put('MatchResults', $result['comparison']);
            $this->MatchResults = $result['comparison'];


            // 4) طابق/حدّث نظامك
            $matched = 0;
            $unmatched = 0;


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







