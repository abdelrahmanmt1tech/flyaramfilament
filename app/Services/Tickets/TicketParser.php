<?php

namespace App\Services\Tickets;

use App\Services\Tickets\DTOs\{TicketDTO, PriceDTO, PassengerDTO, SegmentDTO};

class TicketParser
{
    private const MONTHS = ['JAN'=>'01','FEB'=>'02','MAR'=>'03','APR'=>'04','MAY'=>'05','JUN'=>'06',
        'JUL'=>'07','AUG'=>'08','SEP'=>'09','OCT'=>'10','NOV'=>'11','DEC'=>'12'];
    private int $currentPassengerIdx = -1;
    private ?int $yearHint = null; // خزّنها من D- أو TKOK

    /*
    public function parse(string $fileContent): TicketDTO
    {
        $dto = new TicketDTO();
        $dto->price = new PriceDTO();

        $lines = preg_split('/\R/u', $fileContent) ?: [];

        $dto->gds = $this->detectGds($lines);

        foreach ($lines as $lineRaw) {
            $line = trim($lineRaw);

            // VOID detection
            if (str_starts_with($line, 'AMD ') && stripos($line, 'VOID') !== false) {
                $dto->voided = true;
            }

            if (str_starts_with($line, 'A-')) { $this->parseALine($line, $dto); continue; }
            if (str_starts_with($line, 'D-')) { $this->parseDLine($line, $dto); continue; }

            // Segments: H- or U-
            if (str_starts_with($line, 'H-')) { $seg = $this->parseHLine(substr($line, 2)); if ($seg) $dto->segments[] = $seg; continue; }
            if (str_starts_with($line, 'U-')) { $seg = $this->parseULine(substr($line, 2)); if ($seg) $dto->segments[] = $seg; continue; }

            if (str_starts_with($line, 'I-')) { $this->parseILine(substr($line, 2), $dto); continue; }
            if (str_starts_with($line, 'SSR ')) { $this->parseSSRLine($line, $dto); continue; }

//            if (str_starts_with($line, 'T-')) { $dto->ticketNumber = $this->parseTicketNumber(substr($line, 2)); continue; }
//            if (str_starts_with($line, 'T-')) { $this->parseTicketNumberLine(substr($line, 2), $dto); continue; }
            if (str_starts_with($line, 'T-')) { $this->parseTicketNumberLine(substr($line, 2), $dto); continue; }

            if (str_starts_with($line, 'K-')) { $this->parseKTotals(substr($line, 2), $dto->price); continue; }
            if (str_starts_with($line, 'KFTF')) { $this->parseKFTFTaxes(substr($line, 4), $dto->price); continue; }
            if (str_starts_with($line, 'TAX-')) { $this->parseTaxSummary(substr($line, 4), $dto->price); continue; }

            if (preg_match('/^TK(OK|TL)/', $line)) { $this->parseIssueDate($line, $dto); continue; }

            if (str_starts_with($line, 'MUC1A ')) { $this->parseMUC1A($line, $dto); continue; }
            if (str_starts_with($line, 'M-')) { $this->parseFareBasisFromM($line, $dto); continue; }
            if (str_starts_with($line, 'C-')) { $this->parseCLine($line, $dto); continue; }



            if (str_starts_with($line, 'FV')) { $this->parseFVLine($line, $dto); continue; }





            // احتفظ بالباقي (EMD/MF* /RM/FP/FV/…)
            if ($line !== '' && $line !== 'ENDX') {
                $dto->meta[] = $line;
            }
        }

        $dto->type = $this->inferType($dto->segments);
        $dto->isDomesticFlight = $this->isDomesticTicket($dto); // true/false

        if (!$dto->validatingCarrier) {
            $dto->validatingCarrier = $this->inferValidatingFromPrefix($dto->meta['ticket_number_prefix'] ?? null);
        }


        $dto->type = $this->inferType($dto->segments);
// itinerary_string
        $dto->itineraryString = $this->buildItineraryString($dto->segments, $dto->type);
// ticket_type + code (من كل السطور)
        [$dto->ticketType, $dto->ticketTypeCode] = $this->determineTicketType($lines);
// isDomestic (التي أضفناها سابقًا)
        $dto->isDomesticFlight = $this->isDomesticTicket($dto);
        return $dto;
    }
*/

