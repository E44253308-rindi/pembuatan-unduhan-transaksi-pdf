<?php

namespace App\Http\Controllers;

use App\Services\WindowsPrinterDiscovery;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Throwable;

class PrinterListController extends Controller
{
    public function __invoke(WindowsPrinterDiscovery $discovery): JsonResponse
    {
        try {
            return response()->json([
                'printers' => $discovery->activePrinters(),
                'browser_print_available' => true,
            ]);
        } catch (Throwable $exception) {
            report($exception);
            Log::error('Unable to discover available Windows printers.', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'message' => 'Daftar printer Windows gagal dimuat. Anda masih dapat memilih printer melalui dialog cetak browser.',
                'browser_print_available' => true,
            ], 503);
        }
    }
}
