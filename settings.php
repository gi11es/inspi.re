<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
*/

ini_set('error_reporting', E_ALL);

if (isset($_SERVER['HTTP_HOST'])) $WEBSITE_SERVER = $_SERVER['HTTP_HOST'];
else $WEBSITE_SERVER = 'inspi.re';

$WEBSITE_LOCAL_PATH = dirname(__FILE__).'/';
$WEBSITE_PATH = 'http://'.$WEBSITE_SERVER.(isset($_SERVER['SERVER_PORT']) && strcmp($_SERVER['SERVER_PORT'], '80') !=0?':'.$_SERVER['SERVER_PORT']:'').'/';

require_once(dirname(__FILE__).'/constants.php');

/* This should ultimately be replaced by an array of memcached servers
for redundancy. When that's the case some methods will need tweaking in the Cache class */

$MEMCACHE['HOST'] = 'localhost';
$MEMCACHE['PREFIX'] = 'Inspire-';
$MEMCACHE['PORT'] = 11211;

$MONGODB['SERVER'] = 'localhost';
$MONGODB['DATABASE'] = 'Inspire';

$CURRENT_LOG_LEVEL = $LOG_LEVEL['DEBUG'];
$LOG_TIME_FORMAT = 'Y-m-d H:i:s';
$LOG_FILE_PATH = '/home/daruma/logs/';
$LOG_DATE_FORMAT = 'Y-m-d';
$LOG_CYCLE = 2; // How many days we keep the logs for
$LOG_CURRENT_DATE = date($LOG_DATE_FORMAT);