    public function parse(string $fileContent): TicketDTO
    {
        $dto = new TicketDTO();
        $dto->price = new PriceDTO();

        $lines = preg_split('/\R/u', $fileContent) ?: [];
        $dto->gds = $this->detectGds($lines);

        foreach ($lines as $lineRaw) {
            $line = trim($lineRaw);

            if (str_starts_with($line, 'AMD ') && stripos($line, 'VOID') !== false) {
                $dto->voided = true;
            }

            if (str_starts_with($line, 'A-'))    { $this->parseALine($line, $dto); continue; }
            if (str_starts_with($line, 'D-'))    { $this->parseDLine($line, $dto); continue; }
            if (str_starts_with($line, 'H-'))    { $seg = $this->parseHLine(substr($line, 2)); if ($seg) $dto->segments[] = $seg; continue; }
            if (str_starts_with($line, 'U-'))    { $seg = $this->parseULine(substr($line, 2)); if ($seg) $dto->segments[] = $seg; continue; }
            if (str_starts_with($line, 'I-'))    { $this->parseILine(substr($line, 2), $dto); continue; }
            if (str_starts_with($line, 'SSR '))  { $this->parseSSRLine($line, $dto); continue; }
            if (str_starts_with($line, 'T-'))    { $this->parseTicketNumberLine(substr($line, 2), $dto); continue; }
            if (str_starts_with($line, 'K-'))    { $this->parseKTotals(substr($line, 2), $dto->price); continue; }
            if (str_starts_with($line, 'KFTF'))  { $this->parseKFTFTaxes(substr($line, 4), $dto->price); continue; }
            if (str_starts_with($line, 'TAX-'))  { $this->parseTaxSummary(substr($line, 4), $dto->price); continue; }
            if (preg_match('/^TK(OK|TL)/', $line)) { $this->parseIssueDate($line, $dto); continue; }
            if (str_starts_with($line, 'MUC1A ')){ $this->parseMUC1A($line, $dto); continue; }
            if (str_starts_with($line, 'M-'))    { $this->parseFareBasisFromM($line, $dto); continue; }
            if (str_starts_with($line, 'C-'))    { $this->parseCLine($line, $dto); continue; }
            if (str_starts_with($line, 'FV'))    { $this->parseFVLine($line, $dto); continue; }

            if ($line !== '' && $line !== 'ENDX') {
                $dto->meta[] = $line;
            }
        }

        // نوع التذكرة (one-way/round-trip/multi)
        $dto->type = $this->inferType($dto->segments);

        // itinerary_string
        $dto->itineraryString = $this->buildItineraryString($dto->segments, $dto->type);

        // ticket_type + code
        [$dto->ticketType, $dto->ticketTypeCode] = $this->determineTicketType($lines);

        // isDomestic
        $dto->isDomesticFlight = $this->isDomesticTicket($dto);

        // validating carrier fallback من الـ prefix لو FV غايبة
        if (!$dto->validatingCarrier) {
            $dto->validatingCarrier = $this->inferValidatingFromPrefix($dto->ticketNumberPrefix ?? null);
        }

        return $dto;
    }



    private function determineTicketType(array $lines): array
    {
        $joined = strtoupper(implode("\n", $lines));

        $checks = [
            ['/\b(VOID|CANCEL|CANCELLED|CANXX|CANX|CNL|CXL|CXLD)\b/u', 'ملغاة / مسترجعة', 'VOID'],
            ['/\bEMD\b/u', 'خدمة إلكترونية (EMD)', 'EMD'],
            ['/\b(REI|REISSUE|REISS)\b/u', 'تذكرة معاد إصدارها', 'REI'],

            // ✅ اجعل TKT هنا قبل المذكرات الإدارية
            ['/\b(TKT|TICKETED|TKOK)\b/u', 'تذكرة مؤكدة', 'TKT'],

            ['/\bADMA\b/u', 'مذكرة خصم للوكيل (ADMA)', 'ADMA'],
            ['/\bACMA\b/u', 'مذكرة ائتمان للوكيل (ACMA)', 'ACMA'],
            ['/\bSPDR\b/u', 'تقرير مبيعات (SPDR)', 'SPDR'],

            // (اختياري) ADM/ACM عامة
            ['/\bADM\b/u', 'مذكرة خصم (ADM)', 'ADM'],
            ['/\bACM\b/u', 'مذكرة ائتمان (ACM)', 'ACM'],

            ['/\bAIR-BLK\b/u', 'حجز جماعي (AIR-BLK)', 'AIR-BLK'],
            ['/\bAMD\b/u', 'تذكرة دعم (AMD)', 'AMD'],
            ['/\bRQ\b/u', 'حجز غير مؤكد (RQ)', 'RQ'],
        ];

        foreach ($checks as [$re, $label, $code]) {
            if (preg_match($re, $joined)) return [$label, $code];
        }
        return [null, null];
    }

