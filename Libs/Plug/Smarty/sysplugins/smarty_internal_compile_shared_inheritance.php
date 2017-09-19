<?php
/**
 * Smarty Internal Plugin Compile Shared Inheritance
 * Shared methods for {extends} and {block} tags.
 *
 * @author     Uwe Tews
 */

/**
 * Smarty Internal Plugin Compile Shared Inheritance Class.
 */
class Smarty_Internal_Compile_Shared_Inheritance extends Smarty_Internal_CompileBase
{
    /**
     * Register post compile callback to compile inheritance initialization code.
     *
     * @param \Smarty_Internal_TemplateCompilerBase $compiler
     * @param bool|false                            $initChildSequence if true force child template
     */
    public function registerInit(Smarty_Internal_TemplateCompilerBase $compiler, $initChildSequence = false)
    {
        if ($initChildSequence || !isset($compiler->_cache['inheritanceInit'])) {
            $compiler->registerPostCompileCallback(['Smarty_Internal_Compile_Shared_Inheritance', 'postCompile'],
                                                   [$initChildSequence], 'inheritanceInit', $initChildSequence);

            $compiler->_cache['inheritanceInit'] = true;
        }
    }

    /**
     * Compile inheritance initialization code as prefix.
     *
     * @param \Smarty_Internal_TemplateCompilerBase $compiler
     * @param bool|false                            $initChildSequence if true force child template
     */
    public static function postCompile(Smarty_Internal_TemplateCompilerBase $compiler, $initChildSequence = false)
    {
        $compiler->prefixCompiledCode .= '<?php $_smarty_tpl->ext->_inheritance->init($_smarty_tpl, '.
            var_export($initChildSequence, true).");\n?>\n";
    }
}
