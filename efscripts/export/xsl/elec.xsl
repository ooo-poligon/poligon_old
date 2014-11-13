<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output
encoding="UTF-8"
media-type="text/xml"
doctype-system="http://elec.st/1337701493/xml/pricelist.dtd"/>
<xsl:template match="/">
	<elec_market date="2012-05-28 12:27:11">
		<currencies>
			<currency id="RUR"/>
			<currency id="USD" rate="CBRF"/>
			<currency id="EUR" rate="CBRF" plus="2"/>
		</currencies>
		<categories>
			<xsl:for-each select="catalog/categories/category">
			<xsl:if test="@elec_id">
			<category id="{@id}" rubricaId="{@elec_id}" unit="PCE" currencyId="EUR"><xsl:value-of select="@name"/></category>
			</xsl:if>
			</xsl:for-each>
		</categories>
		<offers>
		<xsl:for-each select="/catalog/goods/item">
		<offer id="{@id}">
			<categoryId><xsl:value-of select="@category_id"/></categoryId>
			<xsl:variable name="c_id" select="@category_id"/>
			<keyword><xsl:value-of select="/catalog/categories/category[@id=(/catalog/categories/category[@id=$c_id]/@parent_id)]/@name"/></keyword>
			<title><xsl:value-of select="@name"/></title>
			<url><xsl:value-of select="@url"/></url>
			
			<xsl:variable name="price">
			<xsl:choose>
				<xsl:when test="@base"><xsl:value-of select="@base"/></xsl:when>
				<xsl:otherwise><xsl:value-of select="@retail"/></xsl:otherwise>
			</xsl:choose>
			</xsl:variable>			
			<price><xsl:value-of select="$price"/></price>
			<artno><xsl:value-of select="@article"/></artno>
			<currencyId>EUR</currencyId>
			<quantity><xsl:value-of select="@quantity"/></quantity>
			
			<xsl:if test="@image = 'http:/poligon.info/images/'">
			<picture><xsl:value-of select="@image"/></picture>
			</xsl:if>
			<vendor><xsl:value-of select="@producer_full"/></vendor>
			<tizer><xsl:value-of select="@preview_text"/></tizer>
		</offer>
		</xsl:for-each>
		</offers>
	</elec_market>
</xsl:template>
</xsl:stylesheet>