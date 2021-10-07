<?php

namespace DDAProduction\Zeo404\Console;

use Carbon\Carbon;
use DDAProduction\Zeo404\Models\CheckTask;
use DDAProduction\Zeo404\Models\CheckTaskPage;
use DDAProduction\Zeo404\Models\CheckTaskPageLink;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use PHPHtmlParser\Dom;
use Symfony\Component\Console\Helper\ProgressBar;

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
    /**
     * @var \EvolutionCMS\Core|\Illuminate\Config\Repository|mixed
     */
    private $ignored_blanks;
    /**
     * @var array
     */
    private $arrayChecked = [];
    /**
     * @var array
     */
    private $validationArray = [];

    private $currentUrl = '';
    /**
     * @var \EvolutionCMS\Core|\Illuminate\Config\Repository|mixed
     */
    private $ignored_all;
    /**
     * @var \EvolutionCMS\Core|\Illuminate\Config\Repository|mixed
     */
    private $email_notify;

    public function __construct()
    {
        parent::__construct();
        $this->sitemap = config('domain.sitemap_url');
        $this->domain = config('domain.current_site');
        $this->ignored_blanks = config('domain.ignored_blanks', []);
        $this->ignored_all = config('domain.ignored_all', []);
        $this->email_notify = config('domain.email_notify', '');
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
        $bar1 = new ProgressBar($this->output, $this->countPages);

        $bar1->start();


        foreach ($this->links as $link) {
            $this->newLine();

            $this->parsePage($link);
            $bar1->advance();
        }
        $bar1->finish();
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
        $skip = true;
        $dom = new Dom;
        try {
            $dom->loadFromUrl($link);
            $status = 200;
        } catch (\Exception $exception) {
            $skip = false;
            $status = 404;
        }
        if ($skip) {
            $urlsOnPage = $dom->getElementsbyTag('a');
            $this->checkedType = 1;
            $bar2 = $this->output->createProgressBar(count($urlsOnPage));
            $bar2->start();


            foreach ($urlsOnPage as $url) {
                $bar2->advance();
                $this->countLinks++;
                $needCheck = $this->checkSkipped($url);
                if ($needCheck) {
                    $urlForCheck = $this->prepareLink($url->href);
                    $this->checkLink($urlForCheck, $url->text);
                }
                $this->validationArray[$url->href] = $this->arrayChecked;
                $this->currentUrl = $url->href;
                $this->saveResult();
            }
            $bar2->finish();
            $imagesOnPage = $dom->getElementsbyTag('img');
            $this->checkedType = 2;
            $bar3 = $this->output->createProgressBar(count($imagesOnPage));
            $bar3->start();
            foreach ($imagesOnPage as $url) {
                $bar3->advance();
                $this->countImages++;
                $urlForCheck = $this->prepareLink($url->src);
                $needCheck = $this->checkSkippedImage($url);
                if ($needCheck) {
                    $this->checkLink($urlForCheck, $url->alt ?? 'Image');
                }
                $this->validationArray[$url->src] = $this->arrayChecked;
                $this->saveResult();
            }
            $bar3->finish();
        }
        $page->status = $status;
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

    private function checkLink($urlForCheck, $info = '')
    {
        if (in_array($urlForCheck, $this->ignored_all)) {
            return;
        }
        try {
            $status = Http::timeout(3)->get($urlForCheck)->status();
        } catch (\Exception $exception) {
            $status = 404;
            if (stristr($exception->getMessage(), 'Connection timed') !== false) {
                $status = 504;
            }
        }

        if ($status != 200) {
            if ($this->checkedType == 1) {
                $this->arrayChecked['errorLinks'] = 1;
                $this->arrayChecked['errorLinksArray'] = ['status' => $status, 'url' => $urlForCheck, 'info' => $info];
            } else {
                $this->arrayChecked['errorImages'] = 1;
                $this->arrayChecked['errorImagesArray'] = ['status' => $status, 'url' => $urlForCheck, 'info' => $info];
            }
        }
    }

    private function checkSkipped($url): bool
    {
        if ($url->target != '') {
            if ($url->target == '_blank') {
                if (!in_array($url->href, $this->ignored_blanks)) {
                    $this->arrayChecked['blankLinks'] = 1;
                    $this->arrayChecked['blankArray'] = ['status' => 1, 'url' => $url->href, 'info' => $url->text];
                }
            }
        }
        if (stristr($url->href, 'javascript:') !== false) {
            $this->arrayChecked['jsLinks'] = 1;

            return false;
        }
        if (trim($url->href) == '') {
            $this->arrayChecked['emptyLinks'] = 1;
            $this->arrayChecked['emptyArray'] = ['status' => 2, 'url' => 'no_link', 'info' => $url->text];


            return false;
        }
        if ($url->href == '#') {
            $this->arrayChecked['jsLinks'] = 1;

            return false;
        }
        if (substr($url->href, 0, 3) == 'tel') {
            $this->arrayChecked['phoneLinks'] = 1;

            return false;
        }

        if (isset($this->validationArray[$url->href])) {
            if (isset($this->validationArray[$url->href]['errorLinks'])) {
                $this->arrayChecked['errorLinks'] = $this->validationArray[$url->href]['errorLinks'];
                $this->arrayChecked['errorLinksArray'] = $this->validationArray[$url->href]['errorLinksArray'];
            }

            return false;
        }

        return true;
    }

    private function checkSkippedImage($url): bool
    {
        if (isset($this->validationArray[$url->src])) {
            if (isset($this->validationArray[$url->src]['errorImages'])) {
                $this->arrayChecked['errorImages'] = $this->validationArray[$url->src]['errorImages'];
                $this->arrayChecked['errorImagesArray'] = $this->validationArray[$url->src]['errorImagesArray'];
            }

            return false;
        }

        if (stristr($url->src, 'noimage-') === false) {
            return true;
        }
        $this->arrayChecked['emptyImages'] = 1;


        return false;
    }

    private function saveIncorrect(int $status, $urlForCheck, $info = '')
    {
        try {
            CheckTaskPageLink::query()->create(
                [
                    'page_id' => $this->pageId,
                    'url' => $urlForCheck,
                    'type' => $this->checkedType,
                    'code' => $status,
                    'info' => $info
                ]
            );
        } catch (\Exception $exception) {
            $this->info($this->currentUrl);
            $this->info($urlForCheck);
            $this->info($status);
            $this->info($exception->getMessage());
            exit();
        }
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
        if ($this->email_notify != '') {
            $this->sendMail($task);
        }
    }

    private function saveResult()
    {
        if (isset($this->arrayChecked['blankLinks'])) {
            $this->blankLinks++;
            $this->saveIncorrect(
                $this->arrayChecked['blankArray']['status'],
                $this->arrayChecked['blankArray']['url'],
                $this->arrayChecked['blankArray']['info']
            );
        }
        if (isset($this->arrayChecked['errorLinks'])) {
            $this->errorLinks++;
            $this->saveIncorrect(
                $this->arrayChecked['errorLinksArray']['status'],
                $this->arrayChecked['errorLinksArray']['url'],
                $this->arrayChecked['errorLinksArray']['info']
            );
        }
        if (isset($this->arrayChecked['emptyLinks'])) {
            $this->emptyLinks++;
            $this->saveIncorrect(
                $this->arrayChecked['emptyArray']['status'],
                $this->arrayChecked['emptyArray']['url'],
                $this->arrayChecked['emptyArray']['info']
            );
        }
        if (isset($this->arrayChecked['phoneLinks'])) {
            $this->phoneLinks++;
        }
        if (isset($this->arrayChecked['jsLinks'])) {
            $this->jsLinks++;
        }
        if (isset($this->arrayChecked['emptyImages'])) {
            $this->emptyImages++;
        }
        if (isset($this->arrayChecked['errorImages'])) {
            $this->errorImages++;
            $this->saveIncorrect(
                $this->arrayChecked['errorImagesArray']['status'],
                $this->arrayChecked['errorImagesArray']['url'],
                $this->arrayChecked['errorImagesArray']['info']
            );
        }


        $this->arrayChecked = [];
    }

    private function sendMail(\Illuminate\Database\Eloquent\Model $task)
    {
        $param = array();
        $param['from'] = evo()->getConfig('emailsender');
        $param['subject'] = 'Task complete ' . $task->name;
        $param['body'] = \Illuminate\Support\Facades\View::make('Zeo::mail', ['task' => $task->toArray()]);
        $param['to'] = $this->email_notify;
        $rs = evo()->sendmail($param);

    }

}