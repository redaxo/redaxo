<?php
namespace Psalm\Internal\Codebase;

use function array_pop;
use function assert;
use function count;
use function explode;
use PhpParser;
use function preg_replace;
use Psalm\Codebase;
use Psalm\CodeLocation;
use Psalm\Context;
use Psalm\Internal\Analyzer\TypeAnalyzer;
use Psalm\Internal\Provider\ClassLikeStorageProvider;
use Psalm\Internal\Provider\FileReferenceProvider;
use Psalm\Internal\Provider\MethodExistenceProvider;
use Psalm\Internal\Provider\MethodParamsProvider;
use Psalm\Internal\Provider\MethodReturnTypeProvider;
use Psalm\Internal\Provider\MethodVisibilityProvider;
use Psalm\StatementsSource;
use Psalm\Storage\ClassLikeStorage;
use Psalm\Storage\FunctionLikeParameter;
use Psalm\Storage\MethodStorage;
use Psalm\Type;
use function reset;
use function strtolower;

/**
 * @internal
 *
 * Handles information about class methods
 */
class Methods
{
    /**
     * @var ClassLikeStorageProvider
     */
    private $classlike_storage_provider;

    /**
     * @var bool
     */
    public $collect_locations = false;

    /**
     * @var FileReferenceProvider
     */
    public $file_reference_provider;

    /**
     * @var ClassLikes
     */
    private $classlikes;

    /** @var MethodReturnTypeProvider */
    public $return_type_provider;

    /** @var MethodParamsProvider */
    public $params_provider;

    /** @var MethodExistenceProvider */
    public $existence_provider;

    /** @var MethodVisibilityProvider */
    public $visibility_provider;

    /**
     * @param ClassLikeStorageProvider $storage_provider
     */
    public function __construct(
        ClassLikeStorageProvider $storage_provider,
        FileReferenceProvider $file_reference_provider,
        ClassLikes $classlikes
    ) {
        $this->classlike_storage_provider = $storage_provider;
        $this->file_reference_provider = $file_reference_provider;
        $this->classlikes = $classlikes;
        $this->return_type_provider = new MethodReturnTypeProvider();
        $this->existence_provider = new MethodExistenceProvider();
        $this->visibility_provider = new MethodVisibilityProvider();
        $this->params_provider = new MethodParamsProvider();
    }

