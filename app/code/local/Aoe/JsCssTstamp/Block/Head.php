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
	 * Example
	 *
	 * All javascript excluding files in the bottomjs bucket:
	 * $this->getJsHtml('*', array('bottomjs'));
	 *
	 * Only files in the bottomjs bucket
	 * $this->getJsHtml('bottomjs');
	 *
	 * @param string|array $includeBuckets
	 * @param array $excludeBuckets
	 * @return string
	 */
	public function getJsHtml($includeBuckets=NULL, array $excludeBuckets=array()) {
		// backup items
		$backupItems = $this->_data['items'];

		if (is_null($includeBuckets) || $includeBuckets === '*') {
			$includeBuckets = $this->getAllJsBucketNames();
		}
		if (!is_array($includeBuckets)) {
			throw new InvalidArgumentException('Invalid includeBuckets parameter.');
		}

		// remove all non js items
		foreach ($this->_data['items'] as $key => $item) {

			// no js file
			if (!in_array($item['type'], array('js', 'skin_js'))) {
				unset($this->_data['items'][$key]);
			}

			// was processed before
			if (!empty($this->_data['items'][$key]['processed'])) {
				unset($this->_data['items'][$key]);
			}

			// does not match white list
			if (!in_array($this->_data['items'][$key]['bucket'], $includeBuckets)) {
				unset($this->_data['items'][$key]);
			}

			// matches the black list
			if (in_array($this->_data['items'][$key]['bucket'], $excludeBuckets)) {
				unset($this->_data['items'][$key]);
			}

			$backupItems[$key]['processed'] = true;
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
	 * Get all js bucket names
	 *
	 * @return array
	 */
	public function getAllJsBucketNames() {
		$bucketNames = array();
		foreach ($this->_data['items'] as $key => $item) {
			if (in_array($item['type'], array('js', 'skin_js'))) {
				if (!empty($item['bucket']) && !in_array($item['bucket'], $bucketNames)) {
					$bucketNames[] = $item['bucket'];
				}
			}
		}
		return $bucketNames;
	}

	/**
	 * Add HEAD Item
	 *
	 * @param string $type
	 * @param string $name
	 * @param string $params
	 * @param string $if
	 * @param string $cond
	 * @param string $bucket
	 * @return Aoe_JsCssTstamp_Block_Head
	 */
	public function addItem($type, $name, $params=null, $if=null, $cond=null, $bucket=NULL) {
		$res = parent::addItem($type, $name, $params, $if, $cond);
		if (is_array($this->_data['items'][$type.'/'.$name])) {
			$this->_data['items'][$type.'/'.$name]['bucket'] = is_null($bucket) ? 'default' : $bucket;
		}
		return $res;
	}

	/**
	 * Add CSS file to HEAD entity
	 *
	 * @param string $name
	 * @param string $params
	 * @param strnig $bucket
	 * @return Mage_Page_Block_Html_Head
	 */
	public function addCss($name, $params = "", $bucket=NULL) {
		$this->addItem('skin_css', $name, $params, NULL, NULL, $bucket);
		return $this;
	}

	/**
	 * Add JavaScript file to HEAD entity
	 *
	 * @param string $name
	 * @param string $params
	 * @param strnig $bucket
	 * @return Mage_Page_Block_Html_Head
	 */
	public function addJs($name, $params = "", $bucket=NULL) {
		$this->addItem('js', $name, $params, NULL, NULL, $bucket);
		return $this;
	}

	/**
	 * Add CSS file for Internet Explorer only to HEAD entity
	 *
	 * @param string $name
	 * @param string $params
	 * @param strnig $bucket
	 * @return Mage_Page_Block_Html_Head
	 */
	public function addCssIe($name, $params = "", $bucket=NULL) {
		$this->addItem('skin_css', $name, $params, 'IE', NULL, $bucket);
		return $this;
	}

	/**
	* Add JavaScript file for Internet Explorer only to HEAD entity
	*
	* @param string $name
	* @param string $params
	* @param strnig $bucket
	* @return Mage_Page_Block_Html_Head
	*/
	public function addJsIe($name, $params = "", $bucket=NULL) {
		$this->addItem('js', $name, $params, 'IE', NULL, $bucket);
		return $this;
	}

	/**
	* Add Link element to HEAD entity
	*
	* @param string $rel forward link types
	* @param string $href URI for linked resource
	* @param strnig $bucket
	* @return Mage_Page_Block_Html_Head
	*/
	public function addLinkRel($rel, $href, $bucket=NULL) {
		$this->addItem('link_rel', $href, 'rel="' . $rel . '"', NULL, NULL, $bucket);
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
	 * @param strnig $bucket
	 * @return Mage_Page_Block_Html_Head
	 */
	public function addSkinJs($name, $params = "", $if=NULL, $cond=NULL, $bucket=NULL) {
		$this->addItem('skin_js', $name, $params, $if, $cond, $bucket);
		return $this;
	}

	/**
	 * Add Skin CSS
	 * convenience method
	 *
	 * @param string $name
	 * @param string $params
	 * @param string $if
	 * @param string $cond
	 * @param strnig $bucket
	 * @return Mage_Page_Block_Html_Head
	 */
	public function addSkinCss($name, $params = "", $if=NULL, $cond=NULL, $bucket=NULL) {
		$this->addItem('skin_css', $name, $params, $if, $cond, $bucket);
		return $this;
	}


}
