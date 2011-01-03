<?php
// cp imports
require_once(CP_DIR.'lib/system/template/TemplatePluginFunction.class.php');
require_once(CP_DIR.'lib/system/template/Template.class.php');

/**
 * The 'htmlcheckboxes' template function generates a list of html checkboxes.
 *
 * Usage:
 * {htmlcheckboxes name="x" options=$array}
 * {htmlcheckboxes name="x" options=$array selected=$foo}
 * {htmlcheckboxes name="x" output=$outputArray}
 * {htmlcheckboxes name="x" output=$outputArray values=$valueArray}
 *
 * @author 		Marcel Werk
 * @copyright		2001-2009 WoltLab GmbH
 * @package		com.develfusion.ikarus
 * @subpackage		system
 * @category		Ikarus Framework
 * @license		GNU Lesser Public License <http://www.gnu.org/licenses/lgpl.txt>
 * @version		1.0.0-0001
 */
class TemplatePluginFunctionHtmlcheckboxes implements TemplatePluginFunction {
	protected $disableEncoding = false;

	/**
	 * @see TemplatePluginFunction::execute()
	 */
	public function execute($tagArgs, Template $tplObj) {
		// get options
		if (isset($tagArgs['output']) && is_array($tagArgs['output'])) {
			if (isset($tagArgs['values']) && is_array($tagArgs['values'])) {
				$tagArgs['options'] = array_combine($tagArgs['values'], $tagArgs['output']);
			}
			else {
				$tagArgs['options'] = array_combine($tagArgs['output'], $tagArgs['output']);
			}
		}

		if (!isset($tagArgs['options']) || !is_array($tagArgs['options'])) {
			throw new SystemException("missing 'options' argument in htmlCheckboxes tag", 12001);
		}

		if (!isset($tagArgs['name'])) {
			throw new SystemException("missing 'name' argument in htmlCheckboxes tag", 12001);
		}

		if (isset($tagArgs['disableEncoding']) && $tagArgs['disableEncoding']) {
			$this->disableEncoding = true;
		}
		else {
			$this->disableEncoding = false;
		}

		// get selected values
		if (isset($tagArgs['selected'])) {
			if (!is_array($tagArgs['selected'])) $tagArgs['selected'] = array($tagArgs['selected']);
		}
		else {
			$tagArgs['selected'] = array();
		}
		if (!isset($tagArgs['separator'])) {
			$tagArgs['separator'] = '';
		}

		// build html
		$html = '';
		foreach ($tagArgs['options'] as $key => $value) {
			if (!empty($html)) $html .= $tagArgs['separator'];
			$html .= '<label><input type="checkbox" name="'.$this->encodeHTML($tagArgs['name']).'[]" value="'.$this->encodeHTML($key).'"'.(in_array($key, $tagArgs['selected']) ? ' checked="checked"' : '').' /> '.$this->encodeHTML($value).'</label>';
		}

		return $html;
	}

	/**
	 * Executes StringUtil::encodeHTML on the given text if disableEncoding is false.
	 * @see StringUtil::encodeHTML()
	 */
	protected function encodeHTML($text) {
		if (!$this->disableEncoding) {
			$text = StringUtil::encodeHTML($text);
		}

		return $text;
	}
}
?>