<?php

namespace App\Controllers;

use CodeIgniter\HTTP\DownloadResponse;

class Home extends BaseController
{
    public function index()
    {
        return view('welcome_message');
    }

    public function small()
    {
        $path = $this->createFile(1024 * 1024);

        return $this->createResponse($path);
    }

    public function large()
    {
        // Create a file large enough to exceed memory capactiy
        $bytes = $this->getMemoryLimit() * 2;
        $path  = $this->createFile($bytes);

        return $this->createResponse($path);
    }

    private function getMemoryLimit(): int
    {
        $value = strtolower(trim(ini_get('memory_limit')));
        $unit  = $value[strlen($value) - 1];
        $num   = (int) rtrim($value, $unit);

        switch ($unit) {
            case 'g': $num *= 1024;
            // no break
            case 'm': $num *= 1024;
            // no break
            case 'k': $num *= 1024;

            // If it is not one of those modifiers then it was numerical bytes, add the final digit back
            // no break
            default:
                $num = (int) ($num . $unit);
        }

        return $num;
    }

    private function createFile(int $bytes): string
    {
        $path   = tempnam(sys_get_temp_dir(), 'cierror');
        $data   = random_bytes(1024);
        $cycles = round($bytes / 1024);
        $handle = fopen($path, 'w');

        // Write $data to $path $cycle times
        for ($i = 0; $i < $cycles; $i++) {
            fwrite($handle, $data);
        }
        fclose($handle);

        return $path;
    }

    private function createResponse(string $path): DownloadResponse
    {
        log_message('debug', 'Creating DownloadResponse for ' . $path . ' size: ' . filesize($path));

        return $this->response->download($path, null, true)->setFileName('cierror-' . time());
    }
}