    private function buildItineraryString(array $segments, ?string $ticketType): ?string
    {
        if (empty($segments)) return null;

        // حوّل المقاطع إلى سلسلة مطارات بالترتيب: ORG, then every DEST
        $codes = [];
        $codes[] = $this->airportCore($segments[0]->origin ?? '');
        foreach ($segments as $seg) {
            $codes[] = $this->airportCore($seg->destination ?? '');
        }

        // نظّف أي أكواد فاضية
        $codes = array_values(array_filter($codes));

        // ONE-WAY ⇒ سلسلة وحيدة
        if ($ticketType === 'ONE-WAY' || count($segments) === 1) {
            return implode('/', $codes);
        }

        // للـ ROUND-TRIP أو MULTI-SEG: جرّب تقسيم عند أكبر فجوة زمنية بين إقلاعين
        $splits = $this->findBiggestGapSplitIndex($segments); // يعيد index المقصود (بين i و i+1) أو null
        if ($splits !== null) {
            // المجموعة الأولى: من codes[0] حتى codes[$splits]
            $out = implode('/', array_slice($codes, 0, $splits + 1));
            // المجموعة الثانية: من codes[$splits] حتى النهاية
            $in  = implode('/', array_slice($codes, $splits, null));
            // إزالة التكرار الملتصق في نقطة الوصل (يظهر طبيعيًا من البناء)
            // مثال: ULH/DXB/CMB  +  CMB/DXB/ULH  ← ممتاز
            return trim($out . ' ' . $in);
        }

        // fallback: سلسلة موحّدة
        return implode('/', $codes);
    }
/*
    private function airportCore(?string $code): ?string
    {
        if (!$code) return null;
        $c = strtoupper($code);
        // أحيانًا تأتي OULH/XDXB... نحذف البادئة O/X
        return preg_replace('/^[OX]/', '', $c);
    }
    */

    private function findBiggestGapSplitIndex(array $segments): ?int
    {
        // نبحث عن أكبر فرق ساعات بين dep[i] و dep[i+1]
        $bestIdx = null;
        $bestGap = 0;

        for ($i = 0; $i < count($segments) - 1; $i++) {
            $a = $segments[$i]->departureDateTime ?? null;
            $b = $segments[$i+1]->departureDateTime ?? null;
            if (!$a || !$b) continue;

            $gap = abs(strtotime($b) - strtotime($a));
            if ($gap > $bestGap) { $bestGap = $gap; $bestIdx = $i + 1; }
        }

        // اعتبر الفجوة "معتبرة" إذا ≥ 18 ساعة (يمكن تعديلها)
        if ($bestIdx !== null && $bestGap >= 18 * 3600) {
            // split happens at $bestIdx (codes index will align accordingly)
            // لاحقًا نستخدم هذا المؤشر في buildItineraryString
            return $bestIdx;
        }

        return null;
    }


    private function isDomesticTicket(TicketDTO $dto): bool
    {
        // قائمة مطارات السعودية (يمكن توسعتها)
        static $saAirports = [
            'JED','RUH','DMM','MED','ELQ','TIF','TUU','HOF','EAM','GIZ','URY','AJF','QUR',
            'BHH','EJH','HAS','ABT','AQI','RAE','DWD','HBT','RAH','SHW','TUI','WAE','KMC',
            'RGB','SLF','UZH','ZUL','QUN','NUM','RSI','ULH','YNB','AHB',
            // أسماء نصّية شائعة أحيانًا
            'JEDDAH','RIYADH','DAMMAM','MADINAH','GASSIM',
        ];

        if (empty($dto->segments)) {
            return false; // بدون مقاطع نعتبرها ليست داخلية
        }

        foreach ($dto->segments as $seg) {
            // أولوية أعلى: كود الدولة إن كان موجودًا
            if (!empty($seg->originCountry) && !empty($seg->destCountry)) {
                if (strtoupper($seg->originCountry) !== 'SA' || strtoupper($seg->destCountry) !== 'SA') {
                    return false;
                }
                continue;
            }

            // fallback: كود المطار / الاسم
            $dep = strtoupper((string)($seg->origin ?? ''));
            $arr = strtoupper((string)($seg->destination ?? ''));

            // في بعض الملفات يظهر ORUH/XIST… لذلك ننزع البادئة O/X إن وجدت
            $dep = preg_replace('/^[OX]/', '', $dep);
            $arr = preg_replace('/^[OX]/', '', $arr);

            if (!in_array($dep, $saAirports, true) || !in_array($arr, $saAirports, true)) {
                return false;
            }
        }

        return true;
    }

    private function parseAgencyFromILine(string $payload, TicketDTO $dto): array
    {
        $parts = array_map('trim', explode(';', $payload));
        $agency = $parts[3] ?? '';
        $supplier = null; $salesRep = null;

        if ($agency !== '') {
            if (preg_match('/-\s*([^-\n]{3,}?)\s*-\s*/u', $agency, $m)) {
                $supplier = trim($m[1]); // الاسم بين شرطتين
            }
            if (preg_match('/\bREF\s+([A-Z0-9]+)\b/i', $agency, $m3)) {
                $salesRep = strtoupper($m3[1]);
            } elseif (preg_match('/\b([A-Z0-9._%-]+)@/i', $agency, $em)) {
                $salesRep = strtoupper(preg_replace('/[^A-Z0-9]+/i', '', $em[1]));
            }
        }

        // ← أعد القيم كما هي (لا تغيّر الواجهة)
        return [$supplier, $salesRep];
    }
/*


    private function parseAgencyFromILine(string $payload, TicketDTO $dto): array
    {
        // مثال: ... ;;APALBARRAK TRAVELS REF SAWAD 0116321114/0509692271;;
        $parts = array_map('trim', explode(';', $payload));
        $agency = $parts[3] ?? ''; // الحقل الثالث غالبًا يحوي سطر الوكالة/الاتصال

        $supplier = null; $salesRep = null;

        if ($agency !== '') {
            // اسم الوكالة: نأخذ كل شيء قبل "REF" إن وُجدت
            if (preg_match('/^([A-Z0-9 \-\/&.]+?)\s+REF\b/i', $agency, $m)) {
                $supplier = trim($m[1]);
            } else {
                // أو أوّل مقطَع كلمات حروفية
                if (preg_match('/([A-Z][A-Z0-9 &\/.-]{4,})/', $agency, $m2)) {
                    $supplier = trim($m2[1]);
                }
            }
            // مندوب المبيعات بعد REF
            if (preg_match('/\bREF\s+([A-Z0-9]+)\b/i', $agency, $m3)) {
                $salesRep = strtoupper($m3[1]);
            }
        }
        return [$supplier, $salesRep];
    }


*/
    private function detectGds(array $lines): ?string
    {
        $joined = implode("\n", $lines);
        return (str_contains($joined, '1A') || str_starts_with(trim($lines[0] ?? ''), 'AIR-BLK'))
            ? 'Amadeus (1A)' : null;
    }

