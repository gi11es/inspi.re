<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)

 	Contains all the constant values needed in the application
*/

$LOG_LEVEL['TRACE'] = 0;
$LOG_LEVEL['DEBUG'] = 1;
$LOG_LEVEL['INFO'] = 2;
$LOG_LEVEL['ERROR'] = 3;
$LOG_LEVEL['CRITICAL'] = 4;

require_once(dirname(__FILE__).'/settings.php');

// Remote and local paths
$GRAPHICS_PATH = $WEBSITE_PATH.'graphics/';
$JS_PATH = $WEBSITE_PATH.'js/';
$JS_LOCAL_PATH = $WEBSITE_LOCAL_PATH.'js/';
$JS_3RDPARTY_PATH = $WEBSITE_PATH.'js/3rdparty/';
$JS_3RDPARTY_LOCAL_PATH = $WEBSITE_LOCAL_PATH.'js/3rdparty/';
$JS_GENERATED_PATH = $WEBSITE_PATH.'js/generated/';
$JS_GENERATED_LOCAL_PATH = $WEBSITE_LOCAL_PATH.'js/generated/';
$CSS_PATH = $WEBSITE_PATH.'css/';
$CSS_LOCAL_PATH = $WEBSITE_LOCAL_PATH.'css/';
$REQUEST_PATH = $WEBSITE_PATH.'request/';
$RSS_PATH = $WEBSITE_PATH.'rss/';

$GRAPHICS_LOCAL_PATH = $WEBSITE_LOCAL_PATH.'graphics/';

$LANGUAGE['EN'] = 0;
$LANGUAGE['FR'] = 1;
$LANGUAGE['DE'] = 2;
$LANGUAGE['ES'] = 3;
$LANGUAGE['FI'] = 4;
$LANGUAGE['AF'] = 5;
$LANGUAGE['FA'] = 6;
$LANGUAGE['IT'] = 7;
$LANGUAGE['NL'] = 8;
$LANGUAGE['CA'] = 9;
$LANGUAGE['AR'] = 10;

$LANGUAGE_CODE[0] = 'EN';
$LANGUAGE_CODE[1] = 'FR';
$LANGUAGE_CODE[2] = 'DE';
$LANGUAGE_CODE[3] = 'ES';
$LANGUAGE_CODE[4] = 'FI';
$LANGUAGE_CODE[5] = 'AF';
$LANGUAGE_CODE[6] = 'FA';
$LANGUAGE_CODE[7] = 'IT';
$LANGUAGE_CODE[8] = 'NL';
$LANGUAGE_CODE[9] = 'CA';
$LANGUAGE_CODE[10] = 'AR';

$LANGUAGE_SOURCE[0] = 0;
$LANGUAGE_SOURCE[1] = 0;
$LANGUAGE_SOURCE[2] = 0;
$LANGUAGE_SOURCE[3] = 0;
$LANGUAGE_SOURCE[4] = 0;
$LANGUAGE_SOURCE[5] = 0;
$LANGUAGE_SOURCE[6] = 0;
$LANGUAGE_SOURCE[7] = 0;
$LANGUAGE_SOURCE[8] = 0;
$LANGUAGE_SOURCE[9] = 0;
$LANGUAGE_SOURCE[10] = 1;

$TINY_MCE_LANGUAGE[0] = 'EN';
$TINY_MCE_LANGUAGE[1] = 'FR';
$TINY_MCE_LANGUAGE[2] = 'DE';
$TINY_MCE_LANGUAGE[3] = 'ES';
$TINY_MCE_LANGUAGE[4] = 'FI';
$TINY_MCE_LANGUAGE[5] = 'EN';
$TINY_MCE_LANGUAGE[6] = 'FA';
$TINY_MCE_LANGUAGE[7] = 'IT';
$TINY_MCE_LANGUAGE[8] = 'EN';
$TINY_MCE_LANGUAGE[9] = 'CA';
$TINY_MCE_LANGUAGE[10] = 'AR';

$LANGUAGE_HIDDEN = array(6, 8, 9, 10);

$LANGUAGE_NAME[$LANGUAGE['EN']] = 'English';
$LANGUAGE_NAME[$LANGUAGE['FR']] = 'Français';
$LANGUAGE_NAME[$LANGUAGE['DE']] = 'Deutsch';
$LANGUAGE_NAME[$LANGUAGE['ES']] = 'Español';
$LANGUAGE_NAME[$LANGUAGE['FI']] = 'Suomi';
$LANGUAGE_NAME[$LANGUAGE['AF']] = 'Afrikaans';
$LANGUAGE_NAME[$LANGUAGE['FA']] = 'فارسی';
$LANGUAGE_NAME[$LANGUAGE['IT']] = 'Italiano';
$LANGUAGE_NAME[$LANGUAGE['NL']] = 'Nederlands';
$LANGUAGE_NAME[$LANGUAGE['CA']] = 'Català';
$LANGUAGE_NAME[$LANGUAGE['AR']] = 'العربية';

$LANGUAGE_NAME_FROM_ID[0] = 'English';
$LANGUAGE_NAME_FROM_ID[1] = 'French';
$LANGUAGE_NAME_FROM_ID[2] = 'German';
$LANGUAGE_NAME_FROM_ID[3] = 'Spanish';
$LANGUAGE_NAME_FROM_ID[4] = 'Finnish';
$LANGUAGE_NAME_FROM_ID[5] = 'Afrikaans';
$LANGUAGE_NAME_FROM_ID[6] = 'Farsi';
$LANGUAGE_NAME_FROM_ID[7] = 'Italian';
$LANGUAGE_NAME_FROM_ID[8] = 'Dutch';
$LANGUAGE_NAME_FROM_ID[9] = 'Catalan';
$LANGUAGE_NAME_FROM_ID[10] = 'Arabic';

$LANGUAGE_FLAG[$LANGUAGE['EN']] = $GRAPHICS_PATH.'gb.gif';
$LANGUAGE_FLAG[$LANGUAGE['FR']] = $GRAPHICS_PATH.'fr.gif';
$LANGUAGE_FLAG[$LANGUAGE['DE']] = $GRAPHICS_PATH.'de.gif';
$LANGUAGE_FLAG[$LANGUAGE['ES']] = $GRAPHICS_PATH.'es.gif';
$LANGUAGE_FLAG[$LANGUAGE['FI']] = $GRAPHICS_PATH.'fi.gif';
$LANGUAGE_FLAG[$LANGUAGE['AF']] = $GRAPHICS_PATH.'za.gif';
$LANGUAGE_FLAG[$LANGUAGE['FA']] = $GRAPHICS_PATH.'ir.gif';
$LANGUAGE_FLAG[$LANGUAGE['IT']] = $GRAPHICS_PATH.'it.gif';
$LANGUAGE_FLAG[$LANGUAGE['NL']] = $GRAPHICS_PATH.'nl.gif';
$LANGUAGE_FLAG[$LANGUAGE['CA']] = $GRAPHICS_PATH.'catalonia.gif';
$LANGUAGE_FLAG[$LANGUAGE['AR']] = $GRAPHICS_PATH.'arabic.gif';

$SUBMENU['HOME'] = 0;
$SUBMENU['COMMUNITIES'] = 1;
$SUBMENU['COMPETITIONS'] = 2;
$SUBMENU['INFORMATION'] = 3;
$SUBMENU['NEXT'] = 4;
$SUBMENU['MESSAGING'] = 5;

// Full paths to files
$PAGE['404'] = $WEBSITE_PATH.'404.php';
$PAGE['BLOG'] = 'http://insideinspire.wordpress.com/';
$PAGE['BOARD'] = $WEBSITE_PATH.'board.php';
$PAGE['BRING_BACK'] = $WEBSITE_PATH.'bringback.php';
$PAGE['BRING_BACK_MEMBER'] = $WEBSITE_PATH.'bringbackmember.php';
$PAGE['CHANGE_EMAIL'] = $WEBSITE_PATH.'changeemail.php';
$PAGE['CHANGE_PASSWORD'] = $WEBSITE_PATH.'changepassword.php';
$PAGE['COMMENTS'] = $WEBSITE_PATH.'comments.php';
$PAGE['COMMUNITIES'] = $WEBSITE_PATH.'communities.php';
$PAGE['COMMUNITY'] = $WEBSITE_PATH.'community.php';
$PAGE['COMMUNITY_APPEAL'] = $WEBSITE_PATH.'communityappeal.php';
$PAGE['COMPETE'] = $WEBSITE_PATH.'compete.php';
$PAGE['CONFIRM'] = $WEBSITE_PATH.'confirm.php';
$PAGE['CONTACTS'] = $WEBSITE_PATH.'contacts.php';
$PAGE['CRITIQUE'] = $WEBSITE_PATH.'critique.php';
$PAGE['DISCUSS'] = $WEBSITE_PATH.'discuss.php';
$PAGE['DISCUSSION_SEARCH'] = $WEBSITE_PATH.'discussionsearch.php';
$PAGE['DISCUSSION_THREAD'] = $WEBSITE_PATH.'discussionthread.php';
$PAGE['EDIT_COMMUNITY'] = $WEBSITE_PATH.'editcommunity.php';
$PAGE['EDIT_CROPPING'] = $WEBSITE_PATH.'editcropping.php';
$PAGE['EDIT_TEAM_MEMBER'] = $WEBSITE_PATH.'editteammember.php';
$PAGE['ENTER'] = $WEBSITE_PATH.'enter.php';
$PAGE['ENTRY'] = $WEBSITE_PATH.'entry.php';
$PAGE['ENTRY_ORDER'] = $WEBSITE_PATH.'entryorder.php';
$PAGE['FAVORITES'] = $WEBSITE_PATH.'favorites.php';
$PAGE['GENERATE_PREMIUM'] = $WEBSITE_PATH.'generatepremium.php';
$PAGE['GIVE_POINTS'] = $WEBSITE_PATH.'givepoints.php';
$PAGE['GRID'] = $WEBSITE_PATH.'grid.php';
$PAGE['HALL_OF_FAME'] = $WEBSITE_PATH.'halloffame.php';
$PAGE['HELP'] = $WEBSITE_PATH.'help.php';
$PAGE['HOME'] = $WEBSITE_PATH.'home.php';
$PAGE['INDEX'] = $WEBSITE_PATH.'index.php';
$PAGE['INVITE'] = $WEBSITE_PATH.'invite.php';
$PAGE['JOIN_COMMUNITIES'] = $WEBSITE_PATH.'joincommunities.php';
$PAGE['LOST_PASSWORD'] = $WEBSITE_PATH.'lostpassword.php';
$PAGE['MARCH_PRIZE'] = $WEBSITE_PATH.'marchprize.php';
$PAGE['MEMBER_SEARCH'] = $WEBSITE_PATH.'membersearch.php';
$PAGE['MERGE_COMMUNITY'] = $WEBSITE_PATH.'mergecommunity.php';
$PAGE['NEW_DISCUSSION_POST'] = $WEBSITE_PATH.'newdiscussionpost.php';
$PAGE['NEW_DISCUSSION_THREAD'] = $WEBSITE_PATH.'newdiscussionthread.php';
$PAGE['NEW_PRIVATE_MESSAGE'] = $WEBSITE_PATH.'newprivatemessage.php';
$PAGE['NEW_THEME'] = $WEBSITE_PATH.'newtheme.php';
$PAGE['OUTBOX'] = $WEBSITE_PATH.'outbox.php';
$PAGE['PRIVATE_MESSAGING'] = $WEBSITE_PATH.'privatemessaging.php';
$PAGE['PREMIUM'] = $WEBSITE_PATH.'premium.php';
$PAGE['PREMIUM_ACTIVATE'] = $WEBSITE_PATH.'premiumactivate.php';
$PAGE['RANKED'] = $WEBSITE_PATH.'ranked.php';
$PAGE['REGISTER'] = $WEBSITE_PATH.'register.php';
$PAGE['SEARCH'] = $WEBSITE_PATH.'search.php';
$PAGE['SETTINGS'] = $WEBSITE_PATH.'editsettings.php';
$PAGE['STATISTICS'] = $WEBSITE_PATH.'statistics.php';
$PAGE['THEMES'] = $WEBSITE_PATH.'themes.php';
$PAGE['TRANSFER_BALANCE'] = $WEBSITE_PATH.'transferbalance.php';
$PAGE['TRANSLATE'] = $WEBSITE_PATH.'translate.php';
$PAGE['VOTE'] = $WEBSITE_PATH.'vote.php';

