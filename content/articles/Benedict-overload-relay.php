<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("keywords", "");
$APPLICATION->SetPageProperty("description", "Публикация о тепловых реле: характеристика, подбор по параметрам двигателя");
$APPLICATION->SetTitle("Тепловые реле Benedict ");
$APPLICATION->AddHeadString('<link href="/css/articles.css"  type="text/css" rel="stylesheet" />',true);
?> 


<h1>Тепловые реле Benedict </h1>
<p>Долговечность энергетического оборудования в значительной степени зависит от перегрузок, которым оно подвергается во время работы. Для любого объекта можно найти зависимость допустимой длительности протекания тока от его значения, при котором обеспечивается надежная и длительная его эксплуатация. При номинальном токе допустимая длительность его протекания стремится к бесконечности. Протекание тока, превышающего номинальный, приводит к дополнительному повышению температуры и дополнительному старению изоляции обмоток двигателя. Поэтому чем больше ток перегрузки, тем меньше должна быть  ее длительность. </p>
<p>Из-за инерционности теплового процесса тепловые реле, имеющие биметаллический элемент, непригодны для защиты цепей от КЗ. Поэтому защита с помощью таких реле должна быть дополнена электромагнитным реле, предохранителями или автоматическими выключателями.</p>
<p>Реле перегрузки имеют регулятор настройки тока срабатывания в амперах, который позволяет произвести быструю настройку реле перегрузки без каких-либо дополнительных расчетов. В соответствии с международными и национальными стандартами, уставка тока срабатывания теплового реле соответствует номинальному току двигателя. При этом реле перегрузки обеспечивает следующие требования: </p>
<ol>
	<li>отключение не происходит при 1,05 х Iср;</li>
	<li>отключение происходит при 1,2 х Iср; Iср - ток уставки.</li>
</ol>

<p>Реле перегрузки должно быть защищено от короткого замыкания. Максимально допустимый номинал предохранителя следует смотреть в технической документации на реле перегрузки.
</p>
<p>Характеристика срабатывания для реле перегрузки серий U3/32, U3/42, U3/74 и U12/16E при работе с трехфазной нагрузкой:</p>
<figure>
	<img src="/images/articles/2012/12/character-chart.jpg" style="width: 360px; margin: 0 auto; display: block;" class="show" alt="Диаграмма характеристики срабатывания тепловых реле"/>
	<figcaption>Характеристика срабатывания для реле перегрузки серий U3/32, U3/42, U3/74 и U12/16E при работе с трехфазной нагрузкой</figcaption>
</figure>

<p>Основные элементы теплового реле  Benedict U3/32 11:</p>
<figure>
	<img src="/images/articles/2012/12/relay-descr.jpg" class="show" alt="Элементы теплового реле"/>
	<figcaption>
	<ol style="text-align: left;">	
		<li>Выводы для подключения к контактору (1L1, 3L2, 5L3 - силовые) + вывод для подключения к вспомогательному контакту контактора</li>
		<li>Маркировочная табличка</li>
		<li>Уставка тока срабатывания</li>
		<li>Кнопка сброса (RESET), возможность выбрать ручной или автоматический сброс</li>
		<li>Кнопка остановки (STOP)</li>
		<li>Сигнальный контакт срабатывания, NC (клеммы 95-96)</li>
		<li>Винтовые клеммы для подключения двигателя (2T1, 4T2, 6T3)</li>
		<li>Система пломбировки</li>
		<li>Индикация состояния теплового реле</li>
		<li>Сигнальный контакт, NO (клеммы 97-98)</li>
		<li>Клемма подключения вспомогательного контакта контакторов K3-10...K3-22</li>
	</ol>
	</figcaption>
</figure>
<p>Тепловое реле U3/32 подходит для защиты трехфазных и однофазных двигателей.</p>

<figure>
	<img src="/images/articles/2012/12/scheme_1~.jpg" alt="Схема подключения теплового реле к однофазному двигателю" class="show"/>
	<figcaption>Схема подключения теплового реле к однофазному двигателю</figcaption>
</figure>

<figure>
	<img src="/images/articles/2012/12/scheme_3~.jpg" alt="Схема подключения теплового реле к трехфазному двигателю" class="show"/>
	<figcaption>Схема подключения теплового реле к трехфазному двигателю</figcaption>
</figure>

