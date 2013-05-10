<?php

/* 
       Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
        
       Payment notifications coming from paypal
*/

require_once(dirname(__FILE__).'/../entities/alert.php');
require_once(dirname(__FILE__).'/../entities/alertinstance.php');
require_once(dirname(__FILE__).'/../entities/alertvariable.php');
require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/picture.php');
require_once(dirname(__FILE__).'/../entities/picturefile.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/email.php');
require_once(dirname(__FILE__).'/../constants.php');

if (strcasecmp($_POST['txn_type'], 'masspay') == 0) exit(0);

$req = 'cmd=_notify-validate';
	
foreach ($_POST as $key => $value) {
	$value = urlencode(stripslashes($value));
	$req .= "&$key=$value";
}

// renvoyer au système PayPal pour validation
$header = "POST /cgi-bin/webscr HTTP/1.0\r\n";
$header .= "Content-Type: application/x-www-form-urlencoded\r\n";
$header .= "Content-Length: " . strlen($req) . "\r\n\r\n";
$fp = fsockopen ('ssl://www.paypal.com', 443, $errno, $errstr, 30);

// affecter les variables du formulaire aux variables locales
$payment_status = $_POST['payment_status'];

$txn_id = $_POST['txn_id'];
$receiver_email = $_POST['receiver_email'];

mail('ipn@inspi.re', 'Canvas IPN '.$txn_id, print_r($_REQUEST, true));

$pricereference = array(1 => array(1 => 18, 2 => 26, 3 => 40), 
						2 => array(1 => 29, 2 => 42, 3 => 58),
						3 => array(1 => 37, 2 => 50, 3 => 70)
						);

