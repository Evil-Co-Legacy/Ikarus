<?php
// cp imports
require_once(CP_DIR.'lib/system/template/TemplatePluginCompiler.class.php');

/**
 * The 'lang' compiler function compiles dynamic language variables.
 *
 * Usage:
 * {lang}$blah{/lang}
 * {lang var=$x}foo{/lang}
 *
 * @author 		Marcel Werk
 * @copyright		2001-2009 WoltLab GmbH
 * @package		com.develfusion.ikarus
 * @subpackage		system
 * @category		Ikarus Framework
 * @license		GNU Lesser Public License <http://www.gnu.org/licenses/lgpl.txt>
 * @version		1.0.0-0001
 */
class TemplatePluginCompilerLang implements TemplatePluginCompiler {
	/**
	 * @see TemplatePluginCompiler::executeStart()
	 */
	public function executeStart($tagArgs, TemplateScriptingCompiler $compiler) {
		$compiler->pushTag('lang');

		$newTagArgs = array();
		foreach ($tagArgs as $key => $arg) {
			$newTagArgs[$key] = 'StringUtil::encodeHTML('.$arg.')';
		}

		$tagArgs = $compiler->makeArgString($newTagArgs);
		return "<?php \$this->tagStack[] = array('lang', array($tagArgs)); ob_start(); ?>";
	}

	/**
	 * @see TemplatePluginCompiler::executeEnd()
	 */
	public function executeEnd(TemplateScriptingCompiler $compiler) {
		$compiler->popTag('lang');
		$hash = StringUtil::getRandomID();
		return "<?php \$_lang".$hash." = ob_get_contents(); ob_end_clean(); echo CP::getLanguage()->get(\$_lang".$hash.", \$this->tagStack[count(\$this->tagStack) - 1][1]); array_pop(\$this->tagStack); ?>";
	}
}
?>