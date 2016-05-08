<?php

/**
 * Description of class
 *
 * @author Ovidijus Baublys
 */
require_once(JPATH_SITE . '/components/com_jblance/gateways/includes/libtopay/WebToPay.php');
defined('_JEXEC') or die('Restricted access');

class paysera_class {

    var $payconfig = array();
    var $details = array();
    var $fields = array();
    var $ipn_data = array();

    function paysera_class($payconfig, $details) {
        $this->payconfig = $payconfig;
        $this->details = $details;
    }

    function payseraPayment() {

        $payconfig = $this->payconfig;
        $projectId = $payconfig->projectId;
        $signPassword = $payconfig->signPassword;
        $ppCurrency = $payconfig->psCurrency;
        $pstestmode = $payconfig->test;

        $details = $this->details;
        $taxrate = $details['taxrate'];
        $amount = $details['amount'];
        $totamt = (float) ($amount + $amount * ($taxrate / 100));
        $totamt = round($totamt, 2)*100;
        $item_num = $details['orderid'];
        $invoiceNo = $details['invoiceNo'];
        $user = JFactory::getUser($details['user_id']);

        $link_status = JURI::base() . 'index.php?option=com_jblance&task=membership.returnafterpayment&gateway=paysera';

        $link_cancel = JRoute::_(JURI::base() . 'index.php?option=com_jblance&view=membership&layout=thankpayment&type=cancel');

        //$this->paysera_url = ($ppTestmode) ? 'https://www.paysera.com/pay/' : 'https://www.paysera.com/pay/';

        $data['projectid'] = $projectId;
        $data['sign_password'] = $signPassword;
        $data['accepturl'] = $link_status;
        $data['callbackurl'] = $link_status;
        $data['cancelurl'] = $link_cancel;
        $data['orderid'] = $item_num;
        $data['invoiceNo'] = $invoiceNo;
        $data['p_firstname'] = $user->name;
        $data['p_email'] = $user->email;
        $data['currency'] = $ppCurrency;
        $data['amount'] = $totamt;

        $data['test'] = 1;
        $data['version'] = '1.6';

        $request = WebToPay::redirectToPayment($data);

        $this->add_field('data', $request['data']);
        $this->add_field('sign', $request['sign']);
        $this->submit_paysera_post();
        ?>
        <script>
            document.paysera_form.submit();
        </script>
        <?php

    }

    function add_field($field, $value) {
        $this->fields["$field"] = $value;
    }

    function submit_paysera_post() {
        echo '<form method="post" name="paysera_form" action="' . $this->paysera_url . '">';
        foreach ($this->fields as $name => $value) {
            echo '<input type="hidden" name="' . $name . '" value="' . $value . '"/>';
        }
        echo JText::sprintf('COM_JBLANCE_PAYMENT_REDIRECTION_PAGE', 'Paysera');
        echo '<br><br><input type="submit" value="' . JText::_('COM_JBLANCE_PROCEED_FOR_PAYMENT') . '">';
        echo '</form>';
    }

    function payseraReturn($data) {
        print_r($data);
        try {
            $response = WebToPay::checkResponse($data, array(
                        'projectid' => 74747,
                        'sign_password' => 'a4a8a31750a23de2da88ef6a491dfd5c',
            ));
            print_r($response);
            $orderId = $response['orderid'];
            $totamt = $response['amount'];
            $currency = $response['currency'];

            if ($response['status'] == 1) {
                $invoice_num = $response['invoiceno'];  // get the invoice number from the post variable
                $return['success'] = true;
                $return['invoice_num'] = $invoice_num;
            } else
                $return['success'] = false;

            //print_r($return);exit;

            return $return;
        } catch (Exception $e) {
            echo get_class($e) . ': ' . $e->getMessage();
        }
    }

}
