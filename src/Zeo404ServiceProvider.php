<?php

namespace DDAProduction\Zeo404;

use DDAProduction\Zeo404\Console\SelfParse;
use EvolutionCMS\ServiceProvider;

class Zeo404ServiceProvider extends ServiceProvider
{
    /**
     * Если указать пустую строку, то сниппеты и чанки будут иметь привычное нам именование
     * Допустим, файл test создаст чанк/сниппет с именем test
     * Если же указан namespace то файл test создаст чанк/сниппет с именем zeo404#test
     * При этом поддерживаются файлы в подпапках. Т.е. файл test из папки subdir создаст элемент с именем subdir/test
     */
    protected $namespace = 'zeo404';

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([SelfParse::class]);

        $this->loadPluginsFrom(
            dirname(__DIR__) . '/plugins/'
        );

        $this->loadViewsFrom(__DIR__ . '/../modules/zeo/views', 'Zeo');


        if (isset($_SESSION['mgrRole']) && ($_SESSION['mgrRole'] == 1 || $_SESSION['mgrRole'] == 8)) {
            $this->app->registerModule(
                'Zeo404',
                dirname(__DIR__) . '/modules/zeo/module.zeo.php'
            );
        }
        $this->publishes([__DIR__ . '/../config/domain.php' => config_path('domain.php', true)]);

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }
}