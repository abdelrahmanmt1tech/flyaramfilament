<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class BspReportProcessor
{

    public static function process(string $pdfAbsolutePath,  $start_date , $end_date): array
    {
        $tables  = \App\Services\TabulaExtractor::extractTablesRobust($pdfAbsolutePath);
        $rows    = \App\Services\TabulaExtractor::tablesToRows($tables);
        $tickets = self::parseTicketsFromRows($rows);
        $summary = self::parseSummaryFromRows($rows);

        // 3) طابق مع DB وأعد صياغة النتائج كما في المثال
        $comparison = self::compareWithDatabase($tickets , $start_date , $end_date);

        return compact('tickets', 'summary', 'comparison');
    }

    /* =====================[ Parsing Tickets ]===================== */

    /**
     * يبني التذاكر من rows (Tabula rows). يعتمد على شكل:
     * [ '157 TKTT 3000008156', '01Jul25 FFFF I* CA  2,794.00  1,405.00', '16.00', 'E3', '602.00', 'YQ', ... ]
     * نلتقط:
     *  - airline_prefix (مثلاً 157 أو 071)
     *  - type (TKTT/...)  ← "نوع التذكرة"
     *  - ticket_id        ← "tkt id" (الرقم بعد TKTT مباشرة)
     *  - issue_date       ← 01Jul25
     *  - carrier          ← رمز شركة الطيران (CA/..)، وإن احتجت airline من قاعدة تعريف أخرى
     *  - amounts:
     *      transaction_amount, fare_amount, accd_net, accd_tax, taxes_by_code (YQ, YR, ...)
     *      std_comm, supp_comm, tax_on_comm, balance
     *
     * ملاحظة: الـ “الأعمدة” يتغير ترتيبها قليلًا حسب صفحة/جدول؛ لذلك ننظّف ونجمّع من نفس البلوك حتى بداية تذكرة جديدة.
     */

    public static function parseTicketsFromRows(array $rows): array
    {
        $tickets = [];
        $current = null;

        // ابحث عن "NNN TKTT NNNNNNNNNN" داخل السطر (بدون ^ و $)
        $reTicketStart = '/(?P<prefix>\d{3})\s+TKTT\s+(?P<id>\d{7,12})\b/i';
        // تاريخ في أي مكان
        $reDate        = '/\b(\d{2}[A-Za-z]{3}\d{2})\b/';
        // أزواج ضريبة (amount ثم code) في أي مكان
        $reTaxPair     = '/(?P<amt>[\d,]+\.\d{2})\s+(?P<code>[A-Z0-9]{1,3})\b/';

        foreach ($rows as $row) {
            // صف واحد = عدة خلايا → نجمعهم بمسافة، ونطبع مسافات زائدة
            $txt = trim(preg_replace('/[ \t]+/u', ' ', implode(' ', array_map('trim', $row))));
            if ($txt === '') continue;

            // 1) بداية تذكرة جديدة؟
            if (preg_match($reTicketStart, $txt, $m)) {
                // ادفع السابقة
                if ($current) {
                    $tickets[] = self::finalizeTicket($current);
                }
                $current = [
                    'airline_prefix'     => $m['prefix'],
                    'type'               => 'TKTT', // نوع التذكرة الأهم
                    'ticket_id'          => $m['id'],
                    'issue_date'         => null,
                    'carrier'            => null,
                    'fare_code'          => null,
                    'transaction_amount' => null,
                    'fare_amount'        => null,
                    'accd_net'           => null,
                    'accd_tax'           => null,
                    'accd_comm'          => null,
                    'taxes'              => [],
                    'std_comm'           => null,
                    'supp_comm'          => null,
                    'tax_on_comm'        => null,
                    'balance'            => null,
                ];
                // لا نعمل continue — لأن نفس السطر قد يحتوي تاريخ/أرقام
            }

            if (!$current) continue;

            // 2) التقط التاريخ إن وُجد في السطر
            if ($current['issue_date'] === null && preg_match($reDate, $txt, $dm)) {
                $current['issue_date'] = self::parseIssueDate($dm[1]);
            }

            // 3) التقط carrier (رمزين) إن ظهر (مثل CA, SV...) — نلتقط أول ظهور رمزين مستقلين
            if ($current['carrier'] === null && preg_match('/\b([A-Z0-9]{2})\b/', $txt, $cm)) {
                $current['carrier'] = $cm[1];
            }

            // 4) التقط المبالغ الكبيرة في نهاية السطر:
            //    غالبًا آخر رقم = Transaction، وقبله Net/ACCD
            if ($current['transaction_amount'] === null || $current['accd_net'] === null) {
                preg_match_all('/([\d,]+\.\d{2})/', $txt, $nums);
                if (!empty($nums[1])) {
                    $vals = array_map(fn($v) => (float)str_replace(',', '', $v), $nums[1]);
                    if (count($vals) >= 1 && $current['transaction_amount'] === null) {
                        $current['transaction_amount'] = end($vals);
                    }
                    if (count($vals) >= 2 && $current['accd_net'] === null) {
                        $current['accd_net'] = prev($vals); // الرقم السابق
                    }
                }
            }

            // 5) التقط أزواج ضرائب (amt code) مثل: "602.00 YQ" أو "168.00 YR"
            if (preg_match_all($reTaxPair, $txt, $tm, PREG_SET_ORDER)) {
                foreach ($tm as $hit) {
                    $code = strtoupper($hit['code']);
                    $amt  = (float) str_replace(',', '', $hit['amt']);
                    // صنّف كضريبة لأكواد معروفة
                    if (preg_match('/^(YQ|YR|IO|E3|G4|PZ|QA|R9|RI|T2|L3|S2|IH|G3)$/i', $code)) {
                        $current['taxes'][$code] = ($current['taxes'][$code] ?? 0) + $amt;
                    }
                }
            }

            // 6) التقط العمولات إن ظهرت مفصولة كلمات (اختياري – حسّنه لاحقًا حسب تنسيقك)
            if (stripos($txt, 'I*') !== false && preg_match_all('/([\d,]+\.\d{2})/', $txt, $am)) {
                // سطر I* قد يحتوي عمولتين (Std, Tax on)
                $vals = array_map(fn($v) => (float)str_replace(',', '', $v), $am[1]);
                if (isset($vals[0]) && $current['std_comm'] === null)    $current['std_comm']    = $vals[0];
                if (isset($vals[1]) && $current['tax_on_comm'] === null) $current['tax_on_comm'] = $vals[1];
            }

            // ملاحظة: إن كانت هناك حقول أخرى بخطوط معينة، نضيف لاقطاتها عند الحاجة
        }

        // ادفع آخر تذكرة
        if ($current) $tickets[] = self::finalizeTicket($current);

        return $tickets;
    }


    private static function finalizeTicket(array $t): array
    {
        // حساب accd_tax (مجموع الأكواد الضريبية) إن لم يُملأ صريحًا
        if ($t['accd_tax'] === null) {
            $t['accd_tax'] = array_sum($t['taxes'] ?? []);
        }
        // تقدير fare_amount إن أمكن (transaction - taxes) كمبدأ احتياطي
        if ($t['fare_amount'] === null && $t['transaction_amount'] !== null) {
            $t['fare_amount'] = max(0, (float)$t['transaction_amount'] - (float)$t['accd_tax']);
        }
        return $t;
    }

    private static function parseIssueDate(string $d): ?string
    {
        // 01Jul25 => 2025-07-01
        if (preg_match('/^(\d{2})([A-Za-z]{3})(\d{2})$/', $d, $m)) {
            $map = ['Jan'=>1,'Feb'=>2,'Mar'=>3,'Apr'=>4,'May'=>5,'Jun'=>6,'Jul'=>7,'Aug'=>8,'Sep'=>9,'Oct'=>10,'Nov'=>11,'Dec'=>12];
            $day = (int)$m[1]; $mon = $map[ucfirst(strtolower($m[2]))] ?? null; $yy = (int)$m[3];
            if ($mon) {
                $year = 2000 + $yy;
                return sprintf('%04d-%02d-%02d', $year, $mon, $day);
            }
        }
        return null;
    }

    /* =====================[ Parsing SUMMARY ]===================== */

    /**
     * نبحث عن كتلة SUMMARY ثم نقرأ الصفوف: BSP TOTAL(SAR), GRAND TOTAL (SAR), ... مع الأعمدة.
     * سنُرجع مصفوفة مثل:
     * [
     *   [
     *     'label' => 'BSP TOTAL(SAR)',
     *     'transaction' => 757437.01,
     *     'fare' => 489048.42,
     *     'tax'  => 114328.79,
     *     'f&c'  => 151285.80,
     *     'pen'  => 2774.00,
     *     'cobl' => 489048.42,
     *     'std_comm' => 344.30,
     *     'supp_comm'=> 0.00,
     *     'tax_on_comm'=> 51.65,
     *     'balance' => 757041.06,
     *   ],
     *   ...
     * ]
     */
    public static function parseSummaryFromRows(array $rows): array
    {
        $summary = [];
        $in = false;

        foreach ($rows as $row) {
            $cells = array_values(array_filter(array_map('trim', $row), fn($v) => $v !== ''));
            $line  = implode(' ', $cells);

            if (!$in) {
                if (Str::contains(strtoupper($line), 'SUMMARY')) {
                    $in = true;
                }
                continue;
            }

            // صفوف القيم: تبدأ بـ Label مثل "BSP TOTAL(SAR)" أو "GRAND TOTAL (SAR)"
            if (!empty($cells) && preg_match('/TOTAL/i', $cells[0])) {
                $label = $cells[0];
                // بعدها أعمدة بالأرقام على الترتيب الظاهر في العيّنة
                // Transaction Amount, FARE Amount, TAX, F&C, PEN, COBL Amount, Std Comm Amt, Supp Comm Amt, Tax on Comm, Balance Payable
                // نعتمد فهارس مرنة بعد أول خلية:
                $nums = array_slice($cells, 1);
                $nums = array_map([self::class, 'n'], $nums);

                $summary[] = [
                    'label'         => $label,
                    'transaction'   => $nums[0] ?? null,
                    'fare'          => $nums[1] ?? null,
                    'tax'           => $nums[2] ?? null,
                    'f&c'           => $nums[3] ?? null,
                    'pen'           => $nums[4] ?? null,
                    'cobl'          => $nums[5] ?? null,
                    'std_comm'      => $nums[6] ?? null,
                    'supp_comm'     => $nums[7] ?? null,
                    'tax_on_comm'   => $nums[8] ?? null,
                    'balance'       => $nums[9] ?? null,
                ];
                continue;
            }

            // نهاية البلوك (صفحة/فاصل)
            if ($in && Str::contains(strtoupper($line), 'SAUDI ARABIA')) {
                break;
            }
        }

        return $summary;
    }

    /* =====================[ Database Comparison ]===================== */

    /**
     * يطابق كل تذكرة مع DB (مثلاً جدول tickets) بناءً على full ticket number:
     * - full_ticket_no قد يكون prefix + '-' + ticket_id أو carrier + ticket_id حسب تصميمك.
     * عدّل حقول DB حسب جدولك.
     */
    public static function compareWithDatabase(array $tickets , $start_date , $end_date): array
    {
        $out = [];
        $exTec = [];

        foreach ($tickets as $t) {
            $exTec[] =$t['ticket_id'] ;
            // حرّف “رقم التذكرة الكامل” حسب نظامك:
          //  $full = ($t['airline_prefix'] ?? '') . '-' . ($t['ticket_id'] ?? '');
            $full = $t['ticket_id'];
            $db = DB::table('tickets')
                ->select([
                    'ticket_number_core',
                    'cost_base_amount',
                    'cost_total_amount',
                    'cost_tax_amount',
                    'issue_date',
                    'validating_carrier_code',
                    'ticket_type_code',
                ])
                ->where('ticket_number_core', $full)
                ->wherebetween('issue_date', [$start_date, $end_date])
                ->first();

            $isInDb = $db !== null;
            $cmp = [
                'tic_number' => $full,
                'is_in_db'   => $isInDb,
                'is_in_pdf'   => true,
                'tic_attr'   => [
                    'amount' => [
                        'amount'     => self::nn($t['transaction_amount'] ?? null),
                        'same_as_db' => $isInDb ? self::eqn($t['transaction_amount'] ?? null, $db->cost_total_amount ?? null) : null,
                        'amount_db'  => $isInDb ? self::nn($db->cost_total_amount) : null,
                    ],
                    'total_taxes' => [
                        'value'      => self::nn($t['accd_tax'] ?? null),
                        'same_as_db' => $isInDb ? self::eqn($t['accd_tax'] ?? null, $db->cost_tax_amount ?? null) : null,
                        'value_db'   => $isInDb ? self::nn($db->cost_tax_amount) : null,
                    ],
                    // يمكنك إضافة حقول أخرى بنفس الصيغة:
                    // fare_amount, accd_net, std_comm, supp_comm, tax_on_comm, balance, issue_date, carrier...
                ],
            ];

            // توحيد مقارنة التاريخ والـ carrier (إن وجد)
            if ($isInDb) {
                $cmp['tic_attr']['issue_date'] = [
                    'value'      => $t['issue_date'] ?? null,
                    'same_as_db' => isset($db->issue_date) ? ((string)$t['issue_date'] === (string)$db->issue_date) : null,
                    'value_db'   => $db->issue_date ?? null,
                ];
//                $cmp['tic_attr']['carrier'] = [
//                    'value'      => $t['carrier'] ?? null,
//                    'same_as_db' => isset($db->carrier) ? ((string)$t['carrier'] === (string)$db->carrier) : null,
//                    'value_db'   => $db->carrier ?? null,
//                ];


            } else {
                $cmp['tic_attr']['issue_date'] = ['value' => $t['issue_date'] ?? null, 'same_as_db' => null, 'value_db' => null];
//                $cmp['tic_attr']['carrier']    = ['value' => $t['carrier'] ?? null, 'same_as_db' => null, 'value_db' => null];
            }

            $cmp['tic_attr']['type'] =
                $isInDb ?
                    ['value' => $t['type'] ?? null, 'same_as_db' => $t['type'] == $db->ticket_type_code , 'value_db' =>  $db->ticket_type_code ??  null] :
                    ['value' => $t['type'] ?? null, 'same_as_db' => null, 'value_db' => null];

            $out[] = $cmp;
        }


        $db_tickets = DB::table('tickets')
            ->select([
                'ticket_number_core',
                'ticket_type_code',
                'cost_total_amount',
            ])
            ->whereNotIn('ticket_number_core', $exTec)
            ->wherebetween('issue_date', [$start_date, $end_date])
            ->get();


        foreach ($db_tickets as $db_ticket){
            $out[] = [
                'tic_number' => $db_ticket->ticket_number_core,
                'is_in_db'   => true,
                'is_in_pdf'   => false,
                'tic_attr'   => [
                    'type'   =>['value' =>  null, 'same_as_db' =>null , 'value_db' =>  $db_ticket->ticket_type_code ??  null] ,
                    'amount'   =>['amount'=> null, 'same_as_db' => null, 'amount_db'  =>  $db_ticket->cost_total_amount,] ,
                ],

            ];

        }




        return $out;
    }

    /* =====================[ Helpers ]===================== */

    private static function n(?string $s): ?float
    {
        if ($s === null || $s === '') return null;
        $s = str_replace([',',' '], '', $s);
        return is_numeric($s) ? (float)$s : null;
    }

    private static function nn($v)
    {
        return $v === null ? null : (float)$v;
    }

    private static function eqn($a, $b): ?bool
    {
        if ($a === null || $b === null) return null;
        return abs((float)$a - (float)$b) < 0.005; // تسامح سنتات
    }
}
