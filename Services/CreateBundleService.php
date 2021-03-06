<?php

namespace Prokl\BundleMakerBundle\Services;

/**
 * Class CreateBundleService
 * @package Prokl\BundleMakerBundle\Services
 */
class CreateBundleService
{
    public const RESOURCE_DIR = '/Resources/config';
    public const DEPENDENCY_DIR = '/DependencyInjection';

    /** @var string[]  */
    private $otherDirs = ['/Controller', '/Services', '/Tests'];

    /** @var string */
    private $resourcesDir = '/Resources/config';

    /** @var string */
    private $dependencyDir = '/DependencyInjection';

    /**
     * @var string $configFileDir Path to configs.
     */
    private $configFileDir;

    /**
     * @var string $configFileName Name of bundles config file.
     */
    private $configFileName;

    /**
     * @var string $nameBundleConfigFile Namespace of bundle.
     */
    private $bundleNamespace;

    /** @var integer */
    private $dirMode;

    /** @var string */
    private $workingDir;

    /** @var string */
    private $errMsg;

    /** @var string */
    private $bundleName;

    /** @var array */
    private $templateFiles;

    /**
     * @see https://php.net/mkdir for file modes
     *
     * @param string  $bundleName      Name of the new bundle (PascalCase).
     * @param string  $workingDir      The directory the bundle resides in.
     * @param string  $configFileDir   Path to config file.
     * @param string  $configFile      Name of config file.
     * @param string  $bundleNamespace Bundle namespace.
     * @param integer $dirMode         The file mode of the to be created directories.
     * @param array   $templateFiles   Array of template file paths.
     */
    public function __construct(
        string $bundleName,
        string $workingDir,
        string $configFileDir,
        string $configFile,
        string $bundleNamespace,
        int $dirMode = 0755,
        array $templateFiles = []
    ) {
        $this->bundleName = $bundleName;
        $this->workingDir = $workingDir;
        $this->dirMode = $dirMode;
        $this->templateFiles = $templateFiles;
        $this->configFileDir = $configFileDir;
        $this->configFileName = $configFile;
        $this->bundleNamespace = $bundleNamespace;
    }

    /**
     * @return bool
     */
    public function createBundleDirectories(): bool
    {
        if (true !== $this->createDir($this->workingDir)) {
            return false;
        }
        $resDir = $this->workingDir . self::RESOURCE_DIR;
        if (true !== $this->createDir($resDir)) {
            return false;
        }
        $this->resourcesDir = $resDir;
        $depDir = $this->workingDir . self::DEPENDENCY_DIR;
        if (true !== $this->createDir($depDir)) {
            return false;
        }
        $this->dependencyDir = $depDir;
        foreach ($this->otherDirs as $dir) {
            $oDir = $this->workingDir . $dir;
            if (true !== $this->createDir($oDir)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return boolean
     */
    public function copyResourceFiles(): bool
    {
        foreach ($this->templateFiles as $key => $template) {
            if ($key === 'services' || $key === 'routes') {
                $dest = $this->resourcesDir . "/$key.yaml";
                if (true !== copy($template, $dest)) {
                    $this->errMsg = 'Cannot copy ' . $key . '.yaml to  ' . $this->resourcesDir;
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Creating Bundle.php and Extension.php.
     *
     * @return void
     */
    public function createBundleClasses(): void
    {
        $datum = date('d.m.Y');
        $shortName = str_replace('Bundle', '', $this->bundleName);
        $bundleSmallShortName = strtolower($shortName);
        foreach ($this->templateFiles as $key => $template) {
            if ($key === 'bundle' || $key === 'extension') {
                $content = file_get_contents($template);
                $content = preg_replace('/\{#bundleName\}/', $this->bundleName, $content);
                $content = preg_replace('/\{#bundleShortName\}/', $shortName, $content);
                $content = preg_replace('/\{#bundleSmallShortName\}/', $bundleSmallShortName, $content);
                $content = preg_replace('/\{#datum\}/', $datum, $content);
                $fileName = ($key === 'bundle') ? $this->bundleName . '.php' : $shortName . 'Extension.php';
                $dest = ($key === 'bundle') ? $this->workingDir . "/$fileName" : $this->dependencyDir . "/$fileName";

                file_put_contents($dest, $content);
            }
        }
    }

    /**
     * ???????????? ?? bundles.php
     *
     * @return boolean
     */
    public function activateBundle(): bool
    {
        $className = $this->bundleNamespace . $this->bundleName . '\\' . $this->bundleName;
        $bundlePhp = getcwd() . $this->configFileDir . $this->configFileName;
        $backUp = getcwd() . $this->configFileDir . $this->configFileName . '.backup';

        /** ????????????????????, ?? ?????????????? ?????????? ???????????????????????? ??????????????. */
        $configDir = getcwd() . $this->configFileDir;

        if (!is_dir($configDir)) {
            $this->errMsg = 'Cannot find directory ' . $configDir;
            return false;
        }

        if (!is_writable($configDir)) {
            $this->errMsg = 'Cannot write in directory ' . $configDir;
            return false;
        }

        if (!is_file($bundlePhp)) {
            $this->errMsg = 'Cannot find file ' . $bundlePhp;
            return false;
        }

        if (!is_readable($bundlePhp)) {
            $this->errMsg = 'Cannot read file ' . $bundlePhp;
            return false;
        }

        if (!is_writable($bundlePhp)) {
            $this->errMsg = 'Cannot write in file ' . $bundlePhp;
            return false;
        }

        $contentArray = file($bundlePhp);
        if (!rename($bundlePhp, $backUp)) {
            $this->errMsg = 'Cannot create backup ' . $backUp;
            return false;
        }

        if (!touch($bundlePhp)) {
            $this->errMsg = 'Cannot create new file ' . $bundlePhp;
            return false;
        }

        $fp = fopen($bundlePhp, 'wb');
        foreach ($contentArray as $line) {
            if (preg_match('/(\];)/', $line)) {
                $newLine = "\t$className::class => ['all' => true],\n";
                fwrite($fp, $newLine);
            }
            fwrite($fp, $line);
        }

        fclose($fp);

        return true;
    }

    /**
     * ????????????.
     *
     * @return string
     */
    public function getErrMsg(): string
    {
        return $this->errMsg;
    }

    /**
     * Creating directories.
     *
     * @param string $dir
     *
     * @return boolean
     */
    private function createDir(string $dir): bool
    {
        if (is_dir($dir)) {
            $this->errMsg = 'Cannot create existing directory ' . $dir;
            return false;
        }
        if (!mkdir($dir, $this->dirMode, true) && !is_dir($dir)) {
            $this->errMsg = 'Cannot create directory ' . $dir;
            return false;
        }

        return true;
    }
}
