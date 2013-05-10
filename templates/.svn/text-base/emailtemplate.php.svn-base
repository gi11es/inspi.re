<?php
    
/* 
 	Copyright (C) Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

require_once(dirname(__FILE__).'/../constants.php');
require_once(dirname(__FILE__).'/../entities/i18n.php');
require_once(dirname(__FILE__).'/../utilities/template.php');

$EMAIL_TEMPLATE['ACTIVATION'] = 1;
$EMAIL_TEMPLATE['LOST_PASSWORD'] = 2;
$EMAIL_TEMPLATE['CRITICAL_ERROR'] = 3;
$EMAIL_TEMPLATE['ACTIVATED'] = 4;
$EMAIL_TEMPLATE['NEW_EMAIL'] = 5;
$EMAIL_TEMPLATE['INVITE_SIMPLE'] = 6;
$EMAIL_TEMPLATE['INVITE_MESSAGE'] = 7;
$EMAIL_TEMPLATE['REMINDER'] = 8;
$EMAIL_TEMPLATE['NO_COMMUNITY'] = 9;
$EMAIL_TEMPLATE['POSTCARD'] = 10;
$EMAIL_TEMPLATE['PREMIUM_CODE'] = 11;
$EMAIL_TEMPLATE['PREMIUM_CODE_LIFETIME'] = 12;
$EMAIL_TEMPLATE['ALERT'] = 13;
$EMAIL_TEMPLATE['COMMUNITY_APPEAL'] = 14;
$EMAIL_TEMPLATE['MIA_APPEAL'] = 15;

class EmailTemplate {
	public static function get($lid, $which) {
		global $EMAIL_TEMPLATE;
		
		$random_hash = md5(date("r", time())); 
		$headers_noreply = "From: inspi.re <no-reply@inspi.re>\nReply-To: no-reply@inspi.re\nReturn-Path: no-reply@inspi.re\nMessage-ID: <".time()."no-reply@inspi.re>X-Mailer: PHP v".phpversion()."\nX-Sender: no-reply@inspi.re\nX-auth-smtp-user: no-reply@inspi.re\nX-abuse-contact: beta@inspi.re\nContent-Type: multipart/alternative;\n\tboundary=--NextPart_048F8BC8A2197DE2036A\nMIME-Version: 1.0";
		$headers_support = "From: inspi.re <support@inspi.re>\nReply-To: support@inspi.re\nReturn-Path: support@inspi.re\nMessage-ID: <".time()."support@inspi.re>X-Mailer: PHP v".phpversion()."\nX-Sender: support@inspi.re\nX-auth-smtp-user: support@inspi.re\nX-abuse-contact: beta@inspi.re\nContent-Type: multipart/alternative;\n\tboundary=--NextPart_048F8BC8A2197DE2036A\nMIME-Version: 1.0";
		
		switch ($which) {
			case $EMAIL_TEMPLATE["ACTIVATION"]:
				$texts = I18N::getArray($lid, array("EMAIL_ACTIVATION_SUBJECT", "EMAIL_ACTIVATION_TITLE", "EMAIL_ACTIVATION_BODY_PLAIN"),
						array("EMAIL_ACTIVATION_SUBJECT" => "inspi.re account activation",
								"EMAIL_ACTIVATION_TITLE" => "account activation",
								"EMAIL_ACTIVATION_BODY_PLAIN" => "In order to activate your inspi.re account, open the following link in your web browser:\n\n#activation_link\n\n\nBest regards,\nThe inspi.re team",
						));

				return array("subject" => $texts["EMAIL_ACTIVATION_SUBJECT"]->getText(),
							 "headers" => $headers_noreply,
							"body" => $texts["EMAIL_ACTIVATION_BODY_PLAIN"]->getText(),
							"title" => $texts["EMAIL_ACTIVATION_TITLE"]->getText()
							);
			case $EMAIL_TEMPLATE["ACTIVATED"]:
				$texts = I18N::getArray($lid, array("EMAIL_ACTIVATED_SUBJECT", "EMAIL_ACTIVATED_TITLE", "EMAIL_ACTIVATED_BODY_PLAIN"),
						array("EMAIL_ACTIVATED_SUBJECT" => "inspi.re account successfully activated",
								"EMAIL_ACTIVATED_TITLE" => "account successfully activated",
								"EMAIL_ACTIVATED_BODY_PLAIN" => "Congratulations! You inspi.re account is now activated.\n\n\nBest regards,\nThe inspi.re team",
						));

				return array("subject" => $texts["EMAIL_ACTIVATED_SUBJECT"]->getText(),
							 "headers" => $headers_noreply,
							"body" => $texts["EMAIL_ACTIVATED_BODY_PLAIN"]->getText(),
							"title" => $texts["EMAIL_ACTIVATED_TITLE"]->getText()
							);
			case $EMAIL_TEMPLATE["LOST_PASSWORD"]:
				$texts = I18N::getArray($lid, array("EMAIL_CHANGE_PASSWORD_SUBJECT", "EMAIL_CHANGE_PASSWORD_TITLE", "EMAIL_CHANGE_PASSWORD_BODY_PLAIN"),
						array("EMAIL_CHANGE_PASSWORD_SUBJECT" => "inspi.re password reset",
								"EMAIL_CHANGE_PASSWORD_TITLE" => "password reset request",
								"EMAIL_CHANGE_PASSWORD_BODY_PLAIN" => "A password reset was requested for your email address on inspi.re. If you do wish to reset your password please open your web browser and paste the following link in it:\n\n#change_password_link\n\n\nBest regards,\nThe inspi.re team",
						));
				return array ("subject" => $texts["EMAIL_CHANGE_PASSWORD_SUBJECT"]->getText(),
							  "headers" => $headers_noreply,
							  "body" => $texts["EMAIL_CHANGE_PASSWORD_BODY_PLAIN"]->getText(),
							  "title" => $texts["EMAIL_CHANGE_PASSWORD_TITLE"]->getText()
							 );
			case $EMAIL_TEMPLATE["NEW_EMAIL"]:
				$texts = I18N::getArray($lid, array("EMAIL_NEW_EMAIL_SUBJECT", "EMAIL_NEW_EMAIL_TITLE", "EMAIL_NEW_EMAIL_BODY_PLAIN"),
						array("EMAIL_NEW_EMAIL_SUBJECT" => "inspi.re email address update",
								"EMAIL_NEW_EMAIL_TITLE" => "email address update",
								"EMAIL_NEW_EMAIL_BODY_PLAIN" => "In order to update your inspi.re account's email address, open the following link in your web browser:\n\n#new_email_link\n\n\nBest regards,\nThe inspi.re team",
						));

				return array("subject" => $texts["EMAIL_NEW_EMAIL_SUBJECT"]->getText(),
							 "headers" => $headers_noreply,
							"body" => $texts["EMAIL_NEW_EMAIL_BODY_PLAIN"]->getText(),
							"title" => $texts["EMAIL_NEW_EMAIL_TITLE"]->getText()
							);
			case $EMAIL_TEMPLATE["INVITE_SIMPLE"]:
				$texts = I18N::getArray($lid, array("EMAIL_INVITE_SIMPLE_SUBJECT", "EMAIL_INVITE_SIMPLE_TITLE", "EMAIL_INVITE_SIMPLE_BODY"),
						array("EMAIL_INVITE_SIMPLE_SUBJECT" => "#username invites you to join inspi.re",
								"EMAIL_INVITE_SIMPLE_TITLE" => "an invitation to join inspi.re",
								"EMAIL_INVITE_SIMPLE_BODY" => "#username is already a member of inspi.re and would like you to join too.\n\nhttp://inspi.re is an art competition hub, where artists can join communities and compete against each other in daily contests. It's a great experience that helps you understand your own level better and improve your art skills.\n\nIt's free to join and you can use the whole website immediately, without the need to fill any form!\n\nBest regards,\nThe inspi.re team",
						));

				return array("subject" => $texts["EMAIL_INVITE_SIMPLE_SUBJECT"]->getText(),
							 "headers" => $headers_noreply,
							"body" => $texts["EMAIL_INVITE_SIMPLE_BODY"]->getText(),
							"title" => $texts["EMAIL_INVITE_SIMPLE_TITLE"]->getText()
							);
			case $EMAIL_TEMPLATE["INVITE_MESSAGE"]:
				$texts = I18N::getArray($lid, array("EMAIL_INVITE_MESSAGE_SUBJECT", "EMAIL_INVITE_MESSAGE_TITLE", "EMAIL_INVITE_MESSAGE_BODY"),
						array("EMAIL_INVITE_MESSAGE_SUBJECT" => "#username invites you to join inspi.re",
								"EMAIL_INVITE_MESSAGE_TITLE" => "an invitation to join inspi.re",
								"EMAIL_INVITE_MESSAGE_BODY" => "#username is already a member of inspi.re and would like you to join too. This is what he/she has to say about it:\n\n\"#message\"\n\nhttp://inspi.re is an art competition hub, where artists can join communities and compete against each other in daily contests. It's a great experience that helps you understand your own level better and improve your art skills.\n\nIt's free to join and you can use the whole website immediately, without the need to fill any form!\n\nBest regards,\nThe inspi.re team",
						));

				return array("subject" => $texts["EMAIL_INVITE_MESSAGE_SUBJECT"]->getText(),
							 "headers" => $headers_noreply,
							"body" => $texts["EMAIL_INVITE_MESSAGE_BODY"]->getText(),
							"title" => $texts["EMAIL_INVITE_MESSAGE_TITLE"]->getText()
							);
			case $EMAIL_TEMPLATE["REMINDER"]:
				$texts = I18N::getArray($lid, array("EMAIL_REMINDER_MESSAGE_SUBJECT", "EMAIL_REMINDER_MESSAGE_TITLE", "EMAIL_REMINDER_MESSAGE_BODY"),
						array("EMAIL_REMINDER_MESSAGE_SUBJECT" => "inspi.re misses you",
								"EMAIL_REMINDER_MESSAGE_TITLE" => "inspi.re misses you and your art",
								"EMAIL_REMINDER_MESSAGE_BODY" => "Dear #username,\n\nYou've registered an account on http://inspi.re but you haven't visited the website in a while. It has evolved considerably since the last time you visited it. You might want to give it another go and see the new features for yourself!\n\nIf you gave up on using the website because you couldn't find your way through it, simply reply to this email and our support team will help you.\n\nBest regards,\nThe inspi.re team",
						));

				return array("subject" => $texts["EMAIL_REMINDER_MESSAGE_SUBJECT"]->getText(),
							 "headers" => $headers_support,
							"body" => $texts["EMAIL_REMINDER_MESSAGE_BODY"]->getText(),
							"title" => $texts["EMAIL_REMINDER_MESSAGE_TITLE"]->getText()
							);
			case $EMAIL_TEMPLATE["NO_COMMUNITY"]:
				$texts = I18N::getArray($lid, array("EMAIL_NO_COMMUNITY_MESSAGE_SUBJECT", "EMAIL_NO_COMMUNITY_MESSAGE_TITLE", "EMAIL_NO_COMMUNITY_MESSAGE_BODY"),
						array("EMAIL_NO_COMMUNITY_MESSAGE_SUBJECT" => "need help using inspi.re?",
								"EMAIL_NO_COMMUNITY_MESSAGE_TITLE" => "need help using inspi.re?",
								"EMAIL_NO_COMMUNITY_MESSAGE_BODY" => "Dear #username,\n\nYou've registered an account on http://inspi.re but you haven't joined any community, which is supposed to be the first step to perform once registered. We're simply curious to know why.\n\nIf you gave up on using the website because you couldn't find your way through it, simply reply to this email and our support team will help you.\n\nBest regards,\nThe inspi.re team",
						));

				return array("subject" => $texts["EMAIL_NO_COMMUNITY_MESSAGE_SUBJECT"]->getText(),
							 "headers" => $headers_support,
							"body" => $texts["EMAIL_NO_COMMUNITY_MESSAGE_BODY"]->getText(),
							"title" => $texts["EMAIL_NO_COMMUNITY_MESSAGE_TITLE"]->getText()
							);
			case $EMAIL_TEMPLATE["POSTCARD"]:
				$texts = I18N::getArray($lid, array("EMAIL_POSTCARD_MESSAGE_SUBJECT", "EMAIL_POSTCARD_MESSAGE_TITLE", "EMAIL_POSTCARD_MESSAGE_BODY"),
						array("EMAIL_POSTCARD_MESSAGE_SUBJECT" => "inspi.re postcard request",
								"EMAIL_POSTCARD_MESSAGE_TITLE" => "inspi.re postcard request",
								"EMAIL_POSTCARD_MESSAGE_BODY" => "A postcard needs to be sent to the following address:\n\n#address\n\nWith the following message:\n\n#message",
						));

				return array("subject" => $texts["EMAIL_POSTCARD_MESSAGE_SUBJECT"]->getText(),
							 "headers" => $headers_noreply,
							"body" => $texts["EMAIL_POSTCARD_MESSAGE_BODY"]->getText(),
							"title" => $texts["EMAIL_POSTCARD_MESSAGE_TITLE"]->getText()
							);
			case $EMAIL_TEMPLATE["PREMIUM_CODE"]:
				$texts = I18N::getArray($lid, array("EMAIL_PREMIUM_CODE_MESSAGE_SUBJECT", "EMAIL_PREMIUM_CODE_MESSAGE_TITLE", "EMAIL_PREMIUM_CODE_MESSAGE_BODY"),
						array("EMAIL_PREMIUM_CODE_MESSAGE_SUBJECT" => "inspi.re premium membership code",
								"EMAIL_PREMIUM_CODE_MESSAGE_TITLE" => "inspi.re premium membership code",
								"EMAIL_PREMIUM_CODE_MESSAGE_BODY" => "Thank you for purchasing a premium membership code!\n\n#code\n\nThe code above is valid indefinitely, so you don't have to activate it immediately. The #days days worth of premium membership that the code holds will start once you activate it. You can activate it either on yourself (on your own profile page), or you can use it to sponsor any other user on inspi.re (simply go to their profile page to do it).\n\nIf you have any questions, you can reach us by replying to this email.\n\nBest regards,\nThe inspi.re team",
						));

				return array("subject" => $texts["EMAIL_PREMIUM_CODE_MESSAGE_SUBJECT"]->getText(),
							 "headers" => $headers_support,
							"body" => $texts["EMAIL_PREMIUM_CODE_MESSAGE_BODY"]->getText(),
							"title" => $texts["EMAIL_PREMIUM_CODE_MESSAGE_TITLE"]->getText()
							);
			case $EMAIL_TEMPLATE["PREMIUM_CODE_LIFETIME"]:
				$texts = I18N::getArray($lid, array("EMAIL_PREMIUM_CODE_MESSAGE_SUBJECT", "EMAIL_PREMIUM_CODE_MESSAGE_TITLE", "EMAIL_PREMIUM_CODE_LIFETIME_MESSAGE_BODY"),
						array("EMAIL_PREMIUM_CODE_MESSAGE_SUBJECT" => "inspi.re premium membership code",
								"EMAIL_PREMIUM_CODE_MESSAGE_TITLE" => "inspi.re premium membership code",
								"EMAIL_PREMIUM_CODE_LIFETIME_MESSAGE_BODY" => "Thank you for purchasing a premium membership code!\n\n#code\n\nThe code above is valid indefinitely, so you don't have to activate it immediately. The lifetime premium membership that the code holds will start once you activate it. You can activate it either on yourself (on your own profile page), or you can use it to sponsor any other user on inspi.re (simply go to their profile page to do it).\n\nIf you have any questions, you can reach us by replying to this email.\n\nBest regards,\nThe inspi.re team",
						));

				return array("subject" => $texts["EMAIL_PREMIUM_CODE_MESSAGE_SUBJECT"]->getText(),
							 "headers" => $headers_support,
							"body" => $texts["EMAIL_PREMIUM_CODE_LIFETIME_MESSAGE_BODY"]->getText(),
							"title" => $texts["EMAIL_PREMIUM_CODE_MESSAGE_TITLE"]->getText()
							);
			case $EMAIL_TEMPLATE["ALERT"]:
				$texts = I18N::getArray($lid, array("EMAIL_ALERT_MESSAGE_SUBJECT", "EMAIL_ALERT_MESSAGE_TITLE", "EMAIL_ALERT_MESSAGE_BODY"),
						array("EMAIL_ALERT_MESSAGE_SUBJECT" => "inspi.re alert",
								"EMAIL_ALERT_MESSAGE_TITLE" => "inspi.re alert",
								"EMAIL_ALERT_MESSAGE_BODY" => "Dear #username,\n\nYou've just received an alert on http://inspi.re :\n\n[#alerttext]\n\nLog onto your inspi.re account and you will find the list of alerts at the top of the website.\n\nBest regards,\nThe inspi.re team\n\nPS: If you want to stop receiving these emails, log onto your account and go to the \"settings\" tab at the bottom of the website. Once there, simply untick \"Receive an email notification for each alert\" in your account preferences.",
						));

				return array("subject" => $texts["EMAIL_ALERT_MESSAGE_SUBJECT"]->getText(),
							 "headers" => $headers_support,
							"body" => $texts["EMAIL_ALERT_MESSAGE_BODY"]->getText(),
							"title" => $texts["EMAIL_ALERT_MESSAGE_TITLE"]->getText()
							);
							
			case $EMAIL_TEMPLATE["COMMUNITY_APPEAL"]:
				$texts = I18N::getArray($lid, array("EMAIL_APPEAL_MESSAGE_SUBJECT", "EMAIL_APPEAL_MESSAGE_TITLE", "EMAIL_APPEAL_MESSAGE_BODY"),
						array("EMAIL_APPEAL_MESSAGE_SUBJECT" => "inspi.re community administration appeal",
								"EMAIL_APPEAL_MESSAGE_TITLE" => "inspi.re community administration appeal",
								"EMAIL_APPEAL_MESSAGE_BODY" => "Dear #username,\n\nYou're the administrator of the #communityname community on http://inspi.re and you've been inactive for over 30 days. #appealname has requested for you to relinquish your administration rights for that community to him/her, with the following message:\n\n[#appealtext]\n\nIf you want to accept and transfer administration rights so that he/she can take care of that community, click on that link (no need to log onto inspi.re):\n#href\n\nIf you do not want to transfer your administration rights, simply ignore this email.\n\nBest regards,\nThe inspi.re team",
						));

				return array("subject" => $texts["EMAIL_APPEAL_MESSAGE_SUBJECT"]->getText(),
							 "headers" => $headers_noreply,
							"body" => $texts["EMAIL_APPEAL_MESSAGE_BODY"]->getText(),
							"title" => $texts["EMAIL_APPEAL_MESSAGE_TITLE"]->getText()
							);
							
			case $EMAIL_TEMPLATE["MIA_APPEAL"]:
				$texts = I18N::getArray($lid, array("EMAIL_MIA_APPEAL_MESSAGE_SUBJECT", "EMAIL_MIA_APPEAL_MESSAGE_TITLE", "EMAIL_MIA_APPEAL_MESSAGE_BODY"),
						array("EMAIL_MIA_APPEAL_MESSAGE_SUBJECT" => "#appealname wants you to come back to inspi.re",
								"EMAIL_MIA_APPEAL_MESSAGE_TITLE" => "#appealname wants you to come back to inspi.re",
								"EMAIL_MIA_APPEAL_MESSAGE_BODY" => "Dear #username,\n\nYou haven't visited http://inspi.re for a while and #appealname would like you to come back and be active on inspi.re again. He/she wrote the following message for you:\n\n[#appealtext]\n\nBest regards,\nThe inspi.re team",
						));

				return array("subject" => $texts["EMAIL_MIA_APPEAL_MESSAGE_SUBJECT"]->getText(),
							 "headers" => $headers_noreply,
							"body" => $texts["EMAIL_MIA_APPEAL_MESSAGE_BODY"]->getText(),
							"title" => $texts["EMAIL_MIA_APPEAL_MESSAGE_TITLE"]->getText()
							);
		}
	}
}

?>
