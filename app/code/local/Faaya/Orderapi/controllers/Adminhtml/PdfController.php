<?php
class Faaya_Orderapi_Adminhtml_PdfController extends Mage_Adminhtml_Controller_Action
{
		public function invoicepdfAction()
		{
            //echo '<pre>';
		    ob_start();
            $invoiceId = Mage::app()->getRequest()->getParam('id');
            $invoice = Mage::getModel('sales/order_invoice')->load($invoiceId);
            $order = $invoice->getOrder();
            //echo $order->getId();
            $order  = Mage::getModel('sales/order')->load($order->getId());
            //$invoicePdf = Mage::helper('orderapi')->getInvoicePdfHtml($invoice,$order);
            //echo $invoicePdf;
            //exit;
            $emailTemplate  = Mage::getModel('core/email_template')->load(1);
            $vars = array('order' => $order,'invoice'=>$invoice);
            //print_R($vars);
            //print_R($emailTemplate->getData());
            //exit;
            $processedTemplate =  $emailTemplate->getProcessedTemplate($vars);
            $txt = $processedTemplate;
            //var_dump($txt);
            //exit;
            $pdf = new TCPDF_TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            // set document information
            $pdf->SetCreator(PDF_CREATOR);
            // remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            // set margins
            $pdf->SetMargins(5, 10, 10);
            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->AddPage();
            $pdf->writeHTML($txt, true, false, true, false, '');
            //Close and output PDF document
            $pdf->Output('invoice_'.time().'.pdf', 'D');
		}
        public function creditmemopdfAction()
        {
            ob_start();
            $creditmemoId = Mage::app()->getRequest()->getParam('id');
            $creditmemo = Mage::getModel('sales/order_creditmemo')->load($creditmemoId);
            $order = $creditmemo->getOrder();
            $order  = Mage::getModel('sales/order')->load($order->getId());
            //print_R($order->getData());
            $emailTemplate  = Mage::getModel('core/email_template')->load(3);
            $vars = array('order' => $order,'creditmemo'=>$creditmemo);
            $processedTemplate =  $emailTemplate->getProcessedTemplate($vars);
            $txt = $processedTemplate;
            //echo $txt;
            //exit;
            $pdf = new TCPDF_TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            // set document information
            $pdf->SetCreator(PDF_CREATOR);
            // remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            // set margins
             $pdf->SetMargins(5, 10, 10);
            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->AddPage();
            $pdf->writeHTML($txt, true, false, true, false, '');
            //Close and output PDF document
            $pdf->Output('creditmemo_'.time().'.pdf', 'D');
        }
         public function shipmentpdfAction()
        {
            ob_start();
            $shipmentId = Mage::app()->getRequest()->getParam('id');
            $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
            $order = $shipment->getOrder(); 
            $order  = Mage::getModel('sales/order')->load($order->getId());
            //print_R($order->getData());
            $emailTemplate  = Mage::getModel('core/email_template')->load(2);
            $vars = array('order' => $order,'shipment'=>$shipment);
            $processedTemplate =  $emailTemplate->getProcessedTemplate($vars);
            $txt = $processedTemplate;
            //echo $txt;
            //exit;
            $pdf = new TCPDF_TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
            // set document information
            $pdf->SetCreator(PDF_CREATOR);
            // remove default header/footer
            $pdf->setPrintHeader(false);
            $pdf->setPrintFooter(false);
            // set default monospaced font
            $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
            // set margins
             $pdf->SetMargins(5, 10, 10);
            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
            // set image scale factor
            $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
            $pdf->AddPage();
            $pdf->writeHTML($txt, true, false, true, false, '');
            //Close and output PDF document
            $pdf->Output('shipment_'.time().'.pdf', 'D');
        }
}
