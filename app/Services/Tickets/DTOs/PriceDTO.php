<?php
// app/Services/Tickets/DTOs/PriceDTO.php
namespace App\Services\Tickets\DTOs;

class PriceDTO
{
    public ?string $baseAmount = null;       // "557.00"
    public ?string $baseCurrency = null;     // "SAR"
    public ?string $totalAmount = null;      // "738.30"
    public ?string $totalCurrency = null;    // "SAR"

    /** @var array<int,array{code:string, amount:string, currency:string}> */
    public array $taxes = []; // e.g. [['code'=>'YR','amount'=>'45.00','currency'=>'SAR'], ...]
}
