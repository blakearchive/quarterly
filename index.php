<!DOCTYPE html>
<html>
	<?php
	require('include/functions.php');
	$pt = array();
	$pt_post = '';
	$q = '';
	$qw = '';
	$html = false;
	$lastDecade = '';
	$lastVol = '';
	$thumbwidth = '158';
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
							<h1>Issue Archive</h1>
						</div>
					</div>
					<div id="main">
						<div id="allIssues" style="display:none;">
			
			<?php
			// First, HTML
			
			$docsHtml = array(); 
			foreach (new DirectoryIterator("./html/") as $fn) {
				if (preg_match('/[0-9]{1,2}.[0-9]{1}[-a-z0-9]{0,3}.toc.html/', $fn->getFilename())) {
					$fn_t = array();
					
					$fn_t['fn'] = $fn->getFilename();	
					
					$fileParts = explode('.', $fn_t['fn']);
					//$fn_t['volIss'] = $fileParts[0].'.'.$fileParts[1];
					$fn_t['file'] = implode('.', array($fileParts[0], $fileParts[1], $fileParts[2]));
					$fn_t['volNum'] = $fileParts[0];
					$fn_t['issueNum'] = $fileParts[1];
					$fn_t['issueShort'] = substr($fn_t['issueNum'], 0, 1);
					
					// Only continue and show if on testing sites or published
					if(showable($fn_t['volNum'], $fn_t['issueShort'])) {
						$FullHTML = file_get_html('html/'.$fn_t['fn']);
						$HTMLsrc = getHtmlElementArray($FullHTML, 'div[id=issueCoverImage] img', 'src');
						$fn_t['img'] = '<img width="'.$thumbwidth.'" src="img/illustrations/'.$fn_t['volNum'].'.'.$fn_t['issueNum'].'/'.$HTMLsrc[0].'" />'; //str_replace("<img ","<img width='".$thumbwidth."' ",$HTMLimg[0]);
						$HTMLdate = getHtmlElementArray($FullHTML, 'div[id=issueDescription] p', 'innertext');
						$fn_t['date'] = preg_replace("@^[<p>]{0,3}(Spring|Summer|Fall|Winter) (2[0-9]{3}[-–0-9]{0,5})(:|<br[ /]{0,2}>).{0,}$@s", "$1 $2", $HTMLdate[0]);
						$HTMLyear = substr($fn_t['date'], stripos($fn_t['date'], ' ')+1, 4);
						$fn_t['decade'] = substr($HTMLyear, 0, 3).'0s';
						
						$docsHtml[] = $fn_t;
					}
					
				}
			}

			usort($docsHtml, 'cmp');

			for ($i=0; $i<count($docsHtml); $i++) {
				if($docsHtml[$i]['decade'] != $lastDecade) {
					if($lastDecade != '') {
						print '</div> <!-- end decade -->';
					}
					print '<h3 class="decadeHeading">'.$docsHtml[$i]['decade'].'</h3><div class="collapse" id="'.$docsHtml[$i]['decade'].'">';
					if($docsHtml[$i]['volNum'] == $lastVol) {
					volumeLabel($docsHtml[$i]['volNum']);
					}
					$lastDecade = $docsHtml[$i]['decade'];
				}
				if($docsHtml[$i]['issueShort'] == 1) {
					echo '<div class="clear"></div>';
					volumeLabel($docsHtml[$i]['volNum']);
				}
				print '<div class="issue html-issue '.$docsHtml[$i]['decade'].' vol'.$docsHtml[$i]['volNum'].' issue'.$docsHtml[$i]['issueShort'].'"><a class="issue-link" href="'.$docsHtml[$i]['file'].'"><span class="cover-thumb-holder">'.$docsHtml[$i]['img'].'</span><span class="issue-link-heading">Volume '.$docsHtml[$i]['volNum'].' &middot; Issue '.$docsHtml[$i]['issueNum'].'<br/>('.$docsHtml[$i]['date'].')</span></a></div>';
				/*
				if($i+1 == count($docsHtml)) {
					print '</div> <!-- end decade -->';
				}
				*/
			}

			
			// XML split into articles
			
			//$lastDecade = ''; // need to reset to avoid extra close div tag
			$lastVol = ''; // need to reset to avoid extra close div tag

			$docsXml = array(); 
			foreach (new DirectoryIterator("./docs/") as $fn) {
				if (preg_match('/[0-9]{1,2}.[0-9]{1}[-a-z0-9]{0,3}.toc.xml/', $fn->getFilename())) {
					$fn_t = array();
					$fn_t['fn'] = $fn->getFilename();	
					
					$fileParts = explode('.', $fn_t['fn']);
					$fn_t['volIss'] = $fileParts[0].'.'.$fileParts[1];
					$fn_t['file'] = implode('.', array($fileParts[0], $fileParts[1], $fileParts[2]));
					$fn_t['volNum'] = $fileParts[0];
					$fn_t['issueNum'] = $fileParts[1];
					$fn_t['issueShort'] = substr($fn_t['issueNum'], 0, 1);

					// Only continue and show if on testing sites or published
					if(showable($fn_t['volNum'], $fn_t['issueShort'])) {
						$FullXML = simplexml_load_file('docs/'.$fn_t['fn']); 
						$XMLimg = $FullXML->xpath('//div1[@id="cover"]/figure/@n');
						$fn_t['img'] = '<img width="'.$thumbwidth.'" src="img/illustrations/'.$XMLimg[0].'.thumb.png"/>';
						$XMLdate = $FullXML->xpath('//editionStmt/edition');
						$fn_t['date'] = $XMLdate[0];
						$XMLyear = $FullXML->xpath('//fileDesc/publicationStmt/date');
						$fn_t['decade'] = substr($XMLyear[0], 0, 3).'0s';
					
						$docsXml[] = $fn_t;
					}
				}
			}
			
			usort($docsXml, 'cmp');
			
			for ($i=0; $i<count($docsXml); $i++) {
				if($docsXml[$i]['decade'] != $lastDecade) {
					if($lastDecade != '') {
						print '</div>';
					}
					print '<h3 class="decadeHeading">'.$docsXml[$i]['decade'].'</h3><div class="collapse" id="'.$docsXml[$i]['decade'].'">';
					if($docsXml[$i]['volNum'] == $lastVol) {
					volumeLabel($docsXml[$i]['volNum']);
					}
					$lastDecade = $docsXml[$i]['decade'];
				}
				if($docsXml[$i]['volNum'] != $lastVol) {
					echo '<div class="clear"></div>';
					volumeLabel($docsXml[$i]['volNum']);
					$lastVol = $docsXml[$i]['volNum'];
				}
				print '<div class="issue xml-issue '.$docsXml[$i]['decade'].' issue'.$docsXml[$i]['issueShort'].'"><a class="issue-link" href="'.$docsXml[$i]['file'].'"><span class="cover-thumb-holder">'.$docsXml[$i]['img'].'</span><span class="issue-link-heading">Volume '.$docsXml[$i]['volNum'].' &middot; Issue '.$docsXml[$i]['issueNum'].'<br/>('.$docsXml[$i]['date'].')</span></a></div>';
				if($i+1 == count($docsXml)) {
					print '</div>';
				}
			}
			
			?>
						</div> <!-- #allIssues -->
					</div> <!-- #main -->
					<?php
					include('include/footer.php');
					?>
				</div> <!-- #all-content-inner -->
			</div> <!-- #all-content -->
		</div> <!-- #outer -->
	</body>
</html>

