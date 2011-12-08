<?php

/**
 * Rewriting package class to add some custom version key to bundled files
 *
 * @author Fabrizio Branca
 */
class Aoe_JsCssTstamp_Model_Package extends Mage_Core_Model_Design_Package {

	const CACHEKEY = 'aoe_jscsststamp_versionkey';

	protected $sumOriginalJsSize;
	protected $sumCompressedJsSize;
	protected $debug = false;

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
		if ($coreHelper->mergeFiles($files, $path, false, array($this, 'beforeMergeJs'), 'js')) {
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
	 * Before merge JS callback function
	 *
	 * @param string $file
	 * @param string $contents
	 * @return string
	 */
	public function beforeMergeJs($file, $contents) {
		require_once Mage::getBaseDir('lib').'/aoe_jscsststamp/JSMin.php';
		if ($this->debug) {
			$lengthBefore = strlen($contents);
			$this->sumOriginalJsSize += $lengthBefore;
		}

		$contents = JSMin::minify($contents);

		if ($this->debug) {
			$lengthAfter = strlen($contents);
			$this->sumCompressedJsSize += $lengthAfter;
			$saved = $lengthBefore - $lengthAfter;
			$percent = 100 - round(100 * $lengthAfter / $lengthBefore);
			$savedSoFar = $this->sumOriginalJsSize - $this->sumCompressedJsSize;
			$percentSoFar = 100 - round(100 * $this->sumCompressedJsSize / $this->sumOriginalJsSize);
			Mage::log(sprintf("Compressing %s: %s -> %s, Saved: %s (%s %%) | Total: %s -> %s, Saved: %s (%s %%)",
				substr($file, -20),
				$this->formatSize($lengthBefore),
				$this->formatSize($lengthAfter),
				$this->formatSize($saved),
				$percent,
				$this->formatSize($this->sumOriginalJsSize),
				$this->formatSize($this->sumCompressedJsSize),
				$this->formatSize($savedSoFar),
				$percentSoFar
			));
		}
		return $contents;
	}

	protected function formatSize($size) {
		$sizes = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
		if ($size == 0) { return('n/a'); } else {
		return (round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizes[$i]); }
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