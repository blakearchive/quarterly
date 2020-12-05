<!DOCTYPE html>
<html>
<!--test-->
	<?php
	//require('/usr/share/php/Solarium/Autoloader.php'); // TJS tweak testing paths
	require('Solarium/Autoloader.php');
	require_once('Symfony/Component/ClassLoader/ClassLoader.php');

	use Symfony\Component\ClassLoader\ClassLoader;

	$loader = new ClassLoader();

	// to enable searching the include path (eg. for PEAR packages)
	$loader->setUseIncludePath(true);

	// ... register namespaces and prefixes here - see below

	$loader->register();
	//require('Autoloader.php');
	\Solarium\Autoloader::register();
	//require('lib/init.php');
	//require('lib/Autoloader.php');
	//Solarium_Autoloader::register();
	
	require('include/functions.php');

	$pt = array('Search');
	
	require('include/head.php');
	?>
	<body>
        <div id="outer">
			<?php
			$q = $_GET["q"];
			$q = preg_replace("/([a-zA-Z])'([a-zA-Z])/", '$1’$2', $q); // straight apostrophes in words become curved apostrophes (to match curved apostrophes in the text)
			
			$qs = 'score'; // sort by this field
			$qso = 'desc'; // sort the field in this order/direction
			$qp = '0'; // where pagination starts (result number)
			$qpp = '0'; // where pagination starts on previous page
			$qpn = '10'; // where pagination starts on next page
			$numRows = 10; // results per page of results
			$types = array();
			$typeKeys = array();
			$fields = array();
			if ($_GET["qs"]) {
				$qs = $_GET["qs"];
			}
			if ($_GET["qso"]) {
				$qso = $_GET["qso"];
			}
			if ($_GET["qp"]) {
				$qp = $_GET["qp"];
			}
			if ($qp != '0') {
				$qpp = $qp - $numRows;
				$qpn = $qp + $numRows;
			}
			if ($_GET["type"]) {
				$types = $_GET["type"];
				foreach($articleTypes as $type) {
					if(in_array ($type['keys'][0], $types)) {
						$typeKeys = array_merge($typeKeys, $type['keys']);
					}
				}
			} else {
				foreach($articleTypes as $type) {
					if($type['keys'][0] != 'toc') {
						$types[] = $type['keys'][0];
						$typeKeys = array_merge($typeKeys, $type['keys']);
					}
				}
			}
			$typeArg = '';
			if(count($types) > 0) {
				if(count($types) > 1) {
					$typeArg = '&type[]='.implode('&type[]=', $types);
				} else {
					$typeArg = '&type[]='.$types[0];
				}
			}
			if ($_GET["field"]) {
				$fields = $_GET["field"];
			} else {
				$fields = array('fulltext', 'title', 'author');
			}
			$fieldArg = '';
			if(count($fields) > 0) {
				if(count($fields) > 1) {
					$fieldArg = '&field[]='.implode('&field[]=', $fields);
				} else {
					$fieldArg = '&field[]='.$fields[0];
				}
			}
			
			$sortOptions = array();
			$sortOptions[0] = array();
			$sortOptions[0]['name'] = 'Most relevant';
			$sortOptions[0]['qs'] = 'score';
			$sortOptions[0]['qso'] = 'desc';
			$sortOptions[1] = array();
			$sortOptions[1]['name'] = 'Newest';
			$sortOptions[1]['qs'] = 'volIss';
			$sortOptions[1]['qso'] = 'desc';
			$sortOptions[2] = array();
			$sortOptions[2]['name'] = 'Oldest';
			$sortOptions[2]['qs'] = 'volIss';
			$sortOptions[2]['qso'] = 'asc';
			$sortOptions[3] = array();
			$sortOptions[3]['name'] = 'Author';
			$sortOptions[3]['qs'] = 'authorLast';
			$sortOptions[3]['qso'] = 'asc';
			$sortOptions[4] = array();
			/* // Apparently, enabling titles to be searchable word by word (set to general text type in schema), means they are also sorted word by word--by whichever word in the title would be sorted highest.
			$sortOptions[4]['name'] = 'Title';
			$sortOptions[4]['qs'] = 'title';
			$sortOptions[4]['qso'] = 'asc';
			*/
			
			$searchFields = array('fulltext' => 'Full Text', 'title' => 'Title', 'author' => 'Author');
			
			require('include/header.php');
			?>
			
			<div id="all-content">
				<div id="all-content-inner">
					<div id="issue-heading">
						<div class="issue-heading-inner">
							<h1>Search</h1>
						</div>
					</div>
					<div id="main">
					<?php
					if ($q) {
						$solrServer = '';
						$solrPort = '';
						if($_SERVER['SERVER_NAME'] == 'bq-dev.blakearchive.org') {
							$solrServer = 'fargo.libint.unc.edu';
							$solrPort = 8983;
						} elseif($_SERVER['SERVER_NAME'] == 'bq.blakearchive.org') {
							$solrServer = 'pierre.libint.unc.edu';
							$solrPort = 8983;
						} else {
							$solrServer = '127.0.0.1';
							$solrPort = 8200;
						}

						$config = array(
							'endpoint' => array(
								'localhost' => array(
									'host' => $solrServer,
									'port' => $solrPort,
									'path' => '/solr/bq/',
								)
							)
						);

						// create a client instance
						//$client = new Solarium_Client($config);
						$client = new Solarium\Client($config);

						// get a select query instance
						$query = $client->createSelect();
						
						if($_SERVER['SERVER_NAME'] == 'bq.blakearchive.org') {
							// create a filterquery
							$query->createFilterQuery('published')->setQuery('volIss:['.$minVol.'.'.$minIss.' TO '.$maxVol.'.'.$maxIss.'] OR idno:[About TO Emend] OR idno:[bonus.a TO bonus.z]');
						}
						if(count($types) < count($articleTypes)) {
							$query->createFilterQuery('types')->setQuery('type:('.implode(' OR ',$typeKeys).')');
						}

						// query each field
						$f = (count($fields)>1) ? implode(' ', $fields) : $fields[0];
						$dismax = $query->getDisMax();
						$dismax->setQueryFields($f);
						
						// set a query
						$query->setQuery($q);

						// set start and rows param (comparable to SQL limit) using fluent interface
						$query->setStart($qp)->setRows($numRows);

						// set fields to fetch (this overrides the default setting 'all fields')
						//$query->setFields(array('idno'));

						// sort the results
						//$query->addSort('idno', 'desc'); //Solarium_Query_Select::SORT_DESC
						//$query->addSort('score', 'desc');
						$query->addSort($qs, $qso);
						if($qs == 'authorLast') {
							$query->addSort('authorLast', 'asc');
						}

						// get highlighting component and apply settings
						$hl = $query->getHighlighting();
						$hl->setFields('fulltext', 'title', 'author');
						$hl->setSimplePrefix('<strong>');
						$hl->setSimplePostfix('</strong>');
						$hl->setSnippets(3);

						// this executes the query and returns the result
						$resultset = $client->select($query);
						$highlighting = $resultset->getHighlighting();

						// display the total number of documents found by solr
						$numResults = $resultset->getNumFound();
						
						if($numResults == 0) {
							echo '<div class="search-info">No results for: '.$q.'</div>';
							adv_search($q, $searchFields, $fields, $types);
						} else {
						
							$firstPageResult = $qp + 1;
							$lastPageResult = $numResults;
							if ($qp + $numRows < $numResults) {
								$lastPageResult = $qp + $numRows;
							}
							echo '<div class="search-info">';
							echo '<p>'.$numResults.' total results for &ldquo;'.$q.'&rdquo; (showing results '.$firstPageResult.'-'.$lastPageResult.')</p>';
							echo '</div>';
							
							echo '<div id="search-sort-holder">';
							echo '<h4 class="search-sort-heading">Sort options</h4>';
							echo '<div id="search-sort" class="collapse">';
							echo '<p>';
							foreach($sortOptions as $sortOption) {
								if($sortOption['qs'] == $qs && $sortOption['qso'] == $qso) {
									echo '<strong>'.$sortOption['name'].'</strong><br/>';
								} else {
									echo '<a href="search?q='.htmlentities($q).$typeArg.$fieldArg.'&qs='.$sortOption['qs'].'&qso='.$sortOption['qso'].'">'.$sortOption['name'].'</a><br/>';
								}
							}
							echo '</p>';
							echo '</div>';
							echo '</div>';
							
							adv_search($q, $searchFields, $fields, $types);

							// show documents using the resultset iterator
							foreach ($resultset as $document) {
								
								$fileParts = explode('.', $document->idno);
								$volNum = $fileParts[0];
								$issueNum = $fileParts[1];
								?>
								<div class="search-result">
									<div class="issue">
										<?php
										$quotes = array('"', "'");
										$qsafe = str_replace($quotes, "", $q);
										$qsafe = str_replace(" ", "+", $qsafe);
										
										$authors = (count($document->author) > 1) ? implode(', ', $document->author) : $document->author[0];
									
										if(preg_match('/html/', $document->format)) { // HTML
											if($document->type == 'illustration') { 
												$date = ($document->date != '') ? $document->date : seasonYearFromDate($document->fullDate);
										?>
												<span class="search-type"><?php echo typeNameShort($document->type); ?></span>
												<span class="search-issue-link"><a href="<?php echo $document->idno; ?>"><?php echo $document->title; ?></a></span>
												<span class="search-main-article"><span class="search-main-article-desc">Illustration for:</span> <a href="<?php echo $document->mainArticle; ?>"><?php echo $document->mainArticleTitle; ?></a></span>
												<span class="search-author"><?php echo $authors; ?></span>
												<span class="search-vol-iss-date"><span class="search-vol-iss">Volume <?php echo $document->volume; ?> &middot; Issue <?php echo $document->issue; ?></span> <span class="search-date">(<?php echo $date; ?>)</span></span>
											<?php												
											} else if(preg_match('/[0-9]{1,2}.[0-9]{1}[-a-z0-9]{0,3}.[a-zA-Z]{1,}/', $document->idno)){
												$date = ($document->date != '') ? $document->date : seasonYearFromDate($document->fullDate);
										?>
												<?php if (preg_match('/[0-9]{1,2}.[0-9]{1}[-a-z0-9]{0,3}.toc/', $document->idno)) { // toc ?>
												<span class="search-issue-link"><a href="<?php echo $document->idno; ?>">Contents</a></span>
												<span class="search-vol-iss-date"><span class="search-vol-iss">Volume <?php echo $volNum; ?> &middot; Issue <?php echo $issueNum; ?></span> <span class="search-date">(<?php echo $date; ?>)</span></span>
												<?php } else { // not toc ?>
												<span class="search-type"><?php echo typeNameShort($document->type); ?></span>
												<span class="search-issue-link"><a href="<?php echo $document->idno; ?>"><?php echo $document->title; ?></a></span>
												<span class="search-author"><?php echo $authors; ?></span>
												<span class="search-vol-iss-date"><span class="search-vol-iss">Volume <?php echo $volNum; ?> &middot; Issue <?php echo $issueNum; ?></span> <span class="search-date">(<?php echo $date; ?>)</span></span>
												<?php } ?>
											<?php
											} else if(preg_match('/bonus.[a-zA-Z]{1,}/', $document->idno)){
										?>
												<?php if (preg_match('/bonus.toc/', $document->idno)) { // toc ?>
												<span class="search-issue-link"><a href="<?php echo $document->idno; ?>">Contents</a></span>
												<span class="search-vol-iss-date"><span class="search-vol-iss">Bonus</span></span>
												<?php } else { // not toc 
												$date = ($document->date != '') ? $document->date : seasonYearFromDate($document->fullDate);
												?>
												<span class="search-type"><?php echo typeNameShort($document->type); ?></span>
												<span class="search-issue-link"><a href="<?php echo $document->idno; ?>"><?php echo $document->title; ?></a></span>
												<span class="search-author"><?php echo $authors; ?></span>
												<span class="search-vol-iss-date"><span class="search-vol-iss">Bonus</span> <span class="search-date">(<?php echo $date; ?>)</span></span>
												<?php } ?>
											<?php
											} else {
											?>
												<span class="search-issue-link"><a href="<?php echo $document->idno; ?>"><?php echo $document->title; ?></a></span>
											<?php
											}
											
										} else { // XML
											if(preg_match('/[0-9]{1,2}.[0-9]{1}[-a-z0-9]{0,3}.[a-zA-Z]{1,}/', $document->idno)){
										?>
												<?php if ($document->type != 'toc') { ?>
												<span class="search-type"><?php echo typeNameShort($document->type); ?></span>
												<?php } ?>
												<span class="search-issue-link"><a href="<?php echo $document->idno; ?>"><?php echo $document->title; ?></a></span>
												<span class="search-author"><?php echo $authors; ?></span>
												<span class="search-vol-iss-date"><span class="search-vol-iss">Volume <?php echo $volNum; ?> &middot; Issue <?php echo $issueNum; ?></span> <span class="search-date">(<?php echo $document->date; ?>)</span></span>
											<?php
											} else {
											?>
												<span class="search-issue-link"><a href="<?php echo $document->idno; ?>"><?php echo $document->title; ?></a></span>
											<?php
											}
										}
										?>
									</div>
									<?php
									/*
									// the documents are also iterable, to get all fields
									foreach ($document as $field => $value) {
										// this converts multivalue fields to a comma-separated string
										if (is_array($value)) {
											$value = implode(', ', $value);
										}

										if ($field == 'idno' or $field == 'fulltext' or $field == 'date' or $field == 'score'){
										}
										else {
											echo '<div>' . $field . ': ' . $value . '</div>';
										}
									}
									*/

									// highlighting results can be fetched by document id (the field defined as uniquekey in this schema)
									$highlightedDoc = $highlighting->getResult($document->idno);
		
									if($highlightedDoc){
										foreach($highlightedDoc as $field => $highlight) {
											echo '<div class="search-highlight">' . implode(' (...) ', $highlight) . '</div>';
											/*
											// snippet links
											// remove anything across a line break
											foreach($highlight as &$value) {
											$valueLines = preg_split('~[\r\n+]~', $value);
											$valueClean = $value;
											foreach($valueLines as $line) {
												if (stripos($line,$q) !== false) {
													$valueClean = $line;
												}
											}
											echo '<a style="color:black" href="' . $document->idno . '&qw=no&qor=f&q=' . str_replace(" ", "+", preg_replace("/ {2,}/", " ", str_replace('"', '%22', strip_tags($valueClean)))) . '">' . $valueClean . '</a>' . ' (...) ';
											}
											*/
										}
									}
			
									//echo '<div class="search-score"><strong>score:</strong> '.$document->score.'</div>';
			
										?>
								</div>
			
							<?php
							}
							
							if($numResults > $numRows) {
							?>
										<div class="search-paginator">
											<?php
											if ($qp > 0) {
												echo '<a href="search?q='.htmlentities($q).$typeArg.$fieldArg.'&qp='.$qpp.'&qs='.$qs.'&qso='.$qso.'">Previous</a> | ';
											}
						
											$numPages = ceil($numResults/$numRows);
											for ($i=0; $i<$numPages; $i++) {
												$page = $i+1;
												$qpi = $i * $numRows;
												if ($i != 0) {
													echo ' | ';
												}
												echo '<a href="search?q='.htmlentities($q).$typeArg.$fieldArg.'&qp='.$qpi.'&qs='.$qs.'&qso='.$qso.'">'.$page.'</a>';
											}
						
											if ($qp+$numRows < $numResults) {
												echo ' | <a href="search?q='.htmlentities($q).$typeArg.$fieldArg.'&qp='.$qpn.'&qs='.$qs.'&qso='.$qso.'">Next</a>';
											}
											?>
										</div>
					<?php
							} // if $numResults > $numRows
						} // if $numResults == 0
					
					} else {
					?>
						<p>Please search using the search form above or the advanced search form below.</p>
					<?php
						adv_search($q, $searchFields, $fields, $types);
					}
					?>
					</div> <!-- #main -->
					<?php
					include('include/footer.php');
					?>
				</div> <!-- #all-content-inner -->
			</div> <!-- #all-content -->
		</div> <!-- #outer -->
	</body>
</html>
