<?php

declare(strict_types=1);

namespace App\Services;

class UrlValidator
{
    private string $siteUrl;
    private string $siteHost;

    public function __construct(string $siteUrl)
    {
        $this->siteUrl = $siteUrl;
        $this->siteHost = parse_url($siteUrl, PHP_URL_HOST);
    }

    public function isInternalPage(string $url): bool
    {
        // skip anchors
        if ('#' === $url[0]) {
            return false;
        }
        // skip FTP links
        if (false !== strpos($url, 'ftp:')) {
            return false;
        }
        // skip archives
        if (preg_match('/\.(?:zip|rar|t?gz)(?:[\?\/]|$)/i', $url)) {
            return false;
        }
        // skip js/tel/etc links
        if (!preg_match('/https?\:/i', $url) && preg_match('/^[a-z]+\:/i', $url)) {
            return false;
        }
        // skip external Urls
        if ((preg_match('/https?:/i', $url) || 0 === strpos($url, '//')) && false === strpos($url, '//'.$this->siteHost)) {
            return false;
        }

        return true;
    }

    public function normalizeUrl(string $url): string
    {
        if (false !== strpos($url, $this->siteHost)) {
            return $url;
        }
        if ('/' !== $url[0]) {
            $url = '/'.$url;
        }

        return $this->siteUrl.$url;
    }
}