    private function parseALine(string $line, TicketDTO $dto): void
    {
        $payload = substr($line, 2);
        $parts = array_map('trim', explode(';', $payload));
        $dto->airlineName = $parts[0] ?? null;
        if (!empty($parts[1]) && preg_match('/([A-Z]{2})\s*\d{1,4}/', $parts[1], $m)) {
            $dto->validatingCarrier = $m[1];
        }
    }

//    private function parseDLine(string $line, TicketDTO $dto): void
//    {
//        $parts = array_map('trim', explode(';', substr($line, 2)));
//        foreach ($parts as $i => $p) {
//            if (preg_match('/^\d{6}$/', $p)) {
//                $y = (int)substr($p,0,2); $y += ($y > 70 ? 1900 : 2000);
//                $iso = sprintf('%04d-%s-%s', $y, substr($p,2,2), substr($p,4,2));
//                if ($i === 0 && !$dto->bookingDate) $dto->bookingDate = $iso;
//                if ($i === 2 && !$dto->issueDate)   $dto->issueDate   = $iso;
//            }
//        }
//    }
/*
    private function parseHLine(string $payload): ?SegmentDTO
    {
        $p = array_map(fn($v)=>trim($v," \t"), explode(';', $payload));
        if (count($p) < 6) return null;

        $seg = new SegmentDTO();

        if (preg_match('/^\d+$/', $p[0])) $seg->index = (int)$p[0];
//
//        $seg->origin         = $this->stripOD($p[1] ?? null);
//        $seg->originName     = $p[2] ?? null;
//        $seg->destination    = $this->stripOD($p[3] ?? null);
//        $seg->destinationName= $p[4] ?? null;
//

        $seg->origin      = $this->airportCore($p[1] ?? null);
        $seg->originName  = $p[2] ?? null;
        $seg->destination = $this->airportCore($p[3] ?? null);
        $seg->destinationName = $p[4] ?? null;





        if (!empty($p[5]) && preg_match('/([A-Z0-9]{2})\s*0*([0-9]{1,4})/', $p[5], $m)) {
            $seg->carrier = $m[1];
            $seg->flightNumber = ltrim($m[2], '0') ?: $m[2];
        }

        $joined = implode(' ', $p);
        if (preg_match('/(\d{3,4})\s+(\d{3,4})\s+(\d{2}[A-Z]{3})/u', $joined, $t)) {
            $date = $this->attachYear($t[3]);
            $seg->departureDateTime = $this->makeIsoDateTime($date, $t[1]);
            $seg->arrivalDateTime   = $this->makeIsoDateTime($date, $t[2]);
        }

        // التقط قيم إضافية إن ظهرت
        foreach ($p as $cell) {
            if (!$seg->status      && preg_match('/\b(OK|HK)\d*\b/', $cell, $m)) $seg->status = $m[1];
            if (!$seg->equipment   && preg_match('/\b(3\d{2}|7\d{2}|32N|32Q|77W|359)\b/', $cell, $m)) $seg->equipment = $m[1];
            if (!$seg->baggage     && preg_match('/\b(\d+PC|\d+K)\b/', $cell, $m)) $seg->baggage = $m[1];
            if (!$seg->bookingClass&& preg_match('/\b([A-Z])\b/', $cell, $m)) $seg->bookingClass = $m[1];
            if (!$seg->eticket     && preg_match('/\bET\b/', $cell)) $seg->eticket = 'ET';
            if (!$seg->meal        && preg_match('/\b(M|N|V|L|S)\b/', $cell)) $seg->meal = trim($cell);
        }

        if (preg_match('/;([A-Z]{2});([A-Z]{2});?$/', $payload, $m)) {
            $seg->originCountry = $m[1]; $seg->destCountry = $m[2];
        }

        return $seg;
    }
*/

