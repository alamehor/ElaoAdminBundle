<?php

namespace Elao\Bundle\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Elao\Bundle\AdminBundle\DependencyInjection\Model\Administration;
use Elao\Bundle\AdminBundle\DependencyInjection\Model\ActionType;

/**
 * Adminsitration compile pass
 */
class AdministrationCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $administrations  = $container->getParameter('elao_admin.parameters.administrations');
        $loaderDefinition = $container->getDefinition('elao_admin.routing_loader');
        $actionTypes      = $this->getActionTypes($container);

        foreach ($administrations as $name => $options) {

            $administration    = (new Administration($name, $options))->processActions($actionTypes);
            $managerDefinition = new DefinitionDecorator($administration->getManager());

            $managerDefinition->replaceArgument(1, $administration->getModel());

            $container->setDefinition($administration->getManagerId(), $managerDefinition);

            $actions = $administration->getActions();

            foreach ($actions as $alias => $action) {

                $actionDefinition = new DefinitionDecorator($action->getParentServiceId());

                $actionDefinition->addMethodCall('setModelManager', [new Reference($administration->getManagerId())]);
                $actionDefinition->addMethodCall('setParameters', [$action->getParameters()]);
                $loaderDefinition->addMethodCall('addRoute', $action->getRoute());

                $container->setDefinition($action->getServiceId(), $actionDefinition);
            }
        }
    }

    /**
     * Load action types
     *
     * @param ContainerBuilder $container
     *
     * @return array
     */
    protected function getActionTypes(ContainerBuilder $container)
    {
        $services = $container->findTaggedServiceIds('elao_admin.action');
        $actions  = [];

        foreach ($services as $id => $tags) {
            foreach ($tags as $attributes) {
                $alias = $attributes['alias'];

                if (preg_match('#^%.+%$#i', $attributes['configuration'])) {
                    if (!$container->hasParameter(trim($attributes['configuration'], '%'))) {
                        throw new InvalidConfigurationException(sprintf(
                            'Invalid configuration "%s" for service action "%s" : this parameter does not exist.',
                            $attributes['configuration'],
                            $alias
                        ));

                    }
                    $configuration = $container->getParameter(trim($attributes['configuration'], '%'));
                } else {
                    if (!class_exists($attributes['configuration'])) {
                        throw new InvalidConfigurationException(sprintf(
                            'Invalid configuration "%s" for service action "%s" : this class does not exist.',
                            $attributes['configuration'],
                            $alias
                        ));

                    }
                    $configuration = $attributes['configuration'];
                }

                $actions[$alias] = new ActionType($id, $alias, $configuration);
            }
        }

        return $actions;
    }
}
