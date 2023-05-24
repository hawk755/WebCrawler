<?php

declare(strict_types=1);

namespace App\Services;

class WebCrawler
{
    private const MAX_PAGES_TO_CRAWL = 100;
    private string $targetDirPath;
    private int $currentPageId = 0;
    private array $urlToNormalizedUrl = [];
    private array $urlNormalizedToLocalFile = [];

    public function __construct(private MultiDownloader $downloader, private UrlValidator $urlValidator)
    {
    }

    public function crawlSiteInto(string $targetDirPath)
    {
        $this->targetDirPath = $targetDirPath;
        if (!is_dir($this->targetDirPath) || !is_writable($this->targetDirPath)) {
            throw new \Exception('Directory is not writeable: '.$this->targetDirPath);
        }

        $pagesProcessed = 0;
        $url = '/';
        $normalizedUrl = $this->urlValidator->normalizeUrl($url);
        $this->urlToNormalizedUrl[$url] = $normalizedUrl;
        $this->urlNormalizedToLocalFile[$normalizedUrl] = 'index.html';
        $nextUrlsSet = [$normalizedUrl];
        $processesAmount = $this->downloader->getProcessesAmount();

        while (!empty($nextUrlsSet)) {
            $urls = array_splice($nextUrlsSet, 0, $processesAmount);
            if ($pagesProcessed + count($urls) > self::MAX_PAGES_TO_CRAWL) {
                $urls = array_splice($urls, 0, self::MAX_PAGES_TO_CRAWL - $pagesProcessed);
            }
            $this->downloader->run($urls);

            foreach ($urls as $index => $url) {
                $pageHtml = $this->downloader->getData($index);
                echo $url."\n";
                $nextUrlsSet = array_merge($nextUrlsSet, $this->savePage($this->urlNormalizedToLocalFile[$url], $pageHtml));
                ++$pagesProcessed;
            }
            if ($pagesProcessed >= self::MAX_PAGES_TO_CRAWL) {
                break;
            }
        }
    }

    private function savePage(string $localFileName, string $html): array
    {
        $nextUrlsSet = [];
        if (preg_match_all('/<a[^>]*?\shref=["\']?(.+?)["\'\s>]/is', $html, $matches)) {
            foreach ($matches[1] as $url) {
                if ($this->urlValidator->isInternalPage($url)) {
                    if (!isset($this->urlToNormalizedUrl[$url])) {
                        $normalizedUrl = $this->urlValidator->normalizeUrl($url);
                        $this->urlToNormalizedUrl[$url] = $normalizedUrl;
                        if (!isset($this->urlNormalizedToLocalFile[$normalizedUrl])) {
                            $this->urlNormalizedToLocalFile[$normalizedUrl] = (++$this->currentPageId).'.html';
                            $nextUrlsSet[] = $normalizedUrl;
                        }
                    }

                    $html = preg_replace(
                        '/(href=["\']?)'.preg_quote($url, '/').'(["\'\s>])/i',
                        '${1}'.$this->urlNormalizedToLocalFile[$this->urlToNormalizedUrl[$url]].'$2',
                        $html
                    );
                }
            }
        }
        if (false === file_put_contents($this->targetDirPath.'/'.$localFileName, $html)) {
            throw new \Exception('Failed to create file: '.$this->targetDirPath.'/'.$localFileName);
        }

        return $nextUrlsSet;
    }
}
