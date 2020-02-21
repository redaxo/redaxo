<?php

namespace Psalm\Internal\Stubs\Generator;

use PhpParser;
use Psalm\Storage\MethodStorage;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Type;

class ClassLikeStubGenerator
{
    public static function getClassLikeNode(
        \Psalm\Codebase $codebase,
        ClassLikeStorage $storage,
        string $classlike_name
    ) : PhpParser\Node\Stmt\ClassLike {
        $subnodes = [
            'stmts' => array_merge(
                self::getConstantNodes($codebase, $storage),
                self::getPropertyNodes($storage),
                self::getMethodNodes($storage)
            )
        ];

        $docblock = ['description' => '', 'specials' => []];

        $template_offset = 0;

        foreach ($storage->template_types ?: [] as $template_name => $map) {
            $type = array_values($map)[0][0];

            $key = isset($storage->template_covariants[$template_offset]) ? 'template-covariant' : 'template';

            $docblock['specials'][$key][] = $template_name . ' as ' . $type->toNamespacedString(
                null,
                [],
                null,
                false
            );

            $template_offset++;
        }

        $attrs = [
            'comments' => $docblock['specials']
                ? [
                    new PhpParser\Comment\Doc(
                        \rtrim(\Psalm\DocComment::render($docblock, '        '))
                    )
                ]
                : []
        ];

        if ($storage->is_interface) {
            if ($storage->direct_interface_parents) {
                $subnodes['extends'] = [];

                foreach ($storage->direct_interface_parents as $direct_interface_parent) {
                    $subnodes['extends'][] = new PhpParser\Node\Name\FullyQualified($direct_interface_parent);
                }
            }

            return new PhpParser\Node\Stmt\Interface_(
                $classlike_name,
                $subnodes,
                $attrs
            );
        }

        if ($storage->is_trait) {
            return new PhpParser\Node\Stmt\Trait_(
                $classlike_name,
                $subnodes,
                $attrs
            );
        }

        if ($storage->parent_class) {
            $subnodes['extends'] = new PhpParser\Node\Name\FullyQualified($storage->parent_class);
        } else

        if ($storage->direct_class_interfaces) {
            $subnodes['implements'] = [];
            foreach ($storage->direct_class_interfaces as $direct_class_interface) {
                $subnodes['implements'][] = new PhpParser\Node\Name\FullyQualified($direct_class_interface);
            }
        }

        return new PhpParser\Node\Stmt\Class_(
            $classlike_name,
            $subnodes,
            $attrs
        );
    }

    /**
     * @return list<PhpParser\Node\Stmt\ClassConst>
     */
    private static function getConstantNodes(\Psalm\Codebase $codebase, ClassLikeStorage $storage) : array
    {
        $constant_nodes = [];

        foreach ($storage->public_class_constants as $constant_name => $_) {
            $resolved_type = $codebase->classlikes->getConstantForClass(
                $storage->name,
                $constant_name,
                \ReflectionProperty::IS_PUBLIC
            ) ?: Type::getMixed();

            $constant_nodes[] = new PhpParser\Node\Stmt\ClassConst(
                [
                    new PhpParser\Node\Const_(
                        $constant_name,
                        StubsGenerator::getExpressionFromType($resolved_type)
                    )
                ],
                PhpParser\Node\Stmt\Class_::MODIFIER_PUBLIC
            );
        }

        foreach ($storage->protected_class_constants as $constant_name => $_) {
            $resolved_type = $codebase->classlikes->getConstantForClass(
                $storage->name,
                $constant_name,
                \ReflectionProperty::IS_PROTECTED
            ) ?: Type::getMixed();

            $constant_nodes[] = new PhpParser\Node\Stmt\ClassConst(
                [
                    new PhpParser\Node\Const_(
                        $constant_name,
                        StubsGenerator::getExpressionFromType($resolved_type)
                    )
                ],
                PhpParser\Node\Stmt\Class_::MODIFIER_PROTECTED
            );
        }

        foreach ($storage->private_class_constants as $constant_name => $_) {
            $resolved_type = $codebase->classlikes->getConstantForClass(
                $storage->name,
                $constant_name,
                \ReflectionProperty::IS_PRIVATE
            ) ?: Type::getMixed();

            $constant_nodes[] = new PhpParser\Node\Stmt\ClassConst(
                [
                    new PhpParser\Node\Const_(
                        $constant_name,
                        StubsGenerator::getExpressionFromType($resolved_type)
                    )
                ],
                PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE
            );
        }

        return $constant_nodes;
    }

