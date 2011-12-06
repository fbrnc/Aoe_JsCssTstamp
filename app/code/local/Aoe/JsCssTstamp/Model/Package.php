<?php

/**
 * Rewriting package class to add some custom version key to bundled files
 *
 * @author Fabrizio Branca
 */
class Aoe_JsCssTstamp_Model_Package extends Mage_Core_Model_Design_Package {

	const CACHEKEY = 'aoe_jscsststamp_versionkey';

	/**
	 * Overwrite original method in order to add a version key
	 *
	 * @param array $files
	 * @return string
	 */
	public function getMergedJsUrl($files) {
		$mergedJsUrl = '';
		$versionKey = $this->getVersionKey($files);
		$targetFilename = md5(implode(',', $files)) . '.' . $versionKey . '.js';
		$targetDir = $this->_initMergerDir('js');
		if (!$targetDir) {
			return '';
		}

		$path = $targetDir . DS . $targetFilename;

		// check cdn (if available)
		$cdnUrl = Mage::helper('aoejscsststamp')->getCdnUrl($path);
		if ($cdnUrl) {
			return $cdnUrl;
		}

		$coreHelper = Mage::helper('core'); /* @var $coreHelper Mage_Core_Helper_Data */
		if ($coreHelper->mergeFiles($files, $path, false, null, 'js')) {
			$mergedJsUrl = Mage::getBaseUrl('media') . 'js/' . $targetFilename;
		}

		// store file to cdn (if available)
		$cdnUrl = Mage::helper('aoejscsststamp')->storeInCdn($path);
		if ($cdnUrl) {
			return $cdnUrl;
		}

		return $mergedJsUrl;
	}

	/**
	 * Overwrite original method in order to add a version key
	 *
	 * @param array $files
	 * @return string
	 */
	public function getMergedCssUrl($files) {
		$mergedCssUrl = '';
		$versionKey = $this->getVersionKey($files);
		$targetFilename = md5(implode(',', $files)) . '.' . $versionKey . '.css';
		$targetDir = $this->_initMergerDir('css');
		if (!$targetDir) {
			return '';
		}

		$path = $targetDir . DS . $targetFilename;

		// check cdn (if available)
		$cdnUrl = Mage::helper('aoejscsststamp')->getCdnUrl($path);
		if ($cdnUrl) {
			return $cdnUrl;
		}

		$coreHelper = Mage::helper('core'); /* @var $coreHelper Mage_Core_Helper_Data */
		if ($coreHelper->mergeFiles($files, $path, false, array($this, 'beforeMergeCss'), 'css')) {
			$mergedCssUrl = Mage::getBaseUrl('media') . 'css/' . $targetFilename;
		}

		// store file to cdn (if available)
		$cdnUrl = Mage::helper('aoejscsststamp')->storeInCdn($path);
		if ($cdnUrl) {
			return $cdnUrl;
		}

		return $mergedCssUrl;
	}

	/**
	 * Get a cached timestamp as version key
	 *
	 * @param array $files
	 * @return int tstamp
	 */
	protected function getVersionKey($files) {
		$tstamp = Mage::app()->loadCache(self::CACHEKEY);
		if (empty($tstamp)) {
			$tstamp = time();
			Mage::app()->saveCache($tstamp, self::CACHEKEY);
		}
		return $tstamp;
	}

}