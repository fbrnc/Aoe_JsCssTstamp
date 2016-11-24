<?php

class Aoe_JsCssTstamp_Test_Config_DefaultMerged extends EcomDev_PHPUnit_Test_Case {

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
    public function defaultMergeJs() {

        $this->assertEquals('0', Mage::getStoreConfig('dev/js/addTstampToJsFiles'));

        $headBlock = new Mage_Page_Block_Html_Head();
        $headBlock->addItem('skin_js', 'js/opcheckout.js');

        $html = $headBlock->getCssJsHtml();

        $this->assertNotContains('js/opcheckout.js', $html);

        $matches = array();
        $result = preg_match('/\/media\/js\/[u|s]\.[a-f0-9]{32}\.js/', $html, $matches);
        $this->assertEquals(1, $result);

        $file = Mage::getBaseDir() . $matches[0];
        $this->assertTrue(file_exists($file));

        $this->assertContains('/* FILE: opcheckout.js */', file_get_contents($file));

    }

    /**
     * @loadFixture
     * @test
     */
    public function defaultMergeCss() {
        $headBlock = new Mage_Page_Block_Html_Head();
        $headBlock->addCss('css/styles.css');

        $html = $headBlock->getCssJsHtml();

        $this->assertNotContains('css/styles.css', $html);

        $matches = array();
        $result = preg_match('/\/media\/css\/[u|s]\.[a-f0-9]{32}\.[0-9]{10}\.css/', $html, $matches);
        $this->assertEquals(1, $result);

        $file = Mage::getBaseDir() . $matches[0];
        $this->assertTrue(file_exists($file));

        $this->assertContains('/* FILE: styles.css */', file_get_contents($file));

    }

}