// Pages that shouldn't be redirected back to using the referer in a login/logout situation
$REDIRECT_BLACKLIST = array('ENTRY', 'INVITE', 'INDEX', 'HOME', 'BLOG', 'CHANGE_PASSWORD', 'CONFIRM', 'SETTINGS', 'EDIT_PROFILE_PICTURE', 'LOST_PASSWORD', 'NO_JAVASCRIPT', 'REGISTER', 'OUTBOX', 'NEW_PRIVATE_MESSAGE', 'CONTACTS');
$WARNING_BLACKLIST = array('CRITIQUE', 'ENTRY', 'HALL_OF_FAME', 'PREMIUM', 'MARCH_PRIZE', 'LEGAL', 'INDEX', 'HELP', 'HOME', 'REGISTER', 'LOST_PASSWORD', 'MEMBERS', 'MEMBER', 'CONFIRM', 'CHANGE_PASSWORD', 'PRESS', 'PRIZE');

$REQUEST['ADD_TO_DONATORS'] = $REQUEST_PATH.'addtodonators.php';
$REQUEST['ADD_TO_FAVORITES'] = $REQUEST_PATH.'addtofavorites.php';
$REQUEST['ADD_TO_MODERATORS'] = $REQUEST_PATH.'addtomoderators.php';
$REQUEST['AVERAGE_CHART_DATA'] = $REQUEST_PATH.'averagechartdata.php';
$REQUEST['BAN'] = $REQUEST_PATH.'ban.php';
$REQUEST['BLOCK_PRIVATE_MESSAGES'] = $REQUEST_PATH.'blockprivatemessages.php';
$REQUEST['BRING_BACK_MEMBER'] = $REQUEST_PATH.'bringbackmember.php';
$REQUEST['CALCULATE_RANKINGS'] = $REQUEST_PATH.'calculaterankings.php';
$REQUEST['CANVAS_IPN'] = $REQUEST_PATH.'canvasipn.php';
$REQUEST['CAST_ENTRY_VOTE'] = $REQUEST_PATH.'castentryvote.php';
$REQUEST['CAST_THEME_VOTE'] = $REQUEST_PATH.'castthemevote.php';
$REQUEST['CHECK_EMAIL'] = $REQUEST_PATH.'checkemail.php';
$REQUEST['COMMUNITY_APPEAL'] = $REQUEST_PATH.'communityappeal.php';
$REQUEST['COMMUNITY_PICTURE_RESET'] = $REQUEST_PATH.'communitypicturereset.php';
$REQUEST['COMMUNITY_PICTURE_UPLOAD'] = $REQUEST_PATH.'communitypictureupload.php';
$REQUEST['CURRENCY_PAYMENT'] = $REQUEST_PATH.'currencypayment.php';
$REQUEST['DELETE_ACCOUNT'] = $REQUEST_PATH.'deleteaccount.php';
$REQUEST['DELETE_ALERT'] = $REQUEST_PATH.'deletealert.php';
$REQUEST['DELETE_COMMENT'] = $REQUEST_PATH.'deletecomment.php';
$REQUEST['DELETE_COMMUNITY'] = $REQUEST_PATH.'deletecommunity.php';
$REQUEST['DELETE_COMPETITION'] = $REQUEST_PATH.'deletecompetition.php';
$REQUEST['DELETE_DISCUSSION_POST'] = $REQUEST_PATH.'deletediscussionpost.php';
$REQUEST['DELETE_ENTRY'] = $REQUEST_PATH.'deleteentry.php';
$REQUEST['DELETE_OUTBOX_MESSAGE'] = $REQUEST_PATH.'deleteoutboxmessage.php';
$REQUEST['DELETE_PRIVATE_MESSAGE'] = $REQUEST_PATH.'deleteprivatemessage.php';
$REQUEST['DELETE_TEAM_MEMBER'] = $REQUEST_PATH.'deleteteammember.php';
$REQUEST['DELETE_THEME'] = $REQUEST_PATH.'deletetheme.php';
$REQUEST['DISQUALIFY'] = $REQUEST_PATH.'disqualify.php';
$REQUEST['DOWNLOAD_ORIGINAL'] = $REQUEST_PATH.'downloadoriginal.php';
$REQUEST['EDIT_COMMUNITY'] = $REQUEST_PATH.'editcommunity.php';
$REQUEST['EDIT_TEAM_MEMBER'] = $REQUEST_PATH.'editteammember.php';
$REQUEST['ENTRY'] = $REQUEST_PATH.'entry.php';
$REQUEST['ENTRY_RESET'] = $REQUEST_PATH.'entryreset.php';
$REQUEST['ENTRY_UPLOAD'] = $REQUEST_PATH.'entryupload.php';
$REQUEST['GENERATE_PREMIUM'] = $REQUEST_PATH.'generatepremium.php';
$REQUEST['GET_ALERTS'] = $REQUEST_PATH.'getalerts.php';
$REQUEST['GET_POINTS'] = $REQUEST_PATH.'getpoints.php';
$REQUEST['GIVE_POINTS'] = $REQUEST_PATH.'givepoints.php';
$REQUEST['HIDE_COMPETITION'] = $REQUEST_PATH.'hidecompetition.php';
$REQUEST['HIDE_GENERAL_DISCUSSION'] = $REQUEST_PATH.'hidegeneraldiscussion.php';
$REQUEST['IMPERSONATE_USER'] = $REQUEST_PATH.'spoofuser.php';
$REQUEST['JOIN_COMMUNITY'] = $REQUEST_PATH.'joincommunity.php';
$REQUEST['JOINABLE_COMMUNITY_LIST'] = $REQUEST_PATH.'joinablecommunitylist.php';
$REQUEST['LEAVE_COMMUNITY'] = $REQUEST_PATH.'leavecommunity.php';
$REQUEST['LOGIN'] = $REQUEST_PATH.'login.php';
$REQUEST['LOGOUT'] = $REQUEST_PATH.'logout.php';
$REQUEST['MERGE_COMMUNITY'] = $REQUEST_PATH.'mergecommunity.php';
$REQUEST['NEW_BUG_REPORT'] = $REQUEST_PATH.'newbugreport.php';
$REQUEST['NEW_COMMENT'] = $REQUEST_PATH.'newcomment.php';
$REQUEST['NEW_DISCUSSION_POST'] = $REQUEST_PATH.'newdiscussionpost.php';
$REQUEST['NEW_DISCUSSION_THREAD'] = $REQUEST_PATH.'newdiscussionthread.php';
$REQUEST['NEW_EMAIL_INVITE'] = $REQUEST_PATH.'newemailinvite.php';
$REQUEST['NEW_POSTCARD_INVITE'] = $REQUEST_PATH.'newpostcardinvite.php';
$REQUEST['NEW_PRIVATE_MESSAGE'] = $REQUEST_PATH.'newprivatemessage.php';
$REQUEST['NEW_THEME'] = $REQUEST_PATH.'newtheme.php';
$REQUEST['NEXT_ENTRY'] = $REQUEST_PATH.'nextentry.php';
$REQUEST['PAYPAL_TRANSFER'] = $REQUEST_PATH.'paypaltransfer.php';
$REQUEST['PERCENTILE_CHART_DATA'] = $REQUEST_PATH.'percentilechartdata.php';
$REQUEST['PERSISTENT_TOKEN_TO_EID'] = $REQUEST_PATH.'persistenttokentoeid.php';
$REQUEST['PREMIUM_ACTIVATE'] = $REQUEST_PATH.'premiumactivate.php';
$REQUEST['PREVIOUS_ENTRY'] = $REQUEST_PATH.'previousentry.php';
$REQUEST['PROFILE_PICTURE_RESET'] = $REQUEST_PATH.'profilepicturereset.php';
$REQUEST['PROFILE_PICTURE_UPLOAD'] = $REQUEST_PATH.'profilepictureupload.php';
$REQUEST['REMOVE_FROM_DONATORS'] = $REQUEST_PATH.'removefromdonators.php';
$REQUEST['REMOVE_FROM_FAVORITES'] = $REQUEST_PATH.'removefromfavorites.php';
$REQUEST['REMOVE_FROM_MODERATORS'] = $REQUEST_PATH.'removefrommoderators.php';
$REQUEST['REPLY_TO_COMMENT'] = $REQUEST_PATH.'replytocomment.php';
$REQUEST['REQUALIFY'] = $REQUEST_PATH.'requalify.php';
$REQUEST['SAVE_COMMUNITY'] = $REQUEST_PATH.'savecommunity.php';
$REQUEST['SEARCH_TRANSLATION'] = $REQUEST_PATH.'searchtranslation.php';
$REQUEST['SHOW_GENERAL_DISCUSSION'] = $REQUEST_PATH.'showgeneraldiscussion.php';
$REQUEST['SEARCH_TRANSLATION'] = $REQUEST_PATH.'searchtranslation.php';
$REQUEST['STATISTIC_DATA'] = $REQUEST_PATH.'statisticdata.php';
$REQUEST['TOGGLE_ENTRY_COMMENT_NOTIFICATION'] = $REQUEST_PATH.'toggleentrycommentnotification.php';
$REQUEST['TRANSFER_ADMINISTRATION'] = $REQUEST_PATH.'transferadministration.php';
$REQUEST['TRANSFER_POINTS'] = $REQUEST_PATH.'transferpoints.php';
$REQUEST['TRANSLATE'] = $REQUEST_PATH.'translate.php';
$REQUEST['UNBLOCK_PRIVATE_MESSAGES'] = $REQUEST_PATH.'unblockprivatemessages.php';
$REQUEST['UPDATE_COMMUNITIES_PER_PAGE'] = $REQUEST_PATH.'updatecommunitiesperpage.php';
$REQUEST['UPDATE_CUSTOM_URL'] = $REQUEST_PATH.'updatecustomurl.php';
$REQUEST['UPDATE_CROPPING'] = $REQUEST_PATH.'updatecropping.php';
$REQUEST['UPDATE_EMAIL'] = $REQUEST_PATH.'updateemail.php';
$REQUEST['UPDATE_LID'] = $REQUEST_PATH.'updatelid.php';
$REQUEST['UPDATE_PRIVATE_MESSAGE_STATUS'] = $REQUEST_PATH.'updateprivatemessagestatus.php';
$REQUEST['UPDATE_NAME'] = $REQUEST_PATH.'updatename.php';
$REQUEST['UPDATE_PAGING'] = $REQUEST_PATH.'updatepaging.php';
$REQUEST['UPDATE_PROFILE'] = $REQUEST_PATH.'updateprofile.php';
$REQUEST['UPDATE_WEB_HISTORY'] = $REQUEST_PATH.'updatewebhistory.php';
$REQUEST['UPLOAD_PROGRESS'] = $WEBSITE_PATH.'uploadprogress';
$REQUEST['VOTING_STATISTICS'] = $REQUEST_PATH.'votingstatistics.php';

