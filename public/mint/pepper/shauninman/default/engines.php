<?php
/******************************************************************************
 Search Engines
 
 Developer		: Shaun Inman
 Plug-in Name	: Default Pepper
 
 http://www.shauninman.com/

 ******************************************************************************/
$SI_SearchEngines[] = array(
						'name'		=> 'Google',
						'url'		=> 'http://www.google.com/search',
						'domain'	=> 'google',
						'query'		=> 'q'
						);
$SI_SearchEngines[] = array(
						'name'		=> 'Yahoo!',
						'url'		=> 'http://search.yahoo.com/search',
						'domain'	=> 'yahoo',
						'query'		=> 'p'
						);
$SI_SearchEngines[] = array(
						'name'		=> 'MSN',
						'url'		=> 'http://search.msn.com/results.aspx',
						'domain'	=> 'search.msn',
						'query'		=> 'q'
						);
/*
Disabled because the lack of a query string was letting in too many non-search
related paths from google images, gmail and yahoo. Sorry Amazon.
$SI_SearchEngines[] = array(
						'name'		=> 'A9',
						'url'		=> 'http://www.a9.com/',
						'domain'	=> 'a9',
						'query'		=> ''
						);
*/
$SI_SearchEngines[] = array(
						'name'		=> 'AlltheWeb',
						'url'		=> 'http://www.alltheweb.com/search',
						'domain'	=> 'alltheweb',
						'query'		=> 'q'
						);
$SI_SearchEngines[] = array(
						'name'		=> 'AOL',
						'url'		=> 'http://search.aol.com/aolcom/search',
						'domain'	=> 'search.aol',
						'query'		=> 'query'
						);
$SI_SearchEngines[] = array(
						'name'		=> 'Ask Jeeves',
						'url'		=> 'http://web.ask.com/web',
						'domain'	=> 'ask',
						'query'		=> 'q'
						);
$SI_SearchEngines[] = array(
						'name'		=> 'AltaVista',
						'url'		=> 'http://www.altavista.com/web/results',
						'domain'	=> 'altavista',
						'query'		=> 'q'
						);
$SI_SearchEngines[] = array(
						'name'		=> 'BBC',
						'url'		=> 'http://www.bbc.co.uk/cgi-bin/search/results.pl',
						'domain'	=> 'bbc',
						'query'		=> 'q'
						);
$SI_SearchEngines[] = array(
						'name'		=> 'HotBot',
						'url'		=> 'http://www.hotbot.com/',
						'domain'	=> 'hotbot',
						'query'		=> 'query'
						);
$SI_SearchEngines[] = array(
						'name'		=> 'Lycos',
						'url'		=> 'search.lycos.com',
						'domain'	=> 'search.lycos',
						'query'		=> 'query'
						);