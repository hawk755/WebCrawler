<?php

declare(strict_types=1);

use App\Services\UrlValidator;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class UrlValidatorTest extends TestCase
{
    private UrlValidator $urlValidator;

    protected function setUp(): void
    {
        $this->urlValidator = new UrlValidator('http://example.com');
    }

    public function testInternalUrls()
    {
        $this->assertFalse($this->urlValidator->isInternalPage('#top'));
        $this->assertFalse($this->urlValidator->isInternalPage('javascript:{}'));
        $this->assertFalse($this->urlValidator->isInternalPage('tel:6031112298'));
        $this->assertFalse($this->urlValidator->isInternalPage('ftp://example.com/page.htm'));
        $this->assertFalse($this->urlValidator->isInternalPage('http://example.com/pages.zip'));
        $this->assertFalse($this->urlValidator->isInternalPage('http://example.com/pages.rar'));
        $this->assertFalse($this->urlValidator->isInternalPage('http://example.com/pages.gz'));
        $this->assertFalse($this->urlValidator->isInternalPage('http://example.com/pages.tgz'));
        $this->assertFalse($this->urlValidator->isInternalPage('http://domain.com/page.htm'));
        $this->assertFalse($this->urlValidator->isInternalPage('http://domain.com/page.php?from=example.com'));

        $this->assertTrue($this->urlValidator->isInternalPage('http://example.com/page.htm'));
        $this->assertTrue($this->urlValidator->isInternalPage('https://example.com/page.htm'));
        $this->assertTrue($this->urlValidator->isInternalPage('//example.com/page.htm'));
        $this->assertTrue($this->urlValidator->isInternalPage('/page.htm'));
        $this->assertTrue($this->urlValidator->isInternalPage('page.htm'));
        $this->assertTrue($this->urlValidator->isInternalPage('dir/page.htm'));
        $this->assertTrue($this->urlValidator->isInternalPage('/dir/page.htm'));
    }

    public function testNormalizeUrls()
    {
        $this->assertEquals($this->urlValidator->normalizeUrl('/'), 'http://example.com/');
        $this->assertEquals($this->urlValidator->normalizeUrl('/page.html'), 'http://example.com/page.html');
        $this->assertEquals($this->urlValidator->normalizeUrl('page.html'), 'http://example.com/page.html');
        $this->assertEquals($this->urlValidator->normalizeUrl('http://example.com/page.html'), 'http://example.com/page.html');
        $this->assertEquals($this->urlValidator->normalizeUrl('https://example.com/page.html'), 'https://example.com/page.html');
    }
}
