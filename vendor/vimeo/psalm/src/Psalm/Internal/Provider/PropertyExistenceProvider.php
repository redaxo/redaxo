<?php
namespace Psalm\Internal\Provider;

use const PHP_VERSION;
use PhpParser;
use Psalm\CodeLocation;
use Psalm\Context;
use Psalm\Plugin\Hook\PropertyExistenceProviderInterface;
use Psalm\StatementsSource;
use function strtolower;
use function version_compare;

class PropertyExistenceProvider
{
    /**
     * @var array<
     *   string,
     *   array<\Closure(
     *     string,
     *     string,
     *     bool,
     *     ?StatementsSource=,
     *     ?Context=,
     *     ?CodeLocation=
     *   ) : ?bool>
     * >
     */
    private static $handlers = [];

    public function __construct()
    {
        self::$handlers = [];
    }

    /**
     * @param  class-string<PropertyExistenceProviderInterface> $class
     *
     * @return void
     */
    public function registerClass(string $class)
    {
        $callable = \Closure::fromCallable([$class, 'doesPropertyExist']);

        foreach ($class::getClassLikeNames() as $fq_classlike_name) {
            /** @psalm-suppress MixedTypeCoercion */
            $this->registerClosure($fq_classlike_name, $callable);
        }
    }

    /**
     * @param \Closure(
     *     string,
     *     string,
     *     bool,
     *     ?StatementsSource=,
     *     ?Context=,
     *     ?CodeLocation=
     *   ) : ?bool $c
     *
     * @return void
     */
    public function registerClosure(string $fq_classlike_name, \Closure $c)
    {
        self::$handlers[strtolower($fq_classlike_name)][] = $c;
    }

    public function has(string $fq_classlike_name) : bool
    {
        return isset(self::$handlers[strtolower($fq_classlike_name)]);
    }

    /**
     * @param  array<PhpParser\Node\Arg>  $call_args
     *
     * @return ?bool
     */
    public function doesPropertyExist(
        string $fq_classlike_name,
        string $property_name,
        bool $read_mode,
        StatementsSource $source = null,
        Context $context = null,
        CodeLocation $code_location = null
    ) {
        foreach (self::$handlers[strtolower($fq_classlike_name)] as $property_handler) {
            $property_exists = $property_handler(
                $fq_classlike_name,
                $property_name,
                $read_mode,
                $source,
                $context,
                $code_location
            );

            if ($property_exists !== null) {
                return $property_exists;
            }
        }

        return null;
    }
}