    /**
     * Whether or not a given method exists
     *
     * @param  ?string      $calling_function_id
     * @param  CodeLocation|null $code_location
     *
     * @return bool
     */
    public function methodExists(
        \Psalm\Internal\MethodIdentifier $method_id,
        $calling_function_id = null,
        CodeLocation $code_location = null,
        StatementsSource $source = null,
        string $file_path = null
    ) {
        $fq_class_name = $method_id->fq_class_name;
        $method_name = $method_id->method_name;

        if ($this->existence_provider->has($fq_class_name)) {
            $method_exists = $this->existence_provider->doesMethodExist(
                $fq_class_name,
                $method_name,
                $source,
                $code_location
            );

            if ($method_exists !== null) {
                return $method_exists;
            }
        }

        if ($calling_function_id) {
            $calling_function_id = strtolower($calling_function_id);
        }

        $old_method_id = null;

        $fq_class_name = strtolower($this->classlikes->getUnAliasedName($fq_class_name));

        try {
            $class_storage = $this->classlike_storage_provider->get($fq_class_name);
        } catch (\InvalidArgumentException $e) {
            return false;
        }

        if (isset($class_storage->declaring_method_ids[$method_name])) {
            $declaring_method_id = $class_storage->declaring_method_ids[$method_name];

            if ((string) $calling_function_id === strtolower((string) $declaring_method_id)) {
                return true;
            }

            if ((string) $method_id !== (string) $declaring_method_id
                && $class_storage->user_defined
                && isset($class_storage->potential_declaring_method_ids[$method_name])
            ) {
                foreach ($class_storage->potential_declaring_method_ids[$method_name] as $potential_id => $_) {
                    if ($calling_function_id) {
                        $this->file_reference_provider->addMethodReferenceToClassMember(
                            $calling_function_id,
                            $potential_id
                        );
                    } elseif ($file_path) {
                        $this->file_reference_provider->addFileReferenceToClassMember(
                            $file_path,
                            $potential_id
                        );
                    }
                }
            } else {
                if ($calling_function_id) {
                    $this->file_reference_provider->addMethodReferenceToClassMember(
                        $calling_function_id,
                        strtolower((string) $declaring_method_id)
                    );
                } elseif ($file_path) {
                    $this->file_reference_provider->addFileReferenceToClassMember(
                        $file_path,
                        strtolower((string) $declaring_method_id)
                    );
                }
            }

            if ($this->collect_locations && $code_location) {
                $this->file_reference_provider->addCallingLocationForClassMethod(
                    $code_location,
                    strtolower((string) $declaring_method_id)
                );
            }

            foreach ($class_storage->class_implements as $fq_interface_name) {
                $interface_method_id_lc = strtolower($fq_interface_name . '::' . $method_name);

                if ($this->collect_locations && $code_location) {
                    $this->file_reference_provider->addCallingLocationForClassMethod(
                        $code_location,
                        $interface_method_id_lc
                    );
                }

                if ($calling_function_id) {
                    $this->file_reference_provider->addMethodReferenceToClassMember(
                        $calling_function_id,
                        $interface_method_id_lc
                    );
                } elseif ($file_path) {
                    $this->file_reference_provider->addFileReferenceToClassMember(
                        $file_path,
                        $interface_method_id_lc
                    );
                }
            }

            $declaring_method_class = $declaring_method_id->fq_class_name;
            $declaring_method_name = $declaring_method_id->method_name;

            $declaring_class_storage = $this->classlike_storage_provider->get($declaring_method_class);

            if (isset($declaring_class_storage->overridden_method_ids[$declaring_method_name])) {
                $overridden_method_ids = $declaring_class_storage->overridden_method_ids[$declaring_method_name];

                foreach ($overridden_method_ids as $overridden_method_id) {
                    if ($this->collect_locations && $code_location) {
                        $this->file_reference_provider->addCallingLocationForClassMethod(
                            $code_location,
                            strtolower((string) $overridden_method_id)
                        );
                    }

                    if ($calling_function_id) {
                        // also store failures in case the method is added later
                        $this->file_reference_provider->addMethodReferenceToClassMember(
                            $calling_function_id,
                            strtolower((string) $overridden_method_id)
                        );
                    } elseif ($file_path) {
                        $this->file_reference_provider->addFileReferenceToClassMember(
                            $file_path,
                            strtolower((string) $overridden_method_id)
                        );
                    }
                }
            }

            return true;
        }

        if ($class_storage->abstract && isset($class_storage->overridden_method_ids[$method_name])) {
            return true;
        }

        // support checking oldstyle constructors
        if ($method_name === '__construct') {
            $method_name_parts = explode('\\', $fq_class_name);
            $old_constructor_name = array_pop($method_name_parts);
            $old_method_id = $fq_class_name . '::' . $old_constructor_name;
        }

        if (!$class_storage->user_defined
            && (CallMap::inCallMap((string) $method_id) || ($old_method_id && CallMap::inCallMap($old_method_id)))
        ) {
            return true;
        }

        foreach ($class_storage->parent_classes + $class_storage->used_traits as $potential_future_declaring_fqcln) {
            $potential_id = strtolower($potential_future_declaring_fqcln) . '::' . $method_name;

            if ($calling_function_id) {
                // also store failures in case the method is added later
                $this->file_reference_provider->addMethodReferenceToMissingClassMember(
                    $calling_function_id,
                    $potential_id
                );
            } elseif ($file_path) {
                $this->file_reference_provider->addFileReferenceToMissingClassMember(
                    $file_path,
                    $potential_id
                );
            }
        }

        if ($calling_function_id) {
            // also store failures in case the method is added later
            $this->file_reference_provider->addMethodReferenceToMissingClassMember(
                $calling_function_id,
                strtolower((string) $method_id)
            );
        } elseif ($file_path) {
            $this->file_reference_provider->addFileReferenceToMissingClassMember(
                $file_path,
                strtolower((string) $method_id)
            );
        }

        return false;
    }

