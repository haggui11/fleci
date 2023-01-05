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

require_once _PS_MODULE_DIR_ . 'glotio/vendor/included-vendors/nikic-parser-3/bootstrap.php';

/**
 * Implements PHP 5.5+ compatible PHP parser (nikic/php-parser library version 3.0, supports parsing from PHP 5.2 to PHP 7.1)
 */
class GlotioPhp55Parser implements GlotioPhpParser
{
    /**
     * @param string $phpFile
     * @return GlotioParsedClassModel[]
     */
    public function parsePhpFileToFindObjectModelClasses($phpFile)
    {
        $sourceCode = file_get_contents($phpFile);

        if (!preg_match(self::EXTENDS_OBJECT_MODEL_REGEX, $sourceCode) || !preg_match(self::HAS_OBJECT_MODEL_DEFINITION_REGEX, $sourceCode)) {
            return array();
        }

        $nodeVisitor = new GlotioPhp55NodeVisitor();

        try {
            $factory = new GlotioPhpParser\ParserFactory();
            $parser = $factory->create(GlotioPhpParser\ParserFactory::PREFER_PHP7);
            $ast    = $parser->parse($sourceCode);

            $traverser = new GlotioPhpParser\NodeTraverser;
            $traverser->addVisitor($nodeVisitor);
            $traverser->traverse($ast);

            return $nodeVisitor->getFoundClassModels();
        } catch (Exception $ex) {
            return array();
        }
    }

    /**
     * @param GlotioPhp55ParsedClassModel $parsedClassModel
     * @param string $module_name
     * @return GlotioModuleModel
     */
    public function getGlotioModuleModel($parsedClassModel, $module_name)
    {
        $tablename = $this->getTableNameFromDefinitionProperty($parsedClassModel->definition_property);
        if (!$tablename) {
            return null;
        }

        $moduleName = Tools::toCamelCase($module_name, true);
        $modelName  = Tools::toCamelCase($parsedClassModel->classname . $tablename, true);
        $classname  = "Glotio{$moduleName}{$modelName}Model";

        $factory = new GlotioPhpParser\BuilderFactory();
        $builder = $factory->class($classname)->extend('ObjectModel');

        // Found class constants
        foreach ($parsedClassModel->class_constants as $class_constant) {
            $builder->addStmt($class_constant);
        }

        // Found public properties
        foreach ($parsedClassModel->public_properties as $public_property) {
            $builder
                ->addStmt(
                    $factory
                        ->property($public_property->name)
                        ->makePublic()
                        ->setDefault($public_property->default)
                );
        }

        // Found definition and original class name
        $builder
            ->addStmt(
                $factory
                    ->property('definition')
                    ->makePublic()
                    ->makeStatic()
                    ->setDefault($parsedClassModel->definition_property->default)
            )
            ->addStmt(
                $factory
                    ->property('originalClassname')
                    ->makePublic()
                    ->makeStatic()
                    ->setDefault($parsedClassModel->classname)
            );

        $prettyPrinter   = new GlotioPhpParser\PrettyPrinter\Standard();
        $gen_source_code = $prettyPrinter->prettyPrint(array($builder->getNode()));

        return GlotioModuleModel::createWithArray(array(
            'module'      => $module_name,
            'classname'   => $classname,
            'table'       => $tablename,
            'source_code' => $gen_source_code,
        ));
    }

    /**
     * Recupera el nombre de la tabla del definition pasado.
     * @param GlotioPhpParser\Node\Stmt\PropertyProperty $definition_property
     * @return string|null
     */
    private function getTableNameFromDefinitionProperty($definition_property)
    {
        $tablename = null;

        if ($definition_property && isset($definition_property->default->items)) {
            foreach ($definition_property->default->items as $item) {
                /** @var GlotioPhpParser\Node\Expr\ArrayItem $item */
                if (isset($item->key) && $item->key->value === 'table') {
                    $tablename = $item->value->value;
                }
            }
        }

        return $tablename;
    }
}
