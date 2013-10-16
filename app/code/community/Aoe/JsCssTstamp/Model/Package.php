<?php

if (class_exists('Aoe_DesignFallback_Model_Design_Package')) {
    class Aoe_JsCssTstamp_Model_Package extends Aoe_DesignFallback_Model_Design_Package {}
} else {
    class Aoe_JsCssTstamp_Model_Package extends Mage_Core_Model_Design_Package {}
}