$CSS['BUG_REPORT'] = $CSS_PATH.'bugreport';
$CSS['BRING_BACK'] = $CSS_PATH.'bringback';
$CSS['CHANGE_PASSWORD'] = $CSS_PATH.'changepassword';
$CSS['COMMENTS'] = $CSS_PATH.'comments';
$CSS['COMMUNITIES'] = $CSS_PATH.'communities';
$CSS['COMPETE'] = $CSS_PATH.'compete';
$CSS['DISCUSS'] = $CSS_PATH.'discuss';
$CSS['EDIT_CROPPING'] = $CSS_PATH.'editcropping';
$CSS['EDITABLE_PICTURE'] = $CSS_PATH.'editablepicture';
$CSS['ENTRY_ORDER'] = $CSS_PATH.'entryorder';
$CSS['GRID'] = $CSS_PATH.'grid';
$CSS['HALL_OF_FAME'] = $CSS_PATH.'halloffame';
$CSS['HOME'] = $CSS_PATH.'home';
$CSS['INVITE'] = $CSS_PATH.'invite';
$CSS['MAIN'] = $CSS_PATH.'main';
$CSS['MARCH_PRIZE'] = $CSS_PATH.'marchprize';
$CSS['MEMBERS'] = $CSS_PATH.'members';
$CSS['NEW_PRIVATE_MESSAGE'] = $CSS_PATH.'newprivatemessage';
$CSS['PREMIUM'] = $CSS_PATH.'premium';
$CSS['PRESS'] = $CSS_PATH.'press';
$CSS['PRIZE'] = $CSS_PATH.'prize';
$CSS['REGISTER'] = $CSS_PATH.'register';
$CSS['SETTINGS'] = $CSS_PATH.'settings';
$CSS['STATISTICS'] = $CSS_PATH.'statistics';
$CSS['THEMES'] = $CSS_PATH.'themes';
$CSS['TRANSLATE'] = $CSS_PATH.'translate';
$CSS['VOTE'] = $CSS_PATH.'vote';

$CSS_LOCAL['BUG_REPORT'] = $CSS_LOCAL_PATH.'bugreport.css';
$CSS_LOCAL['BRING_BACK'] = $CSS_LOCAL_PATH.'bringback.css';
$CSS_LOCAL['CHANGE_PASSWORD'] = $CSS_LOCAL_PATH.'changepassword.css';
$CSS_LOCAL['COMMENTS'] = $CSS_LOCAL_PATH.'comments.css';
$CSS_LOCAL['COMMUNITIES'] = $CSS_LOCAL_PATH.'communities.css';
$CSS_LOCAL['COMPETE'] = $CSS_LOCAL_PATH.'compete.css';
$CSS_LOCAL['DISCUSS'] = $CSS_LOCAL_PATH.'discuss.css';
$CSS_LOCAL['EDIT_CROPPING'] = $CSS_LOCAL_PATH.'editcropping.css';
$CSS_LOCAL['EDITABLE_PICTURE'] = $CSS_LOCAL_PATH.'editablepicture.css';
$CSS_LOCAL['ENTRY_ORDER'] = $CSS_LOCAL_PATH.'entryorder.css';
$CSS_LOCAL['GRID'] = $CSS_LOCAL_PATH.'grid.css';
$CSS_LOCAL['HALL_OF_FAME'] = $CSS_LOCAL_PATH.'halloffame.css';
$CSS_LOCAL['HOME'] = $CSS_LOCAL_PATH.'home.css';
$CSS_LOCAL['INVITE'] = $CSS_LOCAL_PATH.'invite.css';
$CSS_LOCAL['MAIN'] = $CSS_LOCAL_PATH.'main.css';
$CSS_LOCAL['MARCH_PRIZE'] = $CSS_LOCAL_PATH.'marchprize.css';
$CSS_LOCAL['MEMBERS'] = $CSS_LOCAL_PATH.'members.css';
$CSS_LOCAL['NEW_PRIVATE_MESSAGE'] = $CSS_LOCAL_PATH.'newprivatemessage.css';
$CSS_LOCAL['PREMIUM'] = $CSS_LOCAL_PATH.'premium.css';
$CSS_LOCAL['PRESS'] = $CSS_LOCAL_PATH.'press.css';
$CSS_LOCAL['PRIZE'] = $CSS_LOCAL_PATH.'prize.css';
$CSS_LOCAL['REGISTER'] = $CSS_LOCAL_PATH.'register.css';
$CSS_LOCAL['SETTINGS'] = $CSS_LOCAL_PATH.'settings.css';
$CSS_LOCAL['STATISTICS'] = $CSS_LOCAL_PATH.'statistics.css';
$CSS_LOCAL['THEMES'] = $CSS_LOCAL_PATH.'themes.css';
$CSS_LOCAL['TRANSLATE'] = $CSS_LOCAL_PATH.'translate.css';
$CSS_LOCAL['VOTE'] = $CSS_LOCAL_PATH.'vote.css';

$JS['B64'] = $JS_3RDPARTY_PATH.'b64';
$JS['BOARD'] = $JS_PATH.'board';
$JS['BRING_BACK'] = $JS_PATH.'bringback';
$JS['BRING_BACK_MEMBER'] = $JS_PATH.'bringbackmember';
$JS['BUG_REPORT'] = $JS_PATH.'bugreport';
$JS['CHANGE_EMAIL'] = $JS_PATH.'changeemail';
$JS['CHANGE_PASSWORD'] = $JS_PATH.'changepassword';
$JS['COMMENTS'] = $JS_PATH.'comments';
$JS['COMMUNITY'] = $JS_PATH.'community';
$JS['COMMUNITY_APPEAL'] = $JS_PATH.'communityappeal';
$JS['COMPETE'] = $JS_PATH.'compete';
$JS['CRITIQUE'] = $JS_PATH.'critique';
$JS['DEBUGGER'] = $JS_3RDPARTY_PATH.'Debugger';
$JS['DISCUSS'] = $JS_PATH.'discuss';
$JS['DISCUSSION_THREAD'] = $JS_PATH.'discussionthread';
$JS['EDIT_COMMUNITY'] = $JS_PATH.'editcommunity';
$JS['EDIT_CROPPING'] = $JS_PATH.'editcropping';
$JS['EDIT_SETTINGS'] = $JS_PATH.'editsettings';
$JS['EDITABLE_PICTURE'] = $JS_PATH.'editablepicture';
$JS['ENTRY'] = $JS_PATH.'entry';
$JS['ENTRY_ORDER'] = $JS_PATH.'entryorder';
$JS['FAVORITES'] = $JS_PATH.'favorites';
$JS['GOOGLE'] = 'http://www.google.com/jsapi';
$JS['GRID'] = $JS_PATH.'grid';
$JS['HALL_OF_FAME'] = $JS_PATH.'halloffame';
$JS['HOME'] = $JS_PATH.'home';
$JS['INVITE'] = $JS_PATH.'invite';
$JS['JSJAC'] = $JS_3RDPARTY_PATH.'jsjac';
$JS['MAIN'] = $JS_PATH.'main';
$JS['MD5'] = $JS_3RDPARTY_PATH.'md5';
$JS['MEMBER'] = $JS_PATH.'member';
$JS['MEMBER_SEARCH'] = $JS_PATH.'membersearch';
$JS['MEMBERS'] = $JS_PATH.'members';
$JS['MERGE_COMMUNITY'] = $JS_PATH.'mergecommunity';
$JS['NEW_DISCUSSION_POST'] = $JS_PATH.'newdiscussionpost';
$JS['NEW_DISCUSSION_THREAD'] = $JS_PATH.'newdiscussionthread';
$JS['NEW_PRIVATE_MESSAGE'] = $JS_PATH.'newprivatemessage';
$JS['NEW_THEME'] = $JS_PATH.'newtheme';
$JS['PREMIUM'] = $JS_PATH.'premium';
$JS['PREMIUM_ACTIVATE'] = $JS_PATH.'premiumactivate';
$JS['PRIVATE_MESSAGING'] = $JS_PATH.'privatemessaging';
$JS['PUBSUB'] = $JS_3RDPARTY_PATH.'pubsub';
$JS['REGISTER'] = $JS_PATH.'register';
$JS['SHA1'] = $JS_3RDPARTY_PATH.'sha1';
$JS['STROPHE'] = $JS_3RDPARTY_PATH.'strophe';
$JS['SWFOBJECT'] = $JS_3RDPARTY_PATH.'swfobject';
$JS['THEME_LIST'] = $JS_PATH.'themelist';
$JS['TINY_MCE'] = $JS_3RDPARTY_PATH.'tiny_mce/tiny_mce.js';
$JS['TRANSFER_BALANCE'] = $JS_PATH.'transferbalance';
$JS['TRANSLATE'] = $JS_PATH.'translate';
$JS['VIEW_COMMUNITIES'] = $JS_PATH.'viewcommunities';
$JS['VIEW_HALL_OF_FAME'] = $JS_PATH.'viewhalloffame';
$JS['WEBTOOLKIT'] = $JS_3RDPARTY_PATH.'webtoolkit.url';

