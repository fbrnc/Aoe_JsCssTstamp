<?php
/**
 * Head
 *
 * @author Fabrizio Branca
 * @since 2011-12-08
 */
class Aoe_JsCssTstamp_Block_Head extends Mage_Page_Block_Html_Head {

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
}