// By tweaking the date part of the following files one can change if the log files are hourly, daily, etc
$LOG_FILE['Alert'] = $LOG_FILE_PATH.'Alert-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['AlertInstance'] = $LOG_FILE_PATH.'AlertInstance-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['AlertInstanceList'] = $LOG_FILE_PATH.'AlertInstanceList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['AlertList'] = $LOG_FILE_PATH.'AlertList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['AlertVariable'] = $LOG_FILE_PATH.'AlertVariable-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['AlertVariableList'] = $LOG_FILE_PATH.'AlertVariableList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['Cache'] = $LOG_FILE_PATH.'Cache-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['CommentIndex'] = $LOG_FILE_PATH.'CommentIndex-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['CommentIndexList'] = $LOG_FILE_PATH.'CommentIndexList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['Community'] = $LOG_FILE_PATH.'Community-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['CommunityLabel'] = $LOG_FILE_PATH.'CommunityLabel-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['CommunityLabelList'] = $LOG_FILE_PATH.'CommunityLabelList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['CommunityList'] = $LOG_FILE_PATH.'CommunityList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['CommunityMembership'] = $LOG_FILE_PATH.'CommunityMembership-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['CommunityMembershipList'] = $LOG_FILE_PATH.'CommunityMembershipList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['CommunityModerator'] = $LOG_FILE_PATH.'CommunityModerator-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['CommunityModeratorList'] = $LOG_FILE_PATH.'CommunityModeratorList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['Competition'] = $LOG_FILE_PATH.'Competition-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['CompetitionList'] = $LOG_FILE_PATH.'CompetitionList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['CompetitionHide'] = $LOG_FILE_PATH.'CompetitionHide-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['CompetitionHideList'] = $LOG_FILE_PATH.'CompetitionHideList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['CronThumbnails'] = $LOG_FILE_PATH.'CronThumbnails-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['DB'] = $LOG_FILE_PATH.'DB-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['DiscussionPost'] = $LOG_FILE_PATH.'DiscussionPost-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['DiscussionPostIndex'] = $LOG_FILE_PATH.'DiscussionPostIndex-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['DiscussionPostIndexList'] = $LOG_FILE_PATH.'DiscussionPostIndexList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['DiscussionPostList'] = $LOG_FILE_PATH.'DiscussionPostList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['DiscussionThread'] = $LOG_FILE_PATH.'DiscussionThread-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['DiscussionThreadIndex'] = $LOG_FILE_PATH.'DiscussionThreadIndex-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['DiscussionThreadIndexList'] = $LOG_FILE_PATH.'DiscussionThreadIndexList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['DiscussionThreadList'] = $LOG_FILE_PATH.'DiscussionThreadList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['Email'] = $LOG_FILE_PATH.'Email-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['EmailCampaign'] = $LOG_FILE_PATH.'EmailCampaign-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['EmailCampaignList'] = $LOG_FILE_PATH.'EmailCampaignList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['Entry'] = $LOG_FILE_PATH.'Entry-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['EntryCommentNotification'] = $LOG_FILE_PATH.'EntryCommentNotification-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['EntryCommentNotificationList'] = $LOG_FILE_PATH.'EntryCommentNotificationList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['EntryList'] = $LOG_FILE_PATH.'EntryList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['EntryVote'] = $LOG_FILE_PATH.'EntryVote-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['EntryVoteBlocked'] = $LOG_FILE_PATH.'EntryVoteBlocked-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['EntryVoteBlockedList'] = $LOG_FILE_PATH.'EntryVoteBlockedList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['EntryVoteList'] = $LOG_FILE_PATH.'EntryVoteList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['Favorite'] = $LOG_FILE_PATH.'Favorite-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['FavoriteList'] = $LOG_FILE_PATH.'FavoriteList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['I18N'] = $LOG_FILE_PATH.'I18N-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['Inml'] = $LOG_FILE_PATH.'Inml-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['InsightfulMark'] = $LOG_FILE_PATH.'InsightfulMark-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['InsightfulMarkList'] = $LOG_FILE_PATH.'InsightfulMarkList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['Log'] = $LOG_FILE_PATH.'Log-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['MongoConnect'] = $LOG_FILE_PATH.'MongoConnect-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['PersistentToken'] = $LOG_FILE_PATH.'PersistentToken-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['PictureFile'] = $LOG_FILE_PATH.'PictureFile-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['PictureFileList'] = $LOG_FILE_PATH.'PictureFileList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['Picture'] = $LOG_FILE_PATH.'Picture-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['PictureList'] = $LOG_FILE_PATH.'PictureList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['PointsValue'] = $LOG_FILE_PATH.'PointsValue-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['PremiumCode'] = $LOG_FILE_PATH.'PremiumCode-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['PremiumCodeList'] = $LOG_FILE_PATH.'PremiumCodeList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['PrivateMessage'] = $LOG_FILE_PATH.'PrivateMessage-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['PrivateMessageList'] = $LOG_FILE_PATH.'PrivateMessageList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['PrizeWinner'] = $LOG_FILE_PATH.'PrizeWinner-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['PrizeWinnerList'] = $LOG_FILE_PATH.'PrizeWinnerList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['S3'] = $LOG_FILE_PATH.'S3-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['SpecialUser'] = $LOG_FILE_PATH.'SpecialUser-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['Statistic'] = $LOG_FILE_PATH.'Statistic-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['StatisticList'] = $LOG_FILE_PATH.'StatisticList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['System'] = $LOG_FILE_PATH.'System-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['TeamMembership'] = $LOG_FILE_PATH.'TeamMembership-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['TeamMembershipList'] = $LOG_FILE_PATH.'TeamMembershipList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['Theme'] = $LOG_FILE_PATH.'Theme-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['ThemeList'] = $LOG_FILE_PATH.'ThemeList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['ThemeVote'] = $LOG_FILE_PATH.'ThemeVote-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['ThemeVoteList'] = $LOG_FILE_PATH.'ThemeVoteList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['Token'] = $LOG_FILE_PATH.'Token-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['Trackback'] = $LOG_FILE_PATH.'Trackback-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['URL'] = $LOG_FILE_PATH.'URL-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['User'] = $LOG_FILE_PATH.'User-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['UserBlock'] = $LOG_FILE_PATH.'UserBlock-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['UserBlockList'] = $LOG_FILE_PATH.'UserBlockList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['UserLevel'] = $LOG_FILE_PATH.'UserLevel-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['UserLevelList'] = $LOG_FILE_PATH.'UserLevelList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['UserNameIndex'] = $LOG_FILE_PATH.'UserNameIndex-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['UserNameIndexList'] = $LOG_FILE_PATH.'UserNameIndexList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['UserPaging'] = $LOG_FILE_PATH.'UserPaging-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['UserPagingList'] = $LOG_FILE_PATH.'UserPagingList-'.$LOG_CURRENT_DATE.'.log';
$LOG_FILE['UserList'] = $LOG_FILE_PATH.'UserList-'.$LOG_CURRENT_DATE.'.log';

$DATABASE['HOST'] = 'localhost';
$DATABASE['USER'] = 'daruma_inspirebeta';
$DATABASE['PASSWORD'] = 'roumb4l4';
$DATABASE['NAME'] = 'daruma_inspirebeta';
$DATABASE['PREFIX'] = 'inspire_';

