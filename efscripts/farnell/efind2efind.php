<?php

 @set_time_limit(100000);

 include_once('main.inc.php');

 echo "<p>Старт</p>";


 $sql = "insert into efind (NAME_ORDERCODE, `DESC`, MNFR, STOCK, STOCK_DELIVERY, PICTURE_LINK,PDF_LINK, POLI_LINK, CURRENCY, PRICE1, PRICE2, PRICE3, SEARCH_CONTENT) select NAME_ORDERCODE, `DESC`, MNFR, STOCK, STOCK_DELIVERY, PICTURE_LINK,PDF_LINK, POLI_LINK, CURRENCY, PRICE1, PRICE2, PRICE3, SEARCH_CONTENT from efind_farnell";
 echo $sql;
 ExecQuery($sql);
 echo "<p>Выполнено!</p>";




                                                                           
?>

