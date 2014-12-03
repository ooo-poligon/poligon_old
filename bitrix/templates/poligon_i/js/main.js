$(document).ready(function(){
   $("#description td a").click(function(){
      var selected = $(this).attr('href');	
      $.scrollTo(selected, 500);		
      return false;
   });	
});