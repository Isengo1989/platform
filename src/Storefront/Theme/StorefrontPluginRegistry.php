<?php declare(strict_types=1);

namespace Shopware\Storefront\Theme;

use Shopware\Core\Framework\App\ActiveAppsLoader;
use Shopware\Core\Framework\Bundle;
use Shopware\Core\Framework\Feature;
use Shopware\Core\System\Annotation\Concept\ExtensionPattern\Decoratable;
use Shopware\Storefront\Theme\StorefrontPluginConfiguration\AbstractStorefrontPluginConfigurationFactory;
use Shopware\Storefront\Theme\StorefrontPluginConfiguration\StorefrontPluginConfigurationCollection;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @Decoratable
 */
class StorefrontPluginRegistry implements StorefrontPluginRegistryInterface
{
    public const BASE_THEME_NAME = 'Storefront';

    /**
     * @var StorefrontPluginConfigurationCollection|null
     */
    private $pluginConfigurations;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var AbstractStorefrontPluginConfigurationFactory
     */
    private $pluginConfigurationFactory;

    /**
     * @var ActiveAppsLoader|null
     */
    private $activeAppsLoader;

    public function __construct(
        KernelInterface $kernel,
        AbstractStorefrontPluginConfigurationFactory $pluginConfigurationFactory,
        ?ActiveAppsLoader $activeAppsLoader
    ) {
        $this->kernel = $kernel;
        $this->pluginConfigurationFactory = $pluginConfigurationFactory;
        $this->activeAppsLoader = $activeAppsLoader;
    }

    public function getConfigurations(): StorefrontPluginConfigurationCollection
    {
        if ($this->pluginConfigurations) {
            return $this->pluginConfigurations;
        }

        $this->pluginConfigurations = new StorefrontPluginConfigurationCollection();

        $this->addPluginConfigs();
        // remove nullable prop and on-invalid=null behaviour in service declaration
        // when removing the feature flag
        if ($this->activeAppsLoader && Feature::isActive('FEATURE_NEXT_10286')) {
            $this->addAppConfigs();
        }

        return $this->pluginConfigurations;
    }

    private function addPluginConfigs(): void
    {
        foreach ($this->kernel->getBundles() as $bundle) {
            if (!$bundle instanceof Bundle) {
                continue;
            }

            $config = $this->pluginConfigurationFactory->createFromBundle($bundle);

            if (!$config->getIsTheme() && !$config->hasFilesToCompile()) {
                continue;
            }

            $this->pluginConfigurations->add($config);
        }
    }

    private function addAppConfigs(): void
    {
        foreach ($this->activeAppsLoader->getActiveApps() as $app) {
            $config = $this->pluginConfigurationFactory->createFromApp($app['name'], $app['path']);

            if (!$config->getIsTheme() && !$config->hasFilesToCompile()) {
                continue;
            }

            $this->pluginConfigurations->add($config);
        }
    }
}