    private function parseHLine(string $payload): ?SegmentDTO
    {
        $p = array_map(fn($v)=>trim($v," \t"), explode(';', $payload));
        if (count($p) < 6) return null;

        $seg = new SegmentDTO();
        if (preg_match('/^\d+$/', $p[0])) $seg->index = (int)$p[0];

        $seg->origin      = $this->airportCore($p[1] ?? null);
        $seg->originName  = $p[2] ?? null;
        $seg->destination = $this->airportCore($p[3] ?? null);
        $seg->destinationName = $p[4] ?? null;

        if (!empty($p[5]) && preg_match('/([A-Z0-9]{2})\s*0*([0-9]{1,4})/', $p[5], $m)) {
            $seg->carrier = $m[1];
            $seg->flightNumber = ltrim($m[2], '0') ?: $m[2];
        }

        $joined = implode(' ', $p);
        if (preg_match('/(\d{3,4})\s+(\d{3,4})\s+(\d{2}[A-Z]{3})/u', $joined, $t)) {
            // ✅ استخدم yearHint إن متوفر
            $date = $this->attachYear($t[3], $this->yearHint);
            $seg->departureDateTime = $this->makeIsoDateTime($date, $t[1]);
            $seg->arrivalDateTime   = $this->makeIsoDateTime($date, $t[2]);
        }

        foreach ($p as $cell) {
            if (!$seg->status      && preg_match('/\b(OK|HK)\d*\b/', $cell, $m)) $seg->status = $m[1];
            if (!$seg->equipment   && preg_match('/\b(3\d{2}|7\d{2}|32N|32Q|77W|359|7M8)\b/', $cell, $m)) $seg->equipment = $m[1];
            if (!$seg->baggage     && preg_match('/\b(\d+PC|\d+K)\b/', $cell, $m)) $seg->baggage = $m[1];
            if (!$seg->bookingClass&& preg_match('/\b([A-Z])\b/', $cell, $m)) $seg->bookingClass = $m[1];
            if (!$seg->eticket     && preg_match('/\bET\b/', $cell)) $seg->eticket = 'ET';
            if (!$seg->meal        && preg_match('/\b(M|N|V|L|S)\b/', $cell)) $seg->meal = trim($cell);
        }

        if (preg_match('/;([A-Z]{2});([A-Z]{2});?$/', $payload, $m)) {
            $seg->originCountry = $m[1]; $seg->destCountry = $m[2];
        }

        return $seg;
    }


    private function parseULine(string $payload): ?SegmentDTO
    {
        // صيغة U- مشابهة لـ H- لكن ترتيب الأعمدة يختلف قليلًا
        $p = array_map(fn($v)=>trim($v," \t"), explode(';', $payload));
        if (count($p) < 6) return null;

        $seg = new SegmentDTO();

        // مثال مكرر: U-004X;002ORUH;RIYADH;HBE;ALEXANDRIA;MS 065...
        if (preg_match('/^\d+/', $p[0])) $seg->index = (int)preg_replace('/\D/', '', $p[0]);

        $seg->origin         = $this->stripOD($p[1] ?? null);
        $seg->originName     = $p[2] ?? null;
        $seg->destination    = $this->stripOD($p[3] ?? null);
        $seg->destinationName= $p[4] ?? null;

        if (!empty($p[5]) && preg_match('/([A-Z0-9]{2})\s*0*([0-9]{1,4})/', $p[5], $m)) {
            $seg->carrier = $m[1];
            $seg->flightNumber = ltrim($m[2], '0') ?: $m[2];
        }

        $joined = implode(' ', $p);
        if (preg_match('/(\d{3,4})\s+(\d{3,4})\s+(\d{2}[A-Z]{3})/u', $joined, $t)) {
//            $date = $this->attachYear($t[3]);
            $date = $this->attachYear($t[3], $this->yearHint);
            $seg->departureDateTime = $this->makeIsoDateTime($date, $t[1]);
            $seg->arrivalDateTime   = $this->makeIsoDateTime($date, $t[2]);
        }

        foreach ($p as $cell) {
            if (!$seg->status      && preg_match('/\b(OK|HK)\d*\b/', $cell, $m)) $seg->status = $m[1];
            if (!$seg->equipment   && preg_match('/\b(3\d{2}|7\d{2}|32N|32Q|77W|359)\b/', $cell, $m)) $seg->equipment = $m[1];
            if (!$seg->baggage     && preg_match('/\b(\d+PC|\d+K)\b/', $cell, $m)) $seg->baggage = $m[1];
            if (!$seg->bookingClass&& preg_match('/\b([A-Z])\b/', $cell, $m)) $seg->bookingClass = $m[1];
            if (!$seg->eticket     && preg_match('/\bET\b/', $cell)) $seg->eticket = 'ET';
        }

        if (preg_match('/;([A-Z]{2});([A-Z]{2});?$/', $payload, $m)) {
            $seg->originCountry = $m[1]; $seg->destCountry = $m[2];
        }

        if (!$seg->meal && preg_match('/\b(M|N|V|L|S)\b/', $cell, $m)) {
            $seg->meal = $m[1];               // ✅ خُذ الحرف فقط
        }
        if ($seg->meal) {
            $seg->meal = substr($seg->meal, 0, 255);
        }

        return $seg;
    }


