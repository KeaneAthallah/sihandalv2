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
        return $this->format($prefix, $this->allocate($type, $year, 5, null), $year, 5);
    }

    /**
     * Allocate the next document number with a custom digit width and an
     * optional seed resolver used the first time a (type, year) counter is
     * created. The seed keeps new counters consistent with numbers already
     * issued by legacy generation logic (e.g. permintaan dana / transfer dana),
     * so switching those flows to the race-safe counter never re-issues or
     * collides with existing documents.
     *
     * @param  callable(int): int  $seedResolver  Returns the highest number
     *                                            already issued for the year.
     */
    public function nextSeeded(string $type, string $prefix, int $year, int $pad, callable $seedResolver): string
    {
        return $this->format($prefix, $this->allocate($type, $year, $pad, $seedResolver), $year, $pad);
    }

    private function format(string $prefix, int $next, int $year, int $pad): string
    {
        return $prefix.'-'.str_pad((string) $next, $pad, '0', STR_PAD_LEFT).'/'.$year;
    }

    /**
     * Locked read-increment of the (type, year) counter. Returns the next value.
     *
     * @param  callable(int): int|null  $seedResolver
     */
    private function allocate(string $type, int $year, int $pad, ?callable $seedResolver): int
    {
        return DB::transaction(function () use ($type, $year, $seedResolver) {
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

            $seed = $seedResolver !== null ? (int) $seedResolver($year) : 0;

            try {
                DB::table('document_counters')->insert([
                    'type' => $type,
                    'year' => $year,
                    'last_value' => $seed + 1,
                ]);

                return $seed + 1;
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
    }
}
