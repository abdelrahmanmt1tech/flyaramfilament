<?php

namespace App\Services;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class TabulaExtractor
{

    public static function extractTablesRobust(string $pdfAbsolutePath): array
    {
        [$ok2, $tables2] = self::tryJson($pdfAbsolutePath);
        if ($ok2) return $tables2;
        throw new \RuntimeException("Tabula could not extract tables (JSON/CSV) from PDF.");
    }

    private static function tryJson(string $pdfAbsolutePath): array
    {
        [$stdout, $stderr] = self::runTabula($pdfAbsolutePath);

        $trimmed = self::sliceJsonArray($stdout);
        $tables = json_decode($trimmed, true);
        return [is_array($tables) && !empty($tables), $tables ?? []];
    }


// CSV string → rows
    private static function runTabula(  $pdfAbsolutePath): array
    {
        $jar = storage_path('app/vendor/tabula.jar');
        if (!is_file($jar)) throw new \RuntimeException("Tabula jar not found: {$jar}");
        if (!is_file($pdfAbsolutePath)) throw new \RuntimeException("PDF not found: {$pdfAbsolutePath}");
        $flag =  '-t';
        $args = [
            'java', '-jar', $jar,
            "--pages", "all",
            $flag,
        ];
        $args[] = '-g';
        array_push($args, '-f', "JSON",  $pdfAbsolutePath);
        $proc = new \Symfony\Component\Process\Process($args);
        $proc->setTimeout(120);
        $proc->run();
        return [(string)$proc->getOutput(), (string)$proc->getErrorOutput()];
    }


    public static function tablesToRows(array $tables): array
    {
        $rows = [];
        foreach ($tables as $tbl) {
            if (!isset($tbl['data']) || !is_array($tbl['data'])) continue;
            foreach ($tbl['data'] as $rowCells) {
                $row = [];
                foreach ($rowCells as $cell) {
                    $row[] = isset($cell['text'])
                        ? trim(preg_replace('/[ \t]+/u', ' ', $cell['text']))
                        : '';
                }
                if (implode('', $row) !== '') {
                    $rows[] = $row;
                }
            }
        }
        return $rows;
    }

    private static function sliceJsonArray(string $out): string
    {
        $out = trim($out);
        if ($out === '') return $out;
        if (str_starts_with($out, '[') && str_ends_with($out, ']')) return $out;
        $s = strpos($out, '[');
        $e = strrpos($out, ']');
        if ($s !== false && $e !== false && $e > $s) return substr($out, $s, $e - $s + 1);
        return $out;
    }



}
