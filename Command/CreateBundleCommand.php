<?php

namespace Prokl\BundleMakerBundle\Command;

use Exception;
use Prokl\BundleMakerBundle\Services\CreateBundleService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/** Creating skeleton for symfony bundle
 *
 * Class CreateBundleCommand
 * @package Prokl\BundleMakerBundle\Command
 */
class CreateBundleCommand extends Command
{
    /**
     * @var string $pathDirBundles Путь к директории, где лежат бандлы.
     */
    private $pathDirBundles;

    /**
     * @var string $defaultName Название команды.
     */
    protected static $defaultName = 'maker:make-bundle';

    /** @var array Templates to manipulate */
    private $templateFiles = [];

    /** @var string[] Template files in templateDir */
    private $templates = [
        'bundle' => 'Bundle.txt',
        'extension' => 'Extension.txt',
        'services' => 'services.yaml',
        'routes'  => 'routes.yaml'
    ];

    /**
     * @var string $pathStandaloneBundle
     */
    private $pathStandaloneBundle;

    /**
     * @var string $nameBundleConfigFile
     */
    private $nameBundleConfigFile;

    /**
     * @var string $nameBundleConfigFile Namespace of bundle.
     */
    private $bundleNamespace;

    /** @var array $configKeys */
    private $configKeys = ['template_dir', 'bundle_dir', 'config_file_dir', 'config_file', 'namespace'];

    /** @var array $config */
    private $config = [];

    /**
     * CreateBundleCommand constructor.
     *
     * @param array  $config         Configuration.
     *
     * @throws Exception
     */
    public function __construct(array $config = [])
    {
        parent::__construct();

        $this->setConfig($config);
        $this->pathDirBundles = $this->config['bundle_dir'];
        $this->pathStandaloneBundle = (string)$this->config['config_file_dir'];
        $this->nameBundleConfigFile = (string)$this->config['config_file'];
        $this->bundleNamespace = (string)$this->config['namespace'];

        $this->setTemplates();
    }


    /**
     * @inheritDoc
     */
    protected function configure() : void
    {
        $this
            ->setDescription('Creates symfony bundle under `src`')
            ->addArgument('bundleName', InputArgument::REQUIRED, 'name of bundle')
            ->addOption('init',
                'i',
                InputOption::VALUE_OPTIONAL,
                'Init bundle in standalone_bundles.php',
                'true'
            )
        ;
    }


    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return integer
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $bundleName = $input->getArgument('bundleName');
        $needInitialize = $input->getOption('init') === 'true';

        if (true !== $this->checkBundleName($bundleName)) {
            $io->error('Wrong name - pattern does not match ^([A-Z][a-z]*([A-Z][a-z]*)*Bundle)$');
            return 1;
        }

        $pathToWrite = getcwd() . $this->pathDirBundles;
        $workingDir = $pathToWrite . $bundleName;
        $srcPerms = fileperms($pathToWrite) & 0777;

        $io->note("Creating bundle `$bundleName` in " . $workingDir);

        $service = new CreateBundleService(
            $bundleName,
            $workingDir,
            $this->pathStandaloneBundle,
            $this->nameBundleConfigFile,
            $this->bundleNamespace,
            $srcPerms,
            $this->templateFiles
        );

        if ($service->createBundleDirectories() !== true) {
            $io->error($service->getErrMsg());
            return 1;
        }
        if ($service->copyResourceFiles() !== true) {
            $io->error($service->getErrMsg());
            return 1;
        }

        $service->createBundleClasses();

        if ($needInitialize) {
            $activateBundleResult = $service->activateBundle();

            if ($activateBundleResult !== true) {
                $io->newLine(2);
                $io->error($service->getErrMsg());
                $io->newLine(2);
                $errMsg = 'Bundle created with errors on activation in bundle.php!';
                $errMsg .= PHP_EOL;
                $errMsg .= 'Check Error and repair your system. You may have to ';
                $errMsg .= 'activate the bundle in bundles.php by yourself.';
                $io->caution($errMsg);
                return 1;
            }
        }

        $io->success('Bundle created');

        return 0;
    }

    /**
     * Setting config.
     *
     * @param array $config Конфигурация.
     *
     * @return void
     * @throws Exception
     */
    private function setConfig(array $config = []): void
    {
        foreach ($this->configKeys as $key) {
            if (!array_key_exists($key, $config)) {
                $message = 'Config '.$key.' missing! Do you defined it in config?';
                throw new Exception($message);
            }
            $value = $config[$key];
            if (empty($value)) {
                $message = 'Value missing for '.$key.'! Do you defined it in config?';
                throw new Exception($message);
            }

            $this->config[$key] = $value;
        }
    }

    /**
     * Checking templates.
     *
     * @return void
     * @throws Exception
     */
    private function setTemplates(): void
    {
        if ($this->config['template_dir'] === 'default') {
            $templateDir = __DIR__ . '/../installation/templates/';
        } else {
            $templateDir = $this->config['template_dir'];
        }

        $templateDir = (substr($templateDir, -1, 1) === '/') ? $templateDir : $templateDir . '/';
        if (!is_dir($templateDir) || !is_readable($templateDir)) {
            throw new Exception('Cannot access template directory ' . $templateDir);
        }

        foreach ($this->templates as $key => $fileName) {
            $template = $templateDir .  $fileName;
            if (!is_file($template) || !is_readable($template)) {
                throw new Exception('Cannot access template file ' . $template);
            }
            $this->templateFiles[$key] = $template;
        }
    }

    /**
     * @param string $bundleName Название бандла.
     *
     * @return boolean
     */
    private function checkBundleName(string $bundleName): bool
    {
        if (preg_match('/^([A-Z][a-z]*([A-Z][a-z]*)*Bundle)$/', $bundleName)) {
            return true;
        }
        return false;
    }
}