    private function airportCore(?string $raw): ?string
    {
        if (!$raw) return null;
        $c = strtoupper(trim($raw));
        // احذف أي أرقام في البداية (مثل 002)
        $c = preg_replace('/^\d+/', '', $c);
        // احذف بادئة O/X إن وجدت (OCAI / XDXB ...)
        $c = preg_replace('/^[OX]/', '', $c);
        // لو الحقل فيه نص أطول، خذ أول 3 أحرف أبجدية متتالية
        if (preg_match('/([A-Z]{3})/', $c, $m)) {
            return $m[1];
        }
        return null;
    }




    private function parseFareBasisFromM(string $line, TicketDTO $dto): void
    {
        // M-NKRTSAS4       ;VKRTSAS4
        $payload = substr($line, 2);
        $parts = array_map('trim', explode(';', $payload));
        $out = $parts[0] ?? null;
        $in  = $parts[1] ?? null;
        if ($out) $dto->fareBasisOut = $out;
        if ($in) $dto->fareBasisIn = $in;


    }


    private function parseCLine(string $line, TicketDTO $dto): void
    {
        // أمثلة شائعة:
        // C-7906/ 2202U2AS-2202U2AS-I-0--
        // C-XXXX/ ABCD123-ABCD123-...
        $payload = trim(substr($line, 2)); // بعد "C-"
        // التوكين بعد الشرطة المائلة غالبًا يحتوي اليوزر، قبل أول '-'
        // نبحث عن أول مجموعة أحرف/أرقام (6–10) بعد '/'
        if (preg_match('/\/\s*([A-Z0-9]{6,10})\s*-\s*/', $payload, $m)) {
            $dto->createdByUser = $m[1]; // 2202U2AS, 1234FAAS...

        }
    }

    private function parseILine(string $payload, TicketDTO $dto): void
    {
        $p = array_map('trim', explode(';', $payload));
        $nameRaw = $p[1] ?? null;
        if (!$nameRaw) return;

        $pass = new PassengerDTO();
        $pass->fullName = $nameRaw;
        $this->splitName($nameRaw, $pass);

        $inline = implode(' ', $p);
        if (preg_match('/\b[\w.\-]+@[\w\-.]+\.[A-Za-z]{2,}\b/', $inline, $m)) $pass->email = $m[0];
        if (preg_match('/\+?\d{6,}/', $inline, $m)) $pass->phone = $m[0];

        $dto->passengers[] = $pass;
        $this->currentPassengerIdx = count($dto->passengers) - 1;

        [$supplier, $salesRep] = $this->parseAgencyFromILine($payload, $dto);
        if ($supplier && empty($dto->supplier))  $dto->supplier  = $supplier; // خارج meta الآن
        if ($salesRep && empty($dto->salesRep)) $dto->salesRep = $salesRep;


    }

    private function parseSSRLine(string $line, TicketDTO $dto): void
    {
        if (str_starts_with($line, 'SSR DOCS')) {
            if (preg_match('/SSR DOCS\s+[A-Z0-9]{2}\s+\w+\s+(.+)/', $line, $m)) {
                $fields = explode('/', trim($m[1]));
                $doc = [
                    'type'   => $fields[0] ?? null,
                    'nat'    => $fields[1] ?? null,
                    'number' => $fields[2] ?? null,
                    'issuing'=> $fields[3] ?? null,
                    'dob'    => $this->ddMmmYyToIso($fields[4] ?? null),
                    'gender' => $fields[5] ?? null,
                    'expiry' => $this->ddMmmYyToIso($fields[6] ?? null),
                ];
                if (!empty($dto->passengers)) {
                    $dto->passengers[0]->docs[] = $doc;
                }
            }
            return;
        }
        if (str_starts_with($line, 'SSR CTCE')) {
            if (preg_match('/SSR CTCE\s+[A-Z0-9]{2}\s+\w+\/([^ ;]+)/', $line, $m)) {
                $email = str_replace('//', '@', $m[1]);
                if (!empty($dto->passengers) && !$dto->passengers[0]->email) $dto->passengers[0]->email = $email;
            }
            return;
        }
        if (str_starts_with($line, 'SSR CTCM')) {
            if (preg_match('/SSR CTCM\s+[A-Z0-9]{2}\s+\w+\/([+0-9]+)/', $line, $m)) {
                if (!empty($dto->passengers) && !$dto->passengers[0]->phone) $dto->passengers[0]->phone = $m[1];
            }
            return;
        }
    }

    private function parseTicketNumber(string $payload): ?string
    {
        if (preg_match('/(\d{3})-?(\d{10})/', $payload, $m)) return $m[1] . $m[2];
        return trim($payload) ?: null;
    }


    private function setTicketForCurrentPassenger(?string $ticket, TicketDTO $dto): void
    {
        if ($ticket === null) return;
        // خزّن أيضًا على مستوى التذكرة الأولى لو لسه فاضي
        if (empty($dto->ticketNumber)) $dto->ticketNumber = $ticket;
        // اربط بالراكب الحالي إن وُجد
        if ($this->currentPassengerIdx >= 0 && isset($dto->passengers[$this->currentPassengerIdx])) {
            $dto->passengers[$this->currentPassengerIdx]->docs[] = ['ticket' => $ticket];
        }
    }



