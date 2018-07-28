<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_Onestepcheckout
 * @version    1.3.11
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */

class AW_Onestepcheckout_Helper_Related extends Mage_Core_Helper_Data
{
    /**
     * Check is ARP2 enabled
     */
    public function isAutomaticRelatedEnabled()
    {
        return $this->isModuleEnabled('AW_Autorelated');
    }

    /**
     * Get ARP2 rules collection
     */
    public function getAutomaticRelatedRuleCollection()
    {
        $collection = null;
        if ($this->isAutomaticRelatedEnabled()) {
            $collection = Mage::getResourceModel('awautorelated/blocks_collection')
                ->addTypeFilter(AW_Autorelated_Model_Source_Type::SHOPPING_CART_BLOCK)
                ->setPriorityOrder()
            ;
        }
        return $collection;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = array(
            array(
                'value' => null,
                'label' => $this->__('--Please Select--'),
            )
        );
        $ruleCollection = $this->getAutomaticRelatedRuleCollection();
        if (!is_null($ruleCollection)) {
            foreach ($ruleCollection as $key => $rule) {
                $optionArray[] = array(
                    'value' => $key,
                    'label' => $rule->getName(),
                );
            }
        }
        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $array = array();
        $ruleCollection = $this->getAutomaticRelatedRuleCollection();
        if (!is_null($ruleCollection)) {
            foreach ($ruleCollection as $key => $rule) {
                $array[$key] = $rule->getName();
            }
        }
        return $array;
    }



}