/* The cookie file is used when crawling another website that requires authentication.  */
$COOKIE_FILE = $LOG_FILE_PATH.'cookie.txt';

$AD_CODE['HOME_TOP'] = array('ID' => '2468239743', 'WIDTH' => 728, 'HEIGHT' => 90, 'PROBABILITY' => 9);
$AD_CODE['HOME_BOTTOM'] = array('ID' => '4367439118', 'WIDTH' => 728, 'HEIGHT' => 90, 'PROBABILITY' => 9);
$AD_CODE['EDIT_PROFILE'] = array('ID' => '8186924905', 'WIDTH' => 728, 'HEIGHT' => 90, 'PROBABILITY' => 3);
$AD_CODE['THEME_LIST'] = array('ID' => '6792600618', 'WIDTH' => 728, 'HEIGHT' => 90, 'PROBABILITY' => 4);
$AD_CODE['DISCUSSION_SEARCH'] = array('ID' => '9380500365', 'WIDTH' => 728, 'HEIGHT' => 90, 'PROBABILITY' => 3);
$AD_CODE['COMPETE'] = array('ID' => '4970075454', 'WIDTH' => 728, 'HEIGHT' => 90, 'PROBABILITY' => 4);
$AD_CODE['ENTRY_TOP'] = array('ID' => '6961972124', 'WIDTH' => 728, 'HEIGHT' => 90, 'PROBABILITY' => 3);
$AD_CODE['ENTRY_BOTTOM'] = array('ID' => '4367439118', 'WIDTH' => 728, 'HEIGHT' => 90, 'PROBABILITY' => 3);
$AD_CODE['DISCUSSION_THREAD'] = array('ID' => '2390349546', 'WIDTH' => 728, 'HEIGHT' => 90, 'PROBABILITY' => 4);
$AD_CODE['LEADERBOARD'] = array('ID' => '4367439118', 'WIDTH' => 728, 'HEIGHT' => 90, 'PROBABILITY' => 6);
$AD_CODE['VOTE'] = array('ID' => '0213158278', 'WIDTH' => 728, 'HEIGHT' => 90, 'PROBABILITY' => 3);
$AD_CODE['DISCUSS_TOP'] = array('ID' => '7285139231', 'WIDTH' => 728, 'HEIGHT' => 90, 'PROBABILITY' => 3);
$AD_CODE['DISCUSS_BOTTOM'] = array('ID' => '9244123796', 'WIDTH' => 728, 'HEIGHT' => 90, 'PROBABILITY' => 3);
$AD_CODE['DISCUSSION_BOARD'] = array('ID' => '2220442700', 'WIDTH' => 728, 'HEIGHT' => 90, 'PROBABILITY' => 3);
$AD_CODE['THEMES'] = array('ID' => '9174213952', 'WIDTH' => 728, 'HEIGHT' => 90, 'PROBABILITY' => 3);
$AD_CODE['COMMUNITIES'] = array('ID' => '2661524115', 'WIDTH' => 728, 'HEIGHT' => 90, 'PROBABILITY' => 9);
$AD_CODE['MEMBERS'] = array('ID' => '6285330368', 'WIDTH' => 728, 'HEIGHT' => 90, 'PROBABILITY' => 3);
$AD_CODE['HALL_OF_FAME'] = array('ID' => '1781150190', 'WIDTH' => 728, 'HEIGHT' => 90, 'PROBABILITY' => 3);
$AD_CODE['PREMIUM'] = array('ID' => '4367439118', 'WIDTH' => 728, 'HEIGHT' => 90, 'PROBABILITY' => 0);

$ADSENSE_CODE = 'pub-8709313517401733';

$PICTURE_LOCAL_PATH = '/home/daruma/pictures/';
$PICTURE_PATH = 'http://uncle.'.$WEBSITE_SERVER.'/';

$PICTURE_DIMENSION['BIG'] = 256;
$PICTURE_DIMENSION['HUGE'] = 600;
$PICTURE_DIMENSION['MEDIUM'] = 128;
$PICTURE_DIMENSION['SMALL'] = 64;

$S3_BUCKET['IMAGES'] = 'images.inspi.re';
$S3['ID'] = '02VMCFHJBGRBB84TA3R2';
$S3['KEY'] = '9mvXp+54YsPvzfJkJx0gQ992edR4MA+4ArFXNg6P';
$S3['FUNNEL_PATH'] = '/usr/bin/s3funnel';
$S3_PATH = 'http://'.$S3_BUCKET['IMAGES'].'/';