    private function parseKTotals(string $payload, PriceDTO $price): void
    {
        if (preg_match_all('/\b([A-Z]{3})(\d+\.\d{2})\b/u', $payload, $m, PREG_SET_ORDER)) {
            $price->baseCurrency = $m[0][1]; $price->baseAmount = $m[0][2];
            $last = end($m);
            $price->totalCurrency = $last[1]; $price->totalAmount = $last[2];
        }
    }

    private function parseKFTFTaxes(string $payload, PriceDTO $price): void
    {
        if (preg_match_all('/\b([A-Z]{3})(\d+\.\d{2})\s+([A-Z0-9]{2})\b/u', $payload, $m, PREG_SET_ORDER)) {
            foreach ($m as $tax) $price->taxes[] = ['currency'=>$tax[1],'amount'=>$tax[2],'code'=>$tax[3]];
        }
    }

    private function parseTaxSummary(string $payload, PriceDTO $price): void
    {
        if (preg_match_all('/\b([A-Z]{3})(\d+\.\d{2})\s+([A-Z0-9]{2})\b/u', $payload, $m, PREG_SET_ORDER)) {
            foreach ($m as $tax) $price->taxes[] = ['currency'=>$tax[1],'amount'=>$tax[2],'code'=>$tax[3]];
        }
    }

//    private function parseIssueDate(string $line, TicketDTO $dto): void
//    {
//        if (preg_match('/TK(?:OK|TL)(\d{2}[A-Z]{3})/u', $line, $m)) {
//            $dto->issueDate = $this->attachYear($m[1]);
//        }
//    }

    private function stripOD(?string $s): ?string
    {
        if (!$s) return null;
        return preg_replace('/^[OX]/', '', trim($s));
    }

    private function splitName(string $raw, PassengerDTO $pass): void
    {
        [$last, $rest] = array_pad(explode('/', $raw, 2), 2, '');
        $parts = preg_split('/\s+/', trim($rest));
        $pass->lastName  = trim($last) ?: null;
        $pass->firstName = $parts[0] ?? null;
        $pass->title     = (isset($parts[1]) && preg_match('/^(MR|MRS|MS|CHD|INF)$/i', end($parts)))
            ? strtoupper(end($parts)) : null;
    }

    private function ddMmmYyToIso(?string $ddMmmYy): ?string
    {
        if (!$ddMmmYy || !preg_match('/^(\d{2})([A-Z]{3})(\d{2})$/', $ddMmmYy, $m)) return null;
        $day = $m[1]; $mon = self::MONTHS[$m[2]] ?? null; $yy = (int)$m[3];
        $yy += ($yy > 70 ? 1900 : 2000);
        return $mon ? sprintf('%04d-%s-%s', $yy, $mon, $day) : null;
    }


    private function attachYear(string $ddMmm, ?int $yearHint = null): ?string
    {
        if (!preg_match('/^(\d{2})([A-Z]{3})$/', $ddMmm, $m)) return null;
        $year = $yearHint ?: $this->yearHint ?: (int)date('Y');
        $mon  = self::MONTHS[$m[2]] ?? null;
        return $mon ? sprintf('%04d-%s-%s', $year, $mon, $m[1]) : null;
    }

    private function parseIssueDate(string $line, TicketDTO $dto): void
    {
        if (preg_match('/TK(?:OK|TL)(\d{2}[A-Z]{3})/u', $line, $m)) {
            $dto->issueDate = $this->attachYear($m[1]);
            // عيّن yearHint لاستخدامه بمواعيد المقاطع
            $this->yearHint = (int)substr($dto->issueDate, 0, 4);
        }
    }

    private function parseDLine(string $line, TicketDTO $dto): void
    {
        $parts = array_map('trim', explode(';', substr($line, 2)));
        foreach ($parts as $i => $p) {
            if (preg_match('/^\d{6}$/', $p)) {
                $y = (int)substr($p,0,2); $y += ($y > 70 ? 1900 : 2000);
                $iso = sprintf('%04d-%s-%s', $y, substr($p,2,2), substr($p,4,2));
                if ($i === 0 && !$dto->bookingDate) $dto->bookingDate = $iso;
                if ($i === 2 && !$dto->issueDate)   { $dto->issueDate = $iso; $this->yearHint = (int)$y; }
            }
        }
    }
    private function makeIsoDateTime(?string $isoDate, string $hhmm): ?string
    {
        if (!$isoDate) return null;
        $hh = strlen($hhmm) === 4 ? substr($hhmm,0,2) : substr($hhmm,0,2);
        $mm = substr($hhmm,-2);
        return "{$isoDate}T{$hh}:{$mm}";
    }