    /**
     * @param  array<int, PhpParser\Node\Arg> $args
     *
     * @return array<int, FunctionLikeParameter>
     */
    public function getMethodParams(
        \Psalm\Internal\MethodIdentifier $method_id,
        StatementsSource $source = null,
        array $args = null,
        Context $context = null
    ) : array {
        $fq_class_name = $method_id->fq_class_name;
        $method_name = $method_id->method_name;

        if ($this->params_provider->has($fq_class_name)) {
            $method_params = $this->params_provider->getMethodParams(
                $fq_class_name,
                $method_name,
                $args,
                $source,
                $context
            );

            if ($method_params !== null) {
                return $method_params;
            }
        }

        $declaring_method_id = $this->getDeclaringMethodId($method_id);

        $callmap_id = $declaring_method_id ?: $method_id;

        // functions
        if (CallMap::inCallMap((string) $callmap_id)) {
            $class_storage = $this->classlike_storage_provider->get($callmap_id->fq_class_name);

            if (!$class_storage->stubbed) {
                $function_callables = CallMap::getCallablesFromCallMap((string) $callmap_id);

                if ($function_callables === null) {
                    throw new \UnexpectedValueException(
                        'Not expecting $function_callables to be null for ' . $callmap_id
                    );
                }

                if (!$source || $args === null || count($function_callables) === 1) {
                    assert($function_callables[0]->params !== null);

                    return $function_callables[0]->params;
                }

                if ($context && $source instanceof \Psalm\Internal\Analyzer\StatementsAnalyzer) {
                    $was_inside_call = $context->inside_call;

                    $context->inside_call = true;

                    foreach ($args as $arg) {
                        \Psalm\Internal\Analyzer\Statements\ExpressionAnalyzer::analyze(
                            $source,
                            $arg->value,
                            $context
                        );
                    }

                    if (!$was_inside_call) {
                        $context->inside_call = false;
                    }
                }

                $matching_callable = CallMap::getMatchingCallableFromCallMapOptions(
                    $source->getCodebase(),
                    $function_callables,
                    $args,
                    $source->getNodeTypeProvider()
                );

                assert($matching_callable->params !== null);

                return $matching_callable->params;
            }
        }

        if ($declaring_method_id) {
            $storage = $this->getStorage($declaring_method_id);

            $params = $storage->params;

            if ($storage->has_docblock_param_types) {
                return $params;
            }

            $appearing_method_id = $this->getAppearingMethodId($declaring_method_id);

            if (!$appearing_method_id) {
                return $params;
            }

            $appearing_fq_class_name = $appearing_method_id->fq_class_name;
            $appearing_method_name = $appearing_method_id->method_name;

            $class_storage = $this->classlike_storage_provider->get($appearing_fq_class_name);

            if (!isset($class_storage->overridden_method_ids[$appearing_method_name])) {
                return $params;
            }

            if (!isset($class_storage->documenting_method_ids[$appearing_method_name])) {
                return $params;
            }

            $overridden_method_id = $class_storage->documenting_method_ids[$appearing_method_name];

            $overridden_storage = $this->getStorage($overridden_method_id);

            $overriding_fq_class_name = $overridden_method_id->fq_class_name;

            foreach ($params as $i => $param) {
                if (isset($overridden_storage->params[$i]->type)
                    && $overridden_storage->params[$i]->has_docblock_type
                    && $overridden_storage->params[$i]->name === $param->name
                ) {
                    $params[$i] = clone $param;
                    /** @var Type\Union $params[$i]->type */
                    $params[$i]->type = clone $overridden_storage->params[$i]->type;

                    if ($source) {
                        $overridden_class_storage = $this->classlike_storage_provider->get($overriding_fq_class_name);
                        $params[$i]->type = self::localizeType(
                            $source->getCodebase(),
                            $params[$i]->type,
                            $appearing_fq_class_name,
                            $overridden_class_storage->name
                        );
                    }

                    if ($params[$i]->signature_type
                        && $params[$i]->signature_type->isNullable()
                    ) {
                        $params[$i]->type->addType(new Type\Atomic\TNull);
                    }

                    $params[$i]->type_location = $overridden_storage->params[$i]->type_location;
                }
            }

            return $params;
        }

        throw new \UnexpectedValueException('Cannot get method params for ' . $method_id);
    }

