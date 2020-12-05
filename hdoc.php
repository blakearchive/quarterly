<!DOCTYPE html>
<html>
	<?php
	require('include/functions.php');
	
	$fileExists = true;
	$fileShowable = true;
	$file = '';
	$fileSplit = '';
	$volIss = '';
	$volNum = '';
	$issueNum = '';
	$date = '';
	$headingTitle = '';
	$type = '';
	$mainArticle = '';
	$mainArticleTitle = '';
	$issueDesc = '';
	$pt = array();
	$pt_post = '';
	
	$HTML = null;
	$HTMLdoc = null;
	$XSLpath = '';
	$xpath = '';
	$xpath2 = '';
	
	if($_GET["file"]) {
		$file = $_GET["file"];
		$fileParts = explode('.', $file);
		if(count($fileParts) > 2) {
			$volIss = $fileParts[0].'.'.$fileParts[1];
			$volNum = $fileParts[0];
			$issueNum = $fileParts[1];
			$issueShort = substr($issueNum, 0, 1);
			$fileSplit = $fileParts[2];
		} else {
			$volIss = $fileParts[0];
			$fileSplit = $fileParts[1];
		}
	}
	if(file_get_html('html/'.$file.'.html')) {
		// Prepping HTML file
		$HTML = file_get_html('html/'.$file.'.html');
		
		if(preg_match('/[0-9]{1,2}.[0-9]{1}[-a-z0-9]{0,3}/', $volIss)) {
			
			if(showable($volNum, $issueShort)) {
				$fileShowable = true;
							
				if($fileSplit !== 'toc') {
					$HTMLtitle = getHtmlElementArray($HTML, 'meta[name=DC.Title]', 'content');
					$articleTitle = strip_tags(html_entity_decode($HTMLtitle[0]));
					$HTMLauthors = getHtmlElementArray($HTML, 'meta[name=DC.Creator.PersonalName]', 'content');
					$authors = implode(', ', $HTMLauthors);
					$HTMLfullDate = getHtmlElementArray($HTML, 'meta[name=DC.Date.dateSubmitted]', 'content');
					$date = seasonYearFromDate($HTMLfullDate[0]);
					$HTMLtype = getHtmlElementArray($HTML, 'meta[name=DC.Type.articleType]', 'content');
					$type = $HTMLtype[0];
						
					$pt[] = $articleTitle;
					if($authors != '') {
						$pt[] = $authors;
					}
					$pt_post = 'Volume '.$volNum.', Issue '.$issueNum;
					
					// For loading full file
					$XSLpath = 'xsl/quarterlyHtml.xsl';
					$xpath = (count(getHtmlElementArray($HTML, 'div[id=articlecontent]', 'outertext'))>0) ? 'div[id=articlecontent]' : 'div[id=content]';
					$xpath2 = 'div[class=cover]';
					$xpath3 = 'img[class=cover]';
				} else {
					$HTMLdesc = getHtmlElementArray($HTML, 'div[id=issueDescription] p', 'innertext');
					$date = preg_replace("@^[<p>]{0,3}(Spring|Summer|Fall|Winter) (2[0-9]{3}[-–0-9]{0,5})(:|<br[ /]{0,2}>).{0,}$@s", "$1 $2", $HTMLdesc[0]);
					$issueDesc = '';
					if(!preg_match('@^([<p>]{0,3})(Spring|Summer|Fall|Winter) (2[0-9]{3}[-–0-9]{0,5})$@', $HTMLdesc[0])) {
						$issueDesc = preg_replace("@^([<p>]{0,3})(Spring|Summer|Fall|Winter) (2[0-9]{3}[-–0-9]{0,5})(:|<br[ /]{0,2}>)(.{0,})$@s", "$1$5", $HTMLdesc[0]);
					}		
				
					$pt_post = 'Volume '.$volNum.', Issue '.$issueNum;
					
					// For loading full file
					$XSLpath = 'xsl/quarterlyHtmlToc.xsl';
					$xpath = 'div[id=artInfo]';
				}
				$headingTitle = 'Volume '.$volNum.' &middot; Issue '.$issueNum;
			} else {
				$fileShowable = false;
			}
		} else if($volIss == 'bonus') {
			if($fileSplit !== 'toc') {
				$HTMLtitle = getHtmlElementArray($HTML, 'meta[name=DC.Title]', 'content');
				$articleTitle = strip_tags(html_entity_decode($HTMLtitle[0]));
				$HTMLauthors = getHtmlElementArray($HTML, 'meta[name=DC.Creator.PersonalName]', 'content');
				$authors = implode(', ', $HTMLauthors);
				$HTMLfullDate = getHtmlElementArray($HTML, 'meta[name=DC.Date.dateSubmitted]', 'content');
				$date = seasonYearFromDate($HTMLfullDate[0]);
				$HTMLtype = getHtmlElementArray($HTML, 'meta[name=DC.Type.articleType]', 'content');
				$type = $HTMLtype[0];
				
				$HTMLvolume = getHtmlElementArray($HTML, 'meta[name=DC.Source.Volume]', 'content');
				$volNum = $HTMLvolume[0];
				$HTMLissue = getHtmlElementArray($HTML, 'meta[name=DC.Source.Issue]', 'content');
				$issueNum = $HTMLissue[0];
				
				$pt_post = 'Volume '.$volNum.' &middot; Issue '.$issueNum.' &middot; Bonus Content';
				$headingTitle = 'Volume '.$volNum.' &middot; Issue '.$issueNum;
				
				$pt[] = $articleTitle;
				if($authors != '') {
					$pt[] = $authors;
				}
			} else {
				$pt_post = 'Bonus Content';
				$headingTitle = 'Bonus Content';
				
				$HTMLdesc = getHtmlElementArray($HTML, 'div[id=issueDescription] p', 'innertext');
				$descParts = explode(':', $HTMLdesc[0]);
				$date = $descParts[0];
				$issueDesc = $descParts[1];
			}
			
			// For loading full file
			$XSLpath = 'xsl/bonus.xsl';
			$xpath = 'body';
		} else {
			$fileShowable = false;
		}
	} else {
		$fileExists = false;
	}
	
	if($type == 'illustration') {
		$HTMLmainArticle = getHtmlElementArray($HTML, 'meta[name=mainArticle]', 'content');
		$mainArticle = $HTMLmainArticle[0];
		$HTMLmainArticleTitle = getHtmlElementArray($HTML, 'meta[name=mainArticleTitle]', 'content');		
		$mainArticleTitle = $HTMLmainArticleTitle[0];				
	}

	
	if($fileExists && $fileShowable) {
		# LOAD HTML FILE
		
		$HTMLstyle = getHtmlElementArray($HTML, 'style', 'innertext');
		$restrictedHTMLstyle = array();
		for($i=0; $i<count($HTMLstyle); $i++) {
			$restrictedHTMLstyle[] = restrictStyle($HTMLstyle[$i]);
		}
		$styles = implode(' ', $restrictedHTMLstyle);
		
		$HTMLscript = getHtmlElementArray($HTML, 'script', 'outertext'); // Note this removes line breaks; comment at end of line can cut off subsequent code.
		$scripts = implode(' ', $HTMLscript);
		
		/*
		$HTMLidno = getHtmlElementArray($HTML, 'body', 'id');
		$idno = implode(' ', $HTMLidno);
		*/
		
		$bodycontext = 'outertext';//($xpath == 'body') ? 'innertext' : 'outertext';
		$HTMLbody = getHtmlElementArray($HTML, $xpath, $bodycontext);
		$HTMLstring = $HTMLbody[0];//.'<issue-idno>'.$volIss.'</issue-idno>';
		
		$id = ($volIss == 'bonus') ? $volNum.'.'.$issueNum : $volIss;
		$HTMLstring = str_replace('<div id="artInfo">', '<div id="artInfo"><div id="idno">'.$id.'</div>', $HTMLstring); // for toc
		$HTMLstring = str_replace('<div id="articlecontent">', '<div id="articlecontent"><div id="idno">'.$id.'</div>', $HTMLstring); // for articles
		$HTMLstring = str_replace('<div id="content">', '<div id="content"><div id="idno">'.$id.'</div>', $HTMLstring); // for other articles
		
		// Make sure there is a #content tag (for styles)
		$HTMLstring = (strpos($HTMLstring, '<div id="content">') === false) ? '<div id="content">'.$HTMLstring.'</div>' : $HTMLstring;
		
		if($xpath2 != '') {
			$HTMLprefix = getHtmlElementArray($HTML, $xpath2, 'outertext');
			$HTMLstring = $HTMLprefix[0].$HTMLstring;
		}
		if($xpath3 != '') {
			$HTMLprefix = getHtmlElementArray($HTML, $xpath3, 'outertext');
			$HTMLstring = $HTMLprefix[0].$HTMLstring;
		}
		
		$HTMLdoc = DOMDocument::loadHTML ($HTMLstring);
	}
	
	require('include/head.php');
	?>
	<body>
        <div id="outer" class="hdoc-page">
			<?php
			require('include/header.php');
			?>
			<div id="all-content">
				<div id="all-content-inner">
					<?php
					if($fileExists && $fileShowable) {
					?>
					<div id="issue-heading">
						<div class="issue-heading-inner">
							<?php
								if($fileSplit == 'toc') {
								?>
									<h1><?php echo $headingTitle; ?></h1>
									<h2><?php echo $date; ?></h2>
									<?php if($issueDesc !== '') { echo '<p>'.$issueDesc.'</p>'; } ?>
								<?php
								} else if($volIss == 'bonus') {
								?>
									<h1><a href="<?php echo $volNum.'.'.$issueNum; ?>.toc"><?php echo $headingTitle; ?></a> &middot; <a href="<?php echo $volIss; ?>.toc">Bonus Content</a></h1>
									<h2><?php echo $date; ?></h2>
								<?php
									if($type == 'illustration') {
								?>
									<p><em>Illustration for:</em> <?php echo '<a href="'.$mainArticle.'"/>'.$mainArticleTitle.'</a>'; ?></p>
								<?php
									}
								} else if($type == 'illustration') {
								?>
									<h1><a href="<?php echo $volIss; ?>.toc"><?php echo $headingTitle; ?></a></h1>
									<h2><?php echo $date; ?></h2>
									<p><em>Illustration for:</em> <?php echo '<a href="'.$mainArticle.'"/>'.$mainArticleTitle.'</a>'; ?></p>
								<?php
								} else {
								?>
									<div class="volume-issue-date">
										<h1><a href="<?php echo $volIss; ?>.toc"><?php echo $headingTitle; ?></a></h1>
										<h2><?php echo $date; ?></h2>
										<a class="heading-pdf-link" target="_blank" href="pdfs/<?php echo $file; ?>.pdf"><span class="pdf-type">article</span> <span class="pdf-abbr">pdf</span></a>
									</div>
									<div class="clear"></div>
							<?php
								}
							?>
						</div>
					</div>
					<div id="main" class="main-<?php echo $file; ?>">
				<?php						
				# START XSLT 
				$xslt = new XSLTProcessor(); 
				$XSL = new DOMDocument();
				$XSL->load($XSLpath); 
				$xslt->importStylesheet( $XSL ); 
				#PRINT 
				print htmlentities_savetags($xslt->transformToXML( $HTMLdoc ));
				?>
						<div class="clear"></div>
					</div>
					<div class="clear"></div>
			<?php
			} else if ($fileExists) {
			?>
					<div id="issue-heading">
						<div class="issue-heading-inner">
							<h1>Issue Not Yet Published</h1>
						</div>
					</div>
					<div id="main">
						<p>Return to <a href="/">Issue Archive</a>.</p>
					</div>
			<?php
			} else {
				include('include/notfound.php');
			}
			
			include('include/footer.php');
			?>
				</div> <!-- #all-content-inner -->
			</div> <!-- #all-content -->
		</div> <!-- #outer -->
	</body>
</html>