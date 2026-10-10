<?php

namespace App\Http\Controllers\Concerns;

use App\Http\Requests\ImportMasterDataRequest;
use App\Imports\ImportDefinition;
use App\Services\ExcelTemplateService;
use App\Services\MasterImportService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Excel import + template download for a master-data module.
 * Both entry points are admin-gated at the route level.
 */
trait ImportsMasterData
{
    protected function importMaster(ImportMasterDataRequest $request, ImportDefinition $definition): RedirectResponse
    {
        try {
            $result = app(MasterImportService::class)->import(
                $request->file('file')->getPathname(),
                $request->file('file')->extension(),
                $definition
            );
        } catch (Exception) {
            return back()->withErrors([
                'file' => 'File tidak dapat dibaca. Gunakan template yang telah disediakan.',
            ]);
        }

        if ($result['failed'] > 0) {
            return back()
                ->withErrors([
                    'file' => "Impor {$definition->label()} gagal: {$result['failed']} baris tidak valid. Periksa pesan kesalahan di bawah.",
                ])
                ->with('import_errors', $result['errors']);
        }

        return back()->with(
            'success',
            "Impor {$definition->label()} berhasil: {$result['created']} data baru, {$result['updated']} data diperbarui."
        );
    }

    protected function templateMaster(ImportDefinition $definition): StreamedResponse
    {
        return app(ExcelTemplateService::class)->stream($definition);
    }
}
