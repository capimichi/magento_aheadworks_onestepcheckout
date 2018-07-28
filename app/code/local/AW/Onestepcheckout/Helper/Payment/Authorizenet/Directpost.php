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


class AW_Onestepcheckout_Helper_Payment_Authorizenet_Directpost extends Mage_Core_Helper_Data
{
    /**
     * Compatibility for Authorize.net DPM
     */
    public function process($paymentData = array())
    {
        return $this->_processAuthorizenetDirectPost($paymentData);
    }

    /**
     * Send data to Authorize.net Direct Post
     *
     * @param array $paymentData
     *
     * @return bool|string
     */
    protected function _processAuthorizenetDirectPost($paymentData = array())
    {
        /* @var $order Mage_Sales_Model_Order */
        $order = Mage::registry('directpost_order');

        if ($order && $order->getId()) {
            $payment = $order->getPayment();
            if ($payment && $payment->getMethod() == Mage::getModel('authorizenet/directpost')->getCode()) {

                $requestToPaygate = $payment->getMethodInstance()->generateRequestFromOrder($order);
                $requestToPaygate->setControllerActionName('onepage');
                $requestToPaygate->setIsSecure((string)Mage::app()->getStore()->isCurrentlySecure());

                $dataToDPM = $requestToPaygate->getData();
                $year = $paymentData['cc_exp_year'];
                if (strlen($year) > 2) {
                    $year = substr($year, -2);
                }
                $month = $paymentData['cc_exp_month'];
                if (strlen($month) < 10) {
                    $month = '0' . $month;
                }
                $dataToDPM['x_exp_date'] = $month . '/' . $year;
                $dataToDPM['x_card_num'] = $paymentData['cc_number'];
                $dataToDPM['x_card_code'] = $paymentData['cc_cid'];

                $requestQuery = http_build_query($dataToDPM);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_VERBOSE, 1);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 30);
                curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/x-www-form-urlencoded"));
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_URL, $order->getPayment()->getMethodInstance()->getCgiUrl());
                curl_setopt($ch, CURLOPT_POSTFIELDS, $requestQuery);

                $httpResponse = curl_exec($ch);

                return $this->_processAuthorizenetDirectPostResponse($httpResponse);
            }
        }
        return false;
    }

    /**
     * Process response
     *
     * @param string $response
     *
     * @return bool|string
     */
    protected function _processAuthorizenetDirectPostResponse($response = '')
    {
        $anchorRequest = '/authorizenet/directpost_payment/redirect/';
        $offsetEnd = false;
        $offsetStart = stripos($response, $anchorRequest) + strlen($anchorRequest);
        if ($offsetStart !== false) {
            $offsetEnd = stripos($response, '";', $offsetStart);
        }

        if ($offsetStart && $offsetEnd) {
            $requestPart = substr($response, $offsetStart, $offsetEnd - $offsetStart);
            $redirectParams = $this->_parseRequestParams($requestPart);
            if (!empty($redirectParams['success'])
                && isset($redirectParams['x_invoice_num'])
                && isset($redirectParams['controller_action_name'])
            ) {
                Mage::getSingleton('authorizenet/directpost_session')->unsetData('quote_id');
                Mage::getSingleton('authorizenet/directpost_session')->setQuoteId(
                    Mage::getSingleton('checkout/type_onepage')->getQuote()->getId()
                );
            }
            if (!empty($redirectParams['error_msg'])) {
                $cancelOrder = empty($redirectParams['x_invoice_num']);
                $this->_returnCustomerQuote($cancelOrder, $redirectParams['error_msg']);
                return $redirectParams['error_msg'];
            }
            return false;
        }
        return Mage::helper('core')->stripTags($response);
    }

    /**
     * Return customer quote
     *
     * @param bool $cancelOrder
     * @param string $errorMsg
     */
    protected function _returnCustomerQuote($cancelOrder = false, $errorMsg = '')
    {
        $incrementId = Mage::getSingleton('authorizenet/directpost_session')->getLastOrderIncrementId();
        if (
            $incrementId
            && Mage::getSingleton('authorizenet/directpost_session')->isCheckoutOrderIncrementIdExist($incrementId)
        ) {
            /* @var $order Mage_Sales_Model_Order */
            $order = Mage::getModel('sales/order')->loadByIncrementId($incrementId);
            if ($order->getId()) {
                $quote = Mage::getModel('sales/quote')->load($order->getQuoteId());
                if ($quote->getId()) {
                    $quote
                        ->setIsActive(1)
                        ->setReservedOrderId(null)
                        ->save()
                    ;
                    Mage::getSingleton('checkout/type_onepage')->replaceQuote($quote);
                }
                Mage::getSingleton('authorizenet/directpost_session')->removeCheckoutOrderIncrementId($incrementId);
                Mage::getSingleton('authorizenet/directpost_session')->unsetData('quote_id');
                if ($cancelOrder) {
                    $order->registerCancellation($errorMsg)->save();
                }
            }
        }
    }

    /**
     * Parse request string to array(key => value) format
     *
     * @param string $requestParamString
     *
     * @return array
     */
    protected function _parseRequestParams($requestParamString)
    {
        $result = array();
        $arrayParam = preg_split("#[/]#", $requestParamString, -1, PREG_SPLIT_NO_EMPTY);
        for ($i = 0; $i < count($arrayParam); $i+=2) {
            $result[$arrayParam[$i]] = $arrayParam[$i + 1];
        }
        return $result;
    }
}