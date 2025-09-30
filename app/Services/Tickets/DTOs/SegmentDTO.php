<?php
// app/Services/Tickets/DTOs/SegmentDTO.php
namespace App\Services\Tickets\DTOs;

class SegmentDTO
{
    public ?int    $index = null;        // 1..N
    public ?string $origin = null;       // IATA: JED
    public ?string $originName = null;   // JEDDAH KING ABDUL
    public ?string $destination = null;  // URY
    public ?string $destinationName = null;

    public ?string $carrier = null;      // SV
    public ?string $flightNumber = null; // "1291"
    public ?string $bookingClass = null; // "N" مثلاً
    public ?string $status = null;       // OK/HK
    public ?string $equipment = null;    // 321/773...
    public ?string $baggage = null;      // "1PC" أو "40K"
    public ?string $meal = null;
    public ?string $eticket = null;      // ET

    public ?string $departureDateTime = null; // "2025-07-01T12:20"
    public ?string $arrivalDateTime = null;   // "2025-07-01T14:15"
    public ?string $originCountry = null;     // SA
    public ?string $destCountry = null;       // SA
}
