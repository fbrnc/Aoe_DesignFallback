<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Aoe_DesignFallback_Model_Layout_Update extends Mage_Core_Model_Layout_Update
{

    /**
     * Normally it only loads from the current theme, not it loads the complete fallback scheme
     *
     * @param string $handle
     * @return bool
     */
    public function fetchDbLayoutUpdates($handle)
    {
        $_profilerKey = 'layout/db_update: '.$handle;
        Varien_Profiler::start($_profilerKey);
        /* @var $design Aoe_DesignFallback_Model_Design_Package */
        $design = Mage::getSingleton('core/design_package');

        $params = array();
        $design->updateParamDefaults($params);
        $scheme = $design->getFallbackScheme($params);
        $updateStr = '';
        foreach ($scheme as $layout) {
            $updateStr = $this->_getUpdateString($handle,
                array('package' => $layout['_package'], 'theme' => $layout['_theme'])) . $updateStr;
        }

        if (!$updateStr) {
            return false;
        }
        $updateStr = '<update_xml>' . $updateStr . '</update_xml>';
        $updateStr = str_replace($this->_subst['from'], $this->_subst['to'], $updateStr);
        $updateXml = simplexml_load_string($updateStr, $this->getElementClass());
        $this->fetchRecursiveUpdates($updateXml);
        $this->addUpdate($updateXml->innerXml());

        Varien_Profiler::stop($_profilerKey);
        return true;
    }


    /**
     * Get update string
     *
     * @param string $handle
     * @param array  $params
     *
     * @return mixed
     */
    protected function _getUpdateString($handle, $params = array())
    {
        return Mage::getResourceModel('core/layout')->fetchUpdatesByHandle($handle, $params);
    }
}
