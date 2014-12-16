/**
 *	ver 1.01 - 12/10/2011
 *	ver 1.02 - функция показа крупных изображений
 */
var clickedA = new Array();
var type = $('input[type=radio]:checked').val();
var $_GET = parseUrlQuery();

//* фикс  для осла *//
if(!Array.indexOf ){
    Array.prototype.indexOf  = function(obj){
        for(var i=0; i<this.length; i++){
            if(this[i]==obj){
                return i;
            }
        }
        return -1;
    }
}
//* get параметры в массив *//
function parseUrlQuery() {
    var data = {};
    if(location.search) {
        var pair = (location.search.substr(1)).split('&');
        for(var i = 0; i < pair.length; i ++) {
            var param = pair[i].split('=');
            data[param[0]] = param[1];
        }
    }
    return data;
}

function selectFunc(code)
{
	if($('input#'+code).is(":checked") == false){
		clickedA.splice(clickedA.indexOf(code), 1);
		$('label[for='+code+']').removeClass('select');
	} 
	else{
		clickedA.push(code);
		$('label[for='+code+']').addClass('select');
	}
	$('table#table tr[rel=row]').removeAttr('style');
	if(clickedA.length == 0){
	}
	// перебираем все строки, прячем отфильтрованные
		$($('table#table tr[rel=row]')).each(function()
			{
				var tr = $(this); 
				jQuery.each(clickedA, function(){
					if(tr.attr('class').indexOf(this) >= 0){
					}
					else{
						tr.hide();
					}
				}
			);		
		});
	$('input#acdc').change();	
	//return false;
}


$(function(){
	// клики на pdf
	$("a[href$='.pdf']").bind('click contextmenu', function(){
		var page = encodeURIComponent(location);
		var file = encodeURIComponent($(this).attr('href'));
		$.post('/classes/ajax.php', {file: file, page: page});
		return true;		
	});
	// ссылки на pdf. добавляет класс.pdf ко всем текстовым (не содержащим картинку!) ссылкам на файлы с расширением .pdf
	$("a[href$='.pdf']").each(function(){
		if($(this).has('img').length == 0){
			//alert($(this).attr('href');
			$(this).addClass('pdf');
			}
	});

	// вывод картинок релюшек
	$('td.name').hover(
		function(){
			var id = $(this).parents('tr').attr('id');
			$('#'+id+' div.hideImage').fadeIn(555);
		},
		function(){
			var id = $(this).parents('tr').attr('id');
			$('#'+id+' div.hideImage').fadeOut(100);
		}		
	);

	$('.hideImageWrapper').hover(
		function(){
			var img_id = $(this).data('image-id');
			$('img#'+img_id).fadeIn(555);
			
		},
		function(){
			var img_id = $(this).data('image-id');
			$('img#'+img_id).fadeOut(100);			
		}
	);

							
	// картинки сделал красиво
	$('img.show').each(function(){
		var href = $(this).attr('src');
		if(href.indexOf('/pre/') > 0)
			href = href.replace('/pre', '');
		$(this).wrap("<a href='"+href+"' class='show'></a>");
		$(this).removeClass('show');
	});
	
	$('a.open').bind('click', function(){
		var id = $(this).attr('rel');
		$('#'+$(this).attr('rel')).show("slow");
		$(this).toggle();
		$('a.close[rel='+id+']').toggle();
		return false;
	});
	$('a.close').bind('click', function(){
		var id = $(this).attr('rel');
		$('#'+$(this).attr('rel')).hide("slow");
		$(this).toggle();
		$('a.open[rel='+id+']').toggle();
		return false;
	});	

	// для фильтра реле, если были параметры в get
	if($_GET.func != undefined)
	{
		$('input#_'+$_GET.func+'_').click();
		selectFunc('_'+$_GET.func+'_');
		$('caption').html('Найдено подходящих реле: '+$('table#table tr:visible[rel=row]').length);
	}	
	
	// выбор функции
	$('div.function input').bind('click', function(){
		var code = $(this).attr('id');
		selectFunc(code);
	});
	// указали (поменяли) значение тока
	$('input#acdc').bind('change', function(){
		var voltage = Number($('input#acdc').val());
		$($('table#table tr[rel=row]')).addClass('no-voltage');
		$($('table#table tr[rel=row]')).each(function()
			{
				var tr = $(this).attr('id');
				// выбрали все возможные значения напряжений
				var ac_min = Number($('#'+tr+' td.voltage span.ac_min').html());
				var ac_max = Number($('#'+tr+' td.voltage span.ac_max').html());
				var ac_fix = Number($('#'+tr+' td.voltage span.ac_fix').html());
				var dc_min = Number($('#'+tr+' td.voltage span.dc_min').html());
				var dc_max = Number($('#'+tr+' td.voltage span.dc_max').html());
				var dc_fix = Number($('#'+tr+' td.voltage span.dc_fix').html());
				var acdc_min = Number($('#'+tr+' td.voltage span.acdc_min').html());
				var acdc_max = Number($('#'+tr+' td.voltage span.acdc_max').html());
				// в зависимости от типа тока выбираем подходящии
				if(type === undefined)
				{
					if(	((voltage >= ac_min) &&
						(voltage <= ac_max)) ||
						(voltage == ac_fix) ||
						(voltage == dc_fix) ||
						((voltage >= dc_min) &&
						(voltage <= dc_max)) ||
						((voltage >= acdc_min) &&
						(voltage <= acdc_max))
							){
						$('#'+tr).removeClass('no-voltage');
					}
				}
				else if(type == 'AC'){
					if(	((voltage >= ac_min) &&
						(voltage <= ac_max)) ||
						(voltage == ac_fix) ||
						((voltage >= acdc_min) &&
						(voltage <= acdc_max))
					){
						$('#'+tr).removeClass('no-voltage');
					}
				}else if(type == 'DC'){
					if(	((voltage >= dc_min) &&
						(voltage <= dc_max)) ||
						(voltage == dc_fix) ||
						((voltage >= acdc_min) &&
						(voltage <= acdc_max))
					){
						$('#'+tr).removeClass('no-voltage');
					}
				}else{
				}
			});	
		$('caption').html('Найдено подходящих реле: '+$('table#table tr:visible[rel=row]').length);
	});	
	// указали тип тока
	$('input[type=radio]').bind('click', function(){
		type = $(this).val();
		$('input#acdc').change();
	});

	// ссылка про TR2
	$('a.PMtr2').bind('click', function(){
		$('#tr2').fadeIn(1000, function(){			
			$('#tr2').css('opacity', '0.7');
		});
	});	
	// сноски
	$('a.note').bind('click', function(){
		var note = $(this).attr('href');
		$(note).fadeIn(1000, function(){			
			$(note).css('opacity', '0.7');
		});
	});		
});