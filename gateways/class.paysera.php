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
        $projectId      =   $payconfig->projectId;
        $signPassword   =   $payconfig->signPassword;
        $ppCurrency     =   $payconfig->psCurrency;
        $pstestmode     =   $payconfig->test;

        $details = $this->details;
        $amount = $details['amount'];
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
        $data['p_lastname'] = $user->email;
        $data['currency'] = $ppCurrency;
        $data['amount'] = $amount * 100;
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

//    function payseraReturn($data) {
//        $payconfig = $this->payconfig;
//        $pstestmode = $payconfig->$pstestmode;
//        $request = WebToPay::validateAndParseData($data, $this->projectId, $this->signPassword);
//        $return = array();
//        if ($this->validate_ipn($request)) {
//            $item_num = array_key_exists('orderid', $data) ? $data['orderid'] : '';		// get the invoice number from the post variable
//            $return['success'] = true;
//            $return['orderid'] = $item_num;
//        } else {
//            $return['success'] = false;
//        }
//
//        return $return;
//    }
//
//    function validate_ipn($data) {
//        $payconfig = $this->payconfig;
//        $pstestmode = $payconfig->$pstestmode;
//        $request = WebToPay::validateAndParseData($data, $this->projectId, $this->signPassword);
//        if ($data['status'] === 1) {
//            return TRUE;
//        } else {
//            return FALSE;
//        }
//    } 
    }
    

