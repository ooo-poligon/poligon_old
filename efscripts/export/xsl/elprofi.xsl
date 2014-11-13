<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output
method="text"
encoding="windows-1251"
omit-xml-declaration="yes"
indent="yes"/>
<xsl:template match="/">
<xsl:for-each select="catalog/goods/item">
	<xsl:value-of select="@name"/>;<xsl:value-of select="@preview_text"/>;шт.;<xsl:value-of select="@base"/>;<xsl:value-of select="@retail"/>;<xsl:value-of select="@wholesale"/>;<xsl:value-of select="@url"/>;<xsl:if test="@image != 'http://poligon.info/images/'"><xsl:value-of select="@image"/></xsl:if>;
</xsl:for-each>
</xsl:template>
</xsl:stylesheet>