    public static function localizeType(
        Codebase $codebase,
        Type\Union $type,
        string $appearing_fq_class_name,
        string $base_fq_class_name
    ) : Type\Union {
        $class_storage = $codebase->classlike_storage_provider->get($appearing_fq_class_name);
        $extends = $class_storage->template_type_extends;

        if (!$extends) {
            return $type;
        }

        $type = clone $type;

        foreach ($type->getAtomicTypes() as $key => $atomic_type) {
            if ($atomic_type instanceof Type\Atomic\TTemplateParam) {
                if ($atomic_type->defining_class === $base_fq_class_name) {
                    if (isset($extends[$base_fq_class_name][$atomic_type->param_name])) {
                        $extended_param = $extends[$base_fq_class_name][$atomic_type->param_name];

                        $type->removeType($key);
                        $type = Type::combineUnionTypes(
                            $type,
                            $extended_param,
                            $codebase
                        );
                    }
                }
            }

            if ($atomic_type instanceof Type\Atomic\TTemplateParamClass) {
                if ($atomic_type->defining_class === $base_fq_class_name) {
                    if (isset($extends[$base_fq_class_name][$atomic_type->param_name])) {
                        $extended_param = $extends[$base_fq_class_name][$atomic_type->param_name];

                        $types = \array_values($extended_param->getAtomicTypes());

                        if (count($types) === 1 && $types[0] instanceof Type\Atomic\TNamedObject) {
                            $atomic_type->as_type = $types[0];
                        } else {
                            $atomic_type->as_type = null;
                        }
                    }
                }
            }

            if ($atomic_type instanceof Type\Atomic\TArray
                || $atomic_type instanceof Type\Atomic\TIterable
                || $atomic_type instanceof Type\Atomic\TGenericObject
            ) {
                foreach ($atomic_type->type_params as &$type_param) {
                    $type_param = self::localizeType(
                        $codebase,
                        $type_param,
                        $appearing_fq_class_name,
                        $base_fq_class_name
                    );
                }
            }

            if ($atomic_type instanceof Type\Atomic\TCallable
                || $atomic_type instanceof Type\Atomic\TFn
            ) {
                if ($atomic_type->params) {
                    foreach ($atomic_type->params as $param) {
                        if ($param->type) {
                            $param->type = self::localizeType(
                                $codebase,
                                $param->type,
                                $appearing_fq_class_name,
                                $base_fq_class_name
                            );
                        }
                    }
                }

                if ($atomic_type->return_type) {
                    $atomic_type->return_type = self::localizeType(
                        $codebase,
                        $atomic_type->return_type,
                        $appearing_fq_class_name,
                        $base_fq_class_name
                    );
                }
            }
        }

        return $type;
    }

    /**
     * @return bool
     */
    public function isVariadic(\Psalm\Internal\MethodIdentifier $method_id)
    {
        $declaring_method_id = $this->getDeclaringMethodId($method_id);

        if (!$declaring_method_id) {
            return false;
        }

        return $this->getStorage($declaring_method_id)->variadic;
    }