$JS_LOCAL['B64'] = $JS_3RDPARTY_LOCAL_PATH.'b64.js';
$JS_LOCAL['BOARD'] = $JS_LOCAL_PATH.'board.js';
$JS_LOCAL['BRING_BACK'] = $JS_LOCAL_PATH.'bringback.js';
$JS_LOCAL['BRING_BACK_MEMBER'] = $JS_LOCAL_PATH.'bringbackmember.js';
$JS_LOCAL['BUG_REPORT'] = $JS_LOCAL_PATH.'bugreport.js';
$JS_LOCAL['CHANGE_EMAIL'] = $JS_LOCAL_PATH.'changeemail.js';
$JS_LOCAL['CHANGE_PASSWORD'] = $JS_LOCAL_PATH.'changepassword.js';
$JS_LOCAL['COMMENTS'] = $JS_LOCAL_PATH.'comments.js';
$JS_LOCAL['COMMUNITY'] = $JS_LOCAL_PATH.'community.js';
$JS_LOCAL['COMMUNITY_APPEAL'] = $JS_LOCAL_PATH.'communityappeal.js';
$JS_LOCAL['COMPETE'] = $JS_LOCAL_PATH.'compete.js';
$JS_LOCAL['CRITIQUE'] = $JS_LOCAL_PATH.'critique.js';
$JS_LOCAL['DEBUGGER'] = $JS_3RDPARTY_LOCAL_PATH.'Debugger.js';
$JS_LOCAL['DISCUSS'] = $JS_LOCAL_PATH.'discuss.js';
$JS_LOCAL['DISCUSSION_THREAD'] = $JS_LOCAL_PATH.'discussionthread.js';
$JS_LOCAL['EDIT_COMMUNITY'] = $JS_LOCAL_PATH.'editcommunity.js';
$JS_LOCAL['EDIT_CROPPING'] = $JS_LOCAL_PATH.'editcropping.js';
$JS_LOCAL['EDIT_SETTINGS'] = $JS_LOCAL_PATH.'editsettings.js';
$JS_LOCAL['EDITABLE_PICTURE'] = $JS_LOCAL_PATH.'editablepicture.js';
$JS_LOCAL['ENTRY'] = $JS_LOCAL_PATH.'entry.js';
$JS_LOCAL['ENTRY_ORDER'] = $JS_LOCAL_PATH.'entryorder.js';
$JS_LOCAL['FAVORITES'] = $JS_LOCAL_PATH.'favorites.js';
$JS_LOCAL['GRID'] = $JS_LOCAL_PATH.'grid.js';
$JS_LOCAL['HALL_OF_FAME'] = $JS_LOCAL_PATH.'halloffame.js';
$JS_LOCAL['HOME'] = $JS_LOCAL_PATH.'home.js';
$JS_LOCAL['INVITE'] = $JS_LOCAL_PATH.'invite.js';
$JS_LOCAL['JSJAC'] = $JS_3RDPARTY_LOCAL_PATH.'jsjac.js';
$JS_LOCAL['MAIN'] = $JS_LOCAL_PATH.'main.js';
$JS_LOCAL['MD5'] = $JS_3RDPARTY_LOCAL_PATH.'md5.js';
$JS_LOCAL['MEMBER'] = $JS_LOCAL_PATH.'member.js';
$JS_LOCAL['MEMBER_SEARCH'] = $JS_LOCAL_PATH.'membersearch.js';
$JS_LOCAL['MEMBERS'] = $JS_LOCAL_PATH.'members.js';
$JS_LOCAL['MERGE_COMMUNITY'] = $JS_LOCAL_PATH.'mergecommunity.js';
$JS_LOCAL['NEW_DISCUSSION_POST'] = $JS_LOCAL_PATH.'newdiscussionpost.js';
$JS_LOCAL['NEW_DISCUSSION_THREAD'] = $JS_LOCAL_PATH.'newdiscussionthread.js';
$JS_LOCAL['NEW_PRIVATE_MESSAGE'] = $JS_LOCAL_PATH.'newprivatemessage.js';
$JS_LOCAL['NEW_THEME'] = $JS_LOCAL_PATH.'newtheme.js';
$JS_LOCAL['PREMIUM'] = $JS_LOCAL_PATH.'premium.js';
$JS_LOCAL['PREMIUM_ACTIVATE'] = $JS_LOCAL_PATH.'premiumactivate.js';
$JS_LOCAL['PRIVATE_MESSAGING'] = $JS_LOCAL_PATH.'privatemessaging.js';
$JS_LOCAL['PUBSUB'] = $JS_3RDPARTY_LOCAL_PATH.'pubsub.js';
$JS_LOCAL['REGISTER'] = $JS_LOCAL_PATH.'register.js';
$JS_LOCAL['SHA1'] = $JS_3RDPARTY_LOCAL_PATH.'sha1.js';
$JS_LOCAL['STROPHE'] = $JS_3RDPARTY_LOCAL_PATH.'strophe.js';
$JS_LOCAL['SWFOBJECT'] = $JS_3RDPARTY_LOCAL_PATH.'swfobject.js';
$JS_LOCAL['THEME_LIST'] = $JS_LOCAL_PATH.'themelist.js';
$JS_LOCAL['TRANSFER_BALANCE'] = $JS_LOCAL_PATH.'transferbalance.js';
$JS_LOCAL['TRANSLATE'] = $JS_LOCAL_PATH.'translate.js';
$JS_LOCAL['VIEW_COMMUNITIES'] = $JS_LOCAL_PATH.'viewcommunities.js';
$JS_LOCAL['VIEW_HALL_OF_FAME'] = $JS_LOCAL_PATH.'viewhalloffame.js';
$JS_LOCAL['WEBTOOLKIT'] = $JS_3RDPARTY_LOCAL_PATH.'webtoolkit.url.js';

$RSS['COMPETITION'] = $RSS_PATH.'competition.php';

// Database information
$TABLE['ALERT'] = 'alert';
$TABLE['ALERT_INSTANCE'] = 'alert_instance';
$TABLE['ALERT_VARIABLE'] = 'alert_variable';
$TABLE['COMMENT_INDEX'] = 'comment_index';
$TABLE['COMMUNITY'] = 'community';
$TABLE['COMMUNITY_LABEL'] = 'community_label';
$TABLE['COMMUNITY_MEMBERSHIP'] = 'community_membership';
$TABLE['COMMUNITY_MODERATOR'] = 'community_moderator';
$TABLE['COMMUNITY_NAME_INDEX'] = 'community_name_index';
$TABLE['COMPETITION'] = 'competition';
$TABLE['COMPETITION_HIDE'] = 'competition_hide';
$TABLE['DISCUSSION_POST'] = 'discussion_post';
$TABLE['DISCUSSION_POST_INDEX'] = 'discussion_post_index';
$TABLE['DISCUSSION_THREAD'] = 'discussion_thread';
$TABLE['DISCUSSION_THREAD_INDEX'] = 'discussion_thread_index';
$TABLE['EMAIL_CAMPAIGN'] = 'email_campaign';
$TABLE['ENTRY'] = 'entry';
$TABLE['ENTRY_COMMENT_NOTIFICATION'] = 'entry_comment_notification';
$TABLE['ENTRY_VOTE'] = 'entry_vote';
$TABLE['ENTRY_VOTE_BLOCKED'] = 'entry_vote_blocked';
$TABLE['FAVORITE'] = 'favorite';
$TABLE['I18N'] = 'i18n';
$TABLE['INSIGHTFUL_MARK'] = 'insightful_mark';
$TABLE['PICTURE_FILE'] = 'picture_file';
$TABLE['PICTURE'] = 'picture';
$TABLE['POINTS_VALUE'] = 'points_value';
$TABLE['PREMIUM_CODE'] = 'premium_code';
$TABLE['PRIVATE_MESSAGE'] = 'private_message';
$TABLE['PRIZE_WINNER'] = 'prize_winner';
$TABLE['SPECIAL_USER'] = 'special_user';
$TABLE['STATISTIC'] = 'statistic';
$TABLE['TEAM_MEMBERSHIP'] = 'team_membership';
$TABLE['THEME'] = 'theme';
$TABLE['THEME_VOTE'] = 'theme_vote';
$TABLE['TOKEN'] = 'token';
$TABLE['TRACKBACK'] = 'trackback';
$TABLE['USER'] = 'user';
$TABLE['USER_BLOCK'] = 'user_block';
$TABLE['USER_LEVEL'] = 'user_level';
$TABLE['USER_NAME_HISTORY'] = 'user_name_history';
$TABLE['USER_NAME_INDEX'] = 'user_name_index';
$TABLE['USER_HOST_COOKIE_HISTORY'] = 'user_host_cookie_history';
$TABLE['USER_IP_HISTORY'] = 'user_ip_history';
$TABLE['USER_PAGING'] = 'user_paging';
$TABLE['USER_REMEMBER_SESSION_ID'] = 'user_remember_session_id';
$TABLE['USER_WEB_HISTORY'] = 'user_web_history';

