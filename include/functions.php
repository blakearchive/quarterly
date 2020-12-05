<?php
   			include('include/simple_html_dom.php');
   			
   			$root = root();
			
			$minVol = 1;
			$minIss = 1;
			$maxVol = 48;
			$maxIss = 4;
			
			$articleTypes = array();
			$articleTypes[0] = array();
			$articleTypes[0]['name'] = 'Article';
			$articleTypes[0]['nameShort'] = 'Article';
			$articleTypes[0]['namePlural'] = 'Articles';
			$articleTypes[0]['namePluralShort'] = 'Articles';
			$articleTypes[0]['keys'] = array('article','Article','Articles','checklist');
			$articleTypes[0]['umbrellaKey'] = 'article';
			/*
			$articleTypes[1] = array();
			$articleTypes[1]['name'] = 'Checklist of Publications';
			$articleTypes[1]['nameShort'] = 'Checklist';
			$articleTypes[1]['namePlural'] = 'Checklists of Publications';
			$articleTypes[1]['namePluralShort'] = 'Checklists';
			$articleTypes[1]['keys'] = array('checklist');
			*/
			$articleTypes[1] = array();
			$articleTypes[1]['name'] = 'Correction';
			$articleTypes[1]['nameShort'] = 'Correction';
			$articleTypes[1]['namePlural'] = 'Corrections';
			$articleTypes[1]['namePluralShort'] = 'Corrections';
			$articleTypes[1]['keys'] = array('correction','Addenda');
			$articleTypes[1]['umbrellaKey'] = 'correction';
			$articleTypes[2] = array();
			$articleTypes[2]['name'] = 'Discussion';
			$articleTypes[2]['nameShort'] = 'Discussion';
			$articleTypes[2]['namePlural'] = 'Discussion';
			$articleTypes[2]['namePluralShort'] = 'Discussion';
			$articleTypes[2]['keys'] = array('discussion','Discussion');
			$articleTypes[2]['umbrellaKey'] = 'article';
			$articleTypes[3] = array();
			$articleTypes[3]['name'] = 'Minute Particular';
			$articleTypes[3]['nameShort'] = 'Minute Particular';
			$articleTypes[3]['namePlural'] = 'Minute Particulars';
			$articleTypes[3]['namePluralShort'] = 'Minute Particulars';
			$articleTypes[3]['keys'] = array('minute','Minute Particulars');
			$articleTypes[3]['umbrellaKey'] = 'article';
			$articleTypes[4] = array();
			$articleTypes[4]['name'] = 'News';
			$articleTypes[4]['nameShort'] = 'News';
			$articleTypes[4]['namePlural'] = 'News';
			$articleTypes[4]['namePluralShort'] = 'News';
			$articleTypes[4]['keys'] = array('news','News','Journal News');
			$articleTypes[4]['umbrellaKey'] = 'news';
			$articleTypes[5] = array();
			$articleTypes[5]['name'] = 'Note';
			$articleTypes[5]['nameShort'] = 'Note';
			$articleTypes[5]['namePlural'] = 'Notes';
			$articleTypes[5]['namePluralShort'] = 'Notes';
			$articleTypes[5]['keys'] = array('note');
			$articleTypes[5]['umbrellaKey'] = 'article';
			$articleTypes[6] = array();
			$articleTypes[6]['name'] = 'Poem';
			$articleTypes[6]['nameShort'] = 'Poem';
			$articleTypes[6]['namePlural'] = 'Poems';
			$articleTypes[6]['namePluralShort'] = 'Poems';
			$articleTypes[6]['keys'] = array('poem','Poems');
			$articleTypes[6]['umbrellaKey'] = 'poem';
			$articleTypes[7] = array();
			$articleTypes[7]['name'] = 'Query';
			$articleTypes[7]['nameShort'] = 'Query';
			$articleTypes[7]['namePlural'] = 'Queries';
			$articleTypes[7]['namePluralShort'] = 'Queries';
			$articleTypes[7]['keys'] = array('query');
			$articleTypes[7]['umbrellaKey'] = 'article';
			$articleTypes[8] = array();
			$articleTypes[8]['name'] = 'Remembrance';
			$articleTypes[8]['nameShort'] = 'Remembrance';
			$articleTypes[8]['namePlural'] = 'Remembrances';
			$articleTypes[8]['namePluralShort'] = 'Remembrances';
			$articleTypes[8]['keys'] = array('remembrance');
			$articleTypes[8]['umbrellaKey'] = 'remembrance';
			$articleTypes[9] = array();
			$articleTypes[9]['name'] = 'Review';
			$articleTypes[9]['nameShort'] = 'Review';
			$articleTypes[9]['namePlural'] = 'Reviews';
			$articleTypes[9]['namePluralShort'] = 'Reviews';
			$articleTypes[9]['keys'] = array('review','Reviews');
			$articleTypes[9]['umbrellaKey'] = 'review';
			$articleTypes[10] = array();
			$articleTypes[10]['name'] = 'Context (About, Contact, Emendations)';
			$articleTypes[10]['nameShort'] = 'Context';
			$articleTypes[10]['namePlural'] = 'Context (About, Contact, Emendations)';
			$articleTypes[10]['namePluralShort'] = 'Context';
			$articleTypes[10]['keys'] = array('context');
			$articleTypes[10]['umbrellaKey'] = 'context';
			$articleTypes[11] = array();
			$articleTypes[11]['name'] = 'Table of Contents';
			$articleTypes[11]['nameShort'] = 'Contents';
			$articleTypes[11]['namePlural'] = 'Tables of Contents';
			$articleTypes[11]['namePluralShort'] = 'Contents';
			$articleTypes[11]['keys'] = array('toc');
			$articleTypes[11]['umbrellaKey'] = 'toc';
			$articleTypes[12] = array();
			$articleTypes[12]['name'] = 'Illustration';
			$articleTypes[12]['nameShort'] = 'Illustration';
			$articleTypes[12]['namePlural'] = 'Illustrations';
			$articleTypes[12]['namePluralShort'] = 'Illustrations';
			$articleTypes[12]['keys'] = array('illustration');
			$articleTypes[12]['umbrellaKey'] = 'illustration';
			$articleTypes[13]['name'] = 'Index';
			$articleTypes[13]['nameShort'] = 'Index';
			$articleTypes[13]['namePlural'] = 'Indices';
			$articleTypes[13]['namePluralShort'] = 'Indices';
			$articleTypes[13]['keys'] = array('index');
			$articleTypes[13]['umbrellaKey'] = 'index';
			
			function articleUmbrellaTypes ($artTypes) {
				$umbTypes = array();
				foreach($artTypes as $type) {
					if(isset($umbTypes[$type['umbrellaKey']])) {
						// nothing
					} else {
						$umbTypes[$type['umbrellaKey']] = array();
						$umbTypes[$type['umbrellaKey']]['name'] = $type['name'];
						$umbTypes[$type['umbrellaKey']]['nameShort'] = $type['nameShort'];
						$umbTypes[$type['umbrellaKey']]['namePlural'] = '';
						$umbTypes[$type['umbrellaKey']]['namePluralArray'] = array();
						$umbTypes[$type['umbrellaKey']]['namePluralShort'] = $type['namePluralShort'];
						$umbTypes[$type['umbrellaKey']]['keys'] = array();
					}
					$umbTypes[$type['umbrellaKey']]['namePluralArray'][] = $type['namePlural'];
					$umbTypes[$type['umbrellaKey']]['namePlural'] = implode(', ', $umbTypes[$type['umbrellaKey']]['namePluralArray']);
					$umbTypes[$type['umbrellaKey']]['keys'] = array_merge($umbTypes[$type['umbrellaKey']]['keys'], $type['keys']);
				}
				return $umbTypes;
			}
			
			function root () {
				if($_SERVER['SERVER_NAME'] == 'bq.blakearchive.org' || $_SERVER['SERVER_NAME'] == 'bq-dev.blakearchive.org') {
					return '/';
				} else if ($_SERVER['SERVER_NAME'] == 'localhost') {
					return '/bq/';
				} else {
					return '';
				}
			}
			
			function inPubRange ($vol, $iss) {
				global $minVol, $minIss, $maxVol, $maxIss;
				
				if(($vol > $minVol || ($vol == $minVol && $iss >= $minIss)) && ($vol < $maxVol || ($vol == $maxVol && $iss <= $maxIss))) {
					return true;
				} else {
					return false;
				}
			}
			
			function showable ($vol, $iss) {
				if($_SERVER['SERVER_NAME'] == 'bq.blakearchive.org' && inPubRange($vol, $iss)) {
					return true;
				} else if ($_SERVER['SERVER_NAME'] == 'bq-dev.blakearchive.org' || $_SERVER['SERVER_NAME'] == 'localhost') {
					return true;
				} else {
					return false;
				}
			}
			
    		function volFromFile ($f) {
				$v = substr($f, 1, 2);
				
				if(substr($v,0,1) == '0') {
					$v = substr($v,1,1);
				}
				
				return $v;
			}
			
			function cmp(array $a, array $b) {
				if (($cmp = strcmp($b['decade'], $a['decade'])) !== 0) {
					return $cmp;
				} else if(($b['volNum'] - $a['volNum']) !== 0) {
					return $b['volNum'] - $a['volNum'];
				} 
				else {
					return strcmp($a['file'], $b['file']);
				}
			}

			function cmpArticle(array $a, array $b) {
				if (($cmp = strcmp($a['firstAuthorLastName'], $b['firstAuthorLastName'])) !== 0) {
					return $cmp;
				} else if(($cmp2 = strcmp($a['author'], $b['author'])) !== 0) {
					return $cmp2;
				} else {
					return strcmp($a['title'], $b['title']);
				}
			}

			function cmpIllus(array $a, array $b) {
				if (($cmp = strcmp($a['firstAuthorLastName'], $b['firstAuthorLastName'])) !== 0) {
					return $cmp;
				} else if(($cmp2 = strcmp($a['author'], $b['author'])) !== 0) {
					return $cmp2;
				} else if(($cmp3 = strcmp($a['mainArticleTitle'], $b['mainArticleTitle'])) !== 0) {
					return $cmp3;
				} else {
					return strcmp($a['title'], $b['title']);
				}
			}

			function htmlentities_savetags($str_in) {
				$list = get_html_translation_table(HTML_ENTITIES);
				unset($list["'"]);
				unset($list['"']);
				unset($list['<']);
				unset($list['>']);
				unset($list['&']);
				$list['​'] = '&#8203;'; // Zero Width Space
				$list['﻿'] = '&#65279;'; // Byte Order Mark
				$list[' '] = ''; // ?
				$list['А'] = '&#1040;'; // Cyrillic alphabet
				$list['Б'] = '&#1041;';
				$list['В'] = '&#1042;';
				$list['Г'] = '&#1043;';
				$list['Д'] = '&#1044;';
				$list['Е'] = '&#1045;';
				$list['Ж'] = '&#1046;';
				$list['З'] = '&#1047;';
				$list['И'] = '&#1048;';
				$list['Й'] = '&#1049;';
				$list['К'] = '&#1050;';
				$list['Л'] = '&#1051;';
				$list['М'] = '&#1052;';
				$list['Н'] = '&#1053;';
				$list['О'] = '&#1054;';
				$list['П'] = '&#1055;';
				$list['Р'] = '&#1056;';
				$list['С'] = '&#1057;';
				$list['Т'] = '&#1058;';
				$list['У'] = '&#1059;';
				$list['Ф'] = '&#1060;';
				$list['Х'] = '&#1061;';
				$list['Ц'] = '&#1062;';
				$list['Ч'] = '&#1063;';
				$list['Ш'] = '&#1064;';
				$list['Щ'] = '&#1065;';
				$list['Ъ'] = '&#1066;';
				$list['Ы'] = '&#1067;';
				$list['Ь'] = '&#1068;';
				$list['Э'] = '&#1069;';
				$list['Ю'] = '&#1070;';
				$list['Я'] = '&#1071;';
				$list['а'] = '&#1072;';
				$list['б'] = '&#1073;';
				$list['в'] = '&#1074;';
				$list['г'] = '&#1075;';
				$list['д'] = '&#1076;';
				$list['е'] = '&#1077;';
				$list['ж'] = '&#1078;';
				$list['з'] = '&#1079;';
				$list['и'] = '&#1080;';
				$list['й'] = '&#1081;';
				$list['к'] = '&#1082;';
				$list['л'] = '&#1083;';
				$list['м'] = '&#1084;';
				$list['н'] = '&#1085;';
				$list['о'] = '&#1086;';
				$list['п'] = '&#1087;';
				$list['р'] = '&#1088;';
				$list['с'] = '&#1089;';
				$list['т'] = '&#1090;';
				$list['у'] = '&#1091;';
				$list['ф'] = '&#1092;';
				$list['х'] = '&#1093;';
				$list['ц'] = '&#1094;';
				$list['ч'] = '&#1095;';
				$list['ш'] = '&#1096;';
				$list['щ'] = '&#1097;';
				$list['ъ'] = '&#1098;';
				$list['ы'] = '&#1099;';
				$list['ь'] = '&#1100;';
				$list['э'] = '&#1101;';
				$list['ю'] = '&#1102;';
				$list['я'] = '&#1103;';
				$list['Ā'] = '&#256;'; // Latin Extended A (codes 256-383)
				$list['ā'] = '&#257;'; 
				$list['Ă'] = '&#258;'; 
				$list['ă'] = '&#259;'; 
				$list['Ą'] = '&#260;'; 
				$list['ą'] = '&#261;'; 
				$list['Ć'] = '&#262;'; 
				$list['ć'] = '&#263;'; 
				$list['Ĉ'] = '&#264;'; 
				$list['ĉ'] = '&#265;'; 
				$list['Ċ'] = '&#266;'; 
				$list['ċ'] = '&#267;'; 
				$list['Č'] = '&#268;'; 
				$list['č'] = '&#269;'; 
				$list['Ď'] = '&#270;'; 
				$list['ď'] = '&#271;'; 
				$list['Đ'] = '&#272;'; 
				$list['đ'] = '&#273;'; 
				$list['Ē'] = '&#274;'; 
				$list['ē'] = '&#275;'; 
				$list['Ĕ'] = '&#276;'; 
				$list['ĕ'] = '&#277;'; 
				$list['Ė'] = '&#278;'; 
				$list['ė'] = '&#279;'; 
				$list['Ę'] = '&#280;'; 
				$list['ę'] = '&#281;'; 
				$list['Ě'] = '&#282;'; 
				$list['ě'] = '&#283;'; 
				$list['Ĝ'] = '&#284;'; 
				$list['ĝ'] = '&#285;'; 
				$list['Ğ'] = '&#286;'; 
				$list['ğ'] = '&#287;'; 
				$list['Ġ'] = '&#288;'; 
				$list['ġ'] = '&#289;'; 
				$list['Ģ'] = '&#290;'; 
				$list['ģ'] = '&#291;'; 
				$list['Ĥ'] = '&#292;'; 
				$list['ĥ'] = '&#293;'; 
				$list['Ħ'] = '&#294;'; 
				$list['ħ'] = '&#295;'; 
				$list['Ĩ'] = '&#296;'; 
				$list['ĩ'] = '&#297;'; 
				$list['Ī'] = '&#298;'; 
				$list['ī'] = '&#299;'; 
				$list['Ĭ'] = '&#300;'; 
				$list['ĭ'] = '&#301;'; 
				$list['Į'] = '&#302;'; 
				$list['į'] = '&#303;'; 
				$list['İ'] = '&#304;'; 
				$list['ı'] = '&#305;'; 
				$list['Ĳ'] = '&#306;'; 
				$list['ĳ'] = '&#307;'; 
				$list['Ĵ'] = '&#308;'; 
				$list['ĵ'] = '&#309;'; 
				$list['Ķ'] = '&#310;'; 
				$list['ķ'] = '&#311;'; 
				$list['ĸ'] = '&#312;'; 
				$list['Ĺ'] = '&#313;'; 
				$list['ĺ'] = '&#314;'; 
				$list['Ļ'] = '&#315;'; 
				$list['ļ'] = '&#316;'; 
				$list['Ľ'] = '&#317;'; 
				$list['ľ'] = '&#318;'; 
				$list['Ŀ'] = '&#319;'; 
				$list['ŀ'] = '&#320;'; 
				$list['Ł'] = '&#321;'; 
				$list['ł'] = '&#322;'; 
				$list['Ń'] = '&#323;'; 
				$list['ń'] = '&#324;'; 
				$list['Ņ'] = '&#325;'; 
				$list['ņ'] = '&#326;'; 
				$list['Ň'] = '&#327;'; 
				$list['ň'] = '&#328;'; 
				$list['ŉ'] = '&#329;'; 
				$list['Ŋ'] = '&#330;'; 
				$list['ŋ'] = '&#331;'; 
				$list['Ō'] = '&#332;'; 
				$list['ō'] = '&#333;'; 
				$list['Ŏ'] = '&#334;'; 
				$list['ŏ'] = '&#335;'; 
				$list['Ő'] = '&#336;'; 
				$list['ő'] = '&#337;'; 
				$list['Œ'] = '&#338;'; 
				$list['œ'] = '&#339;'; 
				$list['Ŕ'] = '&#340;'; 
				$list['ŕ'] = '&#341;'; 
				$list['Ŗ'] = '&#342;'; 
				$list['ŗ'] = '&#343;'; 
				$list['Ř'] = '&#344;'; 
				$list['ř'] = '&#345;'; 
				$list['Ś'] = '&#346;'; 
				$list['ś'] = '&#347;'; 
				$list['Ŝ'] = '&#348;'; 
				$list['ŝ'] = '&#349;'; 
				$list['Ş'] = '&#350;'; 
				$list['ş'] = '&#351;'; 
				$list['Š'] = '&#352;'; 
				$list['š'] = '&#353;'; 
				$list['Ţ'] = '&#354;'; 
				$list['ţ'] = '&#355;'; 
				$list['Ť'] = '&#356;'; 
				$list['ť'] = '&#357;'; 
				$list['Ŧ'] = '&#358;'; 
				$list['ŧ'] = '&#359;'; 
				$list['Ũ'] = '&#360;'; 
				$list['ũ'] = '&#361;'; 
				$list['Ū'] = '&#362;'; 
				$list['ū'] = '&#363;'; 
				$list['Ŭ'] = '&#364;'; 
				$list['ŭ'] = '&#365;'; 
				$list['Ů'] = '&#366;'; 
				$list['ů'] = '&#367;'; 
				$list['Ű'] = '&#368;'; 
				$list['ű'] = '&#369;'; 
				$list['Ų'] = '&#370;'; 
				$list['ų'] = '&#371;'; 
				$list['Ŵ'] = '&#372;'; 
				$list['ŵ'] = '&#373;'; 
				$list['Ŷ'] = '&#374;'; 
				$list['ŷ'] = '&#375;'; 
				$list['Ÿ'] = '&#376;'; 
				$list['Ź'] = '&#377;'; 
				$list['ź'] = '&#378;'; 
				$list['Ż'] = '&#379;'; 
				$list['ż'] = '&#380;'; 
				$list['Ž'] = '&#381;'; 
				$list['ž'] = '&#382;'; 
				$list['ſ'] = '&#383;'; // Long s
				$list['ǚ'] = '&#474;'; // Further extended Latin characters
				$list['Ș'] = '&#536;';
				$list['ș'] = '&#537;';
				$list['Ț'] = '&#538;';
				$list['ț'] = '&#539;';
				$list['Ḥ'] = '&#7716;';
				$list['ḥ'] = '&#7717;';
				$list['Ḳ'] = '&#7730;';
				$list['ḳ'] = '&#7731;';
				$list['Ṣ'] = '&#7778;';
				$list['ṣ'] = '&#7779;';
				$list['א'] = '&#1488;'; // Hebrew alphabet
				$list['ב'] = '&#1489;';
				$list['ג'] = '&#1490;';
				$list['ד'] = '&#1491;';
				$list['ה'] = '&#1492;';
				$list['ו'] = '&#1493;';
				$list['ז'] = '&#1494;';
				$list['ח'] = '&#1495;';
				$list['ט'] = '&#1496;';
				$list['י'] = '&#1497;';
				$list['ך'] = '&#1498;';
				$list['כ'] = '&#1499;';
				$list['ל'] = '&#1500;';
				$list['ם'] = '&#1501;';
				$list['מ'] = '&#1502;';
				$list['ן'] = '&#1503;';
				$list['נ'] = '&#1504;';
				$list['ס'] = '&#1505;';
				$list['ע'] = '&#1506;';
				$list['ף'] = '&#1507;';
				$list['פ'] = '&#1508;';
				$list['ץ'] = '&#1509;';
				$list['צ'] = '&#1510;';
				$list['ק'] = '&#1511;';
				$list['ר'] = '&#1512;';
				$list['ש'] = '&#1513;';
				$list['ת'] = '&#1514;';
				$list['בּ'] = '&#64305;';
				$list['כּ'] = '&#64315;';
				$list['פּ'] = '&#64324;';
				$list['שׁ'] = '&#64298;';
				$list['שׂ'] = '&#64299;';
				$list['וּ'] = '&#64309;';
				$list['תּ'] = '&#64330;';
				$list['וֹ'] = '&#64331;';
				$list['ְ'] = '&#1456;'; // Hebrew vowels and special characters
				$list['ִ'] = '&#1460;';
				$list['ֵ'] = '&#1461;';
				$list['ֶ'] = '&#1462;';
				$list['ַ'] = '&#1463;';
				$list['ָ'] = '&#1464;';
				$list['ֹ'] = '&#1465;';
				$list['ֺ'] = '&#1466;';
				$list['ֻ'] = '&#1467;';
				$list['ּ'] = '&#1468;';
				$list['耿'] = '&#32831;'; // Selected Chinese, Japanese and Korean ideographs
				$list['力'] = '&#21147;';
				$list['平'] = '&#24179;';
				$list['ʼ'] = '&#700;'; // modifier apostrophe / glottal stop
				$list['ʻ'] = '&#699;'; // turned comma / okina
				$list['̣'] = '&#803;'; // combining dot below
				$list['‑'] = '&#8209;'; // non-breaking hyphen
				$list['♈'] = '&#9800;'; // Astrological signs
				$list['♉'] = '&#9801;';
				$list['♊'] = '&#9802;';
				$list['♋'] = '&#9803;';
				$list['♌'] = '&#9804;';
				$list['♍'] = '&#9805;';
				$list['♎'] = '&#9806;';
				$list['♏'] = '&#9807;';
				$list['♐'] = '&#9808;';
				$list['♑'] = '&#9809;';
				$list['♒'] = '&#9810;';
				$list['♓'] = '&#9811;';
				$list['█'] = '&#9608;'; // full block (which renders just fine without encoding)
				$list['ǀ'] = '&#448;'; // latin letter dental click
				$list['│'] = '&#9474;'; // box drawings light vertical
				$list['┬'] = '&#9516;'; // box drawings light down and horizontal
				$list['⎦'] = '&#9126;'; // right square bracket lower corner
				$list['⎣'] = '&#9123;'; // left square bracket lower corner
				$list['ʹ'] = '&#697;'; // prime
				$list['⅜'] = '&#8540;'; // fraction
				$list['₤'] = '&#8356;'; // lira sign
				
				$search = array_keys($list);
				$values = array_values($list);
				$search = array_map('utf8_encode', $search);

				$str_out = str_replace($search, $values, $str_in);
				return $str_out;
			}
			
			function getHtmlElementArray($HMTL, $selector, $context) {
				$elementArray = array();
				foreach($HMTL->find($selector) as $e) {
					$elementArray[] = $e->$context;
				}
				return $elementArray;
			}
			
			function seasonYearFromDate($date) {
				$seasons = array();
				$seasons[1] = 'Winter';
				$seasons[2] = 'Winter'; // no examples
				$seasons[3] = 'Spring';
				$seasons[4] = 'Spring';
				$seasons[5] = 'Summer';
				$seasons[6] = 'Summer';
				$seasons[7] = 'Summer';
				$seasons[8] = 'Summer'; // no examples
				$seasons[9] = 'Fall'; // no examples
				$seasons[10] = 'Fall';
				$seasons[11] = 'Fall'; // no examples
				$seasons[12] = 'Winter'; // no examples
				
				$dateParts = explode('-', $date);
				$monthStr = $dateParts[1];
				$month = intval($monthStr);
				
				$year = $dateParts[0];
				if($seasons[$month] == 'Winter') {
					$oldYear = $year - 1;
					$year = $oldYear.'-'.substr($year, 2, 2);
				}
				
				return $seasons[$month].' '.$year;
			}
			
			function credits() {
						# LOAD XML FILE 
						$XML = new DOMDocument(); 
						$XML->load( 'docs/About.xml' );
						
						# Remove text (leaving only teiHeader)
						$TEI = $XML->documentElement;
						$text = $TEI->getElementsByTagName('text')->item(0);
						$TEI->removeChild($text);

						# START XSLT 
						$xslt = new XSLTProcessor(); 
						$XSL = new DOMDocument(); 
						$XSL->load( 'xsl/quarterly.xsl'); 
						$xslt->importStylesheet( $XSL ); 
						#PRINT 
						print $xslt->transformToXML( $XML ); 
			}
			
			function adv_search($q, $searchFields, $fields, $types) {
							global $articleTypes;
			
							echo '<div id="search-advanced-holder">';
							echo '<h4 class="search-advanced-heading">Advanced search options</h4>';
							echo '<div id="search-advanced" class="collapse" >';
							echo '<form action="search" method="get">';
							echo '<h4>Keywords:</h4>';
							echo '<input name="q" type="search" value="'.htmlentities($q).'" /><br/>';
							
							echo '<h4>Search only within these fields:</h4>';
							foreach($searchFields as $key => $name) {
								$checked = '';
								if(in_array($key, $fields)) {
									$checked = 'checked="checked"';
								}
								echo '<label><input type="checkbox" name="field[]" value="'.$key.'" '.$checked.' />'.$name.'</label><br/>';
							}
							
							echo '<h4>Filter search by type of content:</h4>';
							foreach($articleTypes as $type) {
								$checked = '';
								if(in_array($type['keys'][0], $types)) {
									$checked = 'checked="checked"';
								}
								echo '<label><input type="checkbox" name="type[]" value="'.$type['keys'][0].'" '.$checked.' />'.$type['name'].'</label><br/>';
							}
							echo '<button type="submit">Search</button>';
							echo '</form>';
							echo '</div>';
							echo '</div>';
			}
			
			function typeNameShort ($key) {
				global $articleTypes;
				
				$nameShort = $key;
				foreach($articleTypes as $type) {
					if(in_array($key, $type['keys'])) {
						$nameShort = $type['nameShort'];
					}
				}
				return $nameShort;
			}
			
			function restrictStyle($styleStr) {
				$nl = '
';
				$styleStr = preg_replace('/[\n\r]{0,}(<!--|-->)[\n\r]{0,}/', '', $styleStr);
				
				$mediaArray = divideStyleMedia($styleStr);
				$restrictedStyleArray = '';
				
				foreach($mediaArray as $medium => $mediumStyle) {
					$mediumStyleRestricted = restrictStyleSingle($mediumStyle);
					if($medium == '') {
						$restrictedStyleArray[] = $mediumStyleRestricted;
					} else {
						$restrictedStyleArray[] = '@media '.$medium.' {'.$nl.$mediumStyleRestricted.$nl.'}';
					}
				}
				$restrictedStyle = implode($nl, $restrictedStyleArray);
				$restrictedStyle = preg_replace('/[ 	]{1,}/', ' ', $restrictedStyle);
				return $restrictedStyle;
			}
			
			function divideStyleMedia($styleStr) {
				if(preg_match('/@media/', $styleStr)) {
					$styleXml = '<style>'.preg_replace('/@media (screen|print)[ ]{0,}\{(.*\})[\r\n ]{0,}\}/sU', '<media type="$1">$2</media>', $styleStr).'</style>'; // s for dot matches newline, U for ungreedy by default

					$simpleXML = simplexml_load_string($styleXml); 
					$XMLtypes = $simpleXML->xpath('//media/@type');
					$XMLstyles = $simpleXML->xpath('//media');
					
					$mediaArray = array_combine($XMLtypes, $XMLstyles);
					
					return $mediaArray;
				} else {
					return array('' => $styleStr);
				}
				
			}
			
			function restrictStyleSingle($styleStr) {
				$nl = '
';
				$styleStr = (strpos($styleStr, $nl) === 0) ? substr($styleStr, 1) : $styleStr;
				$styleSplittable = preg_replace('/}[ \n]{0,}/', '}|||||', $styleStr);
				$styleArray = explode('|||||', $styleSplittable);
				$restrictedStyleArray = array();
				$numAllButLast = count($styleArray) - 1;
				for($i=0; $i<$numAllButLast; $i++) {
					$styleParts = explode('{', $styleArray[$i]);
					$selectors = $styleParts[0];
					$properties = $styleParts[1];
					
					$selectorParts = (strpos($selectors, ',') === false) ? array($selectors) : explode(',', $selectors);
					for($n=0; $n<count($selectorParts); $n++) {
						if (preg_match('/(?<!\.)body[ {]/', $selectorParts[$n])) {
							$selectorParts[$n] = preg_replace('/(?<!\.)body([ {])/', '#content$1', $selectorParts[$n]);
						} else if (preg_match('/(div)?\.cover[ {]/', $selectorParts[$n])) {
							$selectorParts[$n] = preg_replace('/((div)?\.cover[ {])/', '#main $1', $selectorParts[$n]);
						} else if (preg_match('/#content[ {]/', $selectorParts[$n])) {
							// good
						} else {
							$selectorParts[$n] = '#content '.$selectorParts[$n];
						}
					}
					$selectors = implode(', ', $selectorParts);
					$restrictedStyleArray[] = $selectors.'{'.$properties;
				}
				$restrictedStyle = implode($nl, $restrictedStyleArray);
				return $restrictedStyle;
			}
			
			function volumeLabel($volNum) {
				echo '<div class="vol-head-holder">';
				echo '<h4 class="vol-head">Volume '.$volNum.'</h4>';
				echo '</div>';
			}
			
			function echoCSS($path) {
				$CSSstring = file_get_contents($path);
				echo '<style>'.$CSSstring.'</style>';
			}

?>
