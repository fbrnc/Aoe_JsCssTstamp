<?php

class Aoe_JsCssTstamp_Test_Config_DefaultUnmerged extends EcomDev_PHPUnit_Test_Case {

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
    public function defaultJs() {
        $headBlock = new Mage_Page_Block_Html_Head();
        $headBlock->addJs('prototype/prototype.js');
        $headBlock->addJs('mage/translate.js');

        $html = $headBlock->getCssJsHtml();

        $this->assertContains('prototype/prototype.js', $html);
        $this->assertContains('mage/translate.js', $html);
    }

    /**
     * @loadFixture
     * @test
     */
    public function defaultCss() {
        $headBlock = new Mage_Page_Block_Html_Head();
        $headBlock->addCss('css/styles.css');

        $html = $headBlock->getCssJsHtml();

        $this->assertContains('css/styles.css', $html);
    }

}