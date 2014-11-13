<?
$url = "https://api.element14.com/catalog/products?term=any%3Afuse&storeInfo.id=uk.farnell.com&resultsSettings.offset=0&resultsSettings.numberOfResults=1&resultsSettings.refinements.filters=rohsCompliant%2CinStock&resultsSettings.responseGroup=large&callInfo.omitXmlSchema=false&callInfo.responseDataFormat=xml&callinfo.apiKey=w5zdutrkzzxvm6ra9bxdxt8c";

//h8szff9tz97jsg84s4wuq2cf
//w5zdutrkzzxvm6ra9bxdxt8c

// I'm not sure it's right headers... 
$headers = array(
    "X-Originating-IP: 177.222.56.184\n",
    "User-Agent: farnellsss-store\n",
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
//curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__)."/cacert.pem");
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);     
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
curl_setopt($ch, CURLOPT_HEADER, 1);


curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$result = curl_exec($ch);

print curl_error($ch);
print($result);
curl_close($ch);
?>