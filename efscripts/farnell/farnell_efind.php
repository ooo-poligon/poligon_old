<?php
    @set_time_limit(100000);

    include_once('main.inc.php');


  if (isset($_POST['submit']) && $_POST['submit'] == 'Сформировать')
  {
    $edit = $_POST['edit'];
    $id   = $_POST['id']; 
  }

  $edit = $_POST['edit'];
  $id   = $_POST['id']; 

  if (empty($edit)) $edit=LIST_RECORD;


?>
<html>
<head>
</head><body>

<?


  if ($edit==INSERT_RECORD)
  {
     //echo '<p>TRUNCATE efind_farnell</p>';
     //ExecQuery('TRUNCATE `efind_farnell`');

     $sql = "update farnell_gr_links set visible=0";
     ExecQuery($sql);

     $pages = GetRecords('select id from farnell_gr_links order by id');

      for($j=0;$j<count($pages);$j++)
      {
        if (isset($id[$j][$pages[$j]['id']]))
        {
          $sql = "update farnell_gr_links set visible=1 where id=".$pages[$j]['id'];
          ExecQuery($sql);

        }
      }


     $pages = GetRecords('select id from farnell_gr_links where visible=1 order by id');
     echo '<p><b>Копируем данные в таблицу для поиска:</b>';
     for ($j=0;$j<count($pages);$j++)
     {
       $count_page = GetOneRecord('select count(id) as ci from efind_farnell where gr_link='.$pages[$j]['id']);
       if ($count_page['ci']>0)
         echo '<br>&nbsp;группа '.$pages[$j]['id'].' уже существует';
       else
       {
         $sql  = 'insert into efind_farnell (ID, NAME_ORDERCODE, `DESC`, MNFR, STOCK, STOCK_DELIVERY, PICTURE_LINK, ';
         $sql  .= 'PDF_LINK, POLI_LINK, CURRENCY, PRICE1, PRICE2, PRICE3, SEARCH_CONTENT, gr_link) ';
         $sql  .= 'select f.id, f.device, CONCAT(f.art, " (", SUBSTRING(f.code, 1,3),"-",SUBSTRING(f.code, 4,LENGTH(f.code)-3), ")"), f.proizv, "", "3-4 недели", f.pic_url, ';
         $sql  .= 'f.pdf_url, "", "EUR", "", "", "", CONCAT(f.device, " ", f.code), f.gr_link from farnell as f where f.gr_link='.$pages[$j]['id']; 
         ExecQuery($sql);
         echo '<br>&nbsp;группа '.$pages[$j]['id'].' скопирована';
       }
     }


     echo '<p><b>Удаляем лишние данные из таблицы для поиска</b>';
     $sql  = 'delete FROM `efind_farnell` WHERE `gr_link` in (select f.id from farnell_gr_links as f where f.visible=0)'; 
     ExecQuery($sql);


     echo '</p><p>Оптимизируем таблицу для поиска</p>';
     $sql = "OPTIMIZE TABLE `efind_farnell`";
     ExecQuery($sql);
     echo '<p>Операция закончена</p>';
     $edit=LIST_RECORD;
  }

  echo '<form class="text" method="post" name="replace_pages" id="replace_pages">';
  echo '<input type="submit" name="submit" value="Сформировать">';

  $pages = GetRecords('select * from farnell_gr_links order by id');

  for ($j=0;$j<count($pages);$j++)
  {
    echo '<br><input type="checkbox" style="width:25px;height:25px;" name="id['.$j.']['.$pages[$j]['id'].']" '.($pages[$j]['visible']==1 ? 'checked' : '').' value='.($pages[$j]['visible']==1 ? 1 : 0).'>'.$pages[$j]['name_gr'].' (<b>'.$pages[$j]['pages'].' страниц</b>)'; 
  }

  echo '<input type="hidden" name="edit" value="'.INSERT_RECORD.'">';
  echo '<p><input type="submit" name="submit" value="Сформировать"></p>';
  echo '</form>';
  
  PrintBottom();
                                                                           
?>

