;(function($){

	$(document).ready(function(){
		
		/*
		$('.tcf-status a').on('click', function(){
			
			var sel = $(this).data('title');
			var tog = $(this).data('toggle');
			
			$('#'+tog).prop('value', sel);
			
			$('a[data-toggle="'+tog+'"]').not('[data-title="'+sel+'"]').removeClass('active').addClass('notActive');
			$('a[data-toggle="'+tog+'"][data-title="'+sel+'"]').removeClass('notActive').addClass('active');
		});
		*/

		if( $(".tcf-add-input-group").length ){
			
			/*
			if( $( ".sortable .ui-sortable" ).length ){
				
				$( ".sortable .ui-sortable" ).sortable({
					
					placeholder	: "ui-state-highlight",
					items		: "li:not(.ui-state-disabled)",
					start: function (e, ui) {
						
						
					},
					stop: function (e, ui) {
						
						
					}
				});
				
				$( ".sortable .ui-sortable li" ).disableSelection();
			}
			*/
			
			//check task
			
			function tcf_check_task(){
				
				if(this.checked) {
					
					$(this).next("div").css('text-decoration','line-through').next("input").val('on');
				}
				else{
					
					$(this).next("div").css('text-decoration','none').next("input").val('off');
				}				
			}
			
			//input group add row

			$(".tcf-add-input-group")
			.on('click', function(e){
				
				e.preventDefault();
				
				var val = $(this).prev('input').val();
				
				if( val != '' ){

					var target = "." + $(this).data("target");
					
					var clone = $(target).eq(0).clone().removeClass('ui-state-disabled');
					
					clone.css('display','inline-block');
					
					clone.find('.input-value').text(val);
					
					clone.find('input:first').val(val);
					
					$(this).prev('input').val('');
					
					$('<a style="padding: 0px 7px;margin: 10px 0 0 0;border-radius: 20px;" class="remove-input-group" href="#">x</a>').insertAfter(clone.find('input:last'));

					$(this).next(".input-group").append(clone);
					
					$(".input-group input:checkbox").change(tcf_check_task);
				}
			});
			
			$(".input-group input:checkbox").change(tcf_check_task);
			
			$(".input-group")
			.on('click', ".remove-input-group", function(e){

				e.preventDefault();
				$(this).closest('.input-group-row').remove();
			});
		}
	});
		
})(jQuery);