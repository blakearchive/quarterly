<!DOCTYPE html>
<html>
	<?php
	require('include/functions.php');
	
	$fileExists = true;
	$fileShowable = true;
	$file = '';
	$volIss = '';
	$volNum = '';
	$issueNum = '';
	$fileSplit = '';
	$date = '';
	$title = '';
	$pt = array();
	$pt_post = '';
	$addDigitalCredits = false;
	
	if($_GET["file"] && simplexml_load_file('docs/'.$_GET["file"].'.xml')) {
		$file = $_GET["file"];
		
		if (preg_match('/[0-9]{1,2}.[0-9]{1}[-a-z0-9]{0,3}.[-a-z0-9]{1,20}/', $file)) {
			$fileParts = explode('.', $file);
			$volNum = $fileParts[0];
			$issueNum = $fileParts[1];
			$issueShort = substr($issueNum, 0, 1);
			$fileSplit = $fileParts[2];
			$volIss = $fileParts[0].'.'.$fileParts[1];
			
			if(showable($volNum, $issueShort)) {
				$fileShowable = true;
				$addDigitalCredits = true;
				
				$FullXML = simplexml_load_file('docs/'.$file.'.xml');
				$XMLdate = $FullXML->xpath('//editionStmt/edition');
				$date = $XMLdate[0];
				$XMLtitle = $FullXML->xpath('//teiHeader/fileDesc/titleStmt/title');
				$title = $XMLtitle[0];
				$XMLauthors = $FullXML->xpath('//teiHeader/fileDesc/titleStmt/author');
				$authors = implode(', ', $XMLauthors);
		
				$pt[] = $title;
				if($authors != '') {
					$pt[] = $authors;
				}
				$pt_post = 'Volume '.$volNum.', Issue '.$issueNum;
			} else {
				$fileShowable = false;
			}
		} else {
			$fileShowable = true;
		
			$FullXML = simplexml_load_file('docs/'.$file.'.xml');
			$XMLtitle = $FullXML->xpath('//teiHeader/fileDesc/titleStmt/title');
			$title = $XMLtitle[0];
		
			$pt[] = $title;
		}
	} else {
		$fileExists = false;

		$file = $_GET["file"];
		$fileParts = explode('.', $file);
		$volNum = $fileParts[0];
		$issueNum = $fileParts[1];
		$volIss = $fileParts[0].'.'.$fileParts[1];
	}
	
	$html = false;
	
	require('include/head.php');
	?>
	<body>
        <div id="outer" class="doc-page">
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
							if (preg_match('/[0-9]{1,2}.[0-9]{1}[-a-z0-9]{0,3}.[-a-z0-9]{1,20}/', $file)) {
							?>
								<div class="volume-issue-date">
									<?php if ($fileSplit !== 'toc') { ?>
									<h1><a href="<?php echo $volIss.'.toc'; ?>"/>Volume <?php echo $volNum; ?> &middot; Issue <?php echo $issueNum; ?></a></h1>
									<?php } else { ?>
									<h1>Volume <?php echo $volNum; ?> &middot; Issue <?php echo $issueNum; ?></h1>
									<?php } ?>
									<h2><?php echo $date; ?></h2>
									<a class="heading-pdf-link" target="_blank" href="pdfs/<?php echo $file; ?>.pdf"><span class="pdf-type">article</span> <span class="pdf-abbr">pdf</span></a> &middot; <a class="heading-pdf-link" target="_blank" href="pdfs/issues/<?php echo $volIss; ?>.pdf"><span class="pdf-type">issue</span> <span class="pdf-abbr">pdf</span></a>
								</div>
								<div class="clear"></div>
							<?php
							} else {
							?>
								<h1><?php echo $title; ?></h1>
							<?php
							}
							?>
						</div>
					</div>
					<?php
					
						# LOAD XML FILE 
						$XML = new DOMDocument(); 
						$XML->load( 'docs/'.$file.'.xml' );

					
						# START XSLT 
						$xslt = new XSLTProcessor(); 
						$XSL = new DOMDocument(); 
						$XSL->load( 'xsl/quarterly.xsl'); 
						$xslt->importStylesheet( $XSL ); 
						#PRINT 
						print $xslt->transformToXML( $XML ); 
						
						
						# Digital edition credits
						if($addDigitalCredits) {
							credits();
						}

					} else if ($fileExists) { // file exists but is not published
					?>
						<div id="issue-heading">
							<div class="issue-heading-inner">
								<h1>Issue Not Yet Published</h1>
							</div>
						</div>
						<div id="main">
							<div id="core">
								<p>Return to <a href="<?php echo $root; ?>">Issue Archive</a>.</p>
							</div>
						</div>
					<?php
					} else if ($fileShowable && simplexml_load_file('docs/'.$volIss.'.toc.xml')) { // file does not exist but TOC for its issue exists and is published
					?>
						<div id="issue-heading">
							<div class="issue-heading-inner">
								<h1>File Not Found in This Issue</h1>
							</div>
						</div>
						<div id="main">
							<div id="core">
								<p>Browse this issue: <a href="<?php echo $volIss.'.toc'; ?>"/>Volume <?php echo $volNum; ?> &middot; Issue <?php echo $issueNum; ?></a>.</p>
								<p>Return to <a href="<?php echo $root; ?>">Issue Archive</a>.</p>
							</div>
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
