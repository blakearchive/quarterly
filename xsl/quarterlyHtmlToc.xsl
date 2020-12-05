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
    <xsl:template match="@href[parent::a]">
        <xsl:variable name="oldHref"><xsl:value-of select="."/></xsl:variable>
        <xsl:choose>
            <xsl:when test="parent::a = 'PDF' or parent::a = 'HTML'">
                <xsl:variable name="oldKey"><xsl:value-of select="substring-before(substring-after($oldHref, 'article/view/'), '/')"/></xsl:variable>
                <xsl:variable name="oldKeyLength"><xsl:value-of select="string-length($oldKey)"/></xsl:variable>
                <xsl:variable name="idx"><xsl:value-of select="$oldKeyLength - 2"/></xsl:variable>
                <xsl:variable name="keySplit"><xsl:value-of select="substring($oldKey,0,$idx)"/></xsl:variable>
                <xsl:variable name="newKey"><xsl:value-of select="substring($idno, 1, 4)"/>.<xsl:value-of select="$keySplit"/></xsl:variable>
                <xsl:choose>
                    <xsl:when test="parent::a = 'PDF'">
                        <xsl:attribute name="href">pdfs/<xsl:value-of select="$newKey"/>.pdf</xsl:attribute>
                    </xsl:when>
                    <xsl:otherwise>
                        <xsl:attribute name="href"><xsl:value-of select="$newKey"/></xsl:attribute>
                    </xsl:otherwise>
                </xsl:choose>
            </xsl:when>
            <xsl:otherwise>
                <xsl:variable name="oldKey"><xsl:value-of select="substring-after($oldHref, 'article/view/')"/></xsl:variable>
                <xsl:variable name="oldKeyLength"><xsl:value-of select="string-length($oldKey)"/></xsl:variable>
                <xsl:variable name="idx"><xsl:value-of select="$oldKeyLength - 2"/></xsl:variable>
                <xsl:variable name="keySplit"><xsl:value-of select="substring($oldKey,0,$idx)"/></xsl:variable>
                <xsl:variable name="newKey"><xsl:value-of select="substring($idno, 1, 4)"/>.<xsl:value-of select="$keySplit"/></xsl:variable>
                <xsl:attribute name="href"><xsl:value-of select="$newKey"/></xsl:attribute>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>
    <xsl:template match="@src[parent::img]">
        <xsl:variable name="oldSrc"><xsl:value-of select="."/></xsl:variable>
        <xsl:attribute name="src">img/illustrations/<xsl:value-of select="substring($idno, 1, 4)"/>/<xsl:value-of select="$oldSrc"/></xsl:attribute>
    </xsl:template>
    <xsl:template match="div[@id='custFooter']"/>
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