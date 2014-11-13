<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
<xsl:output
encoding="UTF-8"
media-type="text/xml"
doctype-system="http://partner.market.yandex.ru/pages/help/shops.dtd"/> 
	<xsl:template match="/">
		<yml_catalog date="{catalog/@date}">
		<shop>
			<name>ПОЛИГОН</name>
			<company>ООО «ПОЛИГОН»</company>
			<url>http://poligon.info/</url>
			<currencies>
				<currency id="RUR" rate="1"/>
				<currency id="EUR" rate="CBRF" plus="2"/>
			</currencies>
			<categories>
				<xsl:for-each select="catalog/categories/category">
				<xsl:choose>
				<xsl:when test="@parent_id">
				<category id="{@id}" parentId="{@parent_id}"><xsl:value-of select="@name"/></category>
				</xsl:when>
				<xsl:otherwise>
				<category id="{@id}"><xsl:value-of select="@name"/></category>
				</xsl:otherwise>
				</xsl:choose>
				</xsl:for-each>
			</categories>
			<offers>
				<xsl:for-each select="catalog/goods/item">
				<xsl:variable name="onStore">
				<xsl:choose>
					<xsl:when test="@quantity &gt; 0">true</xsl:when>
					<xsl:otherwise>false</xsl:otherwise>
				</xsl:choose>
				</xsl:variable>
				<offer id="{@id}" type="vendor.model" available="{$onStore}">
					<url><xsl:value-of select="@url"/></url>
					
					<xsl:variable name="price">
					<xsl:choose>
						<xsl:when test="@base"><xsl:value-of select="@base"/></xsl:when>
						<xsl:otherwise><xsl:value-of select="@retail"/></xsl:otherwise>
					</xsl:choose>
					</xsl:variable>
					<price><xsl:value-of select="$price"/></price>
					<currencyId>EUR</currencyId>
					<categoryId><xsl:value-of select="@category_id"/></categoryId>					
					<xsl:if test="@image = 'http:/poligon.info/images/'">
					<picture><xsl:value-of select="@image"/></picture>
					</xsl:if>
					<pickup>true</pickup>
					<!--<typePrefix>Принтер</typePrefix>-->
					<vendor><xsl:value-of select="@producer_full"/></vendor>
					
					<xsl:if test="@article">
					<vendorCode><xsl:value-of select="@article"/></vendorCode>
					</xsl:if>
					<model><xsl:value-of select="@name"/></model>
					<description><xsl:value-of select="@preview_text"/></description>
					<!--<manufacturer_warranty>true</manufacturer_warranty>-->
					<!--<country_of_origin>Япония</country_of_origin>-->
				</offer>
				</xsl:for-each>
			</offers>
		</shop>
		</yml_catalog>
	</xsl:template>
</xsl:stylesheet>