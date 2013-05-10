<?php
    
/* 
 	Copyright (C) Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	This class handles the sending of emails
*/

require_once(dirname(__FILE__).'/../templates/emailtemplate.php');
require_once(dirname(__FILE__).'/string.php');
require_once(dirname(__FILE__).'/template.php');

class EmailException extends Exception {}
 
class Email {
	private static $EMAIL_HTML = '<html>
<head>
   <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
   <title>#subject</title>
</head>
<body style="background-color: #FF9D26; font-family: Arial;">

<table width="100%" cellspacing="0" cellpadding="0" bgcolor="#FF9D26">
   <tr>
   <table width="640" cellspacing="0" cellpadding="0" bgcolor="#FF9D26">
   <tr>
      <td align="center">
         <table width="600" cellspacing="0" cellpadding="0" bgcolor="#000000">
            <tr>
               <td height="77" align="left">
                  <table width="600" border="0" cellspacing="0" cellpadding="0">
                     <tr>
                        <td><img src="http://inspi.re/graphics/inspire_email_header.gif" width="150" height="77" alt="inspi.re"></td>
						<td width="450" height="77" bgcolor="#000000" style="color: #ffffff; text-align: center; font-size: 20px;">
						#title
						</td>
                     </tr>
                  </table>
               </td>
            </tr>
         </table>
      </td>
   </tr>
   <tr>
      <td align="center">
         <table width="600" cellspacing="0" cellpadding="0" style="background-color: #FFFFFF;">
         	<tr height="10" width="600"><td>&nbsp;</td></tr>
            <tr>              
               <td width="10"></td>
               <td valign="top" width="580">
                  <p style="text-align: justify; color: #000;">#body</p>
                  
               </td>
               <td width="10"></td>
            </tr>
            <tr height="10" width="600"><td>&nbsp;</td></tr>
         </table>
      </td>
   </tr>
   <tr>
      <td align="center">
         <table width="600" height="10" cellspacing="0" cellpadding="0" style="background-color: #000000;">
         <tr><td>&nbsp;</td></tr>
         </table>
      </td>
    </tr>
   </table>
   </tr>
</table>

</body>
</html>';
 
 	/*
 	 * Send an email message based on an email template
 	 * template_values contains a hashmap of the template values to be replaced
 	 * This function raises an exception if the email wasn't sent succesfully
 	 */
 	public static function mail($email, $lid, $template_name, $template_values) {
 		global $EMAIL_TEMPLATE;
 		
 		$template = EmailTemplate::get($lid, $EMAIL_TEMPLATE[$template_name]);
 		
 		$plainbody = $template['body'];
 		$plainbody = Template::Templatize($plainbody, $template_values);
 		
 		$body = String::fromaform($template['body']);
 		$new_template_values = array();
 		foreach ($template_values as $key => $value) {
 			$new_template_values[$key] = String::fromaform($value, false);
 		}
 		$body = Template::Templatize($body, $new_template_values);
 		
 		$subject = Template::Templatize($template['subject'], $new_template_values);
 		
 		$title = String::fromaform($template['title']);
 		$title = Template::Templatize($title, $new_template_values);
 		
 		$body = Template::Templatize(Email::$EMAIL_HTML, array('subject' => $subject, 'title' => $title, 'body' => $body));
 		
 		Log::trace(__CLASS__, 'Sending the following email to '.$email."\n"
 								.'HEADER: '.$template['headers']."\n"
 								.'SUBJECT: '.$subject."\n"
 								.'BODY: '.$body."\n"
 					);
 		
 		if (!mail($email, 
	 				$subject,
	 				"----NextPart_048F8BC8A2197DE2036A\nContent-Type: text/plain; charset=utf-8\n\n".$plainbody."\n\n----NextPart_048F8BC8A2197DE2036A\nContent-Type: text/html; charset=utf-8\n\n".$body."\n\n----NextPart_048F8BC8A2197DE2036A--\n", 
	 				$template['headers']
	 		)) {
	 		Log::critical(__CLASS__, 'Sending of the last email to '.$email.' failed');
	 		throw new EmailException('Email to '.$email.' using template '.$template_name.' failed to be sent');
	 	}
 	}
}
?>
