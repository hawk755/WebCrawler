<?php

declare(strict_types=1);

use App\Services\MultiDownloader;
use App\Services\UrlValidator;
use App\Services\WebCrawler;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class WebCrawlerTest extends TestCase
{
    public function testCrawl()
    {
        $crawler = new WebCrawler(
            new MultiDownloader(1),
            new UrlValidator('http://www.example.com')
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Directory is not writeable');
        $crawler->crawlSiteInto('/dummy_absent_dir/');

        $tempDir = sys_get_temp_dir().'/WebCrawlerTest/';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0777);
        }
        $crawler->crawlSiteInto($tempDir);
        $this->assertTrue(is_file($tempDir.'index.html'));
        $this->assertStringContainsString('Example Domain', file_get_contents($tempDir.'index.html'));
        unlink($tempDir.'index.html');
        rmdir($tempDir);
    }
}
