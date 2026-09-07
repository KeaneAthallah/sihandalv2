<?php

namespace App\Services;

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    /**
     * Allocate the next document number for a type and fiscal year.
     *
     * Numbers advance per (type, year), are formatted with a leading prefix
     * and at least five digits, and are never re-issued. The counter row is
     * locked while read+incremented so concurrent requests cannot get the
     * same number; the unique (type, year) index guards the first allocation
     * of a brand-new year.
     */
    public function next(string $type, string $prefix, int $year): string
    {
        $next = DB::transaction(function () use ($type, $year) {
            $counter = DB::table('document_counters')
                ->where('type', $type)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if ($counter !== null) {
                $next = (int) $counter->last_value + 1;

                DB::table('document_counters')
                    ->where('id', $counter->id)
                    ->update(['last_value' => $next]);

                return $next;
            }

            try {
                DB::table('document_counters')->insert([
                    'type' => $type,
                    'year' => $year,
                    'last_value' => 1,
                ]);

                return 1;
            } catch (QueryException) {
                $counter = DB::table('document_counters')
                    ->where('type', $type)
                    ->where('year', $year)
                    ->lockForUpdate()
                    ->firstOrFail();

                $next = (int) $counter->last_value + 1;

                DB::table('document_counters')
                    ->where('id', $counter->id)
                    ->update(['last_value' => $next]);

                return $next;
            }
        });

        return $prefix.'-'.str_pad((string) $next, 5, '0', STR_PAD_LEFT).'/'.$year;
    }
}
