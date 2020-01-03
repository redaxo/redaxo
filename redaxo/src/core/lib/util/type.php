<?php

/**
 * Class for var casting.
 *
 * @author gharlan
 *
 * @package redaxo\core
 */
class rex_type
{
    /**
     * Casts the variable $var to $vartype.
     *
     * Possible types:
     *  - 'bool' (or 'boolean')
     *  - 'int' (or 'integer')
     *  - 'double'
     *  - 'string'
     *  - 'float'
     *  - 'real'
     *  - 'object'
     *  - 'array'
     *  - 'array[<type>]', e.g. 'array[int]'
     *  - '' (don't cast)
     *  - a callable
     *  - array(
     *      array(<key>, <vartype>, <default>),
     *      array(<key>, <vartype>, <default>),
     *      ...
     *    )
     *
     * @param mixed $var     Variable to cast
     * @param mixed $vartype Variable type
     *
     * @throws InvalidArgumentException
     *
     * @return mixed Castet value
     */
    public static function cast($var, $vartype)
    {
        if (is_string($vartype)) {
            $casted = true;
            switch ($vartype) {
                // ---------------- PHP types
                case 'bool':
                case 'boolean':
                    $var = (bool) $var;
                    break;
                case 'int':
                case 'integer':
                    $var = (int) $var;
                    break;
                case 'double':
                case 'float':
                case 'real':
                    $var = (float) $var;
                    break;
                case 'string':
                    $var = (string) $var;
                    break;
                case 'object':
                    $var = (object) $var;
                    break;
                case 'array':
                    if ('' === $var) {
                        $var = [];
                    } else {
                        $var = (array) $var;
                    }
                    break;

                    // kein Cast, nichts tun
                case '': break;

                default:
                    // check for array with generic type
                    if (0 === strpos($vartype, 'array[')) {
                        if (empty($var)) {
                            $var = [];
                        } else {
                            $var = (array) $var;
                        }

                        // check if every element in the array is from the generic type
                        $matches = [];
                        if (preg_match('@array\[([^\]]*)\]@', $vartype, $matches)) {
                            foreach ($var as $key => $value) {
                                try {
                                    $var[$key] = self::cast($value, $matches[1]);
                                } catch (InvalidArgumentException $e) {
                                    // Evtl Typo im vartype, mit urspr. typ als fehler melden
                                    throw new InvalidArgumentException('Unexpected vartype "' . $vartype . '" in cast()!');
                                }
                            }
                        } else {
                            throw new InvalidArgumentException('Unexpected vartype "' . $vartype . '" in cast()!');
                        }
                    } else {
                        $casted = false;
                    }
            }
            if ($casted) {
                return $var;
            }
        }

        if (is_callable($vartype)) {
            $var = call_user_func($vartype, $var);
        } elseif (is_array($vartype)) {
            $var = self::cast($var, 'array');
            $newVar = [];
            foreach ($vartype as $cast) {
                if (!is_array($cast) || !isset($cast[0])) {
                    throw new InvalidArgumentException('Unexpected vartype in cast()!');
                }
                $key = $cast[0];
                $innerVartype = $cast[1] ?? '';
                if (array_key_exists($key, $var)) {
                    $newVar[$key] = self::cast($var[$key], $innerVartype);
                } elseif (!isset($cast[2])) {
                    $newVar[$key] = self::cast('', $innerVartype);
                } else {
                    $newVar[$key] = $cast[2];
                }
            }
            $var = $newVar;
        } elseif (is_string($vartype)) {
            throw new InvalidArgumentException('Unexpected vartype "' . $vartype . '" in cast()!');
        } else {
            throw new InvalidArgumentException('Unexpected vartype in cast()!');
        }

        return $var;
    }
}
