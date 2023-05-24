#!/usr/bin/env php
<?php

declare(strict_types=1);

require dirname(__DIR__).'/vendor/autoload.php';

use App\Services\MultiDownloader;
use App\Services\UrlValidator;
use App\Services\WebCrawler;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\SingleCommandApplication;

(new SingleCommandApplication())
    ->addArgument('url', InputArgument::REQUIRED)
    ->addArgument('procAmount', InputArgument::OPTIONAL)
    ->setCode(function (InputInterface $input, OutputInterface $output) {
        try {
            $siteUrl = rtrim($input->getArgument('url'), '/');
            $targetDirPath = __DIR__.'/data/'.preg_replace('/\W/', '_', parse_url($siteUrl, PHP_URL_HOST));

            if (!is_dir($targetDirPath)) {
                mkdir($targetDirPath, 0777);
                if (!is_dir($targetDirPath)) {
                    throw new \Exception('Failed to create directory: '.$targetDirPath);
                }
            }

            $crawler = new WebCrawler(
                new MultiDownloader((int) $input->getArgument('procAmount')),
                new UrlValidator($siteUrl)
            );
            $crawler->crawlSiteInto($targetDirPath);
            $output->writeln('Done');
        } catch (Exception $e) {
            $output->writeln($e->getMessage());
        }
    })
    ->run()
;
