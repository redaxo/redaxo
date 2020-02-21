<?php
namespace Psalm\Issue;

/**
 * This is different from PossiblyNullReference, as PHP throws a notice (vs the possibility of a fatal error with a null
 * reference)
 */
class PossiblyNullArrayAccess extends CodeIssue
{
    const ERROR_LEVEL = 3;
}
