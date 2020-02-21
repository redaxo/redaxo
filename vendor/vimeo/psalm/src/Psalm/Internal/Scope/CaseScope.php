<?php
namespace Psalm\Internal\Scope;

use Psalm\CodeLocation;
use Psalm\Context;
use Psalm\Type;

/**
 * @internal
 */
class CaseScope
{
    /**
     * @var array<string, array<string, CodeLocation>>
     */
    public $unreferenced_vars = [];

    /**
     * @var Context
     */
    public $parent_context;

    /**
     * @var array<string, Type\Union>|null
     */
    public $break_vars;

    public function __construct(Context $parent_context)
    {
        $this->parent_context = $parent_context;
    }

    public function __destruct()
    {
        /** @psalm-suppress PossiblyNullPropertyAssignmentValue */
        $this->parent_context = null;
    }
}
