<?php

namespace DDAProduction\Zeo404\Console;

use Carbon\Carbon;
use DDAProduction\Zeo404\Models\CheckTask;
use DDAProduction\Zeo404\Models\CheckTaskPage;
use DDAProduction\Zeo404\Models\CheckTaskPageLink;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use PHPHtmlParser\Dom;

class SelfParse extends Command
{
    protected $signature = 'self:parse';

    protected $description = 'Self parse for errors';
    /**
     * @var \EvolutionCMS\Core|\Illuminate\Config\Repository|mixed
     */
    private $sitemap;
    /**
     * @var \EvolutionCMS\Core|\Illuminate\Config\Repository|mixed
     */
    private $domain;
    /**
     * @var int
     */
    private $countPages;

    /**
     * @var array
     */
    private $links = [];

    /**
     * @var string
     */
    private $checkedType;

    /**
     * @var integer
     */
    private $taskId;
    /**
     * @var mixed
     */
    private $pageId;
    /**
     * @var int
     */
    private $jsLinks;
    /**
     * @var int
     */
    private $phoneLinks;
    /**
     * @var int
     */
    private $emptyLinks;
    /**
     * @var int
     */
    private $blankLinks;
    /**
     * @var int
     */
    private $errorLinks;
    /**
     * @var int
     */
    private $errorImages;
    /**
     * @var int
     */
    private $emptyImages;
    /**
     * @var int
     */
    private $countImages;
    /**
     * @var int
     */
    private $countLinks;

    public function __construct()
    {
        parent::__construct();
        $this->sitemap = config('domain.sitemap_url');
        $this->domain = config('domain.current_site');
    }

    public function handle()
    {
        $task = CheckTask::query()->create(['name' => $this->domain]);
        $this->taskId = $task->getKey();
        $this->info('Check site: ' . $this->domain);
        try {
            $this->check_sitemap($this->sitemap);
        } catch (\Exception $exception) {
            dd($exception->getMessage());
        }
        $this->countPages = count($this->links);
        $this->checkLinks();
        $this->updateTask($task);
        $this->newLine();
    }

    private function check_sitemap($sitemap)
    {
        $this->info('Check sitemap in file: ' . $sitemap);
        $request = Http::get($sitemap);
        if ($request->status() != 200) {
            throw new \Exception('sitemap not found');
        }
        $this->checkSiteMapBody($request->body());
    }

    private function checkSiteMapBody($sitemapBody)
    {
        $body = simplexml_load_string($sitemapBody);
        if (isset($body->sitemap)) {
            foreach ($body->sitemap as $map) {
                $this->check_sitemap((string)$map->loc);
            }
        } else {
            $this->collectAllLinks($body);
        }
    }

    private function collectAllLinks($links)
    {
        foreach ($links as $link) {
            $this->links[] = (string)$link->loc;
        }
    }

    private function checkLinks()
    {
        $this->info('Found links: ' . $this->countPages);
        $this->info('Start check pages');
        $bar = $this->output->createProgressBar($this->countPages);
        $bar->start();

        foreach ($this->links as $link) {
            $bar->advance();
            $this->parsePage($link);
        }
        $bar->finish();

    }

