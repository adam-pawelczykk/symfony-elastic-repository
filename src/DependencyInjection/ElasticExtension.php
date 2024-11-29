<?php
/** @author Adam Pawełczyk */

namespace ATPawelczyk\Elastic\DependencyInjection;

use ATPawelczyk\Elastic\CommandHandler\SyncDocumentHandler;
use ATPawelczyk\Elastic\IndexManager;
use ATPawelczyk\Elastic\Request;
use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * Class ElasticExtension
 * @package ATPawelczyk\Elastic\DependencyInjection
 */
class ElasticExtension extends ConfigurableExtension
{
    public const ALIAS = 'gd_elastic';

    public function getAlias(): string
    {
        return static::ALIAS;
    }

    /**
     * Configures the passed container according to the merged configuration.
     * @param array $mergedConfig
     * @param ContainerBuilder $container
     * @throws Exception
     */
    public function loadInternal(array $mergedConfig, ContainerBuilder $container): void
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yaml');

        $definitionManager = $container->register('gd_elastic.index_manager', IndexManager::class);
        $definitionManager->setArgument(0, new Reference($mergedConfig['client']));
        $definitionManager->setArgument(1, $mergedConfig['prefix']);
        $definitionManager->setArgument(2, $mergedConfig['indexes']);

        $definitionRequest = $container->register('gd_elastic.request', Request::class);
        $definitionRequest->setArgument(0, new Reference('request_stack'));

        if (isset($mergedConfig['bus'])) {
            $definitionHandler = $container->register('gd_elastic.sync_document_handler', SyncDocumentHandler::class);
            $definitionHandler->setArgument(0, new Reference($mergedConfig['client']));
            $definitionHandler->addTag('messenger.message_handler');

            $definitionManager->setArgument(3, new Reference($mergedConfig['bus']));
        }

        $container->setAlias(IndexManager::class, 'gd_elastic.index_manager');
        $container->setAlias(Request::class, 'gd_elastic.request');
    }
}
