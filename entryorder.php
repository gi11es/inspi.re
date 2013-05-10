<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Page where a canvas print of an entry can be ordered
*/

require_once(dirname(__FILE__).'/entities/competition.php');
require_once(dirname(__FILE__).'/entities/entry.php');
require_once(dirname(__FILE__).'/entities/picture.php');
require_once(dirname(__FILE__).'/entities/picturefile.php');
require_once(dirname(__FILE__).'/entities/theme.php');
require_once(dirname(__FILE__).'/entities/user.php');
require_once(dirname(__FILE__).'/entities/userlevellist.php');
require_once(dirname(__FILE__).'/utilities/page.php');
require_once(dirname(__FILE__).'/utilities/persistenttoken.php');
require_once(dirname(__FILE__).'/utilities/string.php');
require_once(dirname(__FILE__).'/utilities/token.php');
require_once(dirname(__FILE__).'/utilities/ui.php');
require_once(dirname(__FILE__).'/constants.php');
require_once(dirname(__FILE__).'/settings.php');

$user = User::getSessionUser();
$uid = $user->getUid();

$levels = UserLevelList::getByUid($user->getUid());
$ispremium = in_array($USER_LEVEL['PREMIUM'], $levels);

function leave($is_vote) {
	global $PAGE;
	global $user;
	
	if ($is_vote)
		header('Location: '.$PAGE['VOTE'].'?lid='.$user->getLid());
	else
		header('Location: '.$PAGE['HALL_OF_FAME'].'?lid='.$user->getLid());
	exit(0);
}

$entry = null;

try {
	if (isset($_REQUEST['eid'])) {
		$entry = Entry::get($_REQUEST['eid']);
	} elseif (isset($_REQUEST['token'])) {
		$token = Token::get($_REQUEST['token']);
		$exploded = explode('-', $token);
		if (count($exploded) == 2) {
			$token_uid = $exploded[0];
			$eid = $exploded[1];
			if ($token_uid == $user->getUid() || $user->getUid() == $GOOGLE_UID)
				$entry = Entry::get($eid);
		}
	} elseif (isset($_REQUEST['persistenttoken'])) {
		$token = PersistentToken::get($_REQUEST['persistenttoken']);
		$exploded = explode('-', $token);
		if (count($exploded) == 2) {
			$token_uid = $exploded[0];
			$eid = $exploded[1];
			if ($token_uid == $user->getUid() || $user->getUid() == $GOOGLE_UID)
				$entry = Entry::get($eid);
		}
	}
} catch (EntryException $e) {} catch (TokenException $f) {} catch (PersistentTokenException $g) {}

if ($entry === null) leave(true);

$eid = $entry->getEid();

try {
	$author = User::get($entry->getUid());
	
	if (!$author->getAllowSales() && $entry->getUid() != $uid) leave(true);
} catch (UserException $e) {
	leave(true);
}

$cid = $entry->getCid();
$competition = Competition::get($cid);

if ($competition->getStatus() == $COMPETITION_STATUS['VOTING']) {
	$page = new Page('VOTE', 'COMPETITIONS', $user);
} elseif ($competition->getStatus() == $COMPETITION_STATUS['OPEN']) {
	$page = new Page('COMPETE', 'COMPETITIONS', $user);
	$page->addStyle('VOTE');
} else {
	$page = new Page('HALL_OF_FAME', 'COMPETITIONS', $user);
	$page->addStyle('VOTE');
}

$page->addJavascript('ENTRY_ORDER');
$page->addStyle('ENTRY_ORDER');

$markup = 0;
if ($user->getUid() != $entry->getUid()) $markup = $author->getMarkup();

$page->addJavascriptVariable('markup', $markup);
$page->addJavascriptVariable('real_markup', $author->getMarkup());

$page->addJavascriptVariable('quality_1_name', '<translate id="ENTRY_ORDER_QUALITY_1_NAME" escape="htmlentities">(1) Economy: Unstretched Polyester canvas, rolled and shipped in a mailing tube.</translate>');
$page->addJavascriptVariable('quality_2_name', '<translate id="ENTRY_ORDER_QUALITY_2_NAME" escape="htmlentities">(2) Ready to hang: Polyester canvas, stretched onto a 3/4" (1.9cm) wood frame. Ready to hang on a wall.</translate>');
$page->addJavascriptVariable('quality_3_name', '<translate id="ENTRY_ORDER_QUALITY_3_NAME" escape="htmlentities">(3) Gallery quality: Premium canvas, stretched onto a 1.5" (3.8cm) wood frame. Ready to hang on a wall.</translate>');