$COLUMN['ACTIVATION_CODE'] = 'activation_code';
$COLUMN['ACTIVE_MEMBER_COUNT'] = 'active_member_count';
$COLUMN['AD_TEXT'] = 'ad_text';
$COLUMN['AFFILIATE_UID'] = 'affiliate_uid';
$COLUMN['AID'] = 'aid';
$COLUMN['ALERT_EMAIL'] = 'alert_email';
$COLUMN['ALLOW_SALES'] = 'allow_sales';
$COLUMN['ATID'] = 'atid';
$COLUMN['AUTHOR_UID'] = 'author_uid';
$COLUMN['BALANCE'] = 'balance';
$COLUMN['BIG_FID'] = 'big_fid';
$COLUMN['BIG_STATUS'] = 'big_status';
$COLUMN['BIG_TIMESTAMP'] = 'big_timestamp';
$COLUMN['BLOCKED_UID'] = 'blocked_uid';
$COLUMN['BOSH_PASSWORD'] = 'bosh_password';
$COLUMN['CATEGORY'] = 'category';
$COLUMN['CHUNK'] = 'chunk';
$COLUMN['CID'] = 'cid';
$COLUMN['CLID'] = 'clid';
$COLUMN['CODE'] = 'code';
$COLUMN['COMMENTS_RECEIVED'] = 'comments_received';
$COLUMN['COMMUNITIES_PER_PAGE'] = 'communities_per_page';
$COLUMN['COMMUNITY_FILTER_ICONS'] = 'community_filter_icons';
$COLUMN['COUNT'] = 'count';
$COLUMN['CUSTOM_URL'] = 'custom_url';
$COLUMN['CREATION_TIME'] = 'creation_time';
$COLUMN['DELETION_POINTS'] = 'deletion_points';
$COLUMN['DESCRIPTION'] = 'description';
$COLUMN['DESTINATION_UID'] = 'destination_uid';
$COLUMN['DIMENSION'] = 'dimension';
$COLUMN['DISPLAY_GENERAL_DISCUSSION'] = 'display_general_discussion';
$COLUMN['DISPLAY_RANK'] = 'display_rank';
$COLUMN['DURATION'] = 'duration';
$COLUMN['EID'] = 'eid';
$COLUMN['EMAIL'] = 'email';
$COLUMN['END_TIME'] = 'end_time';
$COLUMN['ENTER_LENGTH'] = 'enter_length';
$COLUMN['ENTRIES_COUNT'] = 'entries_count';
$COLUMN['ETID'] = 'etid';
$COLUMN['EXIF_DATE_TIME_ORIGINAL'] = 'exif_date_time_original';
$COLUMN['EXIF_EXPOSURE_TIME'] = 'exif_exposure_time';
$COLUMN['EXIF_FOCAL_LENGTH'] = 'exif_focal_length';
$COLUMN['EXIF_FLASH'] = 'exif_flash';
$COLUMN['EXIF_FNUMBER'] = 'exif_fnumber';
$COLUMN['EXIF_ISO'] = 'exif_iso';
$COLUMN['EXIF_MAKE'] = 'exif_make';
$COLUMN['EXIF_MODEL'] = 'exif_model';
$COLUMN['EXIF_SOFTWARE'] = 'exif_software';
$COLUMN['FAVORITES_PER_PAGE'] = 'favorites_per_page';
$COLUMN['FID'] = 'fid';
$COLUMN['FREQUENCY'] = 'frequency';
$COLUMN['HASH'] = 'hash';
$COLUMN['HEIGHT'] = 'height';
$COLUMN['HIDE_ADS'] = 'hide_ads';
$COLUMN['HOME_ENTRIES_PER_PAGE'] = 'home_entries_per_page';
$COLUMN['HOF_COMPETITIONS_PER_PAGE'] = 'hof_competitions_per_page';
$COLUMN['HOST_COOKIE'] = 'host_cookie';
$COLUMN['HUGE_FID'] = 'huge_fid';
$COLUMN['HUGE_STATUS'] = 'huge_status';
$COLUMN['HUGE_TIMESTAMP'] = 'huge_timestamp';
$COLUMN['INACTIVE_SINCE'] = 'inactive_since';
$COLUMN['INDEXING_STATUS'] = 'indexing_status';
$COLUMN['IP'] = 'ip';
$COLUMN['IS_DEFAULT'] = 'is_default';
$COLUMN['JOIN_TIME'] = 'join_time';
$COLUMN['LAST_TIME'] = 'last_time';
$COLUMN['LAZY'] = 'lazy';
$COLUMN['LEVEL'] = 'level';
$COLUMN['LID'] = 'lid';
$COLUMN['MARKUP'] = 'markup';
$COLUMN['MAXIMUM_THEME_COUNT'] = 'maximum_theme_count';
$COLUMN['MAXIMUM_THEME_COUNT_PER_MEMBER'] = 'maximum_theme_count_per_member';
$COLUMN['MEDIUM_FID'] = 'medium_fid';
$COLUMN['MEDIUM_STATUS'] = 'medium_status';
$COLUMN['MEDIUM_TIMESTAMP'] = 'medium_timestamp';
$COLUMN['MEMBERSHIP_AGE'] = 'membership_age';
$COLUMN['NAME'] = 'name';
$COLUMN['NAME_TIME'] = 'name_time';
$COLUMN['NID'] = 'nid';
$COLUMN['OFFSET_X'] = 'offset_x';
$COLUMN['OFFSET_Y'] = 'offset_y';
$COLUMN['OID'] = 'oid';
$COLUMN['ORIGINAL_FID'] = 'original_fid';
$COLUMN['OUTBOX_STATUS'] = 'outbox_status';
$COLUMN['PASSWORD'] = 'password';
$COLUMN['PGID'] = 'pgid';
$COLUMN['PID'] = 'pid';
$COLUMN['PMID'] = 'pmid';
$COLUMN['POINTS'] = 'points';
$COLUMN['POSTS_PER_PAGE'] = 'posts_per_page';
$COLUMN['PREMIUM_TIME'] = 'premium_time';
$COLUMN['PRIVATE_MESSAGES_PER_PAGE'] = 'private_messages_per_page';
$COLUMN['PVID'] = 'pvid';
$COLUMN['RANK'] = 'rank';
$COLUMN['REPLY_AID'] = 'reply_aid';
$COLUMN['REPLY_TO_OID'] = 'reply_to_oid';
$COLUMN['RULES'] = 'rules';
$COLUMN['SCORE'] = 'score';
$COLUMN['SESSION_ID'] = 'session_id';
$COLUMN['SID'] = 'sid';
$COLUMN['SMALL_FID'] = 'small_fid';
$COLUMN['SMALL_STATUS'] = 'small_status';
$COLUMN['SMALL_TIMESTAMP'] = 'small_timestamp';
$COLUMN['SOURCE_UID'] = 'source_uid';
$COLUMN['START_TIME'] = 'start_time';
$COLUMN['STATUS'] = 'status';
$COLUMN['TEXT'] = 'text';
$COLUMN['THEME_COST'] = 'theme_cost';
$COLUMN['THEME_MINIMUM_SCORE'] = 'theme_minimum_score';
$COLUMN['THEME_RESTRICT_USERS'] = 'theme_restrict_users';
$COLUMN['THEMES_PER_PAGE'] = 'themes_per_page';
$COLUMN['THREADS_PER_PAGE'] = 'threads_per_page';
$COLUMN['TID'] = 'tid';
$COLUMN['TIME_SHIFT'] = 'time_shift';
$COLUMN['TIMESTAMP'] = 'timestamp';
$COLUMN['TITLE'] = 'title';
$COLUMN['TRANSLATE'] = 'translate';
$COLUMN['TRANSLATION'] = 'translation';
$COLUMN['TXNID'] = 'txnid';
$COLUMN['UID'] = 'uid';
$COLUMN['URL'] = 'url';
$COLUMN['USER_AGENT'] = 'user_agent';
$COLUMN['VALUE'] = 'value';
$COLUMN['VOTE_BLOCK_TIMESTAMP'] = 'vote_block_timestamp';
$COLUMN['VOTE_LENGTH'] = 'vote_length';
$COLUMN['VOTE_TIME'] = 'vote_time';
$COLUMN['VOTER_UID'] = 'voter_uid';
$COLUMN['WATERMARK'] = 'watermark';
$COLUMN['WATERMARK_OPACITY'] = 'watermark_opacity';
$COLUMN['WIDTH'] = 'width';
$COLUMN['WORD'] = 'word';
$COLUMN['XID'] = 'xid';

$USER_STATUS['UNREGISTERED'] = 0;
$USER_STATUS['ACTIVE'] = 1;
$USER_STATUS['DELETED'] = 2;
$USER_STATUS['BANNED'] = 3;

$DISCUSSION_THREAD_STATUS['ACTIVE'] = 0;
$DISCUSSION_THREAD_STATUS['LOCKED'] = 1;
$DISCUSSION_THREAD_STATUS['DELETED'] = 2;
$DISCUSSION_THREAD_STATUS['MODERATED'] = 3;
$DISCUSSION_THREAD_STATUS['ANONYMOUS'] = 4;
$DISCUSSION_THREAD_STATUS['ENTRY'] = 5;
$DISCUSSION_THREAD_STATUS['BANNED'] = 6;

