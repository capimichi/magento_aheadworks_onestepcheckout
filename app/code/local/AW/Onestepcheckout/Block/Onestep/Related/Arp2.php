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


class AW_Onestepcheckout_Block_Onestep_Related_Arp2 extends AW_Onestepcheckout_Block_Onestep_Related_Abstract
{

    public function getItems()
    {
        $items = $this->getData('items');
        if (is_null($items)) {
            if ($collection = $this->_getCollection()) {
                $collection->setPageSize($this->_maxItemCount);
                $items = $collection->getItems();
            } else {
                $items = array();
            }
            $this->setData('items', $items);
        }
        return $items;
    }

    protected function _getCollection()
    {
        $storeId = Mage::app()->getStore()->getId();
        $blockId = Mage::helper('aw_onestepcheckout/config')->getAutomaticRelatedRuleId($storeId);

        $collection = $this->_getCollectionFromArp2($blockId, $this->getQuote()->getId());
        return $collection;
    }

    //copy-paste from aw-autorelated api
    protected function _getCollectionFromArp2($blockId, $quoteId)
    {
        /** @var $block AW_Autorelated_Model_Blocks */
        $block = Mage::getModel('awautorelated/blocks')->load($blockId);
        if ($block->getId()) {
            /** @var $layoutBlock AW_Autorelated_Block_Blocks_Shoppingcart */
            $layoutBlock = Mage::getSingleton('core/layout')->createBlock('awautorelated/blocks_shoppingcart');
            $layoutBlock->setData($block->getData());
            $layoutBlock->setData('_quote_id', $quoteId);
            return $layoutBlock->getCollection();
        }
        return null;
    }

}
