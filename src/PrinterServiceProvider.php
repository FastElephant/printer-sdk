<?php


namespace FastElephant\OpenCater;

use Illuminate\Support\ServiceProvider;

class PrinterServiceProvider extends ServiceProvider
{
    /**
     * 启动应用服务
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([$this->getConfigFile() => config_path('printer.php')]);
    }

    /**
     * 在容器中注册绑定。
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom($this->getConfigFile(), 'printer');
    }

    protected function getConfigFile()
    {
        return __DIR__ . DIRECTORY_SEPARATOR;
    }
}
