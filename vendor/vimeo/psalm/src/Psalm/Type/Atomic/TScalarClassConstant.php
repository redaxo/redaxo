<?php
namespace Psalm\Type\Atomic;

use Psalm\CodeLocation;
use Psalm\StatementsSource;

class TScalarClassConstant extends Scalar
{
    /** @var string */
    public $fq_classlike_name;

    /** @var string */
    public $const_name;

    /**
     * @param string $fq_classlike_name
     * @param string $const_name
     */
    public function __construct($fq_classlike_name, $const_name)
    {
        $this->fq_classlike_name = $fq_classlike_name;
        $this->const_name = $const_name;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return 'scalar-class-constant(' . $this->fq_classlike_name . '::' . $this->const_name . ')';
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->fq_classlike_name . '::' . $this->const_name;
    }

    /**
     * @return string
     */
    public function getId(bool $nested = false)
    {
        return $this->getKey();
    }

    /**
     * @param  string|null   $namespace
     * @param  array<string> $aliased_classes
     * @param  string|null   $this_class
     * @param  int           $php_major_version
     * @param  int           $php_minor_version
     *
     * @return string|null
     */
    public function toPhpString(
        $namespace,
        array $aliased_classes,
        $this_class,
        $php_major_version,
        $php_minor_version
    ) {
        return null;
    }

    public function canBeFullyExpressedInPhp()
    {
        return false;
    }

    /**
     * @param  string|null   $namespace
     * @param  array<string, string> $aliased_classes
     * @param  string|null   $this_class
     * @param  bool          $use_phpdoc_format
     *
     * @return string
     */
    public function toNamespacedString(
        ?string $namespace,
        array $aliased_classes,
        ?string $this_class,
        bool $use_phpdoc_format
    ) {
        if ($this->fq_classlike_name === 'static') {
            return 'static::' . $this->const_name;
        }

        return \Psalm\Type::getStringFromFQCLN($this->fq_classlike_name, $namespace, $aliased_classes, $this_class)
            . '::'
            . $this->const_name;
    }

    /**
     * @return string
     */
    public function getAssertionString()
    {
        return 'mixed';
    }

    /**
     * @param  StatementsSource $source
     * @param  CodeLocation     $code_location
     * @param  array<string>    $suppressed_issues
     * @param  array<string, bool> $phantom_classes
     * @param  bool             $inferred
     *
     * @return false|null
     */
    public function check(
        StatementsSource $source,
        CodeLocation $code_location,
        array $suppressed_issues,
        array $phantom_classes = [],
        bool $inferred = true,
        bool $prevent_template_covariance = false
    ) {
        if ($this->checked) {
            return;
        }

        $fq_classlike_name = $this->fq_classlike_name === 'self'
            ? $source->getClassName()
            : $this->fq_classlike_name;

        if (!$fq_classlike_name) {
            return;
        }

        if (\Psalm\Internal\Analyzer\ClassLikeAnalyzer::checkFullyQualifiedClassLikeName(
            $source,
            $fq_classlike_name,
            $code_location,
            null,
            $suppressed_issues,
            $inferred,
            false,
            true,
            $this->from_docblock
        ) === false
        ) {
            return false;
        }

        $class_constant_type = $source->getCodebase()->classlikes->getConstantForClass(
            $fq_classlike_name,
            $this->const_name,
            \ReflectionProperty::IS_PRIVATE,
            null
        );

        if (!$class_constant_type) {
            if (\Psalm\IssueBuffer::accepts(
                new \Psalm\Issue\UndefinedConstant(
                    'Constant ' . $fq_classlike_name . '::' . $this->const_name . ' is not defined',
                    $code_location
                ),
                $source->getSuppressedIssues()
            )) {
                // fall through
            }
        }

        $this->checked = true;
    }
}
