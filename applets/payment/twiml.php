<?php
define('PAYMENT_ACTION', 'paymentAction');
define('PAYMENT_COOKIE', 'payment-' . AppletInstance::getInstanceId());
define('STATE_GATHER_CARD', 'stateGatherCard');
define('STATE_GATHER_MONTH', 'stateGatherMonth');
define('STATE_GATHER_YEAR', 'stateGatherYear');
define('STATE_GATHER_CVC', 'stateGatherCvc');
define('STATE_SEND_PAYMENT', 'stateSendPayment');

$response = new Response();

$state = array(
	PAYMENT_ACTION => STATE_GATHER_CARD,
	'card' => array()
);

$api_key = PluginData::get('api_key');
$amount = AppletInstance::getValue('amount');
$description = AppletInstance::getValue('description');
$digits = isset($_REQUEST['Digits']) ? $_REQUEST['Digits'] : false;
$finishOnKey = '#';
$timeout = 15;
$method = 'POST';
$card_errors = array(
	'invalid_number' => STATE_GATHER_CARD,
	'incorrect_number' => STATE_GATHER_CARD,
	'invalid_expiry_month' => STATE_GATHER_MONTH,
	'invalid_expiry_year' => STATE_GATHER_YEAR,
	'expired_card' => STATE_GATHER_CARD,
	'invalid_cvc' => STATE_GATHER_CVC,
	'incorrect_cvc' => STATE_GATHER_CVC
);

if(isset($_COOKIE[PAYMENT_COOKIE])) {
	$state = json_decode(str_replace(', $Version=0', '', $_COOKIE[PAYMENT_COOKIE]), true);
	if(is_object($state))
		$state = get_object_vars($state);
}

if($digits !== false) {
	switch($state[PAYMENT_ACTION]) {
		case STATE_GATHER_CARD:
			$state['card']['number'] = $digits;
			$state[PAYMENT_ACTION] = STATE_GATHER_MONTH;
			break;
		case STATE_GATHER_MONTH:
			$state['card']['exp_month'] = $digits;
			$state[PAYMENT_ACTION] = STATE_GATHER_YEAR;
			break;
		case STATE_GATHER_YEAR:
			$state['card']['exp_year'] = $digits;
			$state[PAYMENT_ACTION] = STATE_GATHER_CVC;
			break;
		case STATE_GATHER_CVC:
			$state['card']['cvc'] = $digits;
			$state[PAYMENT_ACTION] = STATE_SEND_PAYMENT;
			break;
	}
}

switch($state[PAYMENT_ACTION]) {
	case STATE_GATHER_CARD:
		$gather = $response->addGather(compact('finishOnKey', 'timeout', 'method'));
		$gather->addSay("Please enter your credit card number followed by the pound sign.");
		break;
	case STATE_GATHER_MONTH:
		$gather = $response->addGather(compact('finishOnKey', 'timeout', 'method'));
		$gather->addSay("Please enter the month of the card's expiration date followed by the pound sign.");
		break;
	case STATE_GATHER_YEAR:
		$gather = $response->addGather(compact('finishOnKey', 'timeout', 'method'));
		$gather->addSay("Please enter the year of the expiration date followed by the pound sign.");
		break;
	case STATE_GATHER_CVC:
		$gather = $response->addGather(compact('finishOnKey', 'timeout', 'method'));
		$gather->addSay("Please enter the card's security code followed by the pound sign.");
		break;
	case STATE_SEND_PAYMENT:
		require_once(dirname(dirname(dirname(__FILE__))) . '/stripe-php/lib/Stripe.php');
		Stripe::setApiKey($api_key);
		try {
			$charge = Stripe_Charge::create(array(
				'card' => $state['card'],
				'amount' => $amount,
				'currency' => 'usd',
				'description' => $description
			));
			if($charge->paid && true === $charge->paid) {
				setcookie(PAYMENT_COOKIE);
				$next = AppletInstance::getDropZoneUrl('success');
				if(!empty($next))
					$response->addRedirect($next);
				$response->Respond();
				die;
			}
		}
		catch(Exception $e) {
			$error = $e->getCode();
			$response->addSay($e->getMessage());
			if(array_key_exists($error, $card_errors)) {
				$state[PAYMENT_ACTION] = $card_errors[$error];
				$response->addRedirect();
			}
			else {
				setcookie(PAYMENT_COOKIE);
				$next = AppletInstance::getDropZoneUrl('fail');
				if(!empty($next))
					$response->addRedirect($next);
				$response->Respond();
				die;
			}
		}
}
setcookie(PAYMENT_COOKIE, json_encode($state), time() + (5 * 60));
$response->Respond();
