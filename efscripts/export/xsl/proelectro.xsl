<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output
encoding="UTF-8"
media-type="text/xml"/>
<xsl:template match="/">
<body>
<xsl:for-each select="/catalog/goods/item">
	<h1><xsl:value-of select="@name" /> (<xsl:value-of select="@article"/>)</h1>	
	<xsl:call-template name="table"/>
	<hr/>
</xsl:for-each>
</body>
</xsl:template>

<xsl:template name="table">
	<xsl:value-of select="detail_text" disable-output-escaping="yes"/>
</xsl:template>
</xsl:stylesheet>