    /**
     * @param  string $self_class
     * @param  array<int, PhpParser\Node\Arg>|null $args
     *
     * @return Type\Union|null
     */
    public function getMethodReturnType(
        \Psalm\Internal\MethodIdentifier $method_id,
        &$self_class,
        \Psalm\Internal\Analyzer\SourceAnalyzer $source_analyzer = null,
        array $args = null
    ) {
        $original_fq_class_name = $method_id->fq_class_name;
        $original_method_name = $method_id->method_name;

        $adjusted_fq_class_name = $this->classlikes->getUnAliasedName($original_fq_class_name);

        if ($adjusted_fq_class_name !== $original_fq_class_name) {
            $original_fq_class_name = strtolower($adjusted_fq_class_name);
        }

        $original_class_storage = $this->classlike_storage_provider->get($original_fq_class_name);

        if (isset($original_class_storage->pseudo_methods[$original_method_name])) {
            return $original_class_storage->pseudo_methods[$original_method_name]->return_type;
        }

        $declaring_method_id = $this->getDeclaringMethodId($method_id);

        if (!$declaring_method_id) {
            return null;
        }

        $appearing_method_id = $this->getAppearingMethodId($method_id);

        if (!$appearing_method_id) {
            $class_storage = $this->classlike_storage_provider->get($original_fq_class_name);

            if ($class_storage->abstract && isset($class_storage->overridden_method_ids[$original_method_name])) {
                $appearing_method_id = reset($class_storage->overridden_method_ids[$original_method_name]);
            } else {
                return null;
            }
        }

        $appearing_fq_class_name = $appearing_method_id->fq_class_name;
        $appearing_method_name = $appearing_method_id->method_name;

        $appearing_fq_class_storage = $this->classlike_storage_provider->get($appearing_fq_class_name);

        if (!$appearing_fq_class_storage->user_defined
            && !$appearing_fq_class_storage->stubbed
            && CallMap::inCallMap((string) $appearing_method_id)
        ) {
            if ((string) $appearing_method_id === 'Closure::fromcallable'
                && isset($args[0])
                && $source_analyzer
                && ($first_arg_type = $source_analyzer->getNodeTypeProvider()->getType($args[0]->value))
                && $first_arg_type->isSingle()
            ) {
                foreach ($first_arg_type->getAtomicTypes() as $atomic_type) {
                    if ($atomic_type instanceof Type\Atomic\TCallable
                        || $atomic_type instanceof Type\Atomic\TFn
                    ) {
                        $callable_type = clone $atomic_type;

                        return new Type\Union([new Type\Atomic\TFn(
                            'Closure',
                            $callable_type->params,
                            $callable_type->return_type
                        )]);
                    }

                    if ($atomic_type instanceof Type\Atomic\TNamedObject
                        && $this->methodExists(
                            new \Psalm\Internal\MethodIdentifier($atomic_type->value, '__invoke')
                        )
                    ) {
                        $invokable_storage = $this->getStorage(
                            new \Psalm\Internal\MethodIdentifier($atomic_type->value, '__invoke')
                        );

                        return new Type\Union([new Type\Atomic\TFn(
                            'Closure',
                            $invokable_storage->params,
                            $invokable_storage->return_type
                        )]);
                    }
                }
            }

            $callmap_callables = CallMap::getCallablesFromCallMap((string) $appearing_method_id);

            if (!$callmap_callables || $callmap_callables[0]->return_type === null) {
                throw new \UnexpectedValueException('Shouldn’t get here');
            }

            $return_type_candidate = $callmap_callables[0]->return_type;

            if ($return_type_candidate->isFalsable()) {
                $return_type_candidate->ignore_falsable_issues = true;
            }

            return $return_type_candidate;
        }

        $storage = $this->getStorage($declaring_method_id);

        if ($storage->return_type) {
            $self_class = $appearing_fq_class_storage->name;

            return clone $storage->return_type;
        }

        $class_storage = $this->classlike_storage_provider->get($appearing_fq_class_name);

        if (!isset($class_storage->overridden_method_ids[$appearing_method_name])) {
            return null;
        }

        $candidate_type = null;

        foreach ($class_storage->overridden_method_ids[$appearing_method_name] as $overridden_method_id) {
            $overridden_storage = $this->getStorage($overridden_method_id);

            if ($overridden_storage->return_type) {
                if ($overridden_storage->return_type->isNull()) {
                    if ($candidate_type && !$candidate_type->isVoid()) {
                        return null;
                    }

                    $candidate_type = Type::getVoid();
                    continue;
                }

                $fq_overridden_class = $overridden_method_id->fq_class_name;

                $overridden_class_storage =
                    $this->classlike_storage_provider->get($fq_overridden_class);

                $overridden_return_type = clone $overridden_storage->return_type;

                $self_class = $overridden_class_storage->name;

                if ($candidate_type
                    && $source_analyzer
                ) {
                    $old_contained_by_new = TypeAnalyzer::isContainedBy(
                        $source_analyzer->getCodebase(),
                        $candidate_type,
                        $overridden_return_type
                    );

                    $new_contained_by_old = TypeAnalyzer::isContainedBy(
                        $source_analyzer->getCodebase(),
                        $overridden_return_type,
                        $candidate_type
                    );

                    if (!$old_contained_by_new && !$new_contained_by_old) {
                        $attempted_intersection = Type::intersectUnionTypes(
                            $candidate_type,
                            $overridden_return_type
                        );

                        if ($attempted_intersection) {
                            $candidate_type = $attempted_intersection;
                            continue;
                        }

                        return null;
                    }

                    if ($old_contained_by_new) {
                        continue;
                    }
                }

                $candidate_type = $overridden_return_type;
            }
        }

        return $candidate_type;
    }

