<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MailPoetVendor\Symfony\Component\DependencyInjection\Compiler;

use MailPoetVendor\Symfony\Component\DependencyInjection\ContainerBuilder;
use MailPoetVendor\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use MailPoetVendor\Symfony\Component\DependencyInjection\Reference;
/**
 * Replaces aliases with actual service definitions, effectively removing these
 * aliases.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class ReplaceAliasByActualDefinitionPass extends \MailPoetVendor\Symfony\Component\DependencyInjection\Compiler\AbstractRecursivePass
{
    private $replacements;
    /**
     * Process the Container to replace aliases with service definitions.
     *
     * @throws InvalidArgumentException if the service definition does not exist
     */
    public function process(\MailPoetVendor\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        // First collect all alias targets that need to be replaced
        $seenAliasTargets = [];
        $replacements = [];
        foreach ($container->getAliases() as $definitionId => $target) {
            $targetId = $container->normalizeId($target);
            // Special case: leave this target alone
            if ('service_container' === $targetId) {
                continue;
            }
            // Check if target needs to be replaces
            if (isset($replacements[$targetId])) {
                $container->setAlias($definitionId, $replacements[$targetId])->setPublic($target->isPublic())->setPrivate($target->isPrivate());
            }
            // No need to process the same target twice
            if (isset($seenAliasTargets[$targetId])) {
                continue;
            }
            // Process new target
            $seenAliasTargets[$targetId] = \true;
            try {
                $definition = $container->getDefinition($targetId);
            } catch (\MailPoetVendor\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException $e) {
                throw new \MailPoetVendor\Symfony\Component\DependencyInjection\Exception\InvalidArgumentException(\sprintf('Unable to replace alias "%s" with actual definition "%s".', $definitionId, $targetId), null, $e);
            }
            if ($definition->isPublic() || $definition->isPrivate()) {
                continue;
            }
            // Remove private definition and schedule for replacement
            $definition->setPublic(!$target->isPrivate());
            $definition->setPrivate($target->isPrivate());
            $container->setDefinition($definitionId, $definition);
            $container->removeDefinition($targetId);
            $replacements[$targetId] = $definitionId;
        }
        $this->replacements = $replacements;
        parent::process($container);
        $this->replacements = [];
    }
    /**
     * {@inheritdoc}
     */
    protected function processValue($value, $isRoot = \false)
    {
        if ($value instanceof \MailPoetVendor\Symfony\Component\DependencyInjection\Reference && isset($this->replacements[$referenceId = $this->container->normalizeId($value)])) {
            // Perform the replacement
            $newId = $this->replacements[$referenceId];
            $value = new \MailPoetVendor\Symfony\Component\DependencyInjection\Reference($newId, $value->getInvalidBehavior());
            $this->container->log($this, \sprintf('Changed reference of service "%s" previously pointing to "%s" to "%s".', $this->currentId, $referenceId, $newId));
        }
        return parent::processValue($value, $isRoot);
    }
}
