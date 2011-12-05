<?php

class Aoe_JsCssTstamp_Model_Package extends Mage_Core_Model_Design_Package {
	
	/**
	 * Overwrite original method in order to add filemtime as parameter 
	 * 
	 * @return string
	 */
    public function getMergedJsUrl($files) {
    	$tstamp = $this->getVersionKey($files);
        $targetFilename = md5(implode(',', $files)) . '.' . $tstamp . '.js';
        $targetDir = $this->_initMergerDir('js');
        if (!$targetDir) {
            return '';
        }
        if (Mage::helper('core')->mergeFiles($files, $targetDir . DS . $targetFilename, false, null, 'js')) {
            return Mage::getBaseUrl('media') . 'js/' . $targetFilename;
        }
        return '';
    }
    	
    /**
	 * Overwrite original method in order to add filemtime as parameter 
	 * 
	 * @return string
	 */
     public function getMergedCssUrl($files) {
    	$tstamp = $this->getVersionKey($files);
        $targetFilename = md5(implode(',', $files)) . '.' . $tstamp . '.css';
        $targetDir = $this->_initMergerDir('css');
        if (!$targetDir) {
            return '';
        }
        if (Mage::helper('core')->mergeFiles($files, $targetDir . DS . $targetFilename, false, array($this, 'beforeMergeCss'), 'css')) {
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
     	foreach ($files as $file) {
     		$tstamp = is_null($tstamp) ? filemtime($file) : max($tstamp, filemtime($file));
     	}
     	return $tstamp;
     }
	
}