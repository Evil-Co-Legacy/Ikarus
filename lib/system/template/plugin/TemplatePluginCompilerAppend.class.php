<?php
// cp imports
require_once(CP_DIR.'lib/system/template/TemplatePluginCompiler.class.php');

/**
 * The 'append' compiler function calls the append function on the template object.
 *
 * Usage:
 * {append var=name value="foo"}
 *
 * @author 		Marcel Werk
 * @copyright		2001-2009 WoltLab GmbH
 * @package		com.develfusion.ikarus
 * @subpackage		system
 * @category		Ikarus Framework
 * @license		GNU Lesser Public License <http://www.gnu.org/licenses/lgpl.txt>
 * @version		1.0.0-0001
 */
class TemplatePluginCompilerAppend implements TemplatePluginCompiler {
	/**
	 * @see TemplatePluginCompiler::executeStart()
	 */
	public function executeStart($tagArgs, TemplateScriptingCompiler $compiler) {
		if (!isset($tagArgs['var'])) {
			throw new SystemException($compiler->formatSyntaxError("missing 'var' argument in append tag", $compiler->getCurrentIdentifier(), $compiler->getCurrentLineNo()), 12001);
		}
		if (!isset($tagArgs['value'])) {
			throw new SystemException($compiler->formatSyntaxError("missing 'value' argument in append tag", $compiler->getCurrentIdentifier(), $compiler->getCurrentLineNo()), 12001);
		}

		return "<?php \$this->append(".$tagArgs['var'].", ".$tagArgs['value']."); ?>";
	}

	/**
	 * @see TemplatePluginCompiler::executeEnd()
	 */
	public function executeEnd(TemplateScriptingCompiler $compiler) {
		throw new SystemException($compiler->formatSyntaxError("unknown tag {/append}", $compiler->getCurrentIdentifier(), $compiler->getCurrentLineNo()), 12003);
	}
}
?>