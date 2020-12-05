<!-- 
	William Blake Archive
	Blake/An Illustrated Quarterly: XSL Transform, single issue, full page
	William Shaw 1/19/2010
	Joe Ryan, Adam McCune, Michael Fox 2013-2017
-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:exist="http://exist.sourceforge.net/NS/exist" version="1.0">
    <xsl:output method="html" doctype-system="about:legacy-compat"/>
    <xsl:variable name="idno" select="TEI.2/teiHeader/idno" />
    <xsl:variable name="volIss"><xsl:value-of select="TEI.2/teiHeader/fileDesc/sourceDesc/biblFull/titleStmt/biblScope[@unit='volume']"/>.<xsl:value-of select="TEI.2/teiHeader/fileDesc/sourceDesc/biblFull/titleStmt/biblScope[@unit='issue']"/></xsl:variable>
	<xsl:variable name="lowercase" select="'abcdefghijklmnopqrstuvwxyzàáâãäåæçèéêëìíîïðñòóôõöøùúûüýþÿžšœ'" />
	<xsl:variable name="uppercase" select="'ABCDEFGHIJKLMNOPQRSTUVWXYZÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖØÙÚÛÜÝÞŸŽŠŒ'" />
    <xsl:template match="@*|node()">
        <xsl:copy>
            <xsl:apply-templates select="@*|node()"/>
        </xsl:copy>
    </xsl:template>
    <xsl:template match="TEI.2">
        <xsl:apply-templates/>
    </xsl:template>
    <xsl:template match="tei.2">
        <xsl:apply-templates/>
    </xsl:template>
    <xsl:template match="/">
                    <div id="main" class="main-doc">
                        <div id="core">
							<xsl:call-template name="sectionAndMain" />
							<xsl:apply-templates/>
                        </div>
                    </div>
					<xsl:call-template name="teiHeaderDisplay" />
    </xsl:template>
	<xsl:template name="sectionAndMain">
					<xsl:choose>
						<xsl:when test="//teiHeader/fileDesc/titleStmt/title/@type = 'context'" />
						<xsl:otherwise>
							<xsl:choose>
								<xsl:when test="//text//title[@type='section'] and //text//title[@type='main']" />
								<xsl:otherwise>
									<p>
										<xsl:attribute name="class">section-and-main</xsl:attribute>
										<xsl:choose>
											<xsl:when test="//text//title[@type='section'] and translate(normalize-space(//text//title[@type='section'][1]), $uppercase, $lowercase) = //teiHeader/fileDesc/titleStmt/title/@type" />
											<xsl:when test="//text//title[@type='section'] and //teiHeader/fileDesc/titleStmt/title/@type = 'news' and contains(translate(//text//title[@type='section'][1], $uppercase, $lowercase), 'news')" />
											<xsl:when test="//text//title[@type='section'] and //teiHeader/fileDesc/titleStmt/title/@type = 'query' and translate(normalize-space(//text//title[@type='section'][1]), $uppercase, $lowercase) = 'queries'" />
											<xsl:when test="//text//title[@type='section'] and //teiHeader/fileDesc/titleStmt/title/@type = 'minute' and contains(translate(//text//title[@type='section'][1], $uppercase, $lowercase), 'minute particular')" />
											<xsl:when test="//text//title[@type='section'] and //teiHeader/fileDesc/titleStmt/title/@type = 'correction' and contains(translate(//text//title[@type='section'][1], $uppercase, $lowercase), 'corrigend')" />
											<xsl:when test="//text//title[@type='section'] and //teiHeader/fileDesc/titleStmt/title/@type = 'correction' and contains(translate(//text//title[@type='section'][1], $uppercase, $lowercase), 'errata')" />
											<xsl:when test="//text//title[@type='section'] and //teiHeader/fileDesc/titleStmt/title/@type = 'note' and contains(translate(//text//title[@type='section'][1], $uppercase, $lowercase), 'note')" />
											<xsl:otherwise>
												<xsl:if test="//teiHeader/fileDesc/titleStmt/title/@type and //teiHeader/fileDesc/titleStmt/title/@type != 'toc'">
													<span>
														<xsl:attribute name="class">section-tag</xsl:attribute>
														<xsl:choose>
															<xsl:when test="//teiHeader/fileDesc/titleStmt/title/@type = 'minute'">
																minute particular
															</xsl:when>
															<xsl:otherwise>
																<xsl:value-of select="//teiHeader/fileDesc/titleStmt/title/@type"/>
															</xsl:otherwise>
														</xsl:choose>
													</span>
												</xsl:if>
											</xsl:otherwise>
										</xsl:choose>
										<xsl:choose>
											<xsl:when test="//text//title[@type='main']" />
											<xsl:otherwise>
												<xsl:if test="//teiHeader/fileDesc/titleStmt/title and //teiHeader/fileDesc/titleStmt/title/@type != 'toc'">
													<span>
														<xsl:attribute name="class">main-tag</xsl:attribute>
														<xsl:choose>
															<xsl:when test="//text//title[@type='section'] and translate(normalize-space(//teiHeader/fileDesc/titleStmt/title), $uppercase, $lowercase) = translate(normalize-space(//text//title[@type='section']), $uppercase, $lowercase)"/>
															<xsl:otherwise>
																[<xsl:value-of select="//teiHeader/fileDesc/titleStmt/title"/>]
															</xsl:otherwise>
														</xsl:choose>
													</span>
												</xsl:if>
											</xsl:otherwise>
										</xsl:choose>
									</p>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:otherwise>
					</xsl:choose>
    </xsl:template>
	<xsl:template name="teiHeaderDisplay">
                    <xsl:if test="//teiHeader and $idno != 'Contact'">
                        <div id="teiHeader">
							<xsl:choose>
								<xsl:when test="$idno = 'About' or $idno = 'Emend'">
									<h3>Digital Edition</h3>
								</xsl:when>
								<xsl:otherwise>
									<h3>Print Edition</h3>
								</xsl:otherwise>
							</xsl:choose>
                            <xsl:if test="//teiHeader/fileDesc/sourceDesc/biblFull/publicationStmt/publisher">
                                <ul id="publication">
                                    <li id="publication-heading">Publisher</li>
                                    <li id="publisher">
                                        <xsl:value-of select="//teiHeader/fileDesc/sourceDesc/biblFull/publicationStmt/publisher"/>
                                    </li>
                                    <li id="pubPlace">
                                        <xsl:value-of select="//teiHeader/fileDesc/sourceDesc/biblFull/publicationStmt/pubPlace"/>
                                    </li>
                                </ul>
                            </xsl:if>
                            <ul id="principal">
                                <xsl:for-each select="//respStmt/principal/*">
                                    <li>
                                        <xsl:attribute name="class">
                                            <xsl:value-of select="name(.)"/>
                                        </xsl:attribute>
                                        <xsl:value-of select="."/>
                                    </li>
                                </xsl:for-each>
                            </ul>
                            <ul id="respStmt">
                                <xsl:for-each select="//respStmt/*">
                                    <xsl:choose>
                                        <xsl:when test="name(.) = 'principal'"/>
                                        <xsl:when test="name(.) = 'sponsor'"/>
                                        <xsl:when test="name(.) = 'funder'"/>
                                        <xsl:otherwise>
                                            <li>
                                                <xsl:attribute name="class">
                                                    <xsl:value-of select="name(.)"/>
                                                </xsl:attribute>
                                                <xsl:value-of select="."/>
                                            </li>
                                        </xsl:otherwise>
                                    </xsl:choose>
                                </xsl:for-each>
                            </ul>
                            <xsl:if test="//respStmt/sponsor">
                                <ul id="sponsors">
                                    <li id="sponsors-heading">Sponsors</li>
                                    <xsl:for-each select="//respStmt/sponsor">
                                        <li>
                                            <xsl:attribute name="class">sponsor</xsl:attribute>
                                            <xsl:value-of select="."/>
                                        </li>
                                    </xsl:for-each>
                                </ul>
                            </xsl:if>
                            <xsl:if test="//respStmt/funder">
                                <ul id="funders">
                                    <li id="funders-heading">Funders</li>
                                    <xsl:for-each select="//respStmt/funder">
                                        <li>
                                            <xsl:attribute name="class">funder</xsl:attribute>
                                            <xsl:value-of select="."/>
                                        </li>
                                    </xsl:for-each>
                                </ul>
                            </xsl:if>
                            <xsl:if test="//teiHeader/userestrict">
                                <ul id="userrestrict">
                                    <li id="userestrict-heading">Use Restrictions</li>
                                    <xsl:for-each select="//teiHeader/userestrict">
                                        <li>
                                            <xsl:attribute name="class">userestrict</xsl:attribute>
                                            <xsl:value-of select="."/>
                                        </li>
                                    </xsl:for-each>
                                </ul>
                            </xsl:if>
                        </div>
                    </xsl:if>
	</xsl:template>
    <xsl:template match="teiHeader"/>
    <xsl:template match="date">
        <xsl:apply-templates/>
    </xsl:template>
    <xsl:template match="edition">
        <xsl:apply-templates/>
    </xsl:template>
    <xsl:template match="front">
        <xsl:apply-templates/>
    </xsl:template>
    <xsl:template match="text">
        <xsl:apply-templates/>
    </xsl:template>
    <xsl:template match="div1">
        <xsl:choose>
            <xsl:when test="@id='cover'"/>
            <xsl:when test="@id != ''">
				<div>
					<xsl:attribute name="id">
                        <xsl:value-of select="@id"/>
                    </xsl:attribute>
					<xsl:apply-templates/>
				</div>
			</xsl:when>
            <xsl:otherwise>
                <xsl:apply-templates/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    <xsl:template match="div2">
        <xsl:apply-templates/>
    </xsl:template>
    <xsl:template match="div3">
        <xsl:choose>
            <xsl:when test="@type != ''">
				<div>
					<xsl:attribute name="class">
                        <xsl:value-of select="@type"/>
                    </xsl:attribute>
					<xsl:apply-templates/>
				</div>
			</xsl:when>
            <xsl:otherwise>
                <xsl:apply-templates/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    <xsl:template match="pb">
    	<xsl:choose>
    		<xsl:when test="@rend='hidden'"/>
    		<xsl:otherwise>
				<span class="page-attributes">
					<span class="pb-holder">
						<a class="pb">
							<xsl:attribute name="id">
								<xsl:text>p</xsl:text>
								<xsl:value-of select="@n"/>
							</xsl:attribute>
							<xsl:attribute name="title">Page <xsl:value-of select="@n"/>
							</xsl:attribute>
							<xsl:attribute name="href">#p<xsl:value-of select="@n"/>
							</xsl:attribute>
							<span class="pagelabel">begin page </span>
							<xsl:value-of select="@n"/>
						</a>
					</span>
					<span class="page-attribute-separator"> | </span>
					<span class="top-link-holder">
						<a class="top-link">
							<xsl:attribute name="href">#issue-heading</xsl:attribute>
							<xsl:attribute name="title">Return to top</xsl:attribute>
							&#8593; <span class="top-link-label">back to top</span>
						</a>
					</span>
					<span class="clear"/>
				</span>
        	</xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    <xsl:template match="head">
        <xsl:choose>
            <xsl:when test="following-sibling::table[@id='contents'] and child::title = 'CONTENTS' and ancestor::text//div1[@id='cover']/figure"/>
			<xsl:when test="parent::figure">
				<span class="caption-head">
					<xsl:apply-templates/>
				</span> &#160; 
			</xsl:when>
            <xsl:when test="ancestor::table and @type = 'table-header'">
            	<th>
            		<xsl:if test="@rowspan">
                		<xsl:attribute name="rowspan">
                    		<xsl:value-of select="@rowspan"/>
                		</xsl:attribute>
                	</xsl:if>
                	<xsl:if test="@colspan">
                		<xsl:attribute name="colspan">
                			<xsl:value-of select="@colspan"/>
                		</xsl:attribute>
                	</xsl:if>
            		<xsl:apply-templates/>
            	</th>
            </xsl:when>
			<xsl:otherwise>
                <xsl:apply-templates/>
            </xsl:otherwise>
        </xsl:choose>        
    </xsl:template>
    <xsl:template match="title">
        <xsl:choose>
            <xsl:when test="ancestor::biblFull"/>
            <xsl:when test="ancestor::fileDesc"/>
            <xsl:when test="@type='section'">
                <h2>
                    <xsl:attribute name="class">section</xsl:attribute>
                    <xsl:apply-templates/>
                </h2>
            </xsl:when>
            <xsl:when test="@type='section-subtitle'">
                <h2>
                    <xsl:attribute name="class">section-subtitle</xsl:attribute>
                    <xsl:apply-templates/>
                </h2>
            </xsl:when>
            <xsl:when test="ancestor::div3[@type='in-memoriam']">
                    <xsl:apply-templates/>
            </xsl:when>
            <xsl:when test="@type='main'">
				<xsl:choose>
					<xsl:when test="//teiHeader/fileDesc/titleStmt/title/@type = 'review'">
						<xsl:choose>
							<xsl:when test="@rend = 'main'">
								<h2>
									<xsl:attribute name="class">main</xsl:attribute>
									<xsl:apply-templates/>
								</h2>
							</xsl:when>
							<xsl:otherwise>
								<h3>
									<xsl:attribute name="class">review-main</xsl:attribute>
									<xsl:apply-templates/>
								</h3>
							</xsl:otherwise>
						</xsl:choose>
					</xsl:when>
					<xsl:otherwise>
						<h2>
							<xsl:attribute name="class">main</xsl:attribute>
							<xsl:apply-templates/>
						</h2>
					</xsl:otherwise>
				</xsl:choose>
            </xsl:when>
            <xsl:when test="ancestor::table[@id='contents']">
                <h2>
                    <xsl:apply-templates/>
                </h2>
            </xsl:when>
            <xsl:when test="ancestor::table[@id='article-contents']">
                <h2>
                    <xsl:apply-templates/>
                </h2>
            </xsl:when>
            <xsl:when test="@rend='h4'">
                <h4>
                    <xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute>
                    <xsl:apply-templates/>
                </h4>
            </xsl:when>
            <xsl:otherwise>
                <h3>
                    <xsl:apply-templates/>
                </h3>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    <xsl:template match="ref">
        <xsl:choose>
            <xsl:when test="@type='ext'">
                <a class="ext-ref">
                    <xsl:attribute name="href">
                        <xsl:value-of select="@target"/>
                    </xsl:attribute>
                    <xsl:apply-templates/>
                </a>
            </xsl:when>
            <xsl:when test="@type='issue'">
                <a class="issue-ref">
                    <xsl:attribute name="href">wdoc.php?file=<xsl:value-of select="@issue"/>
                        <xsl:if test="@target">#<xsl:value-of select="@target"/>
                        </xsl:if>
                    </xsl:attribute>
                    <xsl:apply-templates/>
                </a>
            </xsl:when>
            <xsl:when test="@type='html-issue'">
                <a class="issue-ref">
                    <xsl:attribute name="href"><xsl:value-of select="@issue"/>
                        <xsl:if test="@target">#<xsl:value-of select="@target"/>
                        </xsl:if>
                    </xsl:attribute>
                    <xsl:apply-templates/>
                </a>
            </xsl:when>
            <xsl:when test="@type='article'">
                <a class="issue-ref">
                    <xsl:attribute name="href"><xsl:value-of select="@issue"/>
                        <xsl:if test="@target">#<xsl:value-of select="@target"/>
                        </xsl:if>
                    </xsl:attribute>
                    <xsl:apply-templates/>
                </a>
            </xsl:when>
            <xsl:when test="@type='email'">
                <a class="email">
                    <xsl:attribute name="href">mailto:<xsl:if test="@target"><xsl:value-of select="@target"/></xsl:if>
                    </xsl:attribute>
                    <xsl:apply-templates/>
                </a>
            </xsl:when>
            <xsl:otherwise>
                <a class="reference">
                    <xsl:attribute name="href">#<xsl:value-of select="@target"/></xsl:attribute>
                    <xsl:choose>
                    	<xsl:when test="@n">
		                    <xsl:attribute name="id">ref-<xsl:value-of select="@target"/>-<xsl:value-of select="@n"/></xsl:attribute>
		                </xsl:when>
		                <xsl:otherwise>
		                	<xsl:attribute name="id">ref-<xsl:value-of select="@target"/></xsl:attribute>
		                </xsl:otherwise>
		            </xsl:choose>
                    <sup><xsl:apply-templates/></sup>
                </a>
                <xsl:choose>
                    <xsl:when test="@n"/>
                    <xsl:when test="ancestor::table"/>
                    <xsl:when test="ancestor::q"/>
                    <xsl:when test="ancestor::figure"/>
                    <xsl:otherwise>
                        <xsl:call-template name="noteFromRef"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    <xsl:template name="noteFromRef">
        <xsl:for-each select="ancestor::text//note[@id=current()/@target]">
            <span class="fn">
                <xsl:attribute name="id"><xsl:value-of select="@id"/></xsl:attribute>
                <a>
                	<xsl:attribute name="href">#ref-<xsl:value-of select="@id"/></xsl:attribute>
                	<xsl:text>&#8612; </xsl:text>
                </a>
                <xsl:if test="ancestor::text//ref[@target=current()/@id]/@n">
            		<xsl:call-template name="linksToRefs"/>
            	</xsl:if>
                <xsl:apply-templates/>
            </span>
        </xsl:for-each>
        <xsl:text> </xsl:text>
    </xsl:template>
    <xsl:template name="linksToRefs">
        <xsl:for-each select="ancestor::text//ref[@target=current()/@id]">
        	<xsl:choose>
        		<xsl:when test="@n">
                	<a>
                		<xsl:attribute name="href">#ref-<xsl:value-of select="@target"/>-<xsl:value-of select="@n"/></xsl:attribute>
                		<sup><xsl:value-of select="@n"/></sup>
               		</a>
                	<sup> | </sup>
                </xsl:when>
                <xsl:otherwise>
                    <a>
                		<xsl:attribute name="href">#ref-<xsl:value-of select="@target"/></xsl:attribute>
                		<sup><xsl:value-of select="."/></sup>
               		</a>
                	<sup> | </sup>
                </xsl:otherwise>
            </xsl:choose>
        </xsl:for-each>
    </xsl:template>
    <xsl:template name="notesFromElement">
        <xsl:if test="descendant::ref">
            <xsl:for-each select="descendant::ref">
            	<xsl:choose>
            		<xsl:when test="@n" />
            		<xsl:otherwise>
		                <xsl:call-template name="noteFromRef"/>
		            </xsl:otherwise>
		        </xsl:choose>
            </xsl:for-each>
        </xsl:if>
    </xsl:template>
    <xsl:template match="q">
        <xsl:choose>
            <xsl:when test="@rend='qm'">
                “<xsl:apply-templates/>”
            </xsl:when>
            <xsl:when test="ancestor::note">
                <span class="note-quote"><xsl:apply-templates/></span>
            </xsl:when>
            <xsl:otherwise>
                <xsl:call-template name="notesFromElement"/>
                <blockquote>
                    <xsl:apply-templates/>
                </blockquote>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    <xsl:template match="note">
        <xsl:if test="./@type = 'unreferenced'">
            <div class="fnbrk">
                <span/>
            </div>
            <div class="unreferenced-note">
                <xsl:choose>
                    <xsl:when test="./p">
                        <xsl:apply-templates/>
                    </xsl:when>
                    <xsl:otherwise>
                        <p>
                            <xsl:apply-templates/>
                        </p>
                    </xsl:otherwise>
                </xsl:choose>
            </div>
            <div class="fnbrk">
                <span/>
            </div>
        </xsl:if>
    </xsl:template>
    <xsl:template match="lb">
        <xsl:choose>
            <xsl:when test="@rend='-'">-<br/></xsl:when>
			<xsl:otherwise><br/></xsl:otherwise>
		</xsl:choose>
    </xsl:template>
    <xsl:template match="p">
        <xsl:choose>
            <xsl:when test="ancestor::note">
                <span class="note-paragraph">
                    <xsl:apply-templates/>
                </span>                
            </xsl:when>
            <xsl:otherwise>
                <p>
                    <xsl:apply-templates/>
                </p>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    <xsl:template match="rtl">
        <span dir="rtl">
            <xsl:apply-templates/>
        </span>‎</xsl:template>
    <xsl:template match="corr[@type='emend']">
        <xsl:call-template name="emend"/>
    </xsl:template>
    <xsl:template match="gap[@type='emend']">
        <xsl:call-template name="emend"/>
    </xsl:template>
    <xsl:template match="supplied[@type='emend']">
        <xsl:call-template name="emend"/>
    </xsl:template>
    <xsl:template match="supplied[@type='spacer']"/>
    <xsl:template name="emend">
        <span class="emend">
            <xsl:apply-templates/>
        </span><xsl:choose>
            <xsl:when test="@rend = 'plain'"/>
            <xsl:otherwise><a title="Emendation" class="emend-link" href="Emend#{$volIss}"><sup>[e]</sup></a></xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    <xsl:template match="byline">
        <p class="byline">
            <xsl:apply-templates/>
        </p>
    </xsl:template>
    <xsl:template match="byline/docAuthor">
        <xsl:text> </xsl:text>
        <xsl:apply-templates/>
    </xsl:template>
    <xsl:template match="byline/docAuthor/name">
        <xsl:apply-templates/>
    </xsl:template>
    <xsl:template match="phi">
        <span class="phi">
            <xsl:apply-templates/>
        </span>
    </xsl:template>
    <xsl:template match="inline-image">
        <img>
            <xsl:attribute name="class">inline-image</xsl:attribute>
            <xsl:attribute name="src">img/inline/<xsl:value-of select="./@n"/>.png</xsl:attribute>
            <xsl:attribute name="alt">
                <xsl:value-of select="./figTranscr"/>
            </xsl:attribute>
        </img>
    </xsl:template>
    <xsl:template match="figure">
	  <xsl:choose>
	   <xsl:when test="ancestor::table">
        <img>
            <xsl:attribute name="class">table-image</xsl:attribute>
            <xsl:attribute name="src">img/illustrations/<xsl:value-of select="./@n"/>.png</xsl:attribute>
			<xsl:if test="./@width">
			 <xsl:attribute name="width"><xsl:value-of select="./@width"/></xsl:attribute>
			</xsl:if>
			<xsl:if test="./@height">
			 <xsl:attribute name="height"><xsl:value-of select="./@height"/></xsl:attribute>
			</xsl:if>
            <xsl:attribute name="alt">
                <xsl:value-of select="./figTranscr"/>
            </xsl:attribute>
        </img>
	   </xsl:when>
	   <xsl:otherwise>
		<xsl:if test="./@n or ./head or ./figDesc">
			<div class="image">
				<div class="image-inner">
					<xsl:if test="./@n">
					<xsl:choose>
						<xsl:when test="@n = ''"/>
						<xsl:when test="@rend = 'db'">
							<xsl:variable name="src">http://www.blakearchive.org/images/<xsl:value-of select="./@n"/>.300.jpg</xsl:variable>
							<a>
								<xsl:attribute name="class">image-expand</xsl:attribute>
								<xsl:attribute name="href">
									<xsl:value-of select="$src"/>
								</xsl:attribute>
								<img>
									<xsl:attribute name="src">
										<xsl:value-of select="$src"/>
									</xsl:attribute>
									<xsl:attribute name="alt">
										<xsl:value-of select="./figTranscr"/>
									</xsl:attribute>
									<xsl:if test="./@width">
									 <xsl:attribute name="width"><xsl:value-of select="./@width"/></xsl:attribute>
									</xsl:if>
									<xsl:if test="./@height">
									 <xsl:attribute name="height"><xsl:value-of select="./@height"/></xsl:attribute>
									</xsl:if>
								</img>
							</a>
						</xsl:when>
						<xsl:otherwise>
							<xsl:choose>
								<xsl:when test="contains(@n, 'bqscan')">
									<xsl:variable name="src">img/illustrations/<xsl:value-of select="./@n"/>.png</xsl:variable>
									<a>
										<xsl:attribute name="class">image-expand</xsl:attribute>
										<xsl:attribute name="href">
											<xsl:value-of select="$src"/>
										</xsl:attribute>
										<img>
											<xsl:attribute name="src">
												<xsl:value-of select="$src"/>
											</xsl:attribute>
											<xsl:attribute name="alt">
												<xsl:value-of select="./figTranscr"/>
											</xsl:attribute>
											<xsl:if test="./@width">
											 <xsl:attribute name="width"><xsl:value-of select="./@width"/></xsl:attribute>
											</xsl:if>
											<xsl:if test="./@height">
											 <xsl:attribute name="height"><xsl:value-of select="./@height"/></xsl:attribute>
											</xsl:if>
										</img>
									</a>
								</xsl:when>
								<xsl:when test="contains(@n, '.100') or contains(@n, '.bonus')">
									<xsl:variable name="src">img/illustrations/<xsl:value-of select="./@n"/>.jpg</xsl:variable>
									<a>
										<xsl:attribute name="class">image-expand</xsl:attribute>
										<xsl:attribute name="href">
											<xsl:value-of select="$src"/>
										</xsl:attribute>
										<img>
											<xsl:attribute name="src">
												<xsl:value-of select="$src"/>
											</xsl:attribute>
											<xsl:attribute name="alt">
												<xsl:value-of select="./figTranscr"/>
											</xsl:attribute>
											<xsl:if test="./@width">
											 <xsl:attribute name="width"><xsl:value-of select="./@width"/></xsl:attribute>
											</xsl:if>
											<xsl:if test="./@height">
											 <xsl:attribute name="height"><xsl:value-of select="./@height"/></xsl:attribute>
											</xsl:if>
										</img>
									</a>
								</xsl:when>
								<xsl:otherwise>
									<xsl:variable name="src">img/illustrations/<xsl:value-of select="./@n"/>.300.jpg</xsl:variable>
									<a>
										<xsl:attribute name="class">image-expand</xsl:attribute>
										<xsl:attribute name="href">
											<xsl:value-of select="$src"/>
										</xsl:attribute>
										<img>
											<xsl:attribute name="src">
												<xsl:value-of select="$src"/>
											</xsl:attribute>
											<xsl:attribute name="alt">
												<xsl:value-of select="./figTranscr"/>
											</xsl:attribute>
											<xsl:if test="./@width">
											 <xsl:attribute name="width"><xsl:value-of select="./@width"/></xsl:attribute>
											</xsl:if>
											<xsl:if test="./@height">
											 <xsl:attribute name="height"><xsl:value-of select="./@height"/></xsl:attribute>
											</xsl:if>
										</img>
									</a>
								</xsl:otherwise>
							</xsl:choose>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:if>
				<div class="caption">
					<!--<div style="color: #FF0000;"><xsl:apply-templates select="./figTranscr"/></div>-->
					<xsl:apply-templates/>
					<xsl:if test="@id and @work-copy">
						<!--and @rend = 'db'-->
						<br/>
						<a>
							<xsl:attribute name="href">http://blakearchive.org/copy/<xsl:value-of select="@work-copy"/>?descId=<xsl:value-of select="@id"/></xsl:attribute>
							<xsl:attribute name="target">_blank</xsl:attribute>
							[View this object in the William Blake Archive]
						</a>
					</xsl:if>
				</div>
			</div>
			</div>
			<xsl:call-template name="notesFromElement"/>
		</xsl:if>
	   </xsl:otherwise>
	  </xsl:choose>
    </xsl:template>
    <xsl:template match="figDesc">
        <xsl:apply-templates/>
    </xsl:template>
    <xsl:template match="figTranscr"/>
    <xsl:template match="table">
        <xsl:choose>
            <xsl:when test="@type='inline'">
                <table>
                    <xsl:attribute name="class">inline-table</xsl:attribute>
                    <xsl:apply-templates/>
                </table>
            </xsl:when>
            <xsl:when test="@id='contents'">
                <xsl:choose>
                    <xsl:when test="ancestor::text//div1[@id='cover']/figure">
                        <div id="cover-contents">
                            <div id="cover-holder">
                                <xsl:apply-templates select="ancestor::text//div1[@id='cover']/figure"/>
                            </div>
                            <div id="contents-holder">
                                <xsl:if test="preceding-sibling::head/title and (preceding-sibling::head/title='CONTENTS' or preceding-sibling::head/title='Contents')">
                                    <h3><xsl:apply-templates select="preceding-sibling::head/title" /></h3>
                                </xsl:if>
                                <p>
                                    <table border="0" width="90%">
                                        <xsl:attribute name="id">contents</xsl:attribute>
                                        <xsl:apply-templates/>
                                    </table>
                                </p>
                            </div>
                            <div class="clear"></div>
                        </div>
                        <xsl:call-template name="notesFromElement"/>
                    </xsl:when>
                    <xsl:otherwise>
                        <p>
                            <table border="0" width="90%">
                                <xsl:attribute name="id">contents</xsl:attribute>
                                <xsl:apply-templates/>
                            </table>
                        </p>
                        <xsl:call-template name="notesFromElement"/>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:when test="ancestor::note">
                <span>
                    <xsl:attribute name="class">note-table</xsl:attribute>
                    <xsl:apply-templates/>
                </span>
            </xsl:when>
            <xsl:otherwise>
                <p>
                    <xsl:call-template name="notesFromElement"/>
                    <table border="0" width="90%">
                    	<xsl:if test="@rend='unpadded'">
                    		<xsl:attribute name="class">unpadded</xsl:attribute>
                    	</xsl:if>
                        <xsl:apply-templates/>
                    </table>
                </p>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    <xsl:template match="row">
    	<xsl:choose>
            <xsl:when test="ancestor::note">
                <span>
                    <xsl:attribute name="class">note-tr</xsl:attribute>
                    <xsl:apply-templates/>
                </span>
            </xsl:when>
            <xsl:otherwise>
				<tr>
					<xsl:if test="@rend='shaded'">
						<xsl:attribute name="class">shaded</xsl:attribute>
					</xsl:if>
					<xsl:apply-templates/>
				</tr>
			</xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    <xsl:template match="table[@id='contents' or @id='article-contents']//cell">
        <xsl:choose>
            <xsl:when test="string(number(translate(., '-', ''))) != 'NaN'"/>
            <xsl:otherwise>
                <td>
					<xsl:if test="@rowspan">
						<xsl:attribute name="rowspan">
							<xsl:value-of select="@rowspan"/>
						</xsl:attribute>
					</xsl:if>
					<xsl:if test="@colspan">
						<xsl:attribute name="colspan">
							<xsl:value-of select="@colspan"/>
						</xsl:attribute>
					</xsl:if>
                    <xsl:choose>
                        <xsl:when test="ancestor::table[@id='article-contents'] and string(number(following-sibling::cell[1])) != 'NaN'">
                            <a>
                                <xsl:attribute name="href">#p<xsl:value-of select="string(number(following-sibling::cell[1]))"/>
                                </xsl:attribute>
                                <xsl:apply-templates/>
                            </a>
                        </xsl:when>
                        <xsl:otherwise>
                            <xsl:apply-templates/>
                        </xsl:otherwise>
                    </xsl:choose>
                </td>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    <xsl:template match="cell">
    	<xsl:choose>
            <xsl:when test="ancestor::note">
                <span>
                    <xsl:attribute name="class">note-td</xsl:attribute>
                    <xsl:apply-templates/>
                </span>
            </xsl:when>
            <xsl:otherwise>
				<td>
					<xsl:if test="@rowspan">
						<xsl:attribute name="rowspan">
							<xsl:value-of select="@rowspan"/>
						</xsl:attribute>
					</xsl:if>
					<xsl:if test="@colspan">
						<xsl:attribute name="colspan">
							<xsl:value-of select="@colspan"/>
						</xsl:attribute>
					</xsl:if>
					<xsl:apply-templates/>
				</td>
			</xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    <xsl:template match="hi">
        <xsl:choose>
            <xsl:when test="@rend = 'b'">
				<xsl:choose>
					<xsl:when test="ancestor::title[@type='section'] or ancestor::title[@type='section-subtitle'] or ancestor::title[ancestor::table[@id='contents']]">
						<xsl:apply-templates/>
					</xsl:when>
					<xsl:otherwise>
						<strong>
							<xsl:apply-templates/>
						</strong>
					</xsl:otherwise>
				</xsl:choose>
            </xsl:when>
            <xsl:when test="@rend = 'i'">
				<xsl:choose>
					<xsl:when test="ancestor::title[@type='section'] or ancestor::title[@type='section-subtitle'] or ancestor::title[ancestor::table[@id='contents']]">
						<xsl:apply-templates/>
					</xsl:when>
					<xsl:otherwise>
						<em>
							<xsl:apply-templates/>
						</em>
					</xsl:otherwise>
				</xsl:choose>
            </xsl:when>
            <xsl:when test="@rend = 'u'">
				<xsl:choose>
					<xsl:when test="ancestor::title[@type='section'] or ancestor::title[@type='section-subtitle'] or ancestor::title[ancestor::table[@id='contents']]">
						<xsl:apply-templates/>
					</xsl:when>
					<xsl:otherwise>
						<span class="underline">
							<xsl:apply-templates/>
						</span>
					</xsl:otherwise>
				</xsl:choose>
            </xsl:when>
            <xsl:when test="@rend = 'uu'">
				<span class="double-underline">
					<xsl:apply-templates/>
				</span>
            </xsl:when>
            <xsl:when test="@rend = 'uthick'">
				<span class="thick-underline">
					<xsl:apply-templates/>
				</span>
            </xsl:when>
            <xsl:when test="@rend = 'overline'">
				<span class="overline">
					<xsl:apply-templates/>
				</span>
            </xsl:when>
            <xsl:when test="@rend = 's'">
                <sup>
                    <xsl:apply-templates/>
                </sup>
            </xsl:when>
            <xsl:when test="@rend = 'sub'">
                <sub>
                    <xsl:apply-templates/>
                </sub>
            </xsl:when>
            <xsl:when test="@rend = 'smallcaps'">
                <span class="small-caps">
                    <xsl:apply-templates/>
                </span>
            </xsl:when>
            <xsl:when test="@rend = 'strike'">
                <strike>
                    <xsl:apply-templates/>
                </strike>
            </xsl:when>
            <xsl:when test="@rend = 'gray'">
				<span class="gray">
					<xsl:apply-templates/>
				</span>
            </xsl:when>
            <xsl:when test="@rend = 'gothic'">
				<span class="gothic">
					<xsl:apply-templates/>
				</span>
            </xsl:when>
            <xsl:otherwise>
                <xsl:apply-templates/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    <xsl:template match="multiheight">
        <span>
            <xsl:attribute name="style">font-size: <xsl:value-of select="@multiple"/>em;</xsl:attribute>
            <xsl:apply-templates/>
        </span>
    </xsl:template>
    <xsl:template match="metSym[@value='+']">́</xsl:template>
    <xsl:template match="metSym[@value='-']">̆</xsl:template>
    <xsl:template match="list[@type='ordered']">
    	<xsl:choose>
            <xsl:when test="ancestor::note">
                <xsl:apply-templates/>        
            </xsl:when>
            <xsl:otherwise>
				<ol class="plain-ol">
					<xsl:apply-templates/>
				</ol>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    <xsl:template match="list[@type='simple']">
    	<xsl:choose>
            <xsl:when test="ancestor::note">
                <xsl:apply-templates/>        
            </xsl:when>
            <xsl:otherwise>
				<ul class="plain-ul">
    				<xsl:apply-templates/>
    			</ul>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    <xsl:template match="list/item">
    	<xsl:choose>
            <xsl:when test="ancestor::note">
                <span class="note-list-item">
                    <xsl:apply-templates/>
                </span>                
            </xsl:when>
            <xsl:otherwise>
		    	<li><xsl:apply-templates/></li>
            </xsl:otherwise>
        </xsl:choose>		    
    </xsl:template>
</xsl:stylesheet>