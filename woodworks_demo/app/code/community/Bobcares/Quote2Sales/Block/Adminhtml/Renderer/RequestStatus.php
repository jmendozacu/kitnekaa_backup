<?php

/**
 * Class includes all functions for displaying the status of the request
 * @created on 21st Feb 2015
 * @category Renderer class
 * @author Bobcares
 *
 */
class Bobcares_Quote2Sales_Block_Adminhtml_Renderer_RequestStatus extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract {

    /**
     * @desc This function is used for displaying the status in admin panel grid
     * @param Varien_Object $row : input data (request id)
     * @return string : status of the request and the link to corresponding quote/order
     */
    public function render(Varien_Object $row) {
        $requestStatus;

        //If there is a request_id id then fetch the status of that request
        if ($row->getData('request_id') != NULL) {
            $requestId = $row->getData('request_id');

            //Get data from DB
            $requestModel = Mage::getModel('quote2sales/request');
            $userData = $requestModel->getUserData($requestId);
            $requestData = $requestModel->getRequestData($requestId);
            $requestStatusDB = $userData[0]["status"];

            //If there is no quote/order for the request then display the status as "Waiting"
            //Else display the status based on the quote and order
            if ($requestData == NULL) {
                $requestStatus = $requestStatusDB;
            } else {

                //Fetch each quote/order data
                foreach ($requestData as $value) {
                    $quoteId = $value['quote_id'];
                    $orderId = $value['order_id'];
                    $status = $value['status'];

                    //If there is an order then display the status as converted to order and the link to that order
                    //Else display the status as converted to quote and link to that quote
                    if ($orderId != NULL && $orderId != 0) {
                        $requestStatus .= $status . " : <a href='" . Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/view", array('order_id' => $orderId)) . "'>View Order</a>" . "</br>";
                    } elseif ($quoteId != NULL && $quoteId != 0) {
                        $quote = Mage::getModel('sales/quote')->load($quoteId);
                        $quoteStatus = $quote->getIsActive();
                        $orderIncrementalId = $quote->getReservedOrderId();

                        //if the quote is converted to an order then the quote status will be 0, so fetch the order details and update the table
                        //Else set status from DB and also set link to the quote
                        if ($quoteStatus == 0) {
                            $order = Mage::getModel('Mage_Sales_Model_Order');
                            $order->loadByIncrementId($orderIncrementalId);
                            $orderId = $order->getEntityId();

                            //If there is an order id then update the same in DB
                            if ($orderId != "") {

                                //Update status of the request in DB
                                $requestModel = Mage::getModel('quote2sales/request');
                                $requestModel->addOrderId("Converted To Order", $quoteId, $orderId);
                                $requestModel->updateRequestStatus("Converted To Order", $id);
                                $requestStatus .= "Converted To Order : <a href='" . Mage::helper('adminhtml')->getUrl("adminhtml/sales_order/view", array('order_id' => $orderId)) . "'>View Order</a>" . "</br>";
                            } else {
                                $requestStatus .= $status . " : <a href='" . Mage::helper('adminhtml')->getUrl("quote2sales/adminhtml_quote/view", array('quote_id' => $quoteId)) . "'>View Quote</a>" . "</br>";
                            }
                        } else {
                            $requestStatus .= $status . " : <a href='" . Mage::helper('adminhtml')->getUrl("quote2sales/adminhtml_quote/view", array('quote_id' => $quoteId)) . "'>View Quote</a>" . "</br>";
                        }
                    } else {
                        return Mage::helper('quote2sales')->__('NO STATUS ASSIGNED');
                    }
                }
            }
            return $requestStatus;
        } else {
            return Mage::helper('quote2sales')->__('NO STATUS ASSIGNED');
        }
    }

}
