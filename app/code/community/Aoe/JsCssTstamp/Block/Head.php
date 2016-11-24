<?php

/**
 * Head
 *
 * @author Fabrizio Branca
 * @since 2011-12-08
 */
class Aoe_JsCssTstamp_Block_Head extends Mage_Page_Block_Html_Head
{
    const DEFAULT_PRIO = 50;

    /**
     * @var bool
     */
    protected $_sortAssets = false;

    /**
     * @var int
     */
    protected $_addIndex = 0;

    /**
     * Get HEAD HTML with CSS/JS/RSS definitions
     * (actually it also renders other elements, TODO: fix it up or rename this method)
     *
     * @return string
     */
    public function getCssJsHtml()
    {
        if (!isset($this->_data['items'])) {
            return '';
        }

        if ($this->_sortAssets) {
            $this->_sortAssets();
            $this->_sortAssets = false;
        }

        return parent::getCssJsHtml();
    }

    /**
     * Sort the assets.
     * Assets with the same priority will be sorted by their add index
     * @return void
     */
    protected function _sortAssets()
    {
        uasort(
            $this->_data['items'],
            function($a, $b) {
                if ($a == $b) {
                    return 0;
                }

                // use add index for same prio assets
                if ($a['prio'] == $b['prio']) {
                    return $a['add_index'] > $b['add_index'] ? 1 : -1;
                }

                return $a['prio'] > $b['prio'] ? 1 : -1;
            }
        );
    }

    /**
     * Get Js html
     *
     * @return string
     */
    public function getJsHtml()
    {
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
    public function getAllButJsHtml()
    {
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
     * Add HEAD Item
     *
     * Allowed types:
     *  - js
     *  - js_css
     *  - skin_js
     *  - skin_css
     *  - ext_js
     *  - ext_css
     *  - rss
     *
     * @param string $type
     * @param string $name
     * @param string $params
     * @param string $if
     * @param string $cond
     * @param integer $prio
     * @return Mage_Page_Block_Html_Head
     */
    public function addItem($type, $name, $params = null, $if = null, $cond = null, $prio = self::DEFAULT_PRIO)
    {
        if ($type === 'skin_css' && empty($params)) {
            $params = 'media="all"';
        }
        $this->_data['items'][$type . '/' . $name] = [
            'type'      => $type,
            'name'      => $name,
            'params'    => $params,
            'if'        => (bool) $if ? $if : null,
            'cond'      => (bool) $cond ? $cond : null,
            'prio'      => $prio ? (int) $prio : self::DEFAULT_PRIO,
            'add_index' => $this->_addIndex++,
        ];

        if ($prio != self::DEFAULT_PRIO) {
            $this->_sortAssets = true;
        }

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
     * @return Mage_Page_Block_Html_Head
     */
    public function addSkinCss($name, $params = "", $if = NULL, $cond = NULL)
    {
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
    public function addSkinJs($name, $params = "", $if = NULL, $cond = NULL)
    {
        $this->addItem('skin_js', $name, $params, $if, $cond);
        return $this;
    }

    /**
     * Add External JS
     * convenience method
     *
     * @param string $name
     * @param string $params
     * @param string $if
     * @param string $cond
     *
     * @return $this
     */
    public function addExtJs($name, $params = "", $if = null, $cond = null)
    {
        $this->addItem('ext_js', $name, $params, $if, $cond);

        return $this;
    }

    /**
     * Add External CSS
     * convenience method
     *
     * @param string $name
     * @param string $params
     * @param string $if
     * @param string $cond
     *
     * @return $this
     */
    public function addExtCss($name, $params = "", $if = null, $cond = null)
    {
        $this->addItem('ext_css', $name, $params, $if, $cond);

        return $this;
    }

    /**
     * Classify HTML head item and queue it into "lines" array
     *
     * @param array  &$lines
     * @param string $itemIf
     * @param string $itemType
     * @param string $itemParams
     * @param string $itemName
     * @param array  $itemThe
     */
    protected function _separateOtherHtmlHeadElements(&$lines, $itemIf, $itemType, $itemParams, $itemName, $itemThe)
    {
        parent::_separateOtherHtmlHeadElements($lines, $itemIf, $itemType, $itemParams, $itemName, $itemThe);

        $params = $itemParams ? ' ' . $itemParams : '';
        $href = $itemName;
        switch ($itemType) {
            case 'ext_js':
                $lines[$itemIf]['other'][] = sprintf('<script type="text/javascript" src="%s"%s></script>', $href, $params);
                break;
            case 'ext_css':
                $lines[$itemIf]['other'][] = sprintf('<link rel="stylesheet" type="text/css" href="%s"%s />', $href, $params);
                break;
        }
    }

    /**
     * Merge static and skin files of the same format into 1 set of HEAD directives or even into 1 directive
     *
     * Will attempt to merge into 1 directive, if merging callback is provided. In this case it will generate
     * filenames, rather than render urls.
     * The merger callback is responsible for checking whether files exist, merging them and giving result URL
     *
     * @param string $format - HTML element format for sprintf('<element src="%s"%s />', $src, $params)
     * @param array $staticItems - array of relative names of static items to be grabbed from js/ folder
     * @param array $skinItems - array of relative names of skin items to be found in skins according to design config
     * @param callback $mergeCallback
     * @return string
     */
    protected function &_prepareStaticAndSkinElements($format, array $staticItems, array $skinItems, $mergeCallback = null)
    {
        $designPackage = Mage::getDesign();
        $baseJsUrl = Mage::getBaseUrl('js');
        $items = array();
        if ($mergeCallback && !is_callable($mergeCallback)) {
            $mergeCallback = null;
        }

        $staticItems = $this->_reorderItems($staticItems);
        $skinItems = $this->_reorderItems($skinItems);

        // get static files from the js folder, no need in lookups
        foreach ($staticItems as $params => $rows) {
            $items[$params] = array(
                'files' => array(),
                'urls' => array(),
            );
            foreach ($rows as $name) {
                $items[$params]['files'][] = Mage::getBaseDir() . DS . 'js' . DS . $name;
                $items[$params]['urls'][] = $baseJsUrl . $name;
            }
        }

        // lookup each file basing on current theme configuration
        foreach ($skinItems as $params => $rows) {
            if (!isset($items[$params]['files'])) {
                $items[$params] = array(
                    'files' => array(),
                    'urls' => array(),
                );
            }
            foreach ($rows as $name) {
                $items[$params]['files'][] = $designPackage->getFilename($name, array('_type' => 'skin'));
                $items[$params]['urls'][] = $designPackage->getSkinUrl($name, array());
            }
        }

        $html = '';
        foreach ($items as $params => $rows) {
            // attempt to merge
            $mergedUrl = false;
            if ($mergeCallback) {
                $mergedUrl = call_user_func($mergeCallback, $rows['files']);
            }
            // render elements
            $params = trim($params);
            $params = $params ? ' ' . $params : '';
            if ($mergedUrl) {
                $html .= sprintf($format, $mergedUrl, $params);
            } else {
                foreach ($rows['urls'] as $src) {
                    $html .= sprintf($format, $src, $params);
                }
            }
        }

        return $html;
    }

    /**
     * Reorder items - move items by '<empty string>' to the end of the array
     *
     * @param array $items
     * @return array
     */
    protected function _reorderItems(array $items)
    {
        if (isset($items[''])) {
            $defaultItem = $items[''];
            unset($items['']);
            ksort($items);
            $items = array_values($items);
            array_push($items, $defaultItem);
        }

        return $items;
    }
}
