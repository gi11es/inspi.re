<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Displays the information about premium membership
*/

require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();
$page = new Page('PREMIUM', 'HOME', $user);

$page->setTitle('<translate id="PREMIUM_PAGE_TITLE">Premium membership on inspi.re</translate>');

$page->addJavascriptVariable('request_currency_payment', $REQUEST['CURRENCY_PAYMENT']);

$page->startHTML();

echo '<div class="hint hintmargin">',
	 '<div class="hint_title">',
	 '<translate id="PREMIUM_HINT_TITLE">',
	 'Premium membership',
	 '</translate>',
	 '</div> <!-- hint_title -->',
	 '<translate id="PREMIUM_HINT_BODY">',
	 'Gives you access to exclusive features and unlimited storage!',
	 '</translate>',
	 '</div> <!-- hint -->',

	 '<p>',
	 '<translate id="PREMIUM_INTRO">',
	 'Standard membership is limited to 70 artworks hosted at once. As a standard member, you need to delete older entries in order to post new ones. With premium membership, you get <b>unlimited storage</b> and you can keep as many entries as you like on your account.',
	 '<br/><br/>',
	 'Premium membership can also be purchased for others. Give it as a gift to friends and family or sponsor your favorite artists on inspi.re	 by purchasing a premium membership code and activating it on their profile.',
	 '<br/><br/>',
	 'In addition to unlimited storage, premium membership also gives access to the exciting extra features detailed below.',
	 '</translate>',
	 '</p>',

	 '<div class="hint hintmargin">',
	 '<div class="hint_title">',
	 '<translate id="PREMIUM_ADFREE_HINT">',
	 'An ad-free experience',
	 '</translate>',
	 '</div> <!-- hint_title -->',
	 '</div> <!-- hint -->',

	 '<p class="explanations clearboth">',
	 '<translate id="PREMIUM_ADFREE_EXPLANATIONS">',
	 'Premium members have the ability to fully <b>disable the advertisements shown throughout the website</b>. This new option appears on the <a href="'.$PAGE['SETTINGS'].'?lid='.$user->getLid().'">settings</a> page once you\'ve activated your premium membership.',
	 '</translate>',
	 '</p>',

	 '<div class="hint hintmargin">',
	 '<div class="hint_title">',
	 '<translate id="PREMIUM_STATISTICS_HINT">',
	 'Advanced statistics showing your performance',
	 '</translate>',
	 '</div> <!-- hint_title -->',
	 '</div> <!-- hint -->',

	 '<div id="charts">',
	 '<img id="chart_left" src="',$GRAPHICS_PATH,'premium_chart_sample_1.png">',
	 '<img id="chart_middle" src="',$GRAPHICS_PATH,'premium_chart_sample_3.png">',
	 '<img id="chart_right" src="',$GRAPHICS_PATH,'premium_chart_sample_2.png">',
	 '</div>',

	 '<p class="clearboth">',
	 '<translate id="PREMIUM_STATISTICS_EXPLANATIONS">',
	 '<b>Visualize instantly the progression of your performance over time. See how the average amount of stars your entries receive fluctuates. Get to know the average percentage of people you\'ve outperformed in competitions. Access the exact breakdown of votes on your entries.</b><br/><br/>	You can even <b>download the underlying data in CSV format</b> if you want to use it in Excel or any spreadsheet software. Make your own calculations and get to assess your own level better,',
	 '</translate>',
	 '</p>',

	 '<div class="hint hintmargin clearboth">',
	 '<div class="hint_title">',
	 '<translate id="PREMIUM_CUSTOM_URL_TITLE">Custom URL for your profile</translate>',
	 '</div> <!-- hint_title -->',
	 '</div> <!-- hint -->',

	 '<p class="clearboth canvas">',
	 '<translate id="PREMIUM_CUSTOM_URL_EXPLANATIONS">',
	 'Premium members can pick a custom URL for their profile, such as <a target="_blank" href="http://inspi.re/gilles">http://inspi.re/gilles</a> Custom URLs are easier to remember and make your online presence on inspi.re more personalized. You can pick anything you like as a custom URL, as long as it isn\'t already in use by another premium member,',
	 '</translate>',
	 '</p>',

	 '<div class="hint hintmargin clearboth">',
	 '<div class="hint_title">',
	 '<translate id="PREMIUM_CANVAS_TITLE">Custom markup for your canvas print sales</translate>',
	 '</div> <!-- hint_title -->',
	 '</div> <!-- hint -->',

	 '<p class="clearboth canvas">',
	 '<translate id="PREMIUM_CANVAS_EXPLANATIONS">',
	 'In partnership with <a target="_blank" href="http://www.canvasphoto.us">CanvasPhoto.us</a> we offer the ability for members to sell canvas prints of the art they post on inspi.re. Standard members have the choice between selling the canvas prints at retail price - earning no margin - or to add a 10% markup that can later be transferred to their paypal account. <b>Premium members have the ability to set any value for the markup they add to the retail price.</b>',
	 '</translate>',
	 '</p>',

	 '<div class="hint hintmargin clearboth">',
	 '<div class="hint_title">',
	 '<translate id="PREMIUM_FREE_ADS_TITLE">Free advertising for your communities</translate>',
	 '</div> <!-- hint_title -->',
	 '</div> <!-- hint -->',

	 '<p class="clearboth" style="margin-bottom: 0;">',
	 '<translate id="PREMIUM_FREE_ADS_EXPLANATIONS">',
	 'As a premium member, <b>the communities you administrate are automatically promoted with free advertisement</b> displayed throughout the website. It\'s a great way to bring the attention to your communities and to attract new members. You can see just below an example of what the free community advertising looks like,',
	 '</translate>',
	 '</p>',

	 '<ad ad_id="PREMIUM"/>',

	 '<div class="hint hintmargin clearboth">',
	 '<div class="hint_title">',
	 '<translate id="PREMIUM_PAYPAL_TITLE">Purchase with a credit card or a paypal account</translate>',
	 '</div> <!-- hint_title -->',
	 '<img id="credit_cards" src="',$GRAPHICS_PATH,'creditcards.gif">',
	 '</div> <!-- hint -->',

	 '<p>',
	 '<translate id="PREMIUM_PAYPAL_BODY">',
	 'Upon clicking on one of the buttons below, you will be redirected to paypal for payment (a paypal account is not required). After your payment has been processed, you will receive a premium membership code by email. That code (which you\'ll then use to activate your premium membership or to sponsor someone)  will be sent to the email address you enter on paypal, or the one already registered with your paypal account. This is important, since you might be using a different email address for paypal and for your inspi.re account.',
	 '</translate>',
	 '</p>',

	 '<p class="donation_options">';

