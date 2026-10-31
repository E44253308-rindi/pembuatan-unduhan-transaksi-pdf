<?php

namespace App\Services;

use RuntimeException;
use Symfony\Component\Process\Process;

class WindowsPrinterDiscovery
{
    public function activePrinters(): array
    {
        if (PHP_OS_FAMILY !== 'Windows') {
            return [];
        }

        $powershell = rtrim((string) (getenv('WINDIR') ?: 'C:\\Windows'), '\\')
            .'\\System32\\WindowsPowerShell\\v1.0\\powershell.exe';

        $process = null;
        $errorOutput = '';

        for ($attempt = 1; $attempt <= 3; $attempt++) {
            $process = new Process([
                $powershell,
                '-NoProfile',
                '-NonInteractive',
                '-Command',
                '$ErrorActionPreference = "Stop"; $defaultPrinter = Get-CimInstance Win32_Printer | Where-Object { $_.Default } | Select-Object -First 1 -ExpandProperty Name; Get-Printer | Where-Object { $_.PrinterStatus -notin @("PendingDeletion", "Offline", "Error", "Stopped") } | Select-Object Name, @{Name="PrinterStatus";Expression={$_.PrinterStatus.ToString()}}, @{Name="Default";Expression={$_.Name -eq $defaultPrinter}} | ConvertTo-Json -Compress',
            ]);
            $process->setTimeout(8);
            $process->run();

            if ($process->isSuccessful()) {
                break;
            }

            $errorOutput = $process->getErrorOutput();
            if (str_contains($errorOutput, "\0")) {
                $errorOutput = mb_convert_encoding($errorOutput, 'UTF-8', 'UTF-16LE');
            }

            if ($attempt < 3) {
                usleep(200_000);
            }
        }

        if (! $process?->isSuccessful()) {
            throw new RuntimeException('Windows tidak dapat mengambil daftar printer: '.trim($errorOutput));
        }

        $output = trim($process->getOutput());
        if ($output === '') {
            return [];
        }

        $printers = json_decode($output, true, flags: JSON_THROW_ON_ERROR);
        if (! array_is_list($printers)) {
            $printers = [$printers];
        }

        return array_values(array_filter(array_map(function (array $printer): ?array {
            if (! isset($printer['Name']) || ! is_string($printer['Name'])) {
                return null;
            }

            return [
                'name' => $printer['Name'],
                'status' => (string) ($printer['PrinterStatus'] ?? 'Unknown'),
                'default' => (bool) ($printer['Default'] ?? false),
                'thermal' => (bool) preg_match('/pos|thermal|receipt|esc.?pos|58mm|80mm/i', $printer['Name']),
            ];
        }, $printers)));
    }

    public function isActive(string $name): bool
    {
        foreach ($this->activePrinters() as $printer) {
            if ($printer['name'] === $name) {
                return true;
            }
        }

        return false;
    }
}
