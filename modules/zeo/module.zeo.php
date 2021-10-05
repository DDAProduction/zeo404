<?php
//require 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\View;
use EvolutionCMS\EvoImportExportPackage\Models\RoamingTariffs;
use EvolutionCMS\Main\Models\SiteContent;
use EvolutionCMS\Models\SiteTmplvarContentvalue;

if (IN_MANAGER_MODE != "true" || empty($modx) || !($modx instanceof DocumentParser)) {
    die("<b>INCLUDE_ORDERING_ERROR</b><br /><br />Please use the MODX Content Manager instead of accessing this file directly.");
}
if (!$modx->hasPermission('exec_module')) {
    header("location: " . $modx->getManagerPath() . "?a=106");
}

$moduleurl = 'index.php?a=112&id=' . $_GET['id'] . '&';
$action = $_GET['action'] ?? 'main';
$tab = $_GET['tab'] ?? 'main';

$data = [
    'module_url' => $moduleurl,
    'manager_theme' => $modx->config['manager_theme'],
    'action' => $action,
    'tab' => $tab,
];


if (isset($_POST['page_id'])) {
    if(isset($_POST['redirect_id']) && $_POST['redirect_id'] == ''){
        unset($_POST['redirect_id']);
    }
    $url = \DDAProduction\Zeo404\Models\ZeoUrlRedirect::query()->firstOrCreate(['id' => $_POST['redirect_id']], $_POST);
    $url->zeo_url_id = $_POST['zeo_url_id'];
    $url->page_id = $_POST['page_id'];
    $url->save();
}
switch ($action) {
    case 'switch_url':
        $url = \DDAProduction\Zeo404\Models\ZeoUrl::query()->find($_GET['url_id']);
        if (!is_null($url)) {
            if ($url->exclude == 0) {
                $url->exclude = 1;
            } else {
                $url->exclude = 0;
            }
            $url->save();
        }
        exit();
        break;
    case 'switch_redirect_url':
        $url = \DDAProduction\Zeo404\Models\ZeoUrlRedirect::query()->find($_GET['url_id']);
        if (!is_null($url)) {
            if ($url->exclude == 0) {
                $url->exclude = 1;
            } else {
                $url->exclude = 0;
            }
            $url->save();
        }
        exit();
        break;
    case 'delete_url':
        \DDAProduction\Zeo404\Models\ZeoUrlFail::query()->where('zeo_url_id', $_GET['url_id'])->delete();
        \DDAProduction\Zeo404\Models\ZeoUrlRedirect::query()->where('zeo_url_id', $_GET['url_id'])->delete();
        \DDAProduction\Zeo404\Models\ZeoUrl::query()->where('id', $_GET['url_id'])->delete();
        exit();
        break;
    case 'delete_redirect':
        \DDAProduction\Zeo404\Models\ZeoUrlRedirect::query()->where('id', $_GET['url_id'])->delete();
        exit();
        break;
    case 'delete_history':
        $url = \DDAProduction\Zeo404\Models\ZeoUrlFail::query()->find($_GET['url_id']);
        $origin_url = \DDAProduction\Zeo404\Models\ZeoUrl::query()->find($url->zeo_url_id);
        $origin_url->count_error = $origin_url->count_error - 1;
        $origin_url->save();
        $url->delete();
        exit();
        break;
}

switch ($tab) {
    case 'all_error':
        $data['url'] = \DDAProduction\Zeo404\Models\ZeoUrl::find($_GET['url_id'])->url;
        $data['history'] = \DDAProduction\Zeo404\Models\ZeoUrlFail::query()->where('zeo_url_id', $_GET['url_id'])->orderBy('id', 'DESC')->get();
        $outTpl = (string)View::make('Zeo::main', $data);
        return $outTpl;
        break;
    case 'redirect':
        $data['url'] = \DDAProduction\Zeo404\Models\ZeoUrl::find($_GET['url_id'])->url;
        $data['redirects'] = \DDAProduction\Zeo404\Models\ZeoUrlRedirect::query()->where('zeo_url_id', $_GET['url_id'])->orderBy('id', 'DESC')->get();
        $outTpl = (string)View::make('Zeo::main', $data);
        return $outTpl;
        break;
    case 'main':
        $data['links'] = \DDAProduction\Zeo404\Models\ZeoUrl::query()->orderBy('updated_at')->paginate(20)->appends($_GET);
        $outTpl = (string)View::make('Zeo::main', $data);
        return $outTpl;
        break;
    case '404info':
        $data['tasks'] = \DDAProduction\Zeo404\Models\CheckTask::query()->orderBy('updated_at')->paginate(20)->appends($_GET);
        $outTpl = (string)View::make('Zeo::main', $data);
        return $outTpl;
        break;
    case '404info_task':
        $data['task'] = \DDAProduction\Zeo404\Models\CheckTask::query()->find($_GET['task_id']);
        $data['pages'] = \DDAProduction\Zeo404\Models\CheckTaskPage::query()
            ->where('task_id', $_GET['task_id'])
            ->where(function($query) {
                $query->where('count_js_links', '>', 0)
                    ->orWhere('count_empty_links', '>', 0)
                    ->orWhere('count_error_link', '>', 0)
                    ->orWhere('count_blank', '>', 0)
                    ->orWhere('count_error_image', '>', 0)
                    ->orWhere('count_empty_image', '>', 0);
            })
            ->orderBy('updated_at')->paginate(20)->appends($_GET);
        $outTpl = (string)View::make('Zeo::main', $data);
        return $outTpl;
        break;
    case '404info_page':
        $data['page'] = \DDAProduction\Zeo404\Models\CheckTaskPage::query()->find($_GET['page_id']);
        $data['links'] = \DDAProduction\Zeo404\Models\CheckTaskPageLink::query()
            ->where('page_id', $_GET['page_id'])

            ->orderBy('updated_at')->paginate(20)->appends($_GET);
        $outTpl = (string)View::make('Zeo::main', $data);
        return $outTpl;
        break;
}

