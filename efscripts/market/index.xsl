<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output
encoding="UTF-8"
omit-xml-declaration="yes"
cdata-section-elements="namelist"
indent="yes"
media-type="text/html"/> 
	<xsl:template match="/">
	<html><head></head><body>
		<!-- набор инструкций -->
		<xsl:for-each select="catalog/offer">
		<div>
			<h1><xsl:value-of select="name"/></h1>
			<xsl:value-of select="full_description" disable-output-escaping="yes"/>
		</div>
		</xsl:for-each>
		</body></html>
	</xsl:template>
</xsl:stylesheet>