    /**
     * @return bool
     */
    public function getMethodReturnsByRef(\Psalm\Internal\MethodIdentifier $method_id)
    {
        $method_id = $this->getDeclaringMethodId($method_id);

        if (!$method_id) {
            return false;
        }

        $fq_class_storage = $this->classlike_storage_provider->get($method_id->fq_class_name);

        if (!$fq_class_storage->user_defined && CallMap::inCallMap((string) $method_id)) {
            return false;
        }

        $storage = $this->getStorage($method_id);

        return $storage->returns_by_ref;
    }

    /**
     * @param  CodeLocation|null    $defined_location
     *
     * @return CodeLocation|null
     */
    public function getMethodReturnTypeLocation(
        \Psalm\Internal\MethodIdentifier $method_id,
        CodeLocation &$defined_location = null
    ) {
        $method_id = $this->getDeclaringMethodId($method_id);

        if ($method_id === null) {
            return null;
        }

        $storage = $this->getStorage($method_id);

        if (!$storage->return_type_location) {
            $overridden_method_ids = $this->getOverriddenMethodIds($method_id);

            foreach ($overridden_method_ids as $overridden_method_id) {
                $overridden_storage = $this->getStorage($overridden_method_id);

                if ($overridden_storage->return_type_location) {
                    $defined_location = $overridden_storage->return_type_location;
                    break;
                }
            }
        }

        return $storage->return_type_location;
    }

    /**
     * @param string $fq_class_name
     * @param lowercase-string $method_name_lc
     * @param string $declaring_fq_class_name
     * @param lowercase-string $declaring_method_name_lc
     *
     * @return void
     */
    public function setDeclaringMethodId(
        $fq_class_name,
        $method_name_lc,
        $declaring_fq_class_name,
        $declaring_method_name_lc
    ) {
        $class_storage = $this->classlike_storage_provider->get($fq_class_name);

        $class_storage->declaring_method_ids[$method_name_lc] = new \Psalm\Internal\MethodIdentifier(
            $declaring_fq_class_name,
            $declaring_method_name_lc
        );
    }

    /**
     * @param string $fq_class_name
     * @param lowercase-string $method_name_lc
     * @param string $appearing_fq_class_name
     * @param lowercase-string $appearing_method_name_lc
     *
     * @return void
     */
    public function setAppearingMethodId(
        $fq_class_name,
        $method_name_lc,
        $appearing_fq_class_name,
        $appearing_method_name_lc
    ) {
        $class_storage = $this->classlike_storage_provider->get($fq_class_name);

        $class_storage->appearing_method_ids[$method_name_lc] = new \Psalm\Internal\MethodIdentifier(
            $appearing_fq_class_name,
            $appearing_method_name_lc
        );
    }

    public function getDeclaringMethodId(
        \Psalm\Internal\MethodIdentifier $method_id
    ) : ?\Psalm\Internal\MethodIdentifier {
        $fq_class_name = $this->classlikes->getUnAliasedName($method_id->fq_class_name);

        $class_storage = $this->classlike_storage_provider->get($fq_class_name);

        $method_name = $method_id->method_name;

        if (isset($class_storage->declaring_method_ids[$method_name])) {
            return $class_storage->declaring_method_ids[$method_name];
        }

        if ($class_storage->abstract && isset($class_storage->overridden_method_ids[$method_name])) {
            return reset($class_storage->overridden_method_ids[$method_name]);
        }

        return null;
    }

