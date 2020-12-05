<!-- 
	William Blake Archive
	Blake/An Illustrated Quarterly: XSL Transform, single issue, full page
	William Shaw 1/19/2010
	Joe Ryan, Adam McCune, Michael Fox 2013-2017
-->
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" xmlns:exist="http://exist.sourceforge.net/NS/exist" version="1.0">
    <xsl:output method="html" doctype-system="about:legacy-compat"/>
    <xsl:variable name="idno"><xsl:value-of select="//div[@id='idno']"/></xsl:variable>
    <xsl:template match="div[@id='idno']"/>
    <xsl:template match="@*|node()">
        <xsl:copy>
            <xsl:apply-templates select="@*|node()"/>
        </xsl:copy>
    </xsl:template>
    <xsl:template match="/">
        <xsl:apply-templates/>
    </xsl:template>
    <xsl:template match="@src[parent::img]">
        <xsl:variable name="oldSrc"><xsl:value-of select="."/></xsl:variable>
        <xsl:attribute name="src">img/illustrations/bonus/<xsl:value-of select="$oldSrc" /></xsl:attribute>
    </xsl:template>
	<xsl:template match="corr[@type='emend']">
        <xsl:call-template name="emend"/>
    </xsl:template>
    <xsl:template match="gap[@type='emend']">
        <xsl:call-template name="emend"/>
    </xsl:template>
    <xsl:template match="supplied[@type='emend']">
        <xsl:call-template name="emend"/>
    </xsl:template>
    <xsl:template name="emend">
        <span class="emend">
            <xsl:apply-templates/>
        </span><xsl:choose>
            <xsl:when test="@rend = 'plain'"/>
            <xsl:otherwise><a title="Emendation" class="emend-link" href="Emend#{$idno}"><sup>[e]</sup></a></xsl:otherwise>
        </xsl:choose>
    </xsl:template>
</xsl:stylesheet>