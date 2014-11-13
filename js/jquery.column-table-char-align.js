/**
*    columnTableCharAlign
*    @author admin@samizdam.net
*    @since 10.12.2012
*    @ver 1.2.12
*/

(function($){
  jQuery.fn.columnTableCharAlign = function(options){
    options = $.extend({
        cols: 'n1+1', // ����� �������, ��������� ��������� ��� :nth-child(), �� ��������� ���
        use_char: ',', // ������ ��� ������������
        left_offset: 3, // ���-�� �������� ���������� ����� (������� � ���-�� ������ ����� ����� � ���������� ��������)
        right_offset: 2, // ���-�� ������ � ������� ����� �� ���������
        exclude_cells : 3 // TODO! ������ ����� ��� ������� �� ��������� �������������� (����. � ������� � �.�.) :nth-of-type() ����������� ���������
    }, options);
 
    var make = function(){
        $(this).css('font-family', 'monospace');
        $(this).css('text-align', 'left');
        var tds = $(this).find('tbody tr td:nth-child('+options.cols+')');
       //:not(:nth-of-type('+options.exclude_cells+'))');
       // alert(tds.size());
        //tds = tds.filter(':not(:nth-of-type('+options.exclude_cells+'))');
        //alert(tds.size());          
        tds.each(function(i){
            var character = options.use_char;
            var num_value = $(this).text();
            var new_value = "";
            var fraction = num_value.split(character);
            var integer = fraction[0];
            var fractional = fraction[1];
            // ����� ���� �����, ��� � ������ ��������� ����� �����, ��� �������
            // ����� ��� �� ������� �� ������
            if(fractional == undefined){
                fractional = '';
                character = '&nbsp;';
            }
            // ��������� ������� �����
            var difference_integer = options.left_offset - integer.length;
            if(difference_integer > 0){
                new_value += new Array(difference_integer + 1).join('&nbsp;');
            }
            // ��������� �� ������
            new_value += integer+character+fractional;
            // ������� ������
            var difference_fractional = options.right_offset - fractional.length;
            if(difference_fractional > 0){
                 new_value += new Array(difference_fractional + 1).join('&nbsp;');
            }

            $(this).html(new_value);
        });
    }
    return this.each(make);
  }

/**
 * ���������� ��������� ��������� ��� :nth-of-type
 * ����� ��  jQuery Extended Selectors (http://www.keithclark.co.uk/labs/jquery-extended-selectors/)
 */
    
    function getNthIndex(cur, dir) {
        var t = cur, idx = 0;
        while (cur = cur[dir] ) {
            if (t.tagName == cur.tagName) {
                idx++;
            }
        }
        return idx;
    }

    function isNthOf(elm, pattern, dir) {
        var position = getNthIndex(elm, dir), loop;
        if (pattern == "odd" || pattern == "even") {
            loop = 2;
            position -= !(pattern == "odd");
        } else {
            var nth = pattern.indexOf("n");
            if (nth > -1) {
                loop = parseInt(pattern, 10) || parseInt(pattern.substring(0, nth) + "1", 10);
                position -= (parseInt(pattern.substring(nth + 1), 10) || 0) - 1;
            } else {
                loop = position + 1;
                position -= parseInt(pattern, 10) - 1;
            }
        }
        return (loop<0 ? position<=0 : position >= 0) && position % loop == 0
    }    
    var pseudos = {
        "nth-of-type": function(elm, i, match) {
            return isNthOf(elm, match[3], "previousSibling");
        }        
    }
    $.extend($.expr[':'], pseudos);    
      
      
})(jQuery);