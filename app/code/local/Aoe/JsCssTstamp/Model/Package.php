<?php

/**
 * Rewriting package class to add some custom version key to bundled files
 *
 * @author Fabrizio Branca
 */
class Aoe_JsCssTstamp_Model_Package extends Mage_Core_Model_Design_Package {

	/**
	 * Overwrite original method in order to add filemtime as parameter
	 *
	 * @param array $files
	 * @return string
	 */
	public function getMergedJsUrl($files) {
		$versionKey = $this->getVersionKey($files);
		$targetFilename = md5(implode(',', $files)) . '.' . $versionKey . '.js';
		$targetDir = $this->_initMergerDir('js');
		if (!$targetDir) {
			return '';
		}
		$coreHelper = Mage::helper('core'); /* @var $coreHelper Mage_Core_Helper_Data */
		if ($coreHelper->mergeFiles($files, $targetDir . DS . $targetFilename, false, null, 'js')) {
			return Mage::getBaseUrl('media') . 'js/' . $targetFilename;
		}
		return '';
	}

	/**
	 * Overwrite original method in order to add filemtime as parameter
	 *
	 * @param array $files
	 * @return string
	 */
	public function getMergedCssUrl($files)	{
		$versionKey = $this->getVersionKey($files);
		$targetFilename = md5(implode(',', $files)) . '.' . $versionKey . '.css';
		$targetDir = $this->_initMergerDir('css');
		if (!$targetDir) {
			return '';
		}
		$coreHelper = Mage::helper('core'); /* @var $coreHelper Mage_Core_Helper_Data */
		if ($coreHelper->mergeFiles($files, $targetDir . DS . $targetFilename, false, array($this, 'beforeMergeCss'), 'css')) {
			return Mage::getBaseUrl('media') . 'css/' . $targetFilename;
		}
		return '';
	}

	/**
	 * Get the timestamp of the youngest file as version key
	 *
	 * @param array $files
	 * @return int tstamp
	 */
	protected function getVersionKey($files) {
		$tstamp = null;
		if (is_array($files)) {
			foreach ($files as $file) {
				$tstamp = is_null($tstamp) ? filemtime($file) : max($tstamp, filemtime($file));
			}
		}
		return $tstamp;
	}

}