if (!$fp) {
// ERREUR HTTP
} else {
	fputs ($fp, $header . $req);
	while (!feof($fp)) $res = fgets ($fp, 1024);
	fclose ($fp);
		
	if (strcasecmp ($res, "VERIFIED") == 0 && strcasecmp($receiver_email, 'rayshaus@hotmail.com') == 0 && strcasecmp($payment_status, 'Completed') == 0) {
		// Do something on successful order
		$headers = "From: Gilles Dubuc <gilles@inspi.re>\nReply-To: gilles@inspi.re\nReturn-Path: gilles@inspi.re\nCC: rayshaus@gmail.com\nBCC: kouiskas@gmail.com\nMessage-ID: <".time()."gilles@inspi.re>X-Mailer: PHP v".phpversion()."\nX-Sender: gilles@inspi.re\nX-auth-smtp-user: gilles@inspi.re\nX-abuse-contact: gilles@inspi.re\nContent-Type: text/html;";
		
		$artists_cut = 0;
		$inspire_cut = 0;
		$shipping_total = 0;
		$prints_summary = '';

		for ($i = 1; $i <= intval($_REQUEST['num_cart_items']); $i++) {
			$quantity = intval($_REQUEST['quantity'.$i]);
			$shipping = floatval($_REQUEST['mc_shipping'.$i]);
			$price = (floatval($_REQUEST['mc_gross_'.$i]) - $shipping) / $quantity;
			
			$eid = $_REQUEST['item_number'.$i];
			$qualitycode = substr($_REQUEST['option_selection2_'.$i], 1, 1);
			$sizecode = substr($_REQUEST['option_selection1_'.$i], 1, 1);
			
			$entry = Entry::get($eid);
			$picture = Picture::get($entry->getPid());
			$picturefile = PictureFile::get($picture->getFid($PICTURE_SIZE['ORIGINAL']));

			$width = $picturefile->getWidth();
			$height = $picturefile->getHeight();
			$ratio = max($width, $height) / min($width, $height);
			
			$distance = array();
			$distance[23] = abs($ratio - 3/2);
			$distance[34] = abs($ratio - 4/3);
			$distance[45] = abs($ratio - 5/4);
			
			$sizename = array();
			$sizename[45] = array('small' => '8x10"', 'medium' => '11x14"', 'large' => '16x20"');
			$sizename[23] = array('small' => '8x12"', 'medium' => '12x16"', 'large' => '15x20"');
			$sizename[34] = array('small' => '9x12"', 'medium' => '12x18"', 'large' => '16x24"');
			
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
			
			$prints_summary .= '<br/>';
			
			$link = $REQUEST['DOWNLOAD_ORIGINAL'].'?eid='.$eid;
			$prints_summary .= '<b>Artwork file:</b> <a href="'.$link.'">'.$link.'</a><br/>';
			
			$competition = Competition::get($entry->getCid());
			$author = User::get($entry->getUid());
			$prints_summary .= '<b>Author:</b> '.$author->getUniqueName();
			
			$inspire_cut += $quantity * $pricereference[$qualitycode][$sizecode] * 0.1;
			
			if ($price > $pricereference[$qualitycode][$sizecode]) {
				$author_cut = $quantity * ($price - $pricereference[$qualitycode][$sizecode]);
				$artists_cut += $author_cut;
				// Author's cut needs to be transfered
				if ($author_cut > 0) {
					$author->incrementBalance($author_cut);
					
					$alert = new Alert($ALERT_TEMPLATE_ID['CANVAS_SALE']);
					$aid = $alert->getAid();
					
					$alert_variable = new AlertVariable($aid, 'href', $PAGE['ENTRY'].'?lid='.$author->getLid().'&home=true#eid='.$entry->getEid());
					$alert_variable = new AlertVariable($aid, 'tid', $competition->getTid());
					$alert_variable = new AlertVariable($aid, 'commission', $author_cut);
					$alert_variable = new AlertVariable($aid, 'quantity', $quantity);
					
					$alert_instance = new AlertInstance($aid, $entry->getUid(), $ALERT_INSTANCE_STATUS['NEW']);
				} else {
					$alert = new Alert($ALERT_TEMPLATE_ID['CANVAS_SALE_NO_PROFIT']);
					$aid = $alert->getAid();
					
					$alert_variable = new AlertVariable($aid, 'href', $PAGE['ENTRY'].'?lid='.$author->getLid().'&home=true#eid='.$entry->getEid());
					$alert_variable = new AlertVariable($aid, 'tid', $competition->getTid());
					$alert_variable = new AlertVariable($aid, 'quantity', $quantity);
					
					$alert_instance = new AlertInstance($aid, $entry->getUid(), $ALERT_INSTANCE_STATUS['NEW']);
				}
			} else $author_cut = 0;
			$prints_summary .= '<br/>';

			
			switch ($qualitycode) {
				case 1:
					$quality = 'Economy (unstretched)';
					break;
				case 2:
					$quality = 'Economy (stretched)';
					break;
				case 3:
					$quality = 'Premium (stretched)';
					break;
			}
			$prints_summary .= '<b>Quality:</b> '.$quality.'<br/>';
			
			switch ($sizecode) {
				case 1:
					$size = 'Small ('.$sizename[$closestratio]['small'].')';
					break;
				case 2:
					$size = 'Medium ('.$sizename[$closestratio]['medium'].')';
					break;
				case 3:
					$size = 'Large ('.$sizename[$closestratio]['large'].')';
					break;
			}
			$prints_summary .= '<b>Size:</b> '.$size.'<br/>';
			$prints_summary .= '<b>Quantity:</b> '.$quantity.'<br/>';
			$prints_summary .= '<b>Base price:</b> '.$quantity.'x'.$pricereference[$qualitycode][$sizecode].' = $'.($pricereference[$qualitycode][$sizecode] * $quantity).'<br/>';
			$prints_summary .= '<b>Author\'s commission:</b> '.$quantity.'x'.($author_cut / $quantity).' = $'.$author_cut.'<br/>';
		}
		
		$order_summary  = '<b>Paypal txn_id:</b> '.$txn_id.'<br/>';
		$order_summary .= '<b>Total paid:</b> $'.$_REQUEST['mc_gross'].'<br/>';
		$order_summary .= '<b>Amount due to inspi.re for this order:</b> $'.$inspire_cut.'<br/>';
		$order_summary .= '<b>Amount due to artists for this order:</b> $'.$artists_cut.'<br/>';
		$order_summary .= '<b>Amount from total dedicated to shipping:</b> $'.(intval($_REQUEST['mc_shipping']) + intval($_REQUEST['mc_handling'])).'<br/>';
		$order_summary .= '<b>Payer\'s email address:</b> '.$_REQUEST['payer_email'].'<br/>';
		
		if (isset($_REQUEST['memo'])) {
			$order_summary .= '<br/>';
			$order_summary .= '<b>Payer special instructions:</b><br/>';
			$order_summary .= $_REQUEST['memo'].'<br/>';
		}
		
		$order_summary .= '<br/>';
		$order_summary .= '<b>Delivery address:</b><br/>';
		$order_summary .= $_REQUEST['address_name'].'<br/>';
		$order_summary .= $_REQUEST['address_street'].'<br/>';
		$order_summary .= $_REQUEST['address_zip'].' '.$_REQUEST['address_city'].', <b>'.$_REQUEST['address_country'].'</b><br/>';
		$order_summary .= '<br/>';
		$order_summary .= '<b>Details of the print(s) ordered:</b><br/>';
		
		$order_summary .= $prints_summary;
		
		mail('ray@canvasphoto.us', 'inspi.re canvas print order #'.$txn_id, $order_summary, $headers);
	} else {
		mail('beta@inspi.re', '(IPN) Invalid receiver_email or payment_status '.$txn_id, print_r($_REQUEST, true));
	}
}

?>