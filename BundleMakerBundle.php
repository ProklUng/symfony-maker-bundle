<?php

namespace Prokl\BundleMakerBundle;

use Prokl\BundleMakerBundle\DependencyInjection\BundleMakerBundleExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class BundleMakerBundle
 * @package Prokl\BundleMakerBundle
 */
class BundleMakerBundle extends Bundle
{
    /**
     * Overridden to allow for the custom extension alias.
     */
    public function getContainerExtension()
    {
        if (null === $this->extension) {
            $this->extension = new BundleMakerBundleExtension();
        }

        return $this->extension;
    }
}
