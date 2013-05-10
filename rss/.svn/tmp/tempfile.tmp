<?php

/* 
 	Copyright (C) 2008-2009 Gilles Dubuc (www.kouiskas.com - gilles@dubuc.fr)
 	
 	Returns the RSS feed containing the entries of a competition
*/

require_once(dirname(__FILE__).'/../entities/community.php');
require_once(dirname(__FILE__).'/../entities/competition.php');
require_once(dirname(__FILE__).'/../entities/entry.php');
require_once(dirname(__FILE__).'/../entities/entrylist.php');
require_once(dirname(__FILE__).'/../entities/picture.php');
require_once(dirname(__FILE__).'/../entities/picturefile.php');
require_once(dirname(__FILE__).'/../entities/theme.php');
require_once(dirname(__FILE__).'/../entities/user.php');
require_once(dirname(__FILE__).'/../utilities/string.php');
require_once(dirname(__FILE__).'/../utilities/token.php');
require_once(dirname(__FILE__).'/../constants.php');

$cid = isset($_REQUEST['cid'])?$_REQUEST['cid']:null;
$uid = isset($_REQUEST['uid'])?$_REQUEST['uid']:null;

if ($cid === null || $uid === null) exit(0);

try {

$user = User::get($uid);
$competition = Competition::get($cid);
$theme = Theme::get($competition->getTid());
$community = Community::get($theme->getXid());

header('content-type: text/xml; charset=UTF-8');
$doc = new DomDocument('1.0', 'UTF-8');

$rss = $doc->createElement('rss');
$rss->setAttribute('version', '2.0');
$rss->setAttribute('xmlns:media', 'http://search.yahoo.com/mrss/');
$rss->setAttribute('xmlns:atom', 'http://www.w3.org/2005/Atom');
$doc->appendChild($rss);

$channel = $doc->createElement('channel');
$rss->appendChild($channel);

$atom_icon = $doc->createElement('atom:icon');
$atom_icon->appendChild($doc->createTextNode($GRAPHICS_PATH.'logo-small.gif'));
$channel->appendChild($atom_icon);

$channel_title = $doc->createElement("title");
$channel_title->appendChild($doc->createTextNode(String::htmlentities($community->getName())));
$channel->appendChild($channel_title);

$channel_description = $doc->createElement("description");
$channel_description->appendChild($doc->createTextNode(String::htmlentities($community->getDescription())));
$channel->appendChild($channel_description);

$entrylist = EntryList::getByCidAndStatusRandomized($uid, $cid, $ENTRY_STATUS['POSTED']);

foreach ($entrylist as $dump => $eid) {
	$entry = Entry::get($eid);
	try {
		$picture = Picture::get($entry->getPid());
		$picture_file = PictureFile::get($picture->getFid($PICTURE_SIZE['HUGE']));
		$token = new Token($uid.'-'.$eid);
		
		$item = $doc->createElement("item");
		$channel->appendChild($item);
		
		$title = $doc->createElement("title");
		$title->appendChild($doc->createTextNode($theme->getTitle()));
		$item->appendChild($title);
		
		$media_description = $doc->createElement("media:description");
		$media_description->appendChild($doc->createTextNode($theme->getDescription()));
		$item->appendChild($media_description);
		
		$guid = $doc->createElement("guid");
		$guid->appendChild($doc->CreateTextNode($token->getHash()));
		$item->appendChild($guid);
		
		$link = $doc->createElement("link");
		$link->appendChild($doc->createTextNode($PAGE['ENTRY'].'?lid='.$user->getLid().'#token='.$token->getHash()));
		$item->appendChild($link);
		
		$media_thumbnail = $doc->createElement("media:thumbnail");
		$media_thumbnail->setAttribute('url', $picture->getRealThumbnail($PICTURE_SIZE['HUGE']));
		$item->appendChild($media_thumbnail);
		
		$media_content = $doc->createElement("media:content");
		$media_content->setAttribute('url', $picture_file->getURL());
		$item->appendChild($media_content);
	} catch (PictureException $e) {}
}
		
		
echo $doc->saveXML();

} catch (UserException $f) {}
?>