<?php
// Order View Page button
class Faaya_Orderapi_Model_Pdfobserver
{
    public function orderPageButton( Varien_Event_Observer $observer )
    { 
      $block = $observer->getEvent()->getData( 'block' );
        if(get_class($block) =='Mage_Adminhtml_Block_Sales_Order_Invoice_View'
            && $block->getRequest()->getControllerName() == 'sales_order_invoice')
        {
            $invoiceId = Mage::app()->getRequest()->getParam('invoice_id');
            $block->removeButton('print');
            $block->addButton('pdfprint', array(
                'label'     => 'Print',
                'onclick'   => 'setLocation(\'' . $block->getUrl('orderapi/adminhtml_pdf/invoicepdf',array('id' => $invoiceId)) . '\')',
                'class'     => 'go'
            ));
        }
        elseif(get_class($block) =='Mage_Adminhtml_Block_Sales_Order_Creditmemo_View'
            && $block->getRequest()->getControllerName() == 'sales_order_creditmemo')
        {
            $creditmemoId = Mage::app()->getRequest()->getParam('creditmemo_id');
            $block->removeButton('print');
            $block->addButton('pdfprint', array(
                'label'     => 'Print',
                'onclick'   => 'setLocation(\'' . $block->getUrl('orderapi/adminhtml_pdf/creditmemopdf',array('id' => $creditmemoId)) . '\')',
                'class'     => 'go'
            ));
        }
        elseif(get_class($block) =='Mage_Adminhtml_Block_Sales_Order_Shipment_View'
            && $block->getRequest()->getControllerName() == 'sales_order_shipment')
        {
            $shipmentId = Mage::app()->getRequest()->getParam('shipment_id');
            $block->removeButton('print');
            $block->addButton('pdfprint', array(
                'label'     => 'Print',
                'onclick'   => 'setLocation(\'' . $block->getUrl('orderapi/adminhtml_pdf/shipmentpdf',array('id' => $shipmentId)) . '\')',
                'class'     => 'go'
            ));
        }
    }
}
?>