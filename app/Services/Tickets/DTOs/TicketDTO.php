<?php
// app/Services/Tickets/DTOs/TicketDTO.php
namespace App\Services\Tickets\DTOs;

class TicketDTO
{
    public ?string $gds = null;               // "Amadeus 1A" استنتاجًا من الهيدر
    public ?string $airlineName = null;       // SAUDI ARABIAN AIRLINES
    public ?string $validatingCarrier = null; // SV
    public ?string $ticketNumber = null;      // K065-3000299698 -> نحفظ الرقم فقط 0653000299698 اختياريًا

    public ?string $issueDate = null;         // TKOK/TKTL
    public ?string $bookingDate = null;       // من D- أو من Agency line
    public ?string $type = null;              // O/W, R/T لو قدرنا نستنتج (بناءً على المقاطع)

    public PriceDTO $price;
    /** @var PassengerDTO[] */
    public array $passengers = [];
    /** @var SegmentDTO[] */
    public array $segments = [];

    public array $meta = [];                  // أي أشياء إضافية (FP/FV/G-/Q-/L-/M- ...) للاحتفاظ بها
    public array $warnings = [];              // تحذيرات بارسينغ


    public ?bool $isDomesticFlight = null; // نحسبه بعد تعبئة المقاطع
    public ?string $itineraryString = null; // مثال: "ULH/DXB/CMB CMB/DXB/ULH" أو "TUU/CAI"
    public ?string $ticketType = null;      // "تذكرة مؤكدة" ...الخ
    public ?string $ticketTypeCode = null;  // TKT | VOID | REI | EMD | ADM | SPDR | AIR-BLK | AMD | RQ





    public ?string $ticketNumberPrefix = null; // مثال: 325
    public ?string $ticketNumberCore   = null; // مثال: 3000380316

    public ?string $branchCode   = null; // 0101
    public ?string $officeId     = null; // ULHS22220
    public ?string $createdByUser= null; // 2202U2AS, 1234FAAS ...

    public ?string $fareBasisOut = null; // ENPXESOW, ...
    public ?string $supplier     = null; // ARAM TRAVEL AGENCY
    public ?string $pnr   = null;
    public ?string $fareBasisIn   = null;
    public ?string $salesRep   = null;
    public ?string $carrierPnrCarrier  = null;
    public ?string $carrierPnr  = null;


}
