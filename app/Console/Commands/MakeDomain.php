<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Filesystem\Filesystem;

/**
 * @property Filesystem files
 * @property string domainRootPath
 * @property string domainPath
 * @property string domain
 * @property string stubsPath
 * @property string domainUtilsPath
 */
class MakeDomain extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:domain {domain}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new domain structure.';

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
        $this->domainRootPath = app_path().'/Domain';
        $this->domainUtilsPath = app_path().'/DomainUtils';
        $this->stubsPath = __DIR__.'/stubs';
    }

    /**
     * Execute the console command.
     *
     * @return int
     *
     * @throws FileNotFoundException
     */
    public function handle()
    {
        $this->domain = ucfirst($this->argument('domain'));
        $this->domainPath = $this->domainRootPath.'/'.$this->domain;

        $this->initDomainUtils();

        if (! is_dir($this->domainRootPath)) {
            mkdir($this->domainRootPath);
        }

        if (! is_dir($this->domainPath)) {
            mkdir($this->domainPath);

            $modelInfo = $this->buildModels();
            $dalInfo = $this->buildDAL($modelInfo);
            $bllInfo = $this->buildBLL($dalInfo);
            $requestInfo = $this->buildRequests();
            $controllerInfo = $this->buildControllers($bllInfo, $modelInfo, $requestInfo);
            $policyInfo = $this->buildPolicies();
            $this->buildRoutes($controllerInfo);
            $providerInfo = $this->buildProviders(
                $bllInfo,
                $dalInfo,
                $controllerInfo,
                $policyInfo,
                $modelInfo
            );

            $this->info('Domain '.$this->domain.' created successfully!');
            $this->info('Please add '.$providerInfo['namespace'].'\\'.$providerInfo['class']
                .'::class to your providers in config/app.php');

            return 0;
        }

        $this->error('Domain '.$this->domain.' already exist!');

        return 0;
    }

    /**
     * @throws FileNotFoundException
     */
    private function buildFile($path, $stubPath, $search, $replace)
    {
        $stub = $this->files->get($stubPath);

        $stub = str_replace($search, $replace, $stub);

        $this->files->put($path, $stub);
    }

    /**
     * @throws FileNotFoundException
     */
    public function initDomainUtils()
    {
        if (! is_dir($this->domainUtilsPath)) {
            mkdir($this->domainUtilsPath);
        }

        $baseBllPath = $this->domainUtilsPath.'/BaseBLL';
        $baseDalPath = $this->domainUtilsPath.'/BaseDAL';

        if (! is_dir($baseBllPath)) {
            mkdir($baseBllPath);

            $stubBaseBllPath = $this->stubsPath.'/base_bll.stub';
            $stubBaseBllIntPath = $this->stubsPath.'/base_bll_interface.stub';
            $stubBaseBllFileUtPath = $this->stubsPath.'/base_bll_file_utils.stub';
            $baseBllFile = $baseBllPath.'/BaseBLL.php';
            $baseBllInterfaceFile = $baseBllPath.'/BaseBLLInterface.php';
            $baseBllFileUtilsFile = $baseBllPath.'/BaseBLLFileUtils.php';

            $this->buildFile($baseBllFile, $stubBaseBllPath, [], []);
            $this->buildFile($baseBllInterfaceFile, $stubBaseBllIntPath, [], []);
            $this->buildFile($baseBllFileUtilsFile, $stubBaseBllFileUtPath, [], []);
        }

        if (! is_dir($baseDalPath)) {
            mkdir($baseDalPath);

            $stubBaseDalPath = $this->stubsPath.'/base_dal.stub';
            $stubBaseDalIntPath = $this->stubsPath.'/base_dal_interface.stub';

            $baseDalFile = $baseDalPath.'/BaseDAL.php';
            $baseDalInterfaceFile = $baseDalPath.'/BaseDALInterface.php';

            $this->buildFile($baseDalFile, $stubBaseDalPath, [], []);
            $this->buildFile($baseDalInterfaceFile, $stubBaseDalIntPath, [], []);
        }
    }

    /**
     * @return array
     *
     * @throws FileNotFoundException
     */
    private function buildModels()
    {
        $path = $this->domainPath.'/Models';

        if (! is_dir($path)) {
            mkdir($path);
        }

        $stubDalPath = $this->stubsPath.'/domain_model.stub';
        $domainModelFile = $path.'/'.$this->domain.'.php';
        $namespace = 'App\Domain\\'.$this->domain.'\Models';

        $search = [
            '{{ namespace }}',
            '{{ class }}',
        ];

        $replace = [
            $namespace,
            $this->domain,
        ];

        $this->buildFile($domainModelFile, $stubDalPath, $search, $replace);

        return [
            'namespace' => $namespace,
            'class' => $this->domain,
        ];
    }

    /**
     * @return string[]
     *
     * @throws FileNotFoundException
     */
    private function buildDAL($modelInfo)
    {
        $path = $this->domainPath.'/DAL/';

        if (! is_dir($path)) {
            mkdir($path);
        }

        $path .= '/'.$this->domain;

        if (! is_dir($path)) {
            mkdir($path);
        }

        $stubDalPath = $this->stubsPath.'/domain_dal.stub';
        $stubDalInterfacePath = $this->stubsPath.'/domain_dal_interface.stub';
        $domainDalFile = $path.'/'.$this->domain.'DAL.php';
        $domainDalInterfaceFile = $path.'/'.$this->domain.'DALInterface.php';
        $namespace = 'App\Domain\\'.$this->domain.'\DAL\\'.$this->domain;
        $class = $this->domain.'DAL';
        $interface = $this->domain.'DALInterface';

        $search = [
            '{{ namespace }}',
            '{{ class }}',
            '{{ interface }}',
            '{{ modelNamespace }}',
            '{{ modelClass }}',
            '{{ modelVar }}',
        ];

        $replace = [
            $namespace,
            $class,
            $interface,
            $modelInfo['namespace'],
            $modelInfo['class'],
            lcfirst($modelInfo['class']),
        ];

        $this->buildFile($domainDalFile, $stubDalPath, $search, $replace);
        $this->buildFile($domainDalInterfaceFile, $stubDalInterfacePath, $search, $replace);

        return [
            'namespace' => $namespace,
            'class' => $class,
            'interface' => $interface,
        ];
    }

    /**
     * @return string[]
     *
     * @throws FileNotFoundException
     */
    private function buildBLL($dalInfo)
    {
        $path = $this->domainPath.'/BLL/';

        if (! is_dir($path)) {
            mkdir($path);
        }

        $path .= '/'.$this->domain;

        if (! is_dir($path)) {
            mkdir($path);
        }

        $stubBllPath = $this->stubsPath.'/domain_bll.stub';
        $stubBllInterfacePath = $this->stubsPath.'/domain_bll_interface.stub';
        $domainBllFile = $path.'/'.$this->domain.'BLL.php';
        $domainBllInterfaceFile = $path.'/'.$this->domain.'BLLInterface.php';
        $namespace = 'App\Domain\\'.$this->domain.'\BLL\\'.$this->domain;
        $class = $this->domain.'BLL';
        $interface = $this->domain.'BLLInterface';

        $search = [
            '{{ namespace }}',
            '{{ class }}',
            '{{ interface }}',
            '{{ dalNamespace }}',
            '{{ dalInterface }}',
            '{{ dalVar }}',
        ];

        $replace = [
            $namespace,
            $class,
            $interface,
            $dalInfo['namespace'],
            $dalInfo['interface'],
            lcfirst($dalInfo['class']),
        ];

        $this->buildFile($domainBllFile, $stubBllPath, $search, $replace);
        $this->buildFile($domainBllInterfaceFile, $stubBllInterfacePath, $search, $replace);

        return [
            'namespace' => $namespace,
            'class' => $class,
            'interface' => $interface,
        ];
    }

    /**
     * @return string[]
     *
     * @throws FileNotFoundException
     */
    private function buildRequests()
    {
        $path = $this->domainPath.'/Requests';

        if (! is_dir($path)) {
            mkdir($path);
        }

        $stubPolicyPath = $this->stubsPath.'/domain_request.stub';
        $domainPolicyFile = $path.'/'.$this->domain.'Request.php';
        $namespace = 'App\Domain\\'.$this->domain.'\Requests';
        $class = $this->domain.'Request';

        $search = [
            '{{ namespace }}',
            '{{ class }}',
        ];

        $replace = [
            $namespace,
            $class,
        ];

        $this->buildFile($domainPolicyFile, $stubPolicyPath, $search, $replace);

        return [
            'namespace' => $namespace,
            'class' => $class,
        ];
    }

    /**
     * @return string[]
     *
     * @throws FileNotFoundException
     */
    private function buildControllers($bllInfo, $modelInfo, $requestInfo)
    {
        $path = $this->domainPath.'/Controllers';

        if (! is_dir($path)) {
            mkdir($path);
        }

        $stubPath = $this->stubsPath.'/domain_controller.stub';
        $domainControllerFile = $path.'/'.$this->domain.'Controller.php';
        $namespace = 'App\Domain\\'.$this->domain.'\Controllers';
        $class = $this->domain.'Controller';

        $search = [
            '{{ namespace }}',
            '{{ class }}',
            '{{ bllNamespace }}',
            '{{ bllInterface }}',
            '{{ bllVar }}',
            '{{ modelNamespace }}',
            '{{ modelClass }}',
            '{{ modelVar }}',
            '{{ requestNamespace }}',
            '{{ requestClass }}',
        ];

        $replace = [
            $namespace,
            $class,
            $bllInfo['namespace'],
            $bllInfo['interface'],
            lcfirst($bllInfo['class']),
            $modelInfo['namespace'],
            $modelInfo['class'],
            lcfirst($modelInfo['class']),
            $requestInfo['namespace'],
            $requestInfo['class'],
        ];

        $this->buildFile($domainControllerFile, $stubPath, $search, $replace);

        return [
            'namespace' => $namespace,
            'class' => $class,
        ];
    }

    /**
     * @return string[]
     *
     * @throws FileNotFoundException
     */
    private function buildPolicies()
    {
        $path = $this->domainPath.'/Policies';

        if (! is_dir($path)) {
            mkdir($path);
        }

        $stubPolicyPath = $this->stubsPath.'/domain_policy.stub';
        $domainPolicyFile = $path.'/'.$this->domain.'Policy.php';
        $namespace = 'App\Domain\\'.$this->domain.'\Policies';
        $class = $this->domain.'Policy';

        $search = [
            '{{ namespace }}',
            '{{ class }}',
            '{{ domain }}',
        ];

        $replace = [
            $namespace,
            $class,
            $this->domain,
        ];

        $this->buildFile($domainPolicyFile, $stubPolicyPath, $search, $replace);

        return [
            'namespace' => $namespace,
            'class' => $class,
        ];
    }

    /**
     * @throws FileNotFoundException
     */
    private function buildRoutes($controllerInfo)
    {
        $path = $this->domainPath.'/Routes';

        if (! is_dir($path)) {
            mkdir($path);
        }

        $stubRoutesWebPath = $this->stubsPath.'/domain_routes_web.stub';
        $domainRoutesWebFile = $path.'/'.'web.php';
        $stubRoutesApiPath = $this->stubsPath.'/domain_routes_api.stub';
        $domainRoutesApiFile = $path.'/'.'api.php';

        $search = [
            '{{ domainVar }}',
            '{{ controllerClass }}',
        ];

        $replace = [
            lcfirst($this->domain),
            $controllerInfo['class'],
        ];

        $this->buildFile($domainRoutesWebFile, $stubRoutesWebPath, $search, $replace);
        $this->buildFile($domainRoutesApiFile, $stubRoutesApiPath, $search, $replace);
    }

    /**
     * @return string[]
     *
     * @throws FileNotFoundException
     */
    private function buildProviders($bllInfo, $dalInfo, $controllerInfo, $policyInfo, $modelInfo)
    {
        $path = $this->domainPath.'/Providers';

        if (! is_dir($path)) {
            mkdir($path);
        }

        $stubProvidersPath = $this->stubsPath.'/domain_provider.stub';
        $domainProviderFile = $path.'/'.$this->domain.'Provider.php';
        $namespace = 'App\Domain\\'.$this->domain.'\Providers';
        $class = $this->domain.'Provider';

        $search = [
            '{{ domain }}',
            '{{ namespace }}',
            '{{ class }}',
            '{{ bllNamespace }}',
            '{{ bllClass }}',
            '{{ bllInterface }}',
            '{{ dalNamespace }}',
            '{{ dalClass }}',
            '{{ dalInterface }}',
            '{{ controllerNamespace }}',
            '{{ policyNamespace }}',
            '{{ policyClass }}',
            '{{ modelNamespace }}',
            '{{ modelClass }}',
        ];

        $replace = [
            $this->domain,
            $namespace,
            $class,
            $bllInfo['namespace'],
            $bllInfo['class'],
            $bllInfo['interface'],
            $dalInfo['namespace'],
            $dalInfo['class'],
            $dalInfo['interface'],
            $controllerInfo['namespace'],
            $policyInfo['namespace'],
            $policyInfo['class'],
            $modelInfo['namespace'],
            $modelInfo['class'],
        ];

        $this->buildFile($domainProviderFile, $stubProvidersPath, $search, $replace);

        return [
            'namespace' => $namespace,
            'class' => $class,
        ];
    }
}
