<!DOCTYPE html>
<html>
	<?php
	require('include/functions.php');
	$title = 'Index of Articles and Reviews';
	$pt = array($title);
	$pt_post = '';
	$q = '';
	$qw = '';
	$nl ='
';

	$articleUmbrellaTypes = articleUmbrellaTypes($articleTypes);

	require('include/head.php');
	?>
	<body>
        <div id="outer">
			<?php
			require('include/header.php');
			?>
				
			<div id="all-content">
				<div id="all-content-inner">
					<div id="issue-heading">
						<div class="issue-heading-inner">
							<h1><?php echo $title; ?></h1>
						</div>
					</div>
					<div id="main">
						<p>
						<?php
							$anchor_links = array();
							foreach($articleUmbrellaTypes as $articleType) {
								if($articleType['keys'][0] != 'toc' && $articleType['keys'][0] != 'context' && $articleType['keys'][0] != 'illustration') {
									$anchor_links[] = '<a href="#'.$articleType['keys'][0].'">'.$articleType['namePluralShort'].'</a>';
								}
							}
							echo implode(' &middot; ', $anchor_links);
						?>
						</p>
						<p>Below is an index of articles and reviews published in this digital edition of <em>Blake/An Illustrated Quarterly</em>.</p>
						<div id="articles-reviews-index">
			<?php
				
			$docsXml = array();
			$typeTitles = array();
			foreach($articleUmbrellaTypes as $articleType) {
				if($articleType['keys'][0] != 'toc' && $articleType['keys'][0] != 'context' && $articleType['keys'][0] != 'illustration') {
					$docsXml[$articleType['keys'][0]] = array();
					$typeTitles[$articleType['keys'][0]] = $articleType['namePlural'];
				}
			}
			
			$charSwap = array("&amp;ldquo;" => "&ldquo;", "&amp;rdquo;" => "&rdquo;", "&amp;rsquo;" => "&rsquo;", "&amp;ndash;" => "&ndash;", "&amp;mdash;" => "&mdash;", "&amp;eacute;" => "&eacute;", "&amp;auml;" => "&auml;");
			$charKeys = array_keys($charSwap);
			$charValues = array_values($charSwap);
			
			// HTML
			foreach (new DirectoryIterator("./html/") as $fn) {
				if (preg_match('/[0-9]{1,2}.[0-9]{1}[-a-z0-9]{0,3}.[-a-z0-9]{1,20}.html/', $fn->getFilename()) || preg_match('/bonus.[-a-z0-9]{1,20}.html/', $fn->getFilename())) {
					$fn_t = array();
					$fn_t['format'] = 'h';	
					$fn_t['fn'] = $fn->getFilename();	
					
					$fileParts = explode('.', $fn_t['fn']);
					//$fn_t['volIss'] = $fileParts[0].'.'.$fileParts[1];
					$fn_t['volNum'] = $fileParts[0];
					$fn_t['issueNum'] = $fileParts[1];
					$fn_t['issueShort'] = substr($fn_t['issueNum'], 0, 1);
					$fn_t['volIss'] = ($fn_t['volNum'] == 'bonus') ? 'bonus' : $fn_t['volNum'].'.'.$fn_t['issueNum'];
					$fn_t['fileSplit'] = ($fn_t['volNum'] == 'bonus') ? $fileParts[1] : $fileParts[2];
					$fn_t['file'] = ($fn_t['volNum'] == 'bonus') ? implode('.', array($fileParts[0], $fileParts[1])) : implode('.', array($fileParts[0], $fileParts[1], $fileParts[2]));

					// Only continue and show if on testing sites or published
					if($fn_t['fileSplit'] != 'toc') {
						$FullHTML = file_get_html('html/'.$fn_t['fn']); 
						$HTMLdate = getHtmlElementArray($FullHTML, 'meta[name=DC.Date.dateSubmitted]', 'content');
						$fn_t['date'] = (count($HTMLdate) > 0) ? seasonYearFromDate($HTMLdate[0]) : '';
						$HTMLtype = getHtmlElementArray($FullHTML, 'meta[name=DC.Type.articleType]', 'content');
						$fn_t['type'] = $HTMLtype[0];
						$HTMLtitle = getHtmlElementArray($FullHTML, 'meta[name=DC.Title]', 'content'); //getHtmlElementArray($FullHTML, 'div#articlecontent div.header p.title', 'innertext');
						$fn_t['title'] = str_replace($charKeys, $charValues, $HTMLtitle[0]);
						$XMLauthors = getHtmlElementArray($FullHTML, 'meta[name=DC.Creator.PersonalName]', 'content');
						//$XMLauthors = ($XMLauthors == array('G. E. Bentley', 'Jr.')) ? $XMLauthors = array('G. E. Bentley, Jr.') : $XMLauthors;
						$XMLfirstAuthor = (count($XMLauthors) > 0) ? $XMLauthors[0] : '';
						$XMLfirstAuthorNames = explode(' ', $XMLfirstAuthor);
						$fn_t['firstAuthorLastName'] = (strpos($XMLfirstAuthor,', Jr.') !== false) ? $XMLfirstAuthorNames[count($XMLfirstAuthorNames)-2] : $XMLfirstAuthorNames[count($XMLfirstAuthorNames)-1];
						$XMLfirstAuthor = (count($XMLauthors) > 0) ? $fn_t['firstAuthorLastName'].', '.str_replace(' '.$fn_t['firstAuthorLastName'], '', $XMLfirstAuthor) : '';
						$XMLauthors[0] = $XMLfirstAuthor;
						$fn_t['author'] = (count($XMLauthors) > 1) ? implode(', ', $XMLauthors) : $XMLfirstAuthor;
					
						//if($fn_t['fileSplit'] == 'toc' || $fn_t['type'] == 'illustration') {
						if($fn_t['type'] == 'illustration') {
						} else {
							foreach($articleUmbrellaTypes as $articleType) {
								if(in_array($fn_t['type'], $articleType['keys'])) {
									$docsXml[$articleType['keys'][0]][] = $fn_t;
								}
							}
						}
						
					}
				}
			}

			// XML
			foreach (new DirectoryIterator("./docs/") as $fn) {
				if (preg_match('/[0-9]{1,2}.[0-9]{1}[-a-z0-9]{0,3}.[-a-z0-9]{1,20}.xml/', $fn->getFilename())) {
					$fn_t = array();
					$fn_t['format'] = 'x';	
					$fn_t['fn'] = $fn->getFilename();	
					
					$fileParts = explode('.', $fn_t['fn']);
					//$fn_t['volIss'] = $fileParts[0].'.'.$fileParts[1];
					$fn_t['file'] = implode('.', array($fileParts[0], $fileParts[1], $fileParts[2]));
					$fn_t['volNum'] = $fileParts[0];
					$fn_t['issueNum'] = $fileParts[1];
					$fn_t['volIss'] = $fn_t['volNum'].'.'.$fn_t['issueNum'];
					$fn_t['issueShort'] = substr($fn_t['issueNum'], 0, 1);
					$fn_t['fileSplit'] = $fileParts[2];

					// Only continue and show if on testing sites or published
					if($fn_t['fileSplit'] != 'toc') {
						$FullXML = simplexml_load_file('docs/'.$fn_t['fn']); 
						$XMLdate = $FullXML->xpath('//editionStmt/edition');
						$fn_t['date'] = $XMLdate[0];
						$XMLtype = $FullXML->xpath('//teiHeader/fileDesc/titleStmt/title/@type');
						$fn_t['type'] = $XMLtype[0];
						$XMLtitle = $FullXML->xpath('//teiHeader/fileDesc/titleStmt/title');
						$fn_t['title'] = $XMLtitle[0];
						$XMLfirstAuthorLastName = $FullXML->xpath('//teiHeader/fileDesc/titleStmt/author/@n');
						$fn_t['firstAuthorLastName'] = (count($XMLfirstAuthorLastName) > 0) ? $XMLfirstAuthorLastName[0] : '';
						$XMLauthors = $FullXML->xpath('//teiHeader/fileDesc/titleStmt/author');
						$XMLfirstAuthor = (count($XMLauthors) > 0) ? $XMLauthors[0] : '';
						$XMLfirstAuthor = ($XMLfirstAuthor == 'Santa Cruz Blake Study Group' || $XMLfirstAuthor == '') ? $XMLfirstAuthor : $fn_t['firstAuthorLastName'].', '.str_replace(' '.$fn_t['firstAuthorLastName'], '', $XMLfirstAuthor);
						$XMLauthors[0] = $XMLfirstAuthor;
						$fn_t['author'] = (count($XMLauthors) > 1) ? implode(', ', $XMLauthors) : $XMLfirstAuthor;
					
						//if($fn_t['fileSplit'] == 'toc') {
						//} else {
							foreach($articleUmbrellaTypes as $articleType) {
								if(in_array($fn_t['type'], $articleType['keys'])) {
									$docsXml[$articleType['keys'][0]][] = $fn_t;
								}
							}
						//}
						
					}
				}
			}
			
			?>
							<table>
			<?php

			foreach ($docsXml as $key => $index) {
				usort($index, 'cmpArticle');
				$type = $typeTitles[$key];
			?>
								<tr><td colspan="5"><h2 id="<?php echo $key; ?>"><?php echo $type; ?></h2></td></tr>
			<?php
				for ($i=0; $i<count($index); $i++) {
					if(showable($index[$i]['volNum'], $index[$i]['issueShort']) || $index[$i]['volIss'] == 'bonus') {
						print '<tr><td>'.$index[$i]['author'].'</td>'.$nl;
						print '	<td><a class="issue-link" href="'.$index[$i]['file'].'">'.$index[$i]['title'].'</a></td>'.$nl;
						print '	<td>'.$index[$i]['volIss'].'</td><td>'.$index[$i]['date'].'</td>'.$nl;
						print '	<td><a class="index-pdf-link" href="pdfs/'.$index[$i]['file'].'.pdf" target="_blank">PDF</a></td>'.$nl;
						print '</tr>'.$nl;
					}
				}
			
			}
			
			?>
							</table>
						</div> <!-- #articles-reviews-index -->
					</div> <!-- #main -->
					<?php
					include('include/footer.php');
					?>
				</div> <!-- #all-content-inner -->
			</div> <!-- #all-content -->
		</div> <!-- #outer -->
	</body>
</html>