    /**
     * Get the class this method appears in (vs is declared in, which could give a trait
     */
    public function getAppearingMethodId(
        \Psalm\Internal\MethodIdentifier $method_id
    ) : ?\Psalm\Internal\MethodIdentifier {
        $fq_class_name = $this->classlikes->getUnAliasedName($method_id->fq_class_name);

        $class_storage = $this->classlike_storage_provider->get($fq_class_name);

        $method_name = $method_id->method_name;

        if (isset($class_storage->appearing_method_ids[$method_name])) {
            return $class_storage->appearing_method_ids[$method_name];
        }

        return null;
    }

    /**
     * @return array<\Psalm\Internal\MethodIdentifier>
     */
    public function getOverriddenMethodIds(\Psalm\Internal\MethodIdentifier $method_id)
    {
        $class_storage = $this->classlike_storage_provider->get($method_id->fq_class_name);
        $method_name = $method_id->method_name;

        if (isset($class_storage->overridden_method_ids[$method_name])) {
            return $class_storage->overridden_method_ids[$method_name];
        }

        return [];
    }

    /**
     * @return string
     */
    public function getCasedMethodId(\Psalm\Internal\MethodIdentifier $original_method_id)
    {
        $method_id = $this->getDeclaringMethodId($original_method_id);

        if ($method_id === null) {
            return $original_method_id;
        }

        $fq_class_name = $method_id->fq_class_name;
        $new_method_name = $method_id->method_name;

        $old_fq_class_name = $original_method_id->fq_class_name;
        $old_method_name = $original_method_id->method_name;

        $storage = $this->getStorage($method_id);

        if ($old_method_name === $new_method_name
            && strtolower($old_fq_class_name) !== $old_fq_class_name
        ) {
            return $old_fq_class_name . '::' . $storage->cased_name;
        }

        return $fq_class_name . '::' . $storage->cased_name;
    }

    /**
     * @return ?MethodStorage
     */
    public function getUserMethodStorage(\Psalm\Internal\MethodIdentifier $method_id)
    {
        $declaring_method_id = $this->getDeclaringMethodId($method_id);

        if (!$declaring_method_id) {
            throw new \UnexpectedValueException('$storage should not be null for ' . $method_id);
        }

        $storage = $this->getStorage($declaring_method_id);

        if (!$storage->location) {
            return null;
        }

        return $storage;
    }

    /**
     * @return ClassLikeStorage
     */
    public function getClassLikeStorageForMethod(\Psalm\Internal\MethodIdentifier $method_id)
    {
        $fq_class_name = $method_id->fq_class_name;
        $method_name = $method_id->method_name;

        if ($this->existence_provider->has($fq_class_name)) {
            if ($this->existence_provider->doesMethodExist(
                $fq_class_name,
                $method_name,
                null,
                null
            )) {
                return $this->classlike_storage_provider->get($fq_class_name);
            }
        }

        $declaring_method_id = $this->getDeclaringMethodId($method_id);

        if ($declaring_method_id === null) {
            if (CallMap::inCallMap((string) $method_id)) {
                $declaring_method_id = $method_id;
            } else {
                throw new \UnexpectedValueException('$storage should not be null for ' . $method_id);
            }
        }

        $declaring_fq_class_name = $declaring_method_id->fq_class_name;

        return $this->classlike_storage_provider->get($declaring_fq_class_name);
    }

    /**
     * @return MethodStorage
     */
    public function getStorage(\Psalm\Internal\MethodIdentifier $method_id)
    {
        try {
            $class_storage = $this->classlike_storage_provider->get($method_id->fq_class_name);
        } catch (\InvalidArgumentException $e) {
            throw new \UnexpectedValueException($e->getMessage());
        }

        $method_name = $method_id->method_name;

        if (!isset($class_storage->methods[$method_name])) {
            throw new \UnexpectedValueException(
                '$storage should not be null for ' . $method_id
            );
        }

        return $class_storage->methods[$method_name];
    }
}