<p>Тепловые реле Benedict серии U3/32 имеют температурную компенсацию в диапазоне температур от -25°С до +60°С.</p>
<p>В случае, если температура окружающей среды в месте установки теплового реле U3/32 превышает +60°С, следует воспользоваться формулой для расчета правильной уставки тока срабатывания и возможно остановить свой выбор на реле с другим диапазоном тока срабатывания:</p>
<figure>
	<p style="text-align: center"><code class="formula">K=(Toc-20)Х0,125</code></p>
	<figcaption>
	<ul>	
		<li>где K - коэффициент температурной поправки в %;</li>
		<li>Toc - температура окружающей среды.</li>
	</ul>	
	</figcaption>
</figure>

<h3>Например: </h3>
<p>Температура окружающей среды Toc = 70°С, номинальный ток двигателя Iн = 8А. Рассчитываем коэффициент температурной поправки:</p>
<p><code class="formula">K=(70-20)Х0,125=6,25%</code></p>
<p>Рассчитываем ток срабатывания теплового реле (ток уставки теплового реле):</p>
<p><code class="formula">Iср=Iн+6,25%=8,5А</code></p>
<br/>

<script src="/js/jquery.tablehover.v014/jquery.tablehover.pack.js.gz"></script>
<script src="/js/jquery.column-table-char-align.js.gz"></script>
<script>
$(document).ready(function(){
	$('.backlight').columnTableCharAlign({cols: 1, charoff: 5});
	$('.backlight').columnTableCharAlign({cols: 2, charoff: 5});
	$('.backlight').columnTableCharAlign({cols: 5, use_char: '-', charoff: 7});
	
	$('.backlight').tableHover();
});
</script>

<table class="backlight"><caption>Таблица выбора контактора и теплового реле Benedict в зависимости от мощности трехфазного двигателя для работы при температуре -25°С...+60°С:</caption>
<thead>
<tr>
	<th colspan="2">Двигатель</th>
	<th rowspan="2">Контактор Benedict</th>
	<th rowspan="2">Тепловое реле Benedict</th>
	<th rowspan="2">Диапазон настройки тока срабатывания</th>
</tr>
<tr>
	<th>Мощность, кВт</th>	<th>Номинальный ток, А</th>
