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
        $ppTestmode = $payconfig->devtest;

        $details = $this->details;
        $amount = $details['amount'];
        $item_num = $details['orderid'];
        $invoiceNo = $details['invoiceNo'];
        $user = JFactory::getUser($details['user_id']);

        $link_status = JURI::base() . 'index.php?option=com_jblance&task=membership.returnafterpayment&gateway=paysera';

        $link_cancel = JRoute::_(JURI::base() . 'index.php?option=com_jblance&view=membership&layout=thankpayment&type=cancel');

        $this->paysera_url = ($ppTestmode) ? 'https://www.paysera.com/pay/' : 'https://www.paysera.com/pay/';

        $this->add_field('projectid', $projectId);
        $this->add_field('sign_password', $signPassword);
        $this->add_field('accepturl', $link_status);
        $this->add_field('callbackurl', $link_status);
        $this->add_field('cancelurl', $link_cancel);
        $this->add_field('orderid', $item_num);
        $this->add_field('invoiceNo', $invoiceNo);
        $this->add_field('p_firstname', $user->name);
        $this->add_field('p_lastname', $user->email);
        $this->add_field('amount', $amount);
        $this->add_field('currency', $ppCurrency);
        $this->add_field('test', $ppTestmode);
        $this->add_field('version', '1.6');
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
        $payconfig = $this->payconfig;
        $ppTestmode = $payconfig->ppTestmode;
        $this->paysera_url = ($ppTestmode) ? 'https://www.paysera.com/pay/' : 'https://www.paysera.com/pay/';
        $return = array();
        if ($this->validate_ipn($data)) {
            $invoice = array_key_exists('invoiceNo', $data) ? $data['invoiceNo'] : '';  // get the invoice number from the post variable
            $return['success'] = true;
            $return['sign_password'] = $data;
        } else {
            $return['success'] = false;
        }

        return $return;
    }
    function validate_ipn($data) {
        $payconfig  = $this->payconfig;
	$payconfig->signPassword;
             return array(
            'data' => $data,
            'sign' => md5($data . $this->signPassword),
        );
    }

}
