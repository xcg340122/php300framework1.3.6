<?php
/**
 * Smarty Internal Plugin Compile Continue
 * Compiles the {continue} tag.
 *
 * @author     Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile Continue Class.
 */
class Smarty_Internal_Compile_Continue extends Smarty_Internal_CompileBase
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     *
     * @see Smarty_Internal_CompileBase
     */
    public $optional_attributes = ['levels'];

    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     *
     * @see Smarty_Internal_CompileBase
     */
    public $shorttag_order = ['levels'];

    /**
     * Compiles code for the {continue} tag.
     *
     * @param array                                 $args      array with attributes from parser
     * @param \Smarty_Internal_TemplateCompilerBase $compiler  compiler object
     * @param array                                 $parameter array with compilation parameter
     *
     * @throws \SmartyCompilerException
     *
     * @return string compiled code
     */
    public function compile($args, Smarty_Internal_TemplateCompilerBase $compiler, $parameter)
    {
        static $_is_loopy = ['for' => true, 'foreach' => true, 'while' => true, 'section' => true];
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);

        if ($_attr['nocache'] === true) {
            $compiler->trigger_template_error('nocache option not allowed', null, true);
        }

        if (isset($_attr['levels'])) {
            if (!is_numeric($_attr['levels'])) {
                $compiler->trigger_template_error('level attribute must be a numeric constant', null, true);
            }
            $_levels = $_attr['levels'];
        } else {
            $_levels = 1;
        }
        $level_count = $_levels;
        $stack_count = count($compiler->_tag_stack) - 1;
        while ($level_count > 0 && $stack_count >= 0) {
            if (isset($_is_loopy[$compiler->_tag_stack[$stack_count][0]])) {
                $level_count--;
            }
            $stack_count--;
        }
        if ($level_count != 0) {
            $compiler->trigger_template_error("cannot continue {$_levels} level(s)", null, true);
        }

        return "<?php continue {$_levels};?>";
    }
}