</tr>
</thead><tbody>
<tr><td>0,06</td>	<td>0,22</td>	<td><a href="/search/index.php?q=K1-09D10">K1-09D10</a></td>	<td><entity prop="link" name="U12/16E 0,27 K1"/></td>	<td>0,18-0,27</td></tr>
<tr><td>0,09</td>	<td>0,33</td>	<td><a href="/search/index.php?q=K1-09D10">K1-09D10</a></td>	<td><entity prop="link" name="U12/16E 0,4 K1"/></td>	<td>0,27-0,4</td></tr>
<tr><td>0,12</td>	<td>0,42</td>	<td><a href="/search/index.php?q=K1-09D10">K1-09D10</a></td>	<td><entity prop="link" name="U12/16E 0,6 K1"/></td>	<td>0,4-0,6</td></tr>
<tr><td>0,18</td>	<td>0,64</td>	<td><a href="/search/index.php?q=K1-09D10">K1-09D10</a></td>	<td><entity prop="link" name="U12/16E 0,9 K1"/></td>	<td>0,6-0,9</td></tr>
<tr><td>0,25</td>	<td>0,88</td>	<td><a href="/search/index.php?q=K1-09D10">K1-09D10</a></td>	<td><entity prop="link" name="U12/16E 1,2 K1"/></td>	<td>0,9-1,2</td></tr>
<tr><td>0,37</td>	<td>1,22</td>	<td><a href="/search/index.php?q=K1-09D10">K1-09D10</a></td>	<td><entity prop="link" name="U12/16E 1,8 K1"/></td>	<td>1,2-1,8</td></tr>
<tr><td>0,55</td>	<td>1,5</td>	<td><a href="/search/index.php?q=K1-09D10">K1-09D10</a></td>	<td><entity prop="link" name="U12/16E 1,8 K1"/></td>	<td>1,2-1,8</td></tr>
<tr><td>0,75</td>	<td>2</td>	<td><a href="/search/index.php?q=K1-09D10">K1-09D10</a></td>	<td><entity prop="link" name="U12/16e 2,7 K1"/></td>	<td>1,8-2,7</td></tr>
<tr><td>1,1</td>	<td>2,6</td>	<td><a href="/search/index.php?q=K1-09D10">K1-09D10</a></td>	<td><entity prop="link" name="U12/16E 2,7 K1"/></td>	<td>1,8-2,7</td></tr>
<tr><td>1,5</td>	<td>3,5</td>	<td><a href="/search/index.php?q=K1-09D10">K1-09D10</a></td>	<td><entity prop="link" name="U12/16E 4 K1"/></td>	<td>2,7-4</td></tr>
<tr><td>2,2</td>	<td>5</td>	<td><a href="/search/index.php?q=K1-09D10">K1-09D10</a></td>	<td><entity prop="link" name="U12/16E 6 K1"/></td>	<td>4-6</td></tr>
<tr><td>2,5</td>	<td>5,7</td>	<td><a href="/search/index.php?q=K1-09D10">K1-09D10</a></td>	<td><entity prop="link" name="U12/16E 6 K1"/></td>	<td>4-6</td></tr>
<tr><td>3</td>	<td>6,6</td>	<td><a href="/search/index.php?q=K1-09D10">K1-09D10</a></td>	<td><entity prop="link" name="U12/16E 9 K1"/></td>	<td>6-9</td></tr>
<tr><td>3,7</td>	<td>8,2</td>	<td><a href="/search/index.php?q=K3-10ND10">K3-10ND10</a></td>	<td><entity prop="link" name="U3/32 11"/></td>	<td>8-11</td></tr>
<tr><td>4</td>	<td>8,5</td>	<td><a href="/search/index.php?q=K3-10ND10">K3-10ND10</a></td>	<td><entity prop="link" name="U3/32 11"/></td>	<td>8-11</td></tr>
<tr><td>5,5</td>	<td>11,5</td>	<td><a href="/search/index.php?q=K3-14ND10">K3-14ND10</a></td>	<td><entity prop="link" name="U3/32 14"/></td>	<td>10-14</td></tr>
<tr><td>7,5</td>	<td>15,5</td>	<td><a href="/search/index.php?q=K3-18ND10">K3-18ND10</a></td>	<td><entity prop="link" name="U3/32 18"/></td>	<td>13-18</td></tr>
<tr><td>8</td>	<td>16,7</td>	<td><a href="/search/index.php?q=K3-18ND10">K3-18ND10</a></td>	<td><entity prop="link" name="U3/32 18"/></td>	<td>13-18</td></tr>
<tr><td>11</td>	<td>22</td>	<td><a href="/search/index.php?q=K3-22ND10">K3-22ND10</a></td>	<td><entity prop="link" name="U3/32 24"/></td>	<td>17-24</td></tr>
<tr><td>12,5</td>	<td>25</td>	<td><a href="/search/index.php?q=K3-32A00">K3-32A00</a></td>	<td><entity prop="link" name="U3/32 32"/></td>	<td>23-32</td></tr>
<tr><td>15</td>	<td>30</td>	<td><a href="/search/index.php?q=K3-32A00">K3-32A00</a></td>	<td><entity prop="link" name="U3/42 42"/></td>	<td>28-42</td></tr>
<tr><td>18,5</td>	<td>37</td>	<td><a href="/search/index.php?q=K3-40A00">K3-40A00</a></td>	<td><entity prop="link" name="U3/42 42"/></td>	<td>28-42</td></tr>
<tr><td>20</td>	<td>40</td>	<td><a href="/search/index.php?q=K3-40A00">K3-40A00</a></td>	<td><entity prop="link" name="U3/42 42"/></td>	<td>28-42</td></tr>
<tr><td>22</td>	<td>44</td>	<td><a href="/search/index.php?q=K3-50A00">K3-50A00</a></td>	<td><entity prop="link" name="U3/74 52"/></td>	<td>40-52</td></tr>
<tr><td>25</td>	<td>50</td>	<td><a href="/search/index.php?q=K3-50A00">K3-50A00</a></td>	<td><entity prop="link" name="U3/74 52"/></td>	<td>40-52</td></tr>
<tr><td>30</td>	<td>60</td>	<td><a href="/search/index.php?q=K3-62A00">K3-62A00</a></td>	<td><entity prop="link" name="U3/74 65"/></td>	<td>52-65</td></tr>
<tr><td>37</td>	<td>72</td>	<td><a href="/search/index.php?q=K3-74A00">K3-74A00</a></td>	<td><entity prop="link" name="U3/74 74"/></td>	<td>60-74</td></tr>
<tr><td>40</td>	<td>79</td>	<td><a href="/search/index.php?q=K3-90A00">K3-90A00</a></td>	<td><entity prop="link" name="U85 90"/></td>	<td>60-90</td></tr>
<tr><td>45</td>	<td>85</td>	<td><a href="/search/index.php?q=K3-90A00">K3-90A00</a></td>	<td><entity prop="link" name="U85 120"/></td>	<td>80-120</td></tr>
<tr><td>51</td>	<td>97</td>	<td><a href="/search/index.php?q=K3-115A00">K3-115A00</a></td>	<td><entity prop="link" name="U85 120"/></td>	<td>80-120</td></tr>
<tr><td>55</td>	<td>105</td>	<td><a href="/search/index.php?q=K3-115A00">K3-115A00</a></td>	<td><entity prop="link" name="U85 120"/></td>	<td>80-120</td></tr>
<tr><td>59</td>	<td>112</td>	<td><a href="/search/index.php?q=K3-115A00">K3-115A00</a></td>	<td><entity prop="link" name="U180 180"/></td>	<td>120-180</td></tr>
<tr><td>75</td>	<td>140</td>	<td><a href="/search/index.php?q=K3-151A00">K3-151A00</a></td>	<td><entity prop="link" name="U180 180"/></td>	<td>120-180</td></tr>
<tr><td>90</td>	<td>170</td>	<td><a href="/search/index.php?q=K3-176A00">K3-176A00</a></td>	<td><entity prop="link" name="U180 180"/></td>	<td>120-180</td></tr>
<tr><td>110</td>	<td>205</td>	<td><a href="/search/index.php?q=K3-210A00">K3-210A00</a></td>	<td><entity prop="link" name="U320 216"/></td>	<td>144-216</td></tr>
<tr><td>129</td>	<td>242</td>	<td><a href="/search/index.php?q=K3-260A00">K3-260A00</a></td>	<td><entity prop="link" name="U320 320"/></td>	<td>216-320</td></tr>
<tr><td>132</td>	<td>245</td>	<td><a href="/search/index.php?q=K3-260A00">K3-260A00</a></td>	<td><entity prop="link" name="U320 320"/></td>	<td>216-320</td></tr>
<tr><td>147</td>	<td>273</td>	<td><a href="/search/index.php?q=K3-316A00">K3-316A00</a></td>	<td><entity prop="link" name="U800 360"/></td>	<td>240-360</td></tr>
<tr><td>160</td>	<td>295</td>	<td><a href="/search/index.php?q=K3-316A00">K3-316A00</a></td>	<td><entity prop="link" name="U800 360"/></td>	<td>240-360</td></tr>
<tr><td>184</td>	<td>340</td>	<td><a href="/search/index.php?q=K3-450A22">K3-450A22</a></td>	<td><entity prop="link" name="U800 360"/></td>	<td>240-360</td></tr>
<tr><td>200</td>	<td>370</td>	<td><a href="/search/index.php?q=K3-450A22">K3-450A22</a></td>	<td><entity prop="link" name="U800 540"/></td>	<td>360-540</td></tr>
<tr><td>220</td>	<td>408</td>	<td><a href="/search/index.php?q=K3-450A22">K3-450A22</a></td>	<td><entity prop="link" name="U800 540"/></td>	<td>360-540</td></tr>
<tr><td>250</td>	<td>460</td>	<td><a href="/search/index.php?q=K3-550A22">K3-550A22</a></td>	<td><entity prop="link" name="U800 540"/></td>	<td>360-540</td></tr>
<tr><td>257</td>	<td>475</td>	<td><a href="/search/index.php?q=K3-550A22">K3-550A22</a></td>	<td><entity prop="link" name="U800 540"/></td>	<td>360-540</td></tr>
<tr><td>295</td>	<td>546</td>	<td><a href="/search/index.php?q=K3-550A22">K3-550A22</a></td>	<td><entity prop="link" name="U800 800"/></td>	<td>540-800</td></tr>
<tr><td>315</td>	<td>580</td>	<td><a href="/search/index.php?q=K3-700A22">K3-700A22</a></td>	<td><entity prop="link" name="U800 800"/></td>	<td>540-800</td></tr>
<tr><td>355</td>	<td>636</td>	<td><a href="/search/index.php?q=K3-700A22">K3-700A22</a></td>	<td><entity prop="link" name="U800 800"/></td>	<td>540-800</td></tr>
<tr><td>400</td>	<td>710</td>	<td><a href="/search/index.php?q=K3-860A22">K3-860A22</a></td>	<td><entity prop="link" name="U800 800"/></td>	<td>540-800</td></tr>
</tbody></table>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>