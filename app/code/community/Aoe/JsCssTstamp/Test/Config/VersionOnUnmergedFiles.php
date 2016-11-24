<?php

class Aoe_JsCssTstamp_Test_Config_VersionOnUnmergedFiles extends EcomDev_PHPUnit_Test_Case {

    /**
     * Setup
     */
    protected function setUp()
    {
        parent::setUp();
        $this->replaceRegistry(EcomDev_PHPUnit_Model_App::REGISTRY_PATH_DESIGN_PACKAGE_SINGLETON, null);
    }


    /**
     * @test
     */
    public function checkClass() {
        $this->assertInstanceOf('Aoe_JsCssTstamp_Model_Package', Mage::getModel('core/design_package'));
        $this->assertInstanceOf('Aoe_JsCssTstamp_Model_Package', Mage::getSingleton('core/design_package'));
        $this->assertInstanceOf('Aoe_JsCssTstamp_Model_Package', Mage::getDesign());
    }

    /**
     * @loadFixture
     * @test
     */
    public function checkVersionOnUnmergedJsFiles() {

        $this->assertEquals('1', Mage::getStoreConfig('dev/js/addTstampToJsFiles'));

        $headBlock = new Mage_Page_Block_Html_Head();
        $headBlock->addItem('skin_js', 'js/opcheckout.js');

        $html = $headBlock->getCssJsHtml();

        $this->assertNotContains('js/opcheckout.js', $html);
        $this->assertRegExp('/[0-9]{10}\.js/', $html);
        $this->assertRegExp('/js\/opcheckout\.[0-9]{10}\.js/', $html);
    }

    /**
     * @loadFixture
     * @test
     */
    public function checkVersionOnUnmergedCssFiles() {

        $this->assertEquals('1', Mage::getStoreConfig('dev/css/addTstampToCssFiles'));

        $headBlock = new Mage_Page_Block_Html_Head();
        $headBlock->addCss('css/styles.css');

        $html = $headBlock->getCssJsHtml();

        $this->assertNotContains('css/styles.css', $html);
        $this->assertRegExp('/[0-9]{10}\.css/', $html);
        $this->assertRegExp('/css\/styles\.[0-9]{10}\.css/', $html);
    }

}