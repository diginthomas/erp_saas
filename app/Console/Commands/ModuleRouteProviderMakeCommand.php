<?php
/**
 * Concord CRM - https://www.concordcrm.com
 *
 * @version   1.3.5
 *
 * @link      Releases - https://www.concordcrm.com/releases
 * @link      Terms Of Service - https://www.concordcrm.com/terms
 *
 * @copyright Copyright (c) 2022-2024 KONKORD DIGITAL
 */

namespace App\Console\Commands;

use Nwidart\Modules\Commands\GeneratorCommand;
use Nwidart\Modules\Support\Config\GenerateConfigReader;
use Nwidart\Modules\Support\Stub;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class ModuleRouteProviderMakeCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    protected $argumentName = 'module';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'module:route-provider';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Create a new route service provider for the specified module.';

    protected function getArguments(): array
    {
        return [
            ['module', InputArgument::OPTIONAL, 'The name of module will be used.'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['force', null, InputOption::VALUE_NONE, 'Force the operation to run when the file already exists.'],
        ];
    }

    protected function getTemplateContents(): string
    {
        $module = $this->laravel['modules']->findOrFail($this->getModuleName());

        return (new Stub('/route-provider.stub', [
            'NAMESPACE' => $this->getClassNamespace($module),
            'CLASS' => $this->getFileName(),
            'MODULE_NAMESPACE' => $this->laravel['modules']->config('namespace'),
            'MODULE' => $this->getModuleName(),
            'CONTROLLER_NAMESPACE' => $this->getControllerNameSpace(),
            'WEB_ROUTES_PATH' => $this->getWebRoutesPath(),
            'API_ROUTES_PATH' => $this->getApiRoutesPath(),
            'LOWER_NAME' => $module->getLowerName(),
        ]))->render();
    }

    private function getFileName(): string
    {
        return 'RouteServiceProvider';
    }

    protected function getDestinationFilePath(): string
    {
        $path = $this->laravel['modules']->getModulePath($this->getModuleName());

        $generatorPath = GenerateConfigReader::read('provider');

        return $path.$generatorPath->getPath().'/'.$this->getFileName().'.php';
    }

    protected function getWebRoutesPath(): string
    {
        return $this->laravel['modules']->config('stubs.files.routes/web', 'routes/web.php');
        // return '/' . $this->laravel['modules']->config('stubs.files.routes/web', 'Routes/web.php');
    }

    protected function getApiRoutesPath(): string
    {
        return $this->laravel['modules']->config('stubs.files.routes/api', 'routes/api.php');
        // return '/' . $this->laravel['modules']->config('stubs.files.routes/api', 'Routes/api.php');
    }

    public function getDefaultNamespace(): string
    {
        $module = $this->laravel['modules'];

        return $module->config('paths.generator.provider.namespace') ?: $module->config('paths.generator.provider.path', 'Providers');
    }

    private function getControllerNameSpace(): string
    {
        $module = $this->laravel['modules'];

        return str_replace('/', '\\', $module->config('paths.generator.controller.namespace') ?: $module->config('paths.generator.controller.path', 'Controller'));
    }
}
