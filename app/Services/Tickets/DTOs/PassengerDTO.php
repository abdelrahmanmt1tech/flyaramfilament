<?php
// app/Services/Tickets/DTOs/PassengerDTO.php
namespace App\Services\Tickets\DTOs;

class PassengerDTO
{
    public ?string $fullName = null;     // "MATARI/NAJM AL DAIN MR"
    public ?string $title = null;        // MR/MRS/CHD/INF...
    public ?string $firstName = null;
    public ?string $lastName = null;

    public ?string $email = null;        // من SSR CTCE أو سطر I-
    public ?string $phone = null;        // من SSR CTCM أو I-
    public ?array  $docs  = [];          // [ ['type'=>'P','number'=>'...','nat'=>'YEM','dob'=>'1999-01-01','expiry'=>'2026-12-22','gender'=>'M'] ]
}
