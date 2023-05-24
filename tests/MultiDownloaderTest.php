<?php

declare(strict_types=1);

use App\Services\MultiDownloader;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class MultiDownloaderTest extends TestCase
{
    public function testDownload()
    {
        $downloader = new MultiDownloader(2);

        $this->assertEquals(2, $downloader->getProcessesAmount());

        $downloader->run(['http://example.com', 'http://www.lipsum.com']);
        $this->assertStringContainsString('Example Domain', $downloader->getData(0));
        $this->assertStringContainsString('Lorem Ipsum', $downloader->getData(1));
    }
}