    private function parsePage($link)
    {
        $page = CheckTaskPage::query()->create(['task_id' => $this->taskId, 'url' => $link]);
        $this->jsLinks = 0;
        $this->phoneLinks = 0;
        $this->emptyLinks = 0;
        $this->blankLinks = 0;
        $this->errorLinks = 0;
        $this->errorImages = 0;
        $this->emptyImages = 0;
        $this->countImages = 0;
        $this->countLinks = 0;
        $this->pageId = $page->getKey();
        $dom = new Dom;

        $dom->loadFromUrl($link);
        $urlsOnPage = $dom->getElementsbyTag('a');
        $this->checkedType = 1;
        foreach ($urlsOnPage as $url) {
            $this->countLinks++;

                $needCheck = $this->checkSkipped($url);
                if ($needCheck) {
                    $urlForCheck = $this->prepareLink($url->href);
                    $this->checkLink($urlForCheck);
                }


        }
        $imagesOnPage = $dom->getElementsbyTag('img');
        $this->checkedType = 2;
        foreach ($imagesOnPage as $url) {
            $this->countImages++;
            $urlForCheck = $this->prepareLink($url->src);
            $needCheck = $this->checkSkippedImage($url->src);
            if ($needCheck) {
                $this->checkLink($urlForCheck);
            }
        }
        $page->count_link = $this->countLinks;
        $page->count_js_links = $this->jsLinks;
        $page->count_phone_links = $this->phoneLinks;
        $page->count_empty_links = $this->emptyLinks;
        $page->count_error_link = $this->errorLinks;
        $page->count_blank = $this->blankLinks;
        $page->count_image = $this->countImages;
        $page->count_error_image = $this->errorImages;
        $page->count_empty_image = $this->emptyImages;
        $page->save();
    }

    private function prepareLink($href)
    {
        $separator = '/';
        if (stristr($href, 'http:') === false) {
            if (stristr($href, 'https:') === false) {
                if (substr($href, 0, 1) == '/') {
                    $separator = '';
                }
                $href = $this->domain . $separator . $href;
            }
        }

        return $href;
    }

    private function checkLink($urlForCheck)
    {
        try {
            $status = Http::get($urlForCheck)->status();
        } catch (\Exception $exception) {
            $status = 404;
        }

        if ($status != 200) {
            if($this->checkedType == 1) {
                $this->errorLinks++;
            }else {
                $this->errorImages++;
            }
            $this->saveIncorrect($status, $urlForCheck);
        }
    }

    private function checkSkipped($url): bool
    {
        if ($url->target != '') {
            if($url->target == '_blank') {
                $this->blankLinks++;
                $this->saveIncorrect(1, $url->href, $url->text);
            }

        }
        if ($url->href == 'javascript:') {
            $this->jsLinks++;

            return false;
        }
        if ($url->href == '') {
            $this->emptyLinks++;
            $this->saveIncorrect(2, $url->href, $url->text);

            return false;
        }
        if ($url->href == '#') {
            $this->jsLinks++;

            return false;
        }
        if (substr($url->href, 0, 3) == 'tel') {
            $this->phoneLinks++;
            return false;
        }

        return true;
    }

    private function checkSkippedImage($href): bool
    {
        if (stristr($href, 'noimage-') === false) {

            return true;
        }
        $this->emptyImages++;

        return false;
    }

    private function saveIncorrect(int $status, $urlForCheck, $info = '')
    {
        CheckTaskPageLink::query()->create(['page_id' => $this->pageId, 'url'=>$urlForCheck, 'type'=>$this->checkedType, 'code'=>$status, 'info'=>$info]);
    }

    private function updateTask(\Illuminate\Database\Eloquent\Model $task)
    {
        $task->count_page = CheckTaskPage::query()->where('task_id', $task->getKey())->count('id');
        $task->count_link = CheckTaskPage::query()->where('task_id', $task->getKey())->sum('count_link');
        $task->count_js_links = CheckTaskPage::query()->where('task_id', $task->getKey())->sum('count_js_links');
        $task->count_phone_links = CheckTaskPage::query()->where('task_id', $task->getKey())->sum('count_phone_links');
        $task->count_empty_links = CheckTaskPage::query()->where('task_id', $task->getKey())->sum('count_empty_links');
        $task->count_image = CheckTaskPage::query()->where('task_id', $task->getKey())->sum('count_image');
        $task->count_error_link = CheckTaskPage::query()->where('task_id', $task->getKey())->sum('count_error_link');
        $task->count_error_image = CheckTaskPage::query()->where('task_id', $task->getKey())->sum('count_error_image');
        $task->count_blank = CheckTaskPage::query()->where('task_id', $task->getKey())->sum('count_blank');
        $task->count_empty_image = CheckTaskPage::query()->where('task_id', $task->getKey())->sum('count_empty_image');
        $task->date_end = Carbon::now();
        $task->save();
    }

}