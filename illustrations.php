<!DOCTYPE html>
<html>
	<?php
	require('include/functions.php');
	$title = 'Index of Illustrations';
	$pt = array($title);
	$pt_post = '';
	$q = '';
	$qw = '';
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
						<p>Below is an index of external illustrations for articles.</p>
						<div id="illustrations-index">
			<?php
				
			$docsXml = array();
			$docsXml['illustration'] = array();
			$typeTitles = array();
			$typeTitles['illustration'] = 'Illustrations';
			
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
					$fn_t['volNum'] = $fileParts[0];
					$fn_t['issueNum'] = $fileParts[1];
					$fn_t['issueShort'] = substr($fn_t['issueNum'], 0, 1);
					$fn_t['volIss'] = ($fn_t['volNum'] == 'bonus') ? 'bonus' : $fn_t['volNum'].'.'.$fn_t['issueNum'];
					$fn_t['fileSplit'] = ($fn_t['volNum'] == 'bonus') ? $fileParts[1] : $fileParts[2];
					$fn_t['file'] = ($fn_t['volNum'] == 'bonus') ? implode('.', array($fileParts[0], $fileParts[1])) : implode('.', array($fileParts[0], $fileParts[1], $fileParts[2]));

					// Only continue and show if on testing sites or published
					if($fn_t['fileSplit'] != 'toc' && (showable($fn_t['volNum'], $fn_t['issueShort']) || $fn_t['volIss'] == 'bonus')) {
						$FullHTML = file_get_html('html/'.$fn_t['fn']); 
						$HTMLtype = getHtmlElementArray($FullHTML, 'meta[name=DC.Type.articleType]', 'content');
						$fn_t['type'] = $HTMLtype[0];

						if($fn_t['type'] == 'illustration') {
							$HTMLdate = getHtmlElementArray($FullHTML, 'meta[name=DC.Date.dateSubmitted]', 'content');
							$fn_t['date'] = (count($HTMLdate) > 0) ? seasonYearFromDate($HTMLdate[0]) : '';
							$HTMLtitle = getHtmlElementArray($FullHTML, 'meta[name=DC.Title]', 'content'); //getHtmlElementArray($FullHTML, 'div#articlecontent div.header p.title', 'innertext');
							$fn_t['title'] = str_replace($charKeys, $charValues, $HTMLtitle[0]);
							$XMLmainArticle = getHtmlElementArray($FullHTML, 'meta[name=mainArticle]', 'content');
							$fn_t['mainArticle'] = $XMLmainArticle[0];
							$XMLmainArticleTitle = getHtmlElementArray($FullHTML, 'meta[name=mainArticleTitle]', 'content');
							$fn_t['mainArticleTitle'] = $XMLmainArticleTitle[0];
							$XMLauthors = getHtmlElementArray($FullHTML, 'meta[name=DC.Creator.PersonalName]', 'content');
							$XMLauthors = ($XMLauthors == array('G. E. Bentley, Jr.')) ? $XMLauthors = array('G. E. Bentley', 'Jr.') : $XMLauthors;
							$XMLfirstAuthor = (count($XMLauthors) > 0) ? $XMLauthors[0] : '';
							$XMLfirstAuthorNames = explode(' ', $XMLfirstAuthor);
							$fn_t['firstAuthorLastName'] = (strpos($XMLfirstAuthor,', Jr.') !== false) ? $XMLfirstAuthorNames[count($XMLfirstAuthorNames)-2] : $XMLfirstAuthorNames[count($XMLfirstAuthorNames)-1];
							$XMLfirstAuthor = $fn_t['firstAuthorLastName'].', '.str_replace(' '.$fn_t['firstAuthorLastName'], '', $XMLfirstAuthor);
							$XMLauthors[0] = $XMLfirstAuthor;
							$fn_t['author'] = (count($XMLauthors) > 1) ? implode(', ', $XMLauthors) : $XMLfirstAuthor;
					
							$docsXml['illustration'][] = $fn_t;
						}
						
					}
				}
			}
			
			?>
							<table>
			<?php

			$index = $docsXml['illustration'];
			usort($index, 'cmpIllus');

			print '<tr><th>Author</th><th>Illustration</a></th><th>Main Article</th><th>Volume/Issue</th><th>Date</th></div>';

			for ($i=0; $i<count($index); $i++) {
				$mainLink = ($index[$i]['mainArticle'] == '') ? $index[$i]['mainArticleTitle'] : '<a class="issue-link" href="'.$index[$i]['mainArticle'].'">'.$index[$i]['mainArticleTitle'].'</a>';
				print '<tr><td>'.$index[$i]['author'].'</td><td><a class="issue-link" href="'.$index[$i]['file'].'">'.$index[$i]['title'].'</a></td><td>'.$mainLink.'</td><td>'.$index[$i]['volIss'].'</td><td>'.$index[$i]['date'].'</td></div>';
			}
			
			
			
			?>
							</table>
						</div> <!-- #illustrations-index -->
					</div> <!-- #main -->
					<?php
					include('include/footer.php');
					?>
				</div> <!-- #all-content-inner -->
			</div> <!-- #all-content -->
		</div> <!-- #outer -->
	</body>
</html>

