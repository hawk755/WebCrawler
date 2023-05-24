<?php

declare(strict_types=1);

namespace App\Services;

class MultiDownloader
{
    private const DEFAULT_PROCESSES_AMOUNT = 5;
    private array $tempFileNames = [];

    public function __construct(int $processesAmount)
    {
        if ($processesAmount < 1 || $processesAmount > 50) {
            $processesAmount = self::DEFAULT_PROCESSES_AMOUNT;
        }
        for ($i = 0; $i < $processesAmount; ++$i) {
            $this->tempFileNames[$i] = tempnam(sys_get_temp_dir(), 'TMP');
            touch($this->tempFileNames[$i]);
        }
    }

    public function __destruct()
    {
        foreach ($this->tempFileNames as $tempFileName) {
            if (is_file($tempFileName)) {
                unlink($tempFileName);
            }
        }
    }

    public function getProcessesAmount(): int
    {
        return count($this->tempFileNames);
    }

    public function getData(int $index): string
    {
        return file_get_contents($this->tempFileNames[$index]);
    }

    public function run(array $urls)
    {
        $urlsCount = count($urls);
        $multiCurl = curl_multi_init();

        curl_multi_setopt($multiCurl, CURLMOPT_PIPELINING, CURLPIPE_MULTIPLEX);
        curl_multi_setopt($multiCurl, CURLMOPT_MAXCONNECTS, $urlsCount);

        $filePointers = $curlHandlers = [];

        for ($i = 0; $i < $urlsCount; ++$i) {
            $filePointers[$i] = fopen($this->tempFileNames[$i], 'wb');
            if ($filePointers[$i]) {
                $curlHandlers[$i] = curl_init();
                $this->set_option($curlHandlers[$i], $urls[$i], $filePointers[$i]);
                curl_multi_add_handle($multiCurl, $curlHandlers[$i]);
            }
        }

        if (!$curlHandlers) {
            curl_multi_close($multiCurl);

            return;
        }

        // execute the multi handle
        $active = null;
        do {
            $status = curl_multi_exec($multiCurl, $active);
            if ($active) {
                // Wait a short time for more activity
                curl_multi_select($multiCurl);
            }
        } while ($active && CURLM_OK == $status);

        for ($i = 0; $i < $urlsCount; ++$i) {
            curl_multi_remove_handle($multiCurl, $curlHandlers[$i]);
            curl_close($curlHandlers[$i]);
            fclose($filePointers[$i]);
        }

        curl_multi_close($multiCurl);
    }

    private function set_option(\CurlHandle $curlHandler, string $url, $filePointer)
    {
        $opts = [
            CURLOPT_URL => $url,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_HEADER => 0,
            CURLOPT_FOLLOWLOCATION => 1,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_SSL_VERIFYHOST => 0,
            // CURLOPT_RETURNTRANSFER => 1,
            // CURLOPT_ENCODING => 'gzip,deflate',
            // CURLOPT_TCP_FASTOPEN => 1, // Added in cURL 7.49.0. Available since PHP 7.0.7 (if defined ?)
            CURLOPT_FILE => $filePointer];

        curl_setopt_array($curlHandler, $opts);
    }
}
