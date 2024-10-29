<?php

namespace DMT\AbMiddlewareBundle\Tests\Util\App;

use DMT\AbMiddlewareBundle\AbMiddlewareBundle;
use Exception;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    public function registerBundles(): array
    {
        return [
            new FrameworkBundle(),
            new AbMiddlewareBundle(),
        ];
    }

    /**
     * @throws Exception
     */
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(__DIR__ . '/config/config.yml');
    }

    public function getCacheDir(): string
    {
        return sys_get_temp_dir() . '/' . uniqid('dmt_ab_middleware', true);
    }

    public function getLogDir(): string
    {
        return sys_get_temp_dir() . '/' . uniqid('dmt_ab_middleware', true);
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function getRootDir(): string
    {
        return __DIR__;
    }
}