$SERVER_DISK = '/dev/md1';

$IP_MAXIMUM_AGE = 86400; // user IPs 'last time seen' value is updated at worst every 24 hours

/*** cookies ***/

$HOST_COOKIE_MAXIMUM_AGE = 86400; // this cookie is used to track unique hosts
$HOST_COOKIE_NAME = 'inshost';
$HOST_COOKIE_EXPIRY = time() + 315360000;

$SESSION_COOKIE_NAME = 'inssession';
$COOKIE_DOMAIN = '.inspi.re';

$WEB_HISTORY_CHECK_FREQUENCY = 604800; // 7 days in seconds
$WEB_HISTORY_CHECK = array('http://www.redbubble.com/',
	'https://secure.redbubble.com/auth/login',
	'http://flickr.com/signin/',
	'http://www.flickr.com/signin/',
	'http://flickr.com',
	'http://www.flickr.com',
	'https://www.deviantart.com/users/login',
	'http://apps.facebook.com/photographycomp/vote.php',
	'http://photobucket.com/',
	'http://photobucket.com/logout?special_track=nav_logout',
	'http://www.smugmug.com/',
	'https://secure.smugmug.com/login.mg?goTo=http%3A%2F%2Fwww.smugmug.com%2F',
	'http://pa.photoshelter.com/',
	'https://pa.photoshelter.com/login',
	'http://istockphoto.com/index.php',
	'https://secure.istockphoto.com/istock_login.php',
	'http://www.fotolog.com/',
	'http://account.fotolog.com/login',
	'http://www.panoramio.com/',
	'http://www.panoramio.com/signin/',
	'http://picasa.google.com/',
	'http://www.zooomr.com/home',
	'http://www.zooomr.com/login/',
	'http://www.facebook.com/home.php',
	'http://www.kouiskas.com',
	'http://inspi.re/blog/');
	
$LAST_ENTRIES_ACCESS_MAXIMUM_AGE = 10800; // 3 hours

$ANALYTICS_CODE = 'UA-60164-18';

$MAINTENANCE = false;

$GOOGLE_UID = 4925;

$APPEARING_OFFLINE_DELAY = 600; // Appear as offline if no activity in the last 10 minutes

$FRONT_PAGE_BLACKLIST = array(865);
// Sending Kory to the sandbox
$USER_BLACKLIST = array('1708');
$IP_BLACKLIST = array('97.117.13.143');
$COOKIE_BLACKLIST = array('988bfbda285540023a282d6d432d92397f17a463', 
							'5972b96f365595eb8483301593ed200170bc900f',
							'81efbce49ab8cebb3b0b0bae5729e3934e474de2',
							'9e7ab8f07d9257e05c5bcc141162f960511edf4f',
							'061b09c399637871445b7d6f8c5e6c08cc73ccce');

$PRIZE_BLACKLIST = array('59', // Gilles
						 '1583', // Jackie D
						 '120489', // Edgar Benavides
						 '393', // Antje
						 '44473', //
						 '4a3bd165bbee5', // Guillaume Galante
						 '259108', // Klaus (Klusefix)
						 '4a2a64ac12f7f', // Jackie78
						 '1708' // Kory the troll
						 );

$PAYPAL_API_SERVER = 'https://api-3t.paypal.com/nvp';
$PAYPAL_API_USERNAME = 'premium_api1.inspi.re';
$PAYPAL_API_PASSWORD = 'MHRQSK3FYZMNBR5C';
$PAYPAL_API_SIGNATURE = 'AdHJiH0QpZlRD..7zYYDHFpXEl1NA25WrO57uc15A6cPrhd-fH42crMf';

$DEV_SERVER = false;

$DYNAMIC_PICTURES_LOCAL_PATH = $WEBSITE_LOCAL_PATH.'dynamicpictures/';
$DYNAMIC_PICTURES_PATH = $WEBSITE_PATH.'dynamicpictures/';

$COMET_CHANNEL['GENERAL_ACTIVITY'] = 'news-';
$COMET_CHANNEL['USER_ON'] = 'useron';
$COMET_CHANNEL['USER_OFF'] = 'useroff';
$COMET_CHANNEL['USER_REGISTERED'] = 'userregistered';
$COMET_URL = 'http://'.$WEBSITE_SERVER.'/http-bind';
$COMET_SERVER = 'kirby.inspi.re';
$COMET_PORT = 8222;
?>