    /** @param SegmentDTO[] $segments */
    private function inferType(array $segments): ?string
    {
        if (count($segments) <= 1) return 'ONE-WAY';
        $first = $segments[0] ?? null;
        $last  = $segments ? $segments[count($segments)-1] : null;
        if ($first && $last && $first->origin === $last->destination) return 'ROUND-TRIP';
        return 'MULTI-SEG';
    }

  /*  private function parseMUC1A(string $line, TicketDTO $dto): void
    {
        // مثال: MUC1A 7XRO7M002;0503;ULHS22221;71236970;ULHS22221;...
        $payload = trim(substr($line, strlen('MUC1A')));
        // أول توكن يحوي PNR + رقم (002) ملتصقين
        if (preg_match('/([A-Z0-9]{6})(\d{3})?;([^;]*);([^;]*)/', $payload, $m)) {
            $dto->meta['pnr'] = $dto->meta['pnr'] ?? $m[1];     // 7XRO7M
            $dto->meta['branch_code'] = $dto->meta['branch_code'] ?? trim($m[3] ?: ''); // 0503
            $dto->meta['office_id'] = $dto->meta['office_id'] ?? trim($m[4] ?: '');     // ULHS22221 (غالبًا Office Id)
        }
    }*/

    private function parseMUC1A(string $line, TicketDTO $dto): void
    {
        // مثال:
        // MUC1A 9RISKT003;0101;ULHS22220;71236970;ULHS22220;71236970;...;;NP 9RISKT
        $payload = trim(substr($line, strlen('MUC1A')));

        // (أ) PNR + branch + office_id من أول حقول
        if (preg_match('/^\s*([A-Z0-9]{6})(\d{3})?;([^;]*);([^;]*)/', $payload, $m)) {

            $dto->pnr  =   $dto->pnr    ?? $m[1];
            $dto->branchCode  = $dto->branchCode  ?? trim($m[3]);
            $dto->officeId    = $dto->officeId    ?? trim($m[4]);

        }

        // (ب) Carrier PNR في ذيل السطر (مثال: "NP 9RISKT")
        if (preg_match('/\b([A-Z0-9]{2})\s+([A-Z0-9]{6})\b\s*$/', $payload, $m2)) {
            $dto->carrierPnrCarrier = $m2[1];  // NP
            $dto->carrierPnr         = $m2[2];  // 9RISKT
        }
    }

    private function parseFVLine(string $line, TicketDTO $dto): void
    {
        // أمثلة: FVNP;... / FVFZ;... / FVNE;...
        if (preg_match('/^FV([A-Z0-9]{2})\b/u', trim($line), $m)) {
            $dto->validatingCarrier = $m[1]; // NP, FZ, NE...
        }
    }

//    private function parseTicketNumberLine(string $payload, TicketDTO $dto): void
//    {
//        // أمثلة: K325-3000380316 / 325-3000380316
//        if (preg_match('/(?:K)?(\d{3})-?(\d{10})/', $payload, $m)) {
//            $dto->meta['ticket_number_prefix'] = $m[1];    // 325
//            $dto->meta['ticket_number_core']   = $m[2];    // 3000380316
//            $dto->ticketNumber = $m[1] . $m[2];            // 3253000380316
//        }
//    }


    private function parseTicketNumberLine(string $payload, TicketDTO $dto): void
    {
        // أمثلة محتملة: "K325-3000380316" أو "325-3000380316" أو "3253000380316"
        if (preg_match('/(?:K)?(\d{3})-?(\d{10})/', $payload, $m)) {
            $dto->ticketNumberPrefix = $m[1];     // 325
            $dto->ticketNumberCore   = $m[2];     // 3000380316
            $full = $m[1] . $m[2];                // 3253000380316
            if (empty($dto->ticketNumber)) {
                $dto->ticketNumber = $full;
            }

            // اربط بالراكب الحالي إن وجد
            if ($this->currentPassengerIdx >= 0 && isset($dto->passengers[$this->currentPassengerIdx])) {
                $dto->passengers[$this->currentPassengerIdx]->docs[] = ['ticket' => $full];
            }
        }
    }


    private function inferValidatingFromPrefix(?string $prefix): ?string
    {
        if (!$prefix) return null;
        // خريطة مختصرة — وسّعها حسب احتياجك
        $map = [
            '065' => 'SV', // Saudia
            '141' => 'FZ', // flydubai
            '477' => 'NE', // Nesma (مثال شائع)
            '325' => 'NP', // Nile Air (حسب عيناتك)
            // ...
        ];
        return $map[$prefix] ?? null;
    }


//    private function parseTicketNumberLine(string $payload, TicketDTO $dto): void
//    {
//        // T-K065-3000266072 => 0653000266072 (أو 3000266072 لو تريد الـ 10 أرقام فقط)
//        $ticket = null;
//        if (preg_match('/(\d{3})-?(\d{10})/', $payload, $m)) {
//            $ticket = $m[1] . $m[2]; // 0653000266072
//            // لو بدك “الرقم فقط” بدون الـ 3 أرقام الأولى:
//            // $ticket = $m[2]; // 3000266072
//        }
//        $this->setTicketForCurrentPassenger($ticket, $dto);
//    }



}
