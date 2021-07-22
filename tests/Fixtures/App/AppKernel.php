<?php

use Symfony\Bundle\FrameworkBundle\Test\MailerAssertionsTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

/**
 * The kernel used in the application of most functional tests.
 */
class AppKernel extends Kernel
{
    private $withMongoOdm = false;

    public function withMongoOdm(bool $withMongoOdm)
    {
        $this->withMongoOdm = $withMongoOdm;

        return $this;
    }

    public function registerBundles()
    {
        $bundles = [
            new Symfony\Bundle\FrameworkBundle\FrameworkBundle(),
            new Symfony\Bundle\SecurityBundle\SecurityBundle(),
            new Symfony\Bundle\TwigBundle\TwigBundle(),
            new Doctrine\Bundle\DoctrineBundle\DoctrineBundle(),
            new Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle(),
            new EasyCorp\Bundle\EasyAdminBundle\EasyAdminBundle(),
        ];

        if ($this->withMongoOdm) {
            $bundles[] = new Doctrine\Bundle\MongoDBBundle\DoctrineMongoDBBundle();
            $bundles[] = new AlterPHP\EasyAdminMongoOdmBundle\EasyAdminMongoOdmBundle();
        }

        $bundles[] = new AlterPHP\EasyAdminExtensionBundle\EasyAdminExtensionBundle();
        $bundles[] = new AlterPHP\EasyAdminExtensionBundle\Tests\Fixtures\AppTestBundle\AppTestBundle();

        return $bundles;
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(__DIR__.'/config/config_'.$this->getEnvironment().'.yaml');
        $loader->load(function (ContainerBuilder $container) {
            $container->loadFromExtension('framework', [
                'assets' => null,
            ]);
        });

        if (\trait_exists(MailerAssertionsTrait::class)) {
            $loader->load(function (ContainerBuilder $container) {
                $container->loadFromExtension('twig', [
                    'exception_controller' => null,
                ]);
            });
        }
    }

    public function getProjectDir(): string
    {
        return __DIR__;
    }

    public function getCacheDir(): string
    {
        return __DIR__.'/../../../build/cache/'.$this->getEnvironment();
    }

    public function getLogDir(): string
    {
        return __DIR__.'/../../../build/kernel_logs/'.$this->getEnvironment();
    }
}