$DISCUSSION_POST_STATUS['POSTED'] = 0;
$DISCUSSION_POST_STATUS['DELETED'] = 2;
$DISCUSSION_POST_STATUS['MODERATED'] = 3;
$DISCUSSION_POST_STATUS['ANONYMOUS'] = 4;
$DISCUSSION_POST_STATUS['BANNED'] = 5;

$USER_LEVEL['END_USER'] = 1;
$USER_LEVEL['MODERATOR'] = 2;
$USER_LEVEL['ADMINISTRATOR'] = 4;
$USER_LEVEL['TRANSLATOR'] = array($LANGUAGE['FR'] =>  8, 
									$LANGUAGE['DE'] =>  16, 
									$LANGUAGE['EN'] =>  32,
									$LANGUAGE['ES'] =>  64,
									$LANGUAGE['FI'] =>  128,
									$LANGUAGE['AF'] =>  256,
									$LANGUAGE['FA'] =>  1024,
									$LANGUAGE['IT'] =>  2048,
									$LANGUAGE['NL'] =>  9,
									$LANGUAGE['CA'] =>  10,
									$LANGUAGE['AR'] =>  14
								 );
$USER_LEVEL['DONATOR'] = 512;
$USER_LEVEL['ROLE_MODEL'] = 4096;
$USER_LEVEL['PREMIUM'] = 7;
$USER_LEVEL['BIG_COMMENTATOR'] = 11;
$USER_LEVEL['MIA'] = 12; // Hasn't used the website in a long time
$USER_LEVEL['MIA_APPEALED'] = 13; // Hasn't used the website in a long time and has been appealed to by a member

$PICTURE_SIZE['BIG'] = 1; // 256x256
$PICTURE_SIZE['HUGE'] = 4; // 600 max on one side
$PICTURE_SIZE['MEDIUM'] = 2; // 128x128
$PICTURE_SIZE['ORIGINAL'] = 0;
$PICTURE_SIZE['SMALL'] = 3; // 64x64
$PICTURE_SIZE['TINY'] = 5; // 32x32

$PICTURE_SIZE_DIMENSION_X[$PICTURE_SIZE['BIG']] = 256;
$PICTURE_SIZE_DIMENSION_X[$PICTURE_SIZE['HUGE']] = 940;
$PICTURE_SIZE_DIMENSION_X[$PICTURE_SIZE['MEDIUM']] = 128;
$PICTURE_SIZE_DIMENSION_X[$PICTURE_SIZE['SMALL']] = 64;
$PICTURE_SIZE_DIMENSION_X[$PICTURE_SIZE['TINY']] = 32;

$PICTURE_SIZE_DIMENSION_Y[$PICTURE_SIZE['BIG']] = 256;
$PICTURE_SIZE_DIMENSION_Y[$PICTURE_SIZE['HUGE']] = 600;
$PICTURE_SIZE_DIMENSION_Y[$PICTURE_SIZE['MEDIUM']] = 128;
$PICTURE_SIZE_DIMENSION_Y[$PICTURE_SIZE['SMALL']] = 64;
$PICTURE_SIZE_DIMENSION_Y[$PICTURE_SIZE['TINY']] = 32;

$PICTURE_FILE_STATUS['LOCAL'] = 0;
$PICTURE_FILE_STATUS['LOCAL_AND_S3'] = 2;
$PICTURE_FILE_STATUS['S3'] = 1;

$PICTURE_STATUS['FIRST'] = 0;
$PICTURE_STATUS['RAW'] = 1;
$PICTURE_STATUS['THUMBNAILED'] = 2;

$EXIF['DATE_TIME_ORIGINAL'] = 'DateTimeOriginal';
$EXIF['EXPOSURE_TIME'] = 'ExposureTime';
$EXIF['FOCAL_LENGTH'] = 'FocalLength';
$EXIF['FLASH'] = 'Flash';
$EXIF['FNUMBER'] = 'FNumber';
$EXIF['ISO'] = 'ISOSpeedRatings';
$EXIF['MAKE'] = 'Make';
$EXIF['MODEL'] = 'Model';
$EXIF['SOFTWARE'] = 'Software';

$EXIF_NAME[$EXIF['DATE_TIME_ORIGINAL']] = 'Capture date';
$EXIF_NAME[$EXIF['EXPOSURE_TIME']] = 'Exposure time';
$EXIF_NAME[$EXIF['FOCAL_LENGTH']] = 'Focal length';
$EXIF_NAME[$EXIF['FLASH']] = 'Flash';
$EXIF_NAME[$EXIF['FNUMBER']] = 'F-Stop';
$EXIF_NAME[$EXIF['ISO']] = 'ISO';
$EXIF_NAME[$EXIF['MAKE']] = 'Make';
$EXIF_NAME[$EXIF['MODEL']] = 'Model';
$EXIF_NAME[$EXIF['SOFTWARE']] = 'Software';

$EXIF_FLASH = array(
0 => "Flash did not fire",
1 => "Flash fired",
5 => "Strobe return light not detected",
7 => "Strobe return light detected",
9 => "Flash fired, compulsory flash mode",
13 => "Flash fired, compulsory flash mode, return light not detected",
15 => "Flash fired, compulsory flash mode, return light detected",
16 => "Flash did not fire, compulsory flash mode",
24 => "Flash did not fire, auto mode",
25 => "Flash fired, auto mode",
29 => "Flash fired, auto mode, return light not detected",
31 => "Flash fired, auto mode, return light detected",
32 => "No flash function",
65 => "Flash fired, red-eye reduction mode",
69 => "Flash fired, red-eye reduction mode, return light not detected",
71 => "Flash fired, red-eye reduction mode, return light detected",
73 => "Flash fired, compulsory flash mode, red-eye reduction mode",
77 => "Flash fired, compulsory flash mode, red-eye reduction mode, return light not detected",
79 => "Flash fired, compulsory flash mode, red-eye reduction mode, return light detected",
89 => "Flash fired, auto mode, red-eye reduction mode",
93 => "Flash fired, auto mode, return light not detected, red-eye reduction mode",
95 => "Flash fired, auto mode, return light detected, red-eye reduction mode"
);

$COMMUNITY_STATUS['ACTIVE'] = 0;
$COMMUNITY_STATUS['DELETED'] = 1;
$COMMUNITY_STATUS['ANONYMOUS'] = 2;
$COMMUNITY_STATUS['BANNED'] = 3;
$COMMUNITY_STATUS['INACTIVE'] = 4;

$PICTURE_CATEGORY['PROFILE'] = 1;
$PICTURE_CATEGORY['COMMUNITY'] = 2;
$PICTURE_CATEGORY['ENTRY'] = 3;

$PICTURE_CATEGORY_INML_OPTION[$PICTURE_CATEGORY['PROFILE']] = 'profile';
$PICTURE_CATEGORY_INML_OPTION[$PICTURE_CATEGORY['COMMUNITY']] = 'community';
$PICTURE_CATEGORY_INML_OPTION[$PICTURE_CATEGORY['ENTRY']] = 'entry';

$THEME_STATUS['SUGGESTED'] = 0;
$THEME_STATUS['DELETED'] = 1;
$THEME_STATUS['SELECTED'] = 2;
$THEME_STATUS['ANONYMOUS'] = 3;
$THEME_STATUS['BANNED'] = 4;

$THEME_VOTE_STATUS['CAST'] = 0;
$THEME_VOTE_STATUS['ANONYMOUS'] = 1;
$THEME_VOTE_STATUS['BANNED'] = 2;

$COMPETITION_STATUS['OPEN'] = 0;
$COMPETITION_STATUS['VOTING'] = 1;
$COMPETITION_STATUS['CLOSED'] = 2;

$ENTRY_STATUS['POSTED'] = 0;
$ENTRY_STATUS['DELETED'] = 1;
$ENTRY_STATUS['ANONYMOUS'] = 2;
$ENTRY_STATUS['BANNED'] = 3;
$ENTRY_STATUS['DISQUALIFIED'] = 4;

$ENTRY_VOTE_STATUS['CAST'] = 0;
$ENTRY_VOTE_STATUS['ANONYMOUS'] = 1;
$ENTRY_VOTE_STATUS['BANNED'] = 2;
$ENTRY_VOTE_STATUS['BLOCKED'] = 3;

$POINTS_VALUE_ID['ENTRY_VOTING'] = 1;
$POINTS_VALUE_ID['THEME_VOTING'] = 2;
$POINTS_VALUE_ID['INSIGHTFUL_GIVE'] = 3;
$POINTS_VALUE_ID['INSIGHTFUL_RECEIVE'] = 4;
$POINTS_VALUE_ID['THEME_SUGGESTING'] = 5;
$POINTS_VALUE_ID['ENTRY_POSTING'] = 6;
$POINTS_VALUE_ID['COMMUNITY_CREATING'] = 7;

$COMPETE_FILTER['ENTERED'] = 1;
$COMPETE_FILTER['VIRGIN'] = 2;
$COMPETE_FILTER['HIDDEN'] = 3;

$ALERT_INSTANCE_STATUS['NEW'] = 0;
$ALERT_INSTANCE_STATUS['READ'] = 1;
$ALERT_INSTANCE_STATUS['ASYNC'] = 2;

