<?php
define('PAYMENT_ACTION', 'paymentAction');
define('PAYMENT_COOKIE', 'payment-' . AppletInstance::getInstanceId());
define('STATE_GATHER_CARD', 'stateGatherCard');
define('STATE_GATHER_MONTH', 'stateGatherMonth');
define('STATE_GATHER_YEAR', 'stateGatherYear');
define('STATE_GATHER_CVC', 'stateGatherCvc');
define('STATE_SEND_PAYMENT', 'stateSendPayment');

$response = new TwimlResponse;

$state = array(
	PAYMENT_ACTION => STATE_GATHER_CARD,
	'card' => array()
);

$ci =& get_instance();
$settings = PluginData::get('settings');
$amount = AppletInstance::getValue('amount');
$description = AppletInstance::getValue('description');
$digits = clean_digits($ci->input->get_post('Digits'));
$finishOnKey = '#';
$timeout = 15;

$card_errors = array(
	'invalid_number' => STATE_GATHER_CARD,
	'incorrect_number' => STATE_GATHER_CARD,
	'invalid_expiry_month' => STATE_GATHER_MONTH,
	'invalid_expiry_year' => STATE_GATHER_YEAR,
	'expired_card' => STATE_GATHER_CARD,
	'invalid_cvc' => STATE_GATHER_CVC,
	'incorrect_cvc' => STATE_GATHER_CVC
);

if(is_object($settings))
	$settings = get_object_vars($settings);

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
			$state[PAYMENT_ACTION] = $settings['require_cvc'] ? STATE_GATHER_CVC : STATE_SEND_PAYMENT;
			break;
		case STATE_GATHER_CVC:
			$state['card']['cvc'] = $digits;
			$state[PAYMENT_ACTION] = STATE_SEND_PAYMENT;
			break;
	}
}

switch($state[PAYMENT_ACTION]) {
	case STATE_GATHER_CARD:
		$gather = $response->gather(compact('finishOnKey', 'timeout'));
		$gather->say($settings['card_prompt']);
		break;
	case STATE_GATHER_MONTH:
		$gather = $response->gather(compact('finishOnKey', 'timeout'));
		$gather->say($settings['month_prompt']);
		break;
	case STATE_GATHER_YEAR:
		$gather = $response->gather(compact('finishOnKey', 'timeout'));
		$gather->say($settings['year_prompt']);
		break;
	case STATE_GATHER_CVC:
		$gather = $response->gather(compact('finishOnKey', 'timeout'));
		$gather->say($settings['cvc_prompt']);
		break;
	case STATE_SEND_PAYMENT:
		require_once(dirname(dirname(dirname(__FILE__))) . '/stripe-php/lib/Stripe.php');
		Stripe::setApiKey($settings['api_key']);
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
					$response->redirect($next);
				$response->respond();
				die;
			}
		}
		catch(Exception $e) {
			$error = $e->getCode();
			$response->say($e->getMessage());
			if(array_key_exists($error, $card_errors)) {
				$state[PAYMENT_ACTION] = $card_errors[$error];
				$response->redirect();
			}
			else {
				setcookie(PAYMENT_COOKIE);
				$next = AppletInstance::getDropZoneUrl('fail');
				if(!empty($next))
					$response->redirect($next);
				$response->respond();
				die;
			}
		}
}
setcookie(PAYMENT_COOKIE, json_encode($state), time() + (5 * 60));
$response->respond();
