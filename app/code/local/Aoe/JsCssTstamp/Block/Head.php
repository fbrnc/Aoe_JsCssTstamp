<?php
/**
 * Head
 *
 * @author Fabrizio Branca
 * @since 2011-12-08
 */
class Aoe_JsCssTstamp_Block_Head extends Mage_Page_Block_Html_Head {

	/**
	 * Get HEAD HTML with CSS/JS/RSS definitions
	 * (actually it also renders other elements, TODO: fix it up or rename this method)
	 *
	 * @return string
	 */
	public function getCssJsHtml() {
		if (!isset($this->_data['items'])) {
   			return '';
		}
		return parent::getCssJsHtml();
	}


	/**
	 * Get Js html
	 *
	 * @return string
	 */
	public function getJsHtml() {
		// backup items
		$backupItems = $this->_data['items'];

		// remove all non js items
		foreach ($this->_data['items'] as $key => $item) {

			if (!in_array($item['type'], array('js', 'skin_js'))) {
				// no js file
				unset($this->_data['items'][$key]);
			}

		}

		$html = $this->getCssJsHtml();
		// restore items
		$this->_data['items'] = $backupItems;
		return $html;
	}

	/**
	 * Get all html but js files
	 *
	 * @return string
	 */
	public function getAllButJsHtml() {
		// backup items
		$backupItems = $this->_data['items'];

		// remove all non js items
		foreach ($this->_data['items'] as $key => $item) {
			if (in_array($item['type'], array('js', 'skin_js'))) {
				unset($this->_data['items'][$key]);
			}
		}

		$html = $this->getCssJsHtml();
		// restore items
		$this->_data['items'] = $backupItems;
		return $html;
	}

	/**
	 * Add Skin CSS
	 * convenience method
	 *
	 * @param string $name
	 * @param string $params
	 * @param string $if
	 * @param string $cond
	 * @return Mage_Page_Block_Html_Head
	 */
	public function addSkinCss($name, $params = "", $if=NULL, $cond=NULL) {
		$this->addItem('skin_css', $name, $params, $if, $cond);
		return $this;
	}

	/**
	 * Add Skin JS
	 * convenience method
	 *
	 * @param string $name
	 * @param string $params
	 * @param string $if
	 * @param string $cond
	 * @return Mage_Page_Block_Html_Head
	 */
	public function addSkinJs($name, $params = "", $if=NULL, $cond=NULL) {
		$this->addItem('skin_js', $name, $params, $if, $cond);
		return $this;
	}

}