$ALERT_TEMPLATE_ID['COMMENT'] = 1;
$ALERT_TEMPLATE_ID['REPLY'] = 2;
$ALERT_TEMPLATE_ID['RANK'] = 3;
$ALERT_TEMPLATE_ID['INSIGHTFUL_POST'] = 4;
$ALERT_TEMPLATE_ID['INSIGHTFUL_COMMENT'] = 5;
$ALERT_TEMPLATE_ID['OTHER_DISQUALIFIED'] = 6;
$ALERT_TEMPLATE_ID['DISQUALIFIED'] = 7;
$ALERT_TEMPLATE_ID['REQUALIFIED'] = 8;
$ALERT_TEMPLATE_ID['COMMENT_REPLY'] = 9;
$ALERT_TEMPLATE_ID['COMMENT_AUTHOR_REPLY'] = 10;
$ALERT_TEMPLATE_ID['POINTS_REEVALUATED'] = 11;
$ALERT_TEMPLATE_ID['COMMUNITY_DELETE'] = 12;
$ALERT_TEMPLATE_ID['THEME_MODERATED'] = 13;
$ALERT_TEMPLATE_ID['MODERATION_RIGHTS_GIVEN'] = 14;
$ALERT_TEMPLATE_ID['MODERATION_RIGHTS_TAKEN'] = 15;
$ALERT_TEMPLATE_ID['PREMIUM_EXPIRED'] = 16;
$ALERT_TEMPLATE_ID['PREMIUM_SPONSORED'] = 17;
$ALERT_TEMPLATE_ID['PREMIUM_ACTIVATED'] = 18;
$ALERT_TEMPLATE_ID['PREMIUM_SPONSORED_LIFETIME'] = 19;
$ALERT_TEMPLATE_ID['PREMIUM_ACTIVATED_LIFETIME'] = 20;
$ALERT_TEMPLATE_ID['AFFILIATE_JOIN'] = 21;
$ALERT_TEMPLATE_ID['AFFILIATE_ACTIVE'] = 22;
$ALERT_TEMPLATE_ID['ALL_STARS'] = 23;
$ALERT_TEMPLATE_ID['CANVAS_SALE'] = 24;
$ALERT_TEMPLATE_ID['CANVAS_SALE_NO_PROFIT'] = 25;
$ALERT_TEMPLATE_ID['COMMUNITY_MERGED'] = 26;
$ALERT_TEMPLATE_ID['COMMUNITY_TRANSFERRED'] = 27;
$ALERT_TEMPLATE_ID['THEME_TRANSITIONED'] = 28;
$ALERT_TEMPLATE_ID['PRIZE'] = 29;
$ALERT_TEMPLATE_ID['MOST_HELPFUL'] = 30;
$ALERT_TEMPLATE_ID['PRIZE_WINNER'] = 31;
$ALERT_TEMPLATE_ID['PREMIUM_SURVEY'] = 32;
$ALERT_TEMPLATE_ID['AFFILIATE_MIA'] = 33;
$ALERT_TEMPLATE_ID['NEW_BLOG_POST'] = 34;
$ALERT_TEMPLATE_ID['INACTIVE_COMMUNITY'] = 35;
$ALERT_TEMPLATE_ID['INACTIVE_COMMUNITY_DELETE'] = 36;
$ALERT_TEMPLATE_ID['ALL_COMMENT_REPLY'] = 37;
$ALERT_TEMPLATE_ID['ALL_COMMENT_AUTHOR_REPLY'] = 38;
$ALERT_TEMPLATE_ID['ANNOUNCEMENT_NEW'] = 39;
$ALERT_TEMPLATE_ID['ANNOUNCEMENT_REPLY'] = 40;

$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['COMMENT']] = '<user_name uid="#uid"/> wrote <a href="#comment_href">a comment</a> about <a href="#href">your entry</a> in the <theme_title class="white" tid="#tid"/> competition';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['REPLY']] = '<user_name uid="#uid"/> <a href="#reply_href">replied</a> to <a href="#href">your message</a> in the <thread_title nid="#nid"/> community announcement';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['INSIGHTFUL_POST']] = '<user_name uid="#uid"/> marked <a href="#href">your post</a> as insightful';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['INSIGHTFUL_COMMENT']] = 'Someone marked <a href="#href">your comment</a> on <a href="#entry_href">this entry</a> in the <theme_title class="white" tid="#tid"/> competition as insightful';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['RANK']] = '<a href="#href">Your entry</a> ranked <rank value="#rank"/> out of <integer value="#entries_count"/> in the <theme_title class="white" tid="#tid"/> competition';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['OTHER_DISQUALIFIED']] = 'Someone\'s entry was disqualified or requalified in the <theme_title class="white" tid="#tid"/> competition, as a result <a href="#href">your entry</a>\'s new rank is <rank value="#rank"/> out of <integer value="#entries_count"/>';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['DISQUALIFIED']] = '<a href="#href">Your entry</a> in the <theme_title class="white" tid="#tid"/> competition has been disqualified';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['REQUALIFIED']] = '<a href="#href">Your entry</a> in the <theme_title class="white" tid="#tid"/> competition has been requalified';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['COMMENT_REPLY']] = '<user_name uid="#uid"/> <a href="#reply_href">replied</a> to <a href="#comment_href">your comment</a> about <a href="#href">this entry</a> in the <theme_title class="white" tid="#tid"/> competition';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['COMMENT_AUTHOR_REPLY']] = 'The author <a href="#reply_href">replied</a> to <a href="#comment_href">your comment</a> about <a href="#href">this entry</a> in the <theme_title class="white" tid="#tid"/> competition';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['POINTS_REEVALUATED']] = 'The amount of points that actions cost on the website has changed. You can see the new values <a href="#href">here</a>';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['COMMUNITY_DELETE']] = 'The administrator of the <span class="white"><string value="#name"/></span> community decided to delete it. As a direct consequence, past competitions and entries for that community have been deleted.';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['THEME_MODERATED']] = 'A moderator of the <community_name class="white" xid="#xid"/> community has removed your <theme_title class="white" tid="#tid"/> theme suggestion from the list.';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['MODERATION_RIGHTS_GIVEN']] = '<user_name uid="#uid"/>, administrator of <community_name class="white" xid="#xid" link="true"/>, made you a moderator of that community.';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['MODERATION_RIGHTS_TAKEN']] = '<user_name uid="#uid"/>, administrator of <community_name class="white" xid="#xid" link="true"/>, removed your moderation rights on that community.';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['PREMIUM_EXPIRED']] = 'Your premium membership has expired. You can reactivate it with a new <a href="#href">premium membership code</a>';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['PREMIUM_SPONSORED']] = '<user_name uid="#uid"/> sponsored you and gave you premium membership, which is valid for <duration value="#duration"/>.';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['PREMIUM_ACTIVATED']] = 'Your premium membership has been activated and is now valid for <duration value="#duration"/>.';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['PREMIUM_SPONSORED_LIFETIME']] = '<user_name uid="#uid"/> sponsored you and gave you lifetime premium membership.';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['PREMIUM_ACTIVATED_LIFETIME']] = 'You lifetime premium membership has been activated.';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['AFFILIATE_JOIN']] = '<user_name uid="#uid"/> just joined inspi.re thanks to you. If he/she stays active, you\'ll get 7 days of free premium membership.';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['AFFILIATE_ACTIVE']] = '<user_name uid="#uid"/> who joined thanks to you remained active, you\'ve just received 7 days of free premium membership.';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['ALL_STARS']] = 'Congratulations! You came 1st in the <theme_title class="white" tid="#tid"/> competition, <a href="#href">your entry</a> is thus eligible for the <a href="#all_stars_href"><b>inspi.re all stars</b> prize giveaway</a>.';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['CANVAS_SALE']] = '<integer value="#quantity"/> canvas print(s) of <a href="#href">your entry</a> in the <theme_title class="white" tid="#tid"/> competition was/were just sold and earned you $<float value="#commission"/>.';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['CANVAS_SALE_NO_PROFIT']] = '<integer value="#quantity"/> canvas print(s) of <a href="#href">your entry</a> in the <theme_title class="white" tid="#tid"/> competition was/were just sold.';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['COMMUNITY_MERGED']] = 'The administrator of the <span class="white"><string value="#old_name"/></span> community has merged it into the <community_name class="white" xid="#target_xid"/> community. Past entries and competitions have automatically been transferred.';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['COMMUNITY_TRANSFERRED']] = '<user_name uid="#uid"/> has transferred the administration rights of the <community_name class="white" xid="#xid"/> community to you';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['THEME_TRANSITIONED']] = 'Your <theme_title class="white" tid="#tid"/> theme suggestion in the <community_name link="true" class="white" xid="#xid"/> community has been selected by popular vote and is now <a href="#href">a competition</a>.';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['PRIZE']] = 'Congratulations! You came 1st in the <theme_title class="white" tid="#tid"/> competition which had 15 or more participants, <a href="#href">your entry</a> is thus eligible for this month\'s <a href="#prize_href"><b>prize draw</b></a>.';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['MOST_HELPFUL']] = 'Congratulations! Your dedicated commenting earned you the title of <a href="#href">most helpful member</a> of the hour. As a result, you\'ve earned one day of free premium membership, effective immediately.';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['PRIZE_WINNER']] = 'Congratulations! You\'ve just won the <a href="#prize_href"><b>monthly cash prize</b></a>. Someone from our team will contact you by email within the next 48 hours in order to arrange the prize money transfer.';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['PREMIUM_SURVEY']] = 'Thanks to answering the <span class="white"><string value="#survey_name"/></span> survey, you\'ve earned <duration value="#duration"/> of premium membership, effective immediately.';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['AFFILIATE_MIA']] = '<user_name uid="#uid"/> came back to the website thanks to you, you\'ve just received one day of free premium membership.';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['NEW_BLOG_POST']] = 'There\'s a new post titled <span class="white"><string value="#title"/></span> on the official inspi.re blog, go <a href="'.$PAGE['BLOG'].'">check it out</a>!';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['INACTIVE_COMMUNITY_DELETE']] = 'The administrator of the <span class="white"><string value="#name"/></span> community hasn\'t managed to keep it active and it has been automatically deleted. As a direct consequence, past competitions and entries for that community have been deleted.';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['INACTIVE_COMMUNITY']] = 'The <community_name class="white" xid="#xid" link="true"/> community, which you administrate, has been inactive or seen very low activity over the past month. It will be automatically deleted 28 days from now it you don\'t react. More details are available on the community\'s page.';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['ALL_COMMENT_REPLY']] = '<user_name uid="#uid"/> wrote <a href="#reply_href">a new comment</a> on <a href="#href">this entry</a> in the <theme_title class="white" tid="#tid"/> competition';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['ALL_COMMENT_AUTHOR_REPLY']] = 'The author wrote <a href="#reply_href">a new comment</a> on his/her <a href="#href">entry</a> in the <theme_title class="white" tid="#tid"/> competition';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['ANNOUNCEMENT_NEW']] = 'There is <a href="#href">a new announcement</a> in the <community_name class="white" xid="#xid" link="true"/> community, <a href="#href">check it out</a>!';
$ALERT_TEMPLATE[$ALERT_TEMPLATE_ID['ANNOUNCEMENT_REPLY']] = '<user_name uid="#uid"/> replied to <a href="#href">your announcement</a> in the <community_name class="white" xid="#xid" link="true"/> community.';