    /**
     * @return list<PhpParser\Node\Stmt\Property>
     */
    private static function getPropertyNodes(ClassLikeStorage $storage) : array
    {
        $namespace_name = implode('\\', array_slice(explode('\\', $storage->name), 0, -1));

        $property_nodes = [];

        foreach ($storage->properties as $property_name => $property_storage) {
            switch ($property_storage->visibility) {
                case \ReflectionProperty::IS_PRIVATE:
                    $flag = PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE;
                    break;
                case \ReflectionProperty::IS_PROTECTED:
                    $flag = PhpParser\Node\Stmt\Class_::MODIFIER_PROTECTED;
                    break;
                default:
                    $flag = PhpParser\Node\Stmt\Class_::MODIFIER_PUBLIC;
                    break;
            }

            $docblock = ['description' => '', 'specials' => []];

            if ($property_storage->type
                && $property_storage->signature_type !== $property_storage->type
            ) {
                $docblock['specials']['var'][] = $property_storage->type->toNamespacedString(
                    $namespace_name,
                    [],
                    null,
                    false
                );
            }

            $property_nodes[] = new PhpParser\Node\Stmt\Property(
                $flag | ($property_storage->is_static ? PhpParser\Node\Stmt\Class_::MODIFIER_STATIC : 0),
                [
                    new PhpParser\Node\Stmt\PropertyProperty(
                        $property_name,
                        $property_storage->suggested_type
                            ? StubsGenerator::getExpressionFromType($property_storage->suggested_type)
                            : null
                    )
                ],
                [
                    'comments' => $docblock['specials']
                        ? [
                            new PhpParser\Comment\Doc(
                                \rtrim(\Psalm\DocComment::render($docblock, '        '))
                            )
                        ]
                        : []
                ],
                $property_storage->signature_type
                    ? StubsGenerator::getParserTypeFromPsalmType($property_storage->signature_type)
                    : null
            );
        }

        return $property_nodes;
    }

    /**
     * @return list<PhpParser\Node\Stmt\ClassMethod>
     */
    private static function getMethodNodes(ClassLikeStorage $storage) {
        $namespace_name = implode('\\', array_slice(explode('\\', $storage->name), 0, -1));
        $method_nodes = [];

        foreach ($storage->methods as $method_storage) {
            if (!$method_storage->cased_name) {
                throw new \UnexpectedValueException('very bad');
            }

            switch ($method_storage->visibility) {
                case \ReflectionProperty::IS_PRIVATE:
                    $flag = PhpParser\Node\Stmt\Class_::MODIFIER_PRIVATE;
                    break;
                case \ReflectionProperty::IS_PROTECTED:
                    $flag = PhpParser\Node\Stmt\Class_::MODIFIER_PROTECTED;
                    break;
                default:
                    $flag = PhpParser\Node\Stmt\Class_::MODIFIER_PUBLIC;
                    break;
            }

            $docblock = ['description' => '', 'specials' => []];

            foreach ($method_storage->template_types ?: [] as $template_name => $map) {
                $type = array_values($map)[0][0];

                $docblock['specials']['template'][] = $template_name . ' as ' . $type->toNamespacedString(
                    $namespace_name,
                    [],
                    null,
                    false
                );
            }

            foreach ($method_storage->params as $param) {
                if ($param->type && $param->type !== $param->signature_type) {
                    $docblock['specials']['param'][] = $param->type->toNamespacedString(
                        $namespace_name,
                        [],
                        null,
                        false
                    ) . ' $' . $param->name;
                }
            }

            if ($method_storage->return_type
                && $method_storage->signature_return_type !== $method_storage->return_type
            ) {
                $docblock['specials']['return'][] = $method_storage->return_type->toNamespacedString(
                    $namespace_name,
                    [],
                    null,
                    false
                );
            }

            foreach ($method_storage->throws ?: [] as $exception_name => $_) {
                $docblock['specials']['throws'][] = Type::getStringFromFQCLN(
                    $exception_name,
                    $namespace_name,
                    [],
                    null,
                    false
                );
            }

            $method_nodes[] = new PhpParser\Node\Stmt\ClassMethod(
                $method_storage->cased_name,
                [
                    'flags' => $flag
                        | ($method_storage->is_static ? PhpParser\Node\Stmt\Class_::MODIFIER_STATIC : 0)
                        | ($method_storage->abstract ? PhpParser\Node\Stmt\Class_::MODIFIER_ABSTRACT : 0),
                    'params' => StubsGenerator::getFunctionParamNodes($method_storage),
                    'returnType' => $method_storage->signature_return_type
                        ? StubsGenerator::getParserTypeFromPsalmType($method_storage->signature_return_type)
                        : null,
                    'stmts' =>  $storage->is_interface || $method_storage->abstract ? null : [],
                ],
                [
                    'comments' => $docblock['specials']
                        ? [
                            new PhpParser\Comment\Doc(
                                \rtrim(\Psalm\DocComment::render($docblock, '        '))
                            )
                        ]
                        : []
                ]
            );
        }

        return $method_nodes;
    }
}
