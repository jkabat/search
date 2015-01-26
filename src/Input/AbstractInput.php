<?php

/*
 * This file is part of the RollerworksSearch Component package.
 *
 * (c) 2012-2014 Sebastiaan Stok <s.stok@rollerscapes.net>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Rollerworks\Component\Search\Input;

use Rollerworks\Component\Search\Exception\GroupsNestingException;
use Rollerworks\Component\Search\Exception\GroupsOverflowException;
use Rollerworks\Component\Search\Exception\UnknownFieldException;
use Rollerworks\Component\Search\Exception\UnsupportedValueTypeException;
use Rollerworks\Component\Search\FieldAliasResolverInterface;
use Rollerworks\Component\Search\FieldConfigInterface;
use Rollerworks\Component\Search\FieldSet;
use Rollerworks\Component\Search\InputProcessorInterface;
use Rollerworks\Component\Search\Value\Range;

/**
 * AbstractInput provides the shared logic for the InputProcessors.
 *
 * @author Sebastiaan Stok <s.stok@rollerscapes.net>
 */
abstract class AbstractInput implements InputProcessorInterface
{
    /**
     * @var FieldAliasResolverInterface|null
     */
    protected $aliasResolver;

    /**
     * @var ProcessorConfig
     */
    protected $config;

    /**
     * @param FieldAliasResolverInterface $aliasResolver
     */
    public function __construct(FieldAliasResolverInterface $aliasResolver)
    {
        $this->aliasResolver = $aliasResolver;
    }

    /**
     * Get 'real' fieldname.
     *
     * This will pass the Field through the alias resolver.
     *
     * @param string $name
     *
     * @return string
     *
     * @throws UnknownFieldException When there is no field found.
     * @throws \LogicException       When there is no FieldSet configured.
     */
    protected function getFieldName($name)
    {
        $fieldSet = $this->config->getFieldSet();
        $name = $this->aliasResolver->resolveFieldName($fieldSet, $name);

        if (!$fieldSet->has($name)) {
            throw new UnknownFieldException($name);
        }

        return $name;
    }

    /**
     * Checks if the maximum group nesting level is exceeded.
     *
     * @param int $groupIdx
     * @param int $nestingLevel
     *
     * @throws GroupsNestingException
     */
    protected function validateGroupNesting($groupIdx, $nestingLevel)
    {
        if ($nestingLevel > $this->config->getMaxNestingLevel()) {
            throw new GroupsNestingException(
                $this->config->getMaxNestingLevel(),
                $groupIdx,
                $nestingLevel
            );
        }
    }

    /**
     * Checks if the maximum group count is exceeded.
     *
     * @param int $groupIdx
     * @param int $count
     * @param int $nestingLevel
     *
     * @throws GroupsOverflowException
     */
    protected function validateGroupsCount($groupIdx, $count, $nestingLevel)
    {
        if ($count > $this->config->getMaxGroups()) {
            throw new GroupsOverflowException($this->config->getMaxGroups(), $count, $groupIdx, $nestingLevel);
        }
    }

    /**
     * Checks if the given field accepts the given value-type.
     *
     * @param FieldConfigInterface $fieldConfig
     * @param string               $type
     *
     * @throws UnsupportedValueTypeException
     */
    protected function assertAcceptsType(FieldConfigInterface $fieldConfig, $type)
    {
        switch ($type) {
            case 'range':
                if (!$fieldConfig->acceptRanges()) {
                    throw new UnsupportedValueTypeException($fieldConfig->getName(), $type);
                }
                break;

            case 'comparison':
                if (!$fieldConfig->acceptCompares()) {
                    throw new UnsupportedValueTypeException($fieldConfig->getName(), $type);
                }
                break;

            case 'pattern-match':
                if (!$fieldConfig->acceptPatternMatch()) {
                    throw new UnsupportedValueTypeException($fieldConfig->getName(), $type);
                }
                break;
        }
    }
}
