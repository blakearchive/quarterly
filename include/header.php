                    <div id="header">
                        <div id="header-inner">
                        	<div id="logo-search">
                        		<div id="logo">
                            		<a href="<?php echo $root; ?>"><img src="<?php echo $root; ?>img/general/BLAKE.gif" alt="Blake/An Illustrated Quarterly" width="425" height="114"/></a>
                            	</div>
								<div id="archive-label">
									<span><em>issue archive</em></span>
								</div>
                            	<div id="search">
                            		<div id="search-form-holder">
                            			<form class="search-form" action="search" method="get"><input name="q" type="search" value="<?php if (strpos($_SERVER['SCRIPT_FILENAME'], 'search') !== FALSE) { echo htmlentities($q); } ?>" /><button type="submit">Search</button></form>
                            			<div class="clear"></div>
                            		</div>
                            	</div>
                            	<div class="clear"></div>
                            </div>
                            <div id="global_nav">
                                <a href="<?php echo $root; ?>">Issue Archive</a> | <a href="articles">Index</a> | <a href="bonus.toc">Bonus Content</a> | <a href="Emend">Emendations</a> | <a href="About">About</a> | <a href="Contact">Contact</a> | <a href="http://blake.lib.rochester.edu/blakeojs/index.php/blake">Current Issues</a> | <a href="http://www.blakearchive.org/">William Blake Archive</a>
                                <div class="clear"></div>
                            </div>
                        </div>
                    </div>
					<div id="minMaxVI" style="display:none;"><?php 
					if($_SERVER['SERVER_NAME'] != 'localhost' && $_SERVER['SERVER_NAME'] != 'bq-dev.blakearchive.org') {
						echo '<span id="minVol">'.$minVol.'</span><span id="minIss">'.$minIss.'</span><span id="maxVol">'.$maxVol.'</span><span id="maxIss">'.$maxIss.'</span>'; 
					}
					?></div>
