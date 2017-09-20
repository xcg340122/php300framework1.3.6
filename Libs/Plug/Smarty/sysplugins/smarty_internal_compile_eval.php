<?php
/**
 * Smarty Internal Plugin Compile Eval
 * Compiles the {eval} tag.
 *
 * @author     Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile Eval Class.
 */
class Smarty_Internal_Compile_Eval extends Smarty_Internal_CompileBase
{
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     *
     * @see Smarty_Internal_CompileBase
     */
    public $required_attributes = ['var'];
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     *
     * @see Smarty_Internal_CompileBase
     */
    public $optional_attributes = ['assign'];
    /**
     * Attribute definition: Overwrites base class.
     *
     * @var array
     *
     * @see Smarty_Internal_CompileBase
     */
    public $shorttag_order = ['var', 'assign'];

    /**
     * Compiles code for the {eval} tag.
     *
     * @param array  $args     array with attributes from parser
     * @param object $compiler compiler object
     *
     * @return string compiled code
     */
    public function compile($args, $compiler)
    {
        $this->required_attributes = ['var'];
        $this->optional_attributes = ['assign'];
        // check and get attributes
        $_attr = $this->getAttributes($compiler, $args);
        if (isset($_attr['assign'])) {
            // output will be stored in a smarty variable instead of being displayed
            $_assign = $_attr['assign'];
        }

        // create template object
        $_output = "\$_template = new {$compiler->smarty->template_class}('eval:'.".$_attr['var'].', $_smarty_tpl->smarty, $_smarty_tpl);';
        //was there an assign attribute?
        if (isset($_assign)) {
            $_output .= "\$_smarty_tpl->assign($_assign,\$_template->fetch());";
        } else {
            $_output .= 'echo $_template->fetch();';
        }

        return "<?php $_output ?>";
    }
}
