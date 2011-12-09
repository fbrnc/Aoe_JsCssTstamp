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
	protected $debug = true;
	protected $minifyJs = true;
	protected $compressJs = true;
	protected $debugData = array();

	/**
	 * Compress
	 */
	public function __construct() {
		$this->minifyJs = Mage::getStoreConfig('dev/js/minify_files');
		$this->compressJs = Mage::getStoreConfig('dev/js/compress_files');
		$this->debug = Mage::getStoreConfig('dev/js/debug');
	}

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

		$this->debugData = array();

		$coreHelper = Mage::helper('core'); /* @var $coreHelper Mage_Core_Helper_Data */
		if ($coreHelper->mergeFiles($files, $path, false, array($this, 'beforeMergeJs'), 'js')) {
			$mergedJsUrl = Mage::getBaseUrl('media') . 'js/' . $targetFilename;
		}

		if ($this->debug) {
			$sums = array('originalSize' => NULL, 'minifiedSize' => NULL, 'compressedSize' => NULL);
			foreach ($this->debugData as $sizes) {
				$sums['originalSize'] += $sizes['originalSize'];
				if (isset($sizes['minifiedSize'])) { $sums['minifiedSize'] += $sizes['minifiedSize']; }
				if (isset($sizes['compressedSize'])) { $sums['compressedSize'] += $sizes['compressedSize']; }
			}
			array_walk($sums, array($this, 'formatSize'));
			Mage::log($targetFilename . ' ' . var_export($sums, 1));
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

		if ($this->minifyJs) {
			if ($this->debug) {
				if (!isset($this->debugData[$file])) { $this->debugData[$file] = array(); }
				$this->debugData[$file]['originalSize'] = strlen($contents);
			}

			// require_once Mage::getBaseDir('lib').'/aoe_jscsststamp/JSMin.php';
			// $contents = JSMin::minify($contents);
			require_once Mage::getBaseDir('lib').'/aoe_jscsststamp/JSMinPlus.php';
			$contents = JSMinPlus::minify($contents);

			if ($this->debug) {
				if (!isset($this->debugData[$file])) { $this->debugData[$file] = array(); }
				$this->debugData[$file]['minifiedSize'] = strlen($contents);
			}
		}

		if ($this->compressJs && false) {
			// EXPERIMENTAL!
			$tmpFile = tempnam(sys_get_temp_dir(), 'js_compression_');
			file_put_contents($tmpFile, $contents);
			shell_exec('gzip -c ' . $tmpFile . ' > ' . $tmpFile.'.gz');
			Mage::log($tmpFile);
			$contents = file_get_contents($tmpFile.'.gz');
			if (empty($contents)) {
				throw new Exception ('No contetn');
			}
			unlink($tmpFile);
			unlink($tmpFile.'.gz');

			if ($this->debug) {
				if (!isset($this->debugData[$file])) { $this->debugData[$file] = array(); }
				$this->debugData[$file]['compressedSize'] = strlen($contents);
			}
		}

		return $contents;
	}

	/**
	 * Format size
	 *
	 * @param int $size
	 * @return string
	 */
	protected function formatSize(&$size) {
		$sizes = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
		if ($size != 0) {
			$size = round($size/pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizes[$i];
		}
		return $size;
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