$picture = Picture::get($entry->getPid());
$picturefile = PictureFile::get($picture->getFid($PICTURE_SIZE['ORIGINAL']));

$width = $picturefile->getWidth();
$height = $picturefile->getHeight();
$ratio = max($width, $height) / min($width, $height);

$minimumsurface = array('small' => 1080000, 'medium' => 2160000, 'large' => 3840000);
$surface = $width*$height;

$page->startHTML();

if ($competition->getStatus() == $COMPETITION_STATUS['CLOSED']) {
	echo '<div class="hint hintmargin" id="entry_author">';
	
	$amount = $competition->getEntriesCount();
	
	if ($author->getStatus() == $USER_STATUS['BANNED']) {
		$rank = $entry->getBannedRank();
		if ($rank > $amount) $amount = $rank;
	} else $rank = $entry->getRank();

	echo UI::RenderEntryAuthor($user, $entry->getUid(), $rank, $amount, false, true);
	echo '</div> <!-- entry_author -->';
}

$theme = Theme::get($competition->getTid());

echo UI::RenderCompetitionShortDescription($user, $competition, ($competition->getStatus() == $COMPETITION_STATUS['CLOSED']?$PAGE['RANKED']:$PAGE['GRID']).'?cid='.$cid);

echo '<div class="hint hintmargin">';
echo '<div class="hint_title">';
echo '<translate id="ENTRY_ORDER_HINT_TITLE">';
echo 'Order a canvas print of this artwork';
echo '</translate>';
echo '</div> <!-- hint_title -->';
echo '<div class="hint_body">';
echo '<translate id="ENTRY_ORDER_HINT_BODY">';
echo 'Our partner <a href="http://www.canvasphoto.us" target="_blank">CanvasPhoto.us</a> will handle the manufacturing and shipping of the print. <b>All prices are quoted in US dollars</b>';
echo '</translate>';
echo '</div> <!-- hint_body -->';
echo '</div> <!-- hint -->';

echo '<div id="entry_container">';
echo '<picture size="huge" pid="'.$entry->getPid().'"/>';
echo '</div> <!-- entry_container -->';

if ($user->getUid() == $entry->getUid() && !$user->getAllowSales()) {
	echo '<div class="warning hintmargin abovemargin">';
	echo '<div class="warning_title">';
	echo '<translate id="ENTRY_ORDER_WARNING_NO_SALES">';
	echo 'Currently, you are the only person able to order this artwork as a canvas print';
	echo '</translate>';
	echo '</div> <!-- warning_title -->';
	echo '<div class="warning_body">';
	echo '<translate id="ENTRY_ORDER_WARNING_NO_SALES_BODY">';
	echo 'If you want to put your artworks for sale as canvas prints and earn a commission on the sales, activate the corresponding option on the <a href="'.$PAGE['SETTINGS'].'?lid='.$user->getLid().'">settings</a> page.';
	echo '</translate>';
	echo '</div> <!-- warning_body -->';
	echo '</div> <!-- warning -->';
} elseif ($user->getUid() == $entry->getUid() && $user->getAllowSales() && $user->getMarkup() > 0) {
	echo '<div class="warning hintmargin abovemargin">';
	echo '<div class="warning_title">';
	echo '<translate id="ENTRY_ORDER_WARNING_OWN_MARKUP">';
	echo 'Since you\'re looking at one of your own artworks, the markup you\'ve set up for your canvas sales doesn\'t apply';
	echo '</translate>';
	echo '</div> <!-- warning_title -->';
	echo '<div class="warning_body">';
	echo '<translate id="ENTRY_ORDER_WARNING_OWN_MARKUP_BODY">';
	echo 'If you were a different person looking at this page, the current price for this canvas print would be <b>$<span id="margin_simulate"></span></b>';
	echo '</translate>';
	echo '</div> <!-- warning_body -->';
	echo '</div> <!-- warning -->';
}

