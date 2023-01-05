<?php
/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2021 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

/**
 * Store data parsed from classes with an static property named definition with almost an element 'multilang' => true.
 * In other words, ObjetModel definitions for multilang tables.
 */
class GlotioPhp55NodeVisitor extends GlotioPhpParser\NodeVisitorAbstract implements GlotioPhpNodeVisitor
{
    /**
     * @var GlotioParsedClassModel[]
     */
    private $foundClassModel = array();

    /**
     * @var string
     */
    private $lastClassname = null;

    public function enterNode(GlotioPhpParser\Node $node)
    {
        if ($node instanceof GlotioPhpParser\Node\Stmt\Class_ && $node->extends && preg_match(self::OBJECT_MODEL_REGEX, $node->extends->toString())) {
            $this->lastClassname = $node->name;
        }

        if ($this->lastClassname && $node instanceof GlotioPhpParser\Node\Stmt\ClassMethod && $node->name === 'getTranslationsFieldsChild') {
            $this->getFoundClass($this->lastClassname)->is_multilang_defined = true;
            return;
        }

        if ($this->lastClassname && $node instanceof GlotioPhpParser\Node\Stmt\ClassConst) {
            $this->getFoundClass($this->lastClassname)->addToFoundClassClassConstant($node);
            return;
        }

        if (!($node instanceof GlotioPhpParser\Node\Stmt\Property) || !$this->lastClassname) {
            return;
        }

        foreach ($node->props as $nodeProperty) {
            if ($node->isPublic() && !$node->isStatic()) {
                $this->getFoundClass($this->lastClassname)->addToFoundClassPublicProperty($nodeProperty);
            } elseif ($nodeProperty->name === 'definition' && $nodeProperty->default instanceof GlotioPhpParser\Node\Expr\Array_ && $nodeProperty->default->items) {
                $this->getFoundClass($this->lastClassname)->addToFoundClassDefinitionProperty($nodeProperty);

                foreach ($nodeProperty->default->items as $item) {
                    /** @var GlotioPhpParser\Node\Expr\ArrayItem $item */
                    if ($item->key->value === 'multilang') {
                        if (isset($item->value->name->parts[0]) && Tools::strtolower($item->value->name->parts[0]) === 'true') {
                            $this->getFoundClass($this->lastClassname)->is_multilang_defined = true;
                        } elseif (isset($item->value->value) && Tools::strtolower($item->value->value) === 'true') {
                            $this->getFoundClass($this->lastClassname)->is_multilang_defined = true;
                        }

                        break;
                    }
                }
            }
        }
    }

    public function leaveNode(GlotioPhpParser\Node $node)
    {
        if ($node instanceof GlotioPhpParser\Node\Stmt\Class_) {
            $this->lastClassname = null;
        }
    }

    /**
     * @return GlotioParsedClassModel[]
     */
    public function getFoundClassModels()
    {
        return $this->foundClassModel;
    }

    /**
     * @param string $classname
     * @return GlotioParsedClassModel
     */
    private function getFoundClass($classname)
    {
        if (!isset($this->foundClassModel[$classname])) {
            $this->foundClassModel[$classname] = new GlotioPhp55ParsedClassModel($classname);
        }

        return $this->foundClassModel[$classname];
    }
}