$COMMUNITY_ORDER['RECENT'] = 1;
$COMMUNITY_ORDER['OLD'] = 2;
$COMMUNITY_ORDER['BIG'] = 3;
$COMMUNITY_ORDER['SMALL'] = 4;

$COMMUNITY_LABEL['PHOTOGRAPHY'] = 1;
$COMMUNITY_LABEL['POST_PROCESSING'] = 2;
$COMMUNITY_LABEL['DESIGN'] = 3;
$COMMUNITY_LABEL['ILLUSTRATION'] = 4;
$COMMUNITY_LABEL['3D'] = 5;
$COMMUNITY_LABEL['DRAWING'] = 6;
$COMMUNITY_LABEL['PAINTING'] = 7;
$COMMUNITY_LABEL['SCULPTURE'] = 8;
$COMMUNITY_LABEL['CRAFTS'] = 9;
$COMMUNITY_LABEL['ABSTRACT'] = 10;
$COMMUNITY_LABEL['TANGIBLE'] = 11;
$COMMUNITY_LABEL['ANIMATE'] = 12;
$COMMUNITY_LABEL['INANIMATE'] = 13;
$COMMUNITY_LABEL['TRADITIONAL'] = 14;
$COMMUNITY_LABEL['MODERN'] = 15;
$COMMUNITY_LABEL['BLACK_AND_WHITE'] = 16;
$COMMUNITY_LABEL['COLOR'] = 17;
$COMMUNITY_LABEL['SLOW'] = 18;
$COMMUNITY_LABEL['FAST'] = 19;
$COMMUNITY_LABEL['DIGITAL'] = 20;
$COMMUNITY_LABEL['ANALOGIC'] = 21;
$COMMUNITY_LABEL['BEGINNER'] = 22;
$COMMUNITY_LABEL['INTERMEDIATE'] = 23;
$COMMUNITY_LABEL['EXPERT'] = 24;
$COMMUNITY_LABEL['ALL_LEVELS'] = 25;

$COMMUNITY_LABEL_NAME[$COMMUNITY_LABEL['PHOTOGRAPHY']] = 'Photography';
$COMMUNITY_LABEL_NAME[$COMMUNITY_LABEL['POST_PROCESSING']] = 'Post-processing';
$COMMUNITY_LABEL_NAME[$COMMUNITY_LABEL['DESIGN']] = 'Design';
$COMMUNITY_LABEL_NAME[$COMMUNITY_LABEL['ILLUSTRATION']] = 'Illustration';
$COMMUNITY_LABEL_NAME[$COMMUNITY_LABEL['3D']] = '3D';
$COMMUNITY_LABEL_NAME[$COMMUNITY_LABEL['DRAWING']] = 'Drawing';
$COMMUNITY_LABEL_NAME[$COMMUNITY_LABEL['PAINTING']] = 'Painting';
$COMMUNITY_LABEL_NAME[$COMMUNITY_LABEL['SCULPTURE']] = 'Sculpture';
$COMMUNITY_LABEL_NAME[$COMMUNITY_LABEL['CRAFTS']] = 'Crafts';
$COMMUNITY_LABEL_NAME[$COMMUNITY_LABEL['ABSTRACT']] = 'Abstract';
$COMMUNITY_LABEL_NAME[$COMMUNITY_LABEL['TANGIBLE']] = 'Tangible';
$COMMUNITY_LABEL_NAME[$COMMUNITY_LABEL['ANIMATE']] = 'Animate';
$COMMUNITY_LABEL_NAME[$COMMUNITY_LABEL['INANIMATE']] = 'Inanimate';
$COMMUNITY_LABEL_NAME[$COMMUNITY_LABEL['TRADITIONAL']] = 'Traditional';
$COMMUNITY_LABEL_NAME[$COMMUNITY_LABEL['MODERN']] = 'Modern';
$COMMUNITY_LABEL_NAME[$COMMUNITY_LABEL['BLACK_AND_WHITE']] = 'Black and white';
$COMMUNITY_LABEL_NAME[$COMMUNITY_LABEL['COLOR']] = 'Color';
$COMMUNITY_LABEL_NAME[$COMMUNITY_LABEL['SLOW']] = 'Slow';
$COMMUNITY_LABEL_NAME[$COMMUNITY_LABEL['FAST']] = 'Fast';
$COMMUNITY_LABEL_NAME[$COMMUNITY_LABEL['DIGITAL']] = 'Digital';
$COMMUNITY_LABEL_NAME[$COMMUNITY_LABEL['ANALOGIC']] = 'Analogic';
$COMMUNITY_LABEL_NAME[$COMMUNITY_LABEL['BEGINNER']] = 'Beginner';
$COMMUNITY_LABEL_NAME[$COMMUNITY_LABEL['INTERMEDIATE']] = 'Intermediate';
$COMMUNITY_LABEL_NAME[$COMMUNITY_LABEL['EXPERT']] = 'Expert';
$COMMUNITY_LABEL_NAME[$COMMUNITY_LABEL['ALL_LEVELS']] = 'All levels';

$COMMUNITY_MEMBERSHIP_STATUS['UNREGISTERED'] = 0;
$COMMUNITY_MEMBERSHIP_STATUS['ACTIVE'] = 1;
$COMMUNITY_MEMBERSHIP_STATUS['DELETED'] = 2;
$COMMUNITY_MEMBERSHIP_STATUS['BANNED'] = 3;

$INDEXING_STATUS['UNINDEXED'] = 0;
$INDEXING_STATUS['INDEXED'] = 1;

$PRIVATE_MESSAGE_STATUS['NEW'] = 0;
$PRIVATE_MESSAGE_STATUS['READ'] = 1;
$PRIVATE_MESSAGE_STATUS['DELETED'] = 2;
$PRIVATE_MESSAGE_STATUS['BLOCKED'] = 3;

$PRIVATE_MESSAGE_OUTBOX_STATUS['SENT'] = 0;
$PRIVATE_MESSAGE_OUTBOX_STATUS['DELETED'] = 1;

$PAGING['DISCUSSION_THREAD_POSTS'] = 0;
$PAGING['BOARD_THREADS'] = 1;
$PAGING['THEME_LIST_THEMES'] = 2;
$PAGING['HALL_OF_FAME_COMPETITIONS'] = 3;
$PAGING['HOME_ENTRIES'] = 4;
$PAGING['HOME_PRIVATE_MESSAGES'] = 5;
$PAGING['HOME_FAVORITES'] = 6;
$PAGING['COMMUNITIES_COMMUNITIES'] = 7;
$PAGING['MEMBERS_SEARCH'] = 8;
$PAGING['DISCUSS_RECENT_POSTS'] = 9;
$PAGING['ALERTS'] = 10;
$PAGING['PROFILE_COMMUNITIES'] = 11;
$PAGING['PROFILE_ENTRIES'] = 12;
$PAGING['PROFILE_ADMINISTRATED_COMMUNITIES'] = 13;
$PAGING['PROFILE_MODERATED_COMMUNITIES'] = 14;
$PAGING['COMMUNITY_MEMBERS'] = 15;
$PAGING['SEARCH_MEMBERS_SUBSET'] = 16;
$PAGING['BRING_BACK'] = 17;
$PAGING['MESSAGING_COMMENTS'] = 18;

$PAGING_DEFAULT['DISCUSSION_THREAD_POSTS'] = 10;
$PAGING_DEFAULT['BOARD_THREADS'] = 10;
$PAGING_DEFAULT['THEME_LIST_THEMES'] = 10;
$PAGING_DEFAULT['HALL_OF_FAME_COMPETITIONS'] = 10;
$PAGING_DEFAULT['HOME_ENTRIES'] = 10;
$PAGING_DEFAULT['HOME_PRIVATE_MESSAGES'] = 5;
$PAGING_DEFAULT['HOME_FAVORITES'] = 28;
$PAGING_DEFAULT['COMMUNITIES_COMMUNITIES'] = 10;
$PAGING_DEFAULT['MEMBERS_SEARCH'] = 10;
$PAGING_DEFAULT['DISCUSS_RECENT_POSTS'] = 5;
$PAGING_DEFAULT['ALERTS'] = 4;
$PAGING_DEFAULT['PROFILE_COMMUNITIES'] = 3;
$PAGING_DEFAULT['PROFILE_ENTRIES'] = 21;
$PAGING_DEFAULT['PROFILE_ADMINISTRATED_COMMUNITIES'] = 2;
$PAGING_DEFAULT['PROFILE_MODERATED_COMMUNITIES'] = 2;
$PAGING_DEFAULT['COMMUNITY_MEMBERS'] = 56;
$PAGING_DEFAULT['SEARCH_MEMBERS_SUBSET'] = 3;
$PAGING_DEFAULT['BRING_BACK'] = 196;
$PAGING_DEFAULT['MESSAGING_COMMENTS'] = 10;

$CURRENCY['EUR'] = 1;
$CURRENCY['USD'] = 2;
$CURRENCY['CAD'] = 3;
$CURRENCY['AUD'] = 4;
$CURRENCY['NZD'] = 5;
$CURRENCY['GBP'] = 6;

$SPECIAL_USER['MOST_HELPFUL'] = 1;
$SPECIAL_USER['MOST_PROLIFIC'] = 2;
$SPECIAL_USER['BIGGEST_VOTER'] = 3;

$STATISTIC['ACTIVE_MEMBERS'] = 1;
$STATISTIC['REGISTRATIONS'] = 2;
$STATISTIC['COMMENTS_WORDCOUNT'] = 3;
$STATISTIC['VOTES'] = 4;
$STATISTIC['ENTRIES'] = 5;
$STATISTIC['COMMENTS_ENTRIES_RATIO'] = 99;
?>