$ip_history = $user->getIpHistory();
$last_ip = array_shift(array_keys($ip_history));
$record = @geoip_record_by_name($last_ip);

$currency = $CURRENCY['EUR'];

if (isset($record['country_code3'])) switch (strtoupper($record['country_code3'])) {
	case 'USA':
	case 'GUM':
	case 'HTI':
	case 'MHL':
	case 'FSM':
	case 'MNP':
	case 'PLW':
	case 'PAN':
	case 'PRI':
	case 'VGB':
	case 'TCA':
	case 'UMI':
	case 'VGB':
	case 'VIR':
		$currency = $CURRENCY['USD'];
		break;
	case 'CAN':
		$currency = $CURRENCY['CAD'];
		break;
	case 'AUS':
	case 'CXR':
	case 'CCK':
	case 'HMD':
	case 'KIR':
	case 'NRU':
	case 'NFK':
	case 'TUV':
		$currency = $CURRENCY['AUD'];
		break;
	case 'NZL':
	case 'COK':
	case 'NIU':
	case 'PCN':
	case 'TKL':
		$currency = $CURRENCY['NZD'];
		break;
	case 'GBR':
	case 'SGS':
		$currency = $CURRENCY['GBP'];
		break;
}

if (isset($record['country_code'])) {
	$urladdition = '&lc='.$record['country_code'];
} else $urladdition = '';

echo '<div id="donation_options">';

echo UI::RenderCurrencyPayment($user, $currency, $urladdition);

