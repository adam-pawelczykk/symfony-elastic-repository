<?php
/** @author Adam Pawełczyk */

namespace ATPawelczyk\Elastic;

use ATPawelczyk\Elastic\DependencyInjection\ElasticExtension;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class ElasticBundle
 * @package ATPawelczyk\Elastic
 */
class ElasticBundle extends Bundle
{
    public function getContainerExtension(): ExtensionInterface
    {
        if ($this->extension === null) {
            $this->extension = new ElasticExtension();
        }

        return $this->extension;
    }
}
