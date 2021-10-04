<?php


Event::listen('evolution.OnPageNotFound', function ($params) {
    try {
        $url = \DDAProduction\Zeo404\Models\ZeoUrl::query()
            ->firstOrCreate(['url_md5' => md5($_GET['q'])], ['url' => $_GET['q']]);
    } catch (Exception $exception) {
        return '';
    }
    if ($url->exclude == 0) {
        $url->count_error = $url->count_error + 1;
        $url->save();
        if (isset($_SERVER['HTTP_USER_AGENT']) && !is_null($_SERVER['HTTP_USER_AGENT'])) {
            $user_data = $_SERVER['HTTP_USER_AGENT'];
        } else {
            $user_data = ' ';
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? ' ';
        $referer = substr($referer, 0, 254);
        \DDAProduction\Zeo404\Models\ZeoUrlFail::query()
            ->create([
                         'zeo_url_id' => $url->getKey(),
                         'referer' => $referer,
                         'user_data' => $user_data
                     ]);
        $redirect = \DDAProduction\Zeo404\Models\ZeoUrlRedirect::query()
            ->where('zeo_url_id', $url->getKey())
            ->where('exclude', 0)->first();
        if (!is_null($redirect)) {
            EvolutionCMS()->sendRedirect(
                \UrlProcessor::makeUrl($redirect->page_id),
                '',
                '',
                "HTTP/1.1 301 Moved Permanently"
            );
        }
    }
});