if ($surface < $minimumsurface['small']) {
	echo '<div class="warning abovemargin">';
	echo '<div class="warning_title">';
	echo '<translate id="ENTRY_ORDER_TOO_SMALL">';
	echo 'The resolution of the source image for this artwork is too small';
	echo '</translate>';
	echo '</div> <!-- warnging_title -->';
	echo '<div class="hint_body">';
	echo '<translate id="ENTRY_ORDER_TOO_SMALL_BODY">';
	echo 'The resolution of the image is too low for a canvas print to be of good enough quality. It is recommended for members to upload the full resolution of their artworks if they want canvas printing to be available. The smallest size of canvas requires at least 1.1 megapixels and the largest size needs 3.9.';
	echo '</translate>';
	echo '</div> <!-- hint_body -->';
	echo '</div> <!-- hint -->';
} else {
	echo '<div class="hint hintmargin abovemargin">';
	echo '<div class="hint_title">';
	echo '<translate id="ENTRY_ORDER_QUALITY_HINT_TITLE">';
	echo 'Quality';
	echo '</translate>';
	echo '</div> <!-- hint_title -->';
	echo '<div class="hint_body">';
	echo '<translate id="ENTRY_ORDER_QUALITY_HINT_BODY">';
	echo 'All options include UV coating for lasting colors. The edge treatment for our prints is black, which means that the front of the canvas contains the full picture and the sides of the canvas (when framed) are black. If you want to request a special edge treatment such as gallery wrap, please mention it in the "Add special instructions to merchant" section of your paypal order.';
	echo '</translate>';
	echo '</div> <!-- hint_body -->';
	echo '</div> <!-- hint -->';
	
	echo '<div id="quality_options">';
	echo '<div class="quality_option">';
	echo '<input id="quality_1" type="radio" name="quality" value="1"> <translate id="ENTRY_ORDER_QUALITY_ECONOMY"><span class="quality_name">Economy</span>Unstretched Polyester canvas, rolled and shipped in a mailing tube.</translate>';
	echo '</div> <!-- qualiy_option -->';
	
	echo '<div class="quality_option">';
	echo '<input id="quality_2" type="radio" name="quality" value="2" checked> <translate id="ENTRY_ORDER_QUALITY_READY"><span class="quality_name">Ready to hang</span>Polyester canvas, stretched onto a 3/4" (1.9cm) wood frame. Ready to hang on a wall.</translate>';
	echo '</div> <!-- qualiy_option -->';
	
	echo '<div class="quality_option">';
	echo '<input id="quality_3" type="radio" name="quality" value="3"> <translate id="ENTRY_ORDER_QUALITY_PREMIUM"><span class="quality_name">Gallery quality</span>Premium canvas, stretched onto a 1.5" (3.8cm) wood frame. Ready to hang on a wall.</translate>';
	echo '</div> <!-- qualiy_option -->';
	echo '</div> <!-- quality_options -->';
	
	echo '<div class="hint hintmargin">';
	echo '<div class="hint_title">';
	echo '<translate id="ENTRY_ORDER_SIZE_HINT_TITLE">';
	echo 'Size';
	echo '</translate>';
	echo '</div> <!-- hint_title -->';
	echo '<div class="hint_body">';
	echo '<translate id="ENTRY_ORDER_SIZE_HINT_BODY">';
	echo 'If the aspect ratio of the artwork is very unusual, the print will be manufactured with a custom aspect ratio, the total surface of which will be equivalent to the size you pick below.';
	echo '</translate>';
	echo '</div> <!-- hint_body -->';
	echo '</div> <!-- hint -->';
	
	$distance = array();
	$distance[23] = abs($ratio - 3/2);
	$distance[34] = abs($ratio - 4/3);
	$distance[45] = abs($ratio - 5/4);
	
	$size = array();
	$size[45] = array('small' => '8x10" (20.3x25.4cm)', 'medium' => '11x14" (27.9x35.6cm)', 'large' => '16x20" (40.6x50.8cm)');
	$size[23] = array('small' => '8x12" (20.3x30.5cm)', 'medium' => '12x16" (30.5x40.6cm)', 'large' => '15x20" (38.1x50.8cm)');
	$size[34] = array('small' => '9x12" (22.9x30.5cm)', 'medium' => '12x18" (30.5x45.7cm)', 'large' => '16x24" (40.6x61cm)');
	
	switch (min($distance[23], $distance[34], $distance[45])) {
		case $distance[23]:
			$closestratio = 23;
		case $distance[34]:
			$closestratio = 34;
			break;
		case $distance[45]:
			$closestratio = 45;
			break;
	}
	
	$page->addJavascriptVariable('size_1_name', '<translate id="ENTRY_ORDER_SIZE_1_NAME" escape="js">(1) Small</translate>: '.$size[$closestratio]['small']);
	$page->addJavascriptVariable('size_2_name', '<translate id="ENTRY_ORDER_SIZE_2_NAME" escape="js">(2) Medium</translate>: '.$size[$closestratio]['small']);
	$page->addJavascriptVariable('size_3_name', '<translate id="ENTRY_ORDER_SIZE_3_NAME" escape="js">(3) Large</translate>: '.$size[$closestratio]['small']);
	
	echo '<div id="size_options">';
	echo '<div class="quality_option">';
	echo '<input id="size_1" type="radio" name="size" value="1"> <span class="quality_name"><translate id="ENTRY_ORDER_SIZE_SMALL">Small</translate></span> '.$size[$closestratio]['small'];
	echo '</div> <!-- qualiy_option -->';
	
	$enabled = ($surface > $minimumsurface['medium']);
	echo '<div class="quality_option">';
	echo '<input id="size_2" type="radio" name="size" value="2" '.($enabled?'checked':'disabled').'> <span class="quality_name"><translate id="ENTRY_ORDER_SIZE_MEDIUM">Medium</translate></span> '.$size[$closestratio]['medium'];
	if (!$enabled) echo ' <b><translate id="ENTRY_ORDER_SIZE_OPTION_UNAVAILABLE">This option is unavailable because the resolution of the artwork\'s original file is too low.</translate></b>';
	echo '</div> <!-- qualiy_option -->';
	
	$enabled = ($surface > $minimumsurface['large']);
	echo '<div class="quality_option">';
	echo '<input id="size_3" type="radio" name="size" value="3" '.($enabled?'':'disabled').'> <span class="quality_name"><translate id="ENTRY_ORDER_SIZE_LARGE">Large</translate></span> '.$size[$closestratio]['large'];
	if (!$enabled) echo ' <b><translate id="ENTRY_ORDER_SIZE_OPTION_UNAVAILABLE">This option is unavailable because the resolution of this artwork\'s original file is too low.</translate></b>';
	echo '</div> <!-- qualiy_option -->';
	
	echo '</div> <!-- size_options -->';
	
	$ip_history = $user->getIpHistory();
	$last_ip = array_shift(array_keys($ip_history));
	$record = @geoip_record_by_name($last_ip);
	
	$shipping_region = 3;
	
	if (isset($record['country_code3'])) {
		if (strcasecmp($record['country_code3'], 'USA') == 0)
			$shipping_region = 1;
		elseif (strcasecmp($record['country_code3'], 'CAN') == 0)
			$shipping_region = 2;
		elseif (strcasecmp($record['country_code3'], 'AUS') == 0)
			$shipping_region = 4;
		elseif (strcasecmp($record['country_code3'], 'CXR') == 0)
			$shipping_region = 4;
		elseif (strcasecmp($record['country_code3'], 'CCK') == 0)
			$shipping_region = 4;
		elseif (strcasecmp($record['country_code3'], 'HMD') == 0)
			$shipping_region = 4;
		elseif (strcasecmp($record['country_code3'], 'KIR') == 0)
			$shipping_region = 4;
		elseif (strcasecmp($record['country_code3'], 'NRU') == 0)
			$shipping_region = 4;
		elseif (strcasecmp($record['country_code3'], 'NFK') == 0)
			$shipping_region = 4;
		elseif (strcasecmp($record['country_code3'], 'TUV') == 0)
			$shipping_region = 4;
		elseif (strcasecmp($record['country_code3'], 'NZL') == 0)
			$shipping_region = 4;
		elseif (strcasecmp($record['country_code3'], 'COK') == 0)
			$shipping_region = 4;
		elseif (strcasecmp($record['country_code3'], 'NIU') == 0)
			$shipping_region = 4;
		elseif (strcasecmp($record['country_code3'], 'PCN') == 0)
			$shipping_region = 4;
		elseif (strcasecmp($record['country_code3'], 'TKL') == 0)
			$shipping_region = 4;
	}
	
	$page->addJavascriptVariable('shipping_region', $shipping_region);
	
	echo '<div class="hint hintmargin">';
	echo '<div class="hint_title">';
	echo '<translate id="ENTRY_ORDER_SHIPPING_HINT_TITLE">';
	echo 'Shipping';
	echo '</translate>';
	echo '</div> <!-- hint_title -->';
	echo '<div class="hint_body">';
	echo '<translate id="ENTRY_ORDER_SHIPPING_HINT_BODY">';
	echo 'We currently only ship to the regions listed here. Handling and manufacturing takes approximately 2 working days prior to shipping.	';
	echo '</translate>';
	echo '</div> <!-- hint_body -->';
	echo '</div> <!-- hint -->';
	
	echo '<div id="shipping_options">';
	echo '<div class="quality_option">';
	echo '<input id="shipping_1" type="radio" name="shipping" value="1" '.($shipping_region == 1?'checked':'').'> <translate id="ENTRY_ORDER_SHIPPING_USA"><span class="shipping_name">USA</span>Includes Alaska and Hawaii. Shipped with US Postal Service Priority mail, takes 2 to 3 working days.</translate>';
	echo '</div> <!-- qualiy_option -->';
	
	echo '<div class="quality_option">';
	echo '<input id="shipping_2" type="radio" name="shipping" value="2" '.($shipping_region == 2?'checked':'').'> <translate id="ENTRY_ORDER_SHIPPING_CANADA"><span class="shipping_name">Canada</span>Shipped with US Postal Service Priority mail, takes 6 to 10 working days.</translate>';
	echo '</div> <!-- qualiy_option -->';
	
	echo '<div class="quality_option">';
	echo '<input id="shipping_3" type="radio" name="shipping" value="3" '.($shipping_region == 3?'checked':'').'> <translate id="ENTRY_ORDER_SHIPPING_EUROPE"><span class="shipping_name">Western Europe, South America, Japan, South Africa</span>Shipped with US Postal Service Priority mail, takes 6 to 10 working days.</translate>';
	echo '</div> <!-- qualiy_option -->';
	
	echo '<div class="quality_option">';
	echo '<input id="shipping_4" type="radio" name="shipping" value="4" '.($shipping_region == 4?'checked':'').'> <translate id="ENTRY_ORDER_SHIPPING_OCEANIA"><span class="shipping_name">Australia, New-Zealand</span>Shipped with US Postal Service Priority mail, takes 6 to 10 working days.</translate>';
	echo '</div> <!-- qualiy_option -->';
	echo '</div> <!-- quality_options -->';
	
	echo '<div class="warning">';
	echo '<div class="warning_title">';
	echo '<translate id="ENTRY_ORDER_TOTAL">';
	echo '$<span id="total"><integer value="45"/></span> + $<span id="base_shipping"><integer value="15"/></span> for shipping (shipping is reduced to $<span id="additional_shipping"><integer value="6"/></span> if this is an extra item on an existing order)';
	echo '</translate>';
	
	echo '<form id="order" target="paypal" action="https://www.paypal.com/cgi-bin/webscr" method="post">';
	echo '<input type="hidden" name="add" value="1">';
	echo '<input type="hidden" name="cmd" value="_cart">';
	echo '<input type="hidden" name="business" value="rayshaus@hotmail.com">';
	echo '<input type="hidden" name="item_number" value="'.$eid.'">';
	echo '<input type="hidden" name="item_name" value="&quot;'.String::fromaform($theme->getTitle()).'&quot; <translate id="ENTRY_ORDER_PAYPAL_ITEM_NAME">canvas print</translate>">';
	echo '<input id="paypal_amount" type="hidden" name="amount" value="45.00">';
	echo '<input type="hidden" name="currency_code" value="USD">';
	echo '<input type="hidden" name="on0" value="Size">';
	echo '<input id="paypal_size" type="hidden" name="os0" value="<translate id="ENTRY_ORDER_SIZE_2_NAME">Medium</translate>">';
	echo '<input type="hidden" name="on1" value="Quality">';
	echo '<input id="paypal_quality" type="hidden" name="os1" value="<translate id="ENTRY_ORDER_QUALITY_2_NAME">Ready to hang</translate>">';
	echo '<input id="paypal_handling" type="hidden" name="handling_cart" value="9">';
	echo '<input id="paypal_shipping" type="hidden" name="shipping" value="6">';
	echo '<input id="paypal_additional_shipping" type="hidden" name="shipping2" value="6">';
	echo '<input type="hidden" name="notify_url" value="'.$REQUEST['CANVAS_IPN'].'">';
	echo '<input type="hidden" name="charset" value="utf-8">';
	echo '<input type="hidden" name="no_shipping" value="2">';
	if (isset($record['country_code'])) echo '<input type="hidden" name="lc" value="'.$record['country_code'].'">';
	echo '<input id="submit" type="submit" value="<translate id="ENTRY_ORDER_SUBMIT">Add this print to your order</translate>">';
	echo '</form>';
	
	echo '</div> <!-- watning_title -->';
	echo '</div> <!-- warning -->';
	
}

$page->endHTML();
$page->render();
?>
