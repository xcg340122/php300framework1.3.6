<?php
/**
 * Smarty plugin.
 */

/**
 * evaluate compiler parameter.
 *
 * @param array $params  parameter array as given to the compiler function
 * @param int   $index   array index of the parameter to convert
 * @param mixed $default value to be returned if the parameter is not present
 *
 * @throws SmartyException if parameter is not a literal (but an expression, variable, …)
 *
 * @return mixed evaluated value of parameter or $default
 *
 * @author Rodney Rehm
 */
function smarty_literal_compiler_param($params, $index, $default = null)
{
    // not set, go default
    if (!isset($params[$index])) {
        return $default;
    }
    // test if param is a literal
    if (!preg_match('/^([\'"]?)[a-zA-Z0-9-]+(\\1)$/', $params[$index])) {
        throw new SmartyException('$param['.$index.'] is not a literal and is thus not evaluatable at compile time');
    }

    $t = null;
    eval('$t = '.$params[$index].';');

    return $t;
}