echo '</div> <!-- donation_options -->',

	 '<div id="currency_options">',
	 '<translate id="PREMIUM_SWITCH_CURRENCY">',
	 'Switch the currency to <a href="javascript:switchToCurrency(',$CURRENCY['EUR'],');">Euros</a>, <a href="javascript:switchToCurrency(',$CURRENCY['USD'],');">US dollars</a>, <a href="javascript:switchToCurrency(',$CURRENCY['CAD'],');">Canadian dollars</a>, <a href="javascript:switchToCurrency(',$CURRENCY['AUD'],');">Australian dollars</a>, <a href="javascript:switchToCurrency(',$CURRENCY['NZD'],');">New-Zealand dollars</a>, <a href="javascript:switchToCurrency(',$CURRENCY['GBP'],');">British pounds</a>,',
	 '</translate>',
	 '</div> <!-- currency_options -->',

	 '<div class="clearboth">',

	 '<div class="hint hintmargin">',
	 '<div class="hint_title">',
	 '<translate id="PREMIUM_BANK_TRANSFER_TITLE">Purchase with a bank transfer (Europe only)</translate>',
	 '</div> <!-- hint_title -->',
	 '</div> <!-- hint -->',

	 '<p class="clearboth canvas">',
	 '<translate id="PREMIUM_BANK_TRANSFER_INTRO">',
	 'If you have a bank account in Europe, you can make your purchase with a bank transfer in <b>EUROS</b> (we don\'t accept any other currency by bank transfer) to the following bank account:',
	 '</translate><br/><br/>',
	 '<b><translate id="PREMIUM_BANK_TRANSFER_HOLDER">Account holder</translate>: Gilles Dubuc</b><br/>',
	 '<b><translate id="PREMIUM_BANK_TRANSFER_BANK">Bank</translate>: Société Générale</b><br/>',
	 '<b><translate id="PREMIUM_BANK_TRANSFER_ADDRESS">Bank address</translate>: Société Générale - 29 Bd HAUSSMANN - 75009 Paris - FRANCE</b><br/>',
	 '<br/>',
	 '<b>BIC-SWIFT: SOGEFRPP</b><br/>',
	 '<b>IBAN: FR76 30003 00733 00051644848 76</b><br/><br/>',
	 '<translate id="PREMIUM_BANK_TRANSFER_BODY">',
	 'Please refer to the pricing information in euros mentioned above on this page to see how much you need to send. Please specify your user name or email address clearly in the bank transfer information, otherwise we won\'t be able to send you the premium membership code. Due to the delays associated with bank transfers, it will take a few days before you receive the premium membership code by email if you do a bank transfer,',
	 '</translate>',
	 '</p>',

	 '<div class="hint hintmargin clearboth" id="free_membership">',
	 '<div class="hint_title">',
	 '<translate id="PREMIUM_FREE">Ways to get free premium membership</translate>',
	 '</div> <!-- hint_title -->',
	 '</div> <!-- hint -->',
	 '<p class="clearboth canvas">',
	 '<b>- <translate id="PREMIUM_COMMENT_TITLE">Comment more than other members</translate></b><br/><br/>',
	 '<translate id="PREMIUM_COMMENT_BODY">If you write enough comments and critiques on other people\'s artworks to be nominated as <a href="/Members/s3-l',$user->getLid(),'">the most helpful member of the hour</a>, you will receive some free premium membership. We give away more than 3 weeks worth of premium membership to the most helpful members every day.</translate><br/><br/>',
 	 '<b>- <translate id="PREMIUM_INVITE_TITLE">Invite people to join inspi.re</translate></b><br/><br/>',
	 '<translate id="PREMIUM_INVITE_BODY">By <a href="',$PAGE['INVITE'],'?lid=',$user->getLid(),'">spreading the word about inspi.re</a>, you can earn free premium membership if the people who join thanks to you become active. There is no limit to that offer and you earn 7 days of premium membership for each new active member!</translate><br/><br/>',
	 '<b>- <translate id="PREMIUM_BRING_BACK_TITLE">Bring back old friends</translate></b><br/><br/>',
	 '<translate id="PREMIUM_BRING_BACK_BODY">By <a href="',$PAGE['BRING_BACK'],'?lid=',$user->getLid(),'">bringing back members of inspi.re who haven\'t visited the website for a while</a>, you can earn free premium membership. Each person you appeal to who logs back into his/her inspi.re account will make you earn one day of free premium membership.</translate><br/><br/>';
	 
if ($user->getLid() == $LANGUAGE['EN']) {
	echo '<b>- <translate id="PREMIUM_SURVEY">Answer surveys (English-speaking countries only)</translate></b><br/><br/>',
	'<translate id="PREMIUM_SURVEY_EXPLANATION">Simply select a survey in the list below and answer it. You will receive the quoted duration of free premium membership upon completion of the survey. There can be a delay of a few minutes between finishing the survey and receiving the premium membership (for which you will be notified by an alert).</translate><br/><br/>',
	'<script type="text/javascript" src="http://data.cpalead.com/asd/asd_load.php?pub=17004&subid=',$user->getUid(),'"></script>';
}
	 
echo '</p>',
	 '</div> <!-- clearboth -->';



$page->endHTML();
$page->render();
?>
