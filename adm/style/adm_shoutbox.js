(function($){  // Avoid conflicts with other libraries
	'use strict';

	/** Change image after select **/
	shoutbox.updateColor = function(newcolor,id){
		$('#'+id).attr('title', newcolor);
		$('#color_image').attr({'src': config.colorPath+newcolor+'.webp','alt': newcolor,'title': newcolor});
	}
	/** Change image for panel **/
	shoutbox.updatePanel = function(newImg,newTitle,id){
		$('#shout_'+id).attr('title',newTitle);
		$('#'+id).attr('src',config.panelPath+newImg);
	}
	/** Play sounds **/
	shoutbox.playSound = function(file){
		if(file != 0){
			$('#shout_sound').attr('src',config.soundsPath+file+'.mp3');
			if($('#shout_sound').prop('paused')){
				$('#shout_sound').trigger('play');
			}else{
				$('#shout_sound').trigger('pause'); 
			}
		}
	}
	/** Move rules from top to selected box **/
	shoutbox.MoveRules = function(id,ancre){
		clearInterval(config.timerIn);
		config.timerIn = false;
		var cible = id.replace('rules_text','rules_view');
		$('#'+id).val($('#in_rules').val());
		$('#'+cible).html($('#rules_preview').html());
		$('#in_rules').val('');
		$('#rules_target').html('');
		$('#rules_preview').html('');
		$('html, body').animate({scrollTop: parseInt($('#'+ancre).offset().top)},'slow');
		$('input[name="button_lang"]').each(function(){
			$(this).show();
		});
	}
	/** Move rules from selected box to top  **/
	shoutbox.EditRules = function(id,title){
		var button = id.replace('rules_text','button');
		$('input[name="button_lang"]').each(function(){
			if($(this).attr('id') != button){
				$(this).hide();
			}
		});
		$('#in_rules').val($('#'+id).val()).focus();
		$('#'+id).html('');
		$('#rules_target').html(title);
		$('html,body').animate({scrollTop: $("#top_rules").offset().top},'slow');
	}
	/** Start the preview **/
	shoutbox.previewRules = function(texte){
		if(config.timerIn == false){
			config.timerIn = setInterval(shoutbox.previewRulesAjax, 5000);
		}
	}
	/** Refresh the preview  **/
	shoutbox.previewRulesAjax = function(){
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.previewAjax,
			data: 'user='+config.userId+'&sort=2'+'&content='+encodeURIComponent($('#in_rules').val())+'&timer='+config.timerIn,
			async: true,
			cache: false,
			success: function(update){
				$('#rules_preview').html(update.content);
			}
		});
	}
	/** Update the date format  **/
	shoutbox.changeDateFormat = function(value){
		$('#shout_dateformat2').css('background', 'white url('+config.imgPath+'"ajax_loader.gif") no-repeat 90% 50%');
		$('#shout_dateformat3').html('<img src="'+config.imgPath+'ajax_loader_2.gif" alt="loader" />');
		$('#shout_dateformat4').html('');
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.dateFormat,
			data: 'user='+config.userId+'&sort=2'+'&date='+value,
			async: true,
			cache: false,
			success: function(response){
				$('#shout_dateformat2').css('background','').val(response.format);
				$('#shout_dateformat3').html(response.date);
				$('#shout_dateformat4').html(response.date2);
			}
		});
	}
	/** Move smilies **/
	shoutbox.displaySmiley = function(smiley,sort){
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.displayAjax,
			data: 'user='+config.userId+'&sort=2'+'&smiley='+smiley+'&display='+sort,
			async: true,
			cache: false,
			success: function(update){
				var listeSmilies = '',listeSmiliesPop = '';
				for(var i = 0; i < update.total; i++){
					var smilie = update.smilies[i];
					listeSmilies += '<a onclick="shoutbox.displaySmiley('+smilie.id+',1);return false;" class="smilies-none" title="'+smilie.emotion+'">';
					listeSmilies += '<img src="'+update.url+smilie.image+'" alt="'+smilie.code+'" title="'+smilie.emotion+'" class="smilies" width="'+smilie.width+'" height="'+smilie.height+'"></a> ';
				}
				for(var j = 0; j < update.totalPop; j++){
					var smilie = update.smiliesPop[j];
					listeSmiliesPop += '<a onclick="shoutbox.displaySmiley('+smilie.id+',0);return false;" class="smilies-none" title="'+smilie.emotion+'">';
					listeSmiliesPop += '<img src="'+update.url+smilie.image+'" alt="'+smilie.code+'" title="'+smilie.emotion+'" class="smilies" width="'+smilie.width+'" height="'+smilie.height+'"></a> ';
				}
				$('#smil').html(listeSmilies);
				$('#smil_pop').html(listeSmiliesPop);
			}
		});
	}
	/** Construct color palets on div with special function **/
	shoutbox.colorPalette = function(target){
		if($('#shoutcolor_'+target).is(':hidden')){
			var r = 0, g = 0, b = 0, width = '15', height = '12', first = true, color = '', numberList = {0:'00',1:'40',2:'80',3:'BF',4:'FF'};
			var returnColor = '<div id="table-color-'+target+'">';
			for(r = 0; r < 5; r++){
				returnColor += '<div class="row-chars">';
				for(g = 0; g < 5; g++){
					for(b = 0; b < 5; b++){
						first = (g === 0 && b === 0) ? true : false;
						color = String(numberList[r])+String(numberList[g])+String(numberList[b]);
						returnColor += first ? '' : '<span class="cell-separate"></span>';
						returnColor += '<span name="'+target+'" class="cell-colors" style="background:#'+color+';" title="'+color+'"></span>';
					}
				}
				returnColor += '</div>';
				returnColor += '<div class="row-chars row-separate"></div>';
			}
			returnColor += '</div>';
			$('#shoutcolor_'+target).html(returnColor).show();
			$('#shoutcolor_'+target+' span.cell-colors').each(function(){
				$(this).on('click', function(){
					shoutbox.insertColor($(this).attr('title'),$(this).attr('name'));
				});
			});
		}else{
			$('#shoutcolor_'+target).html('').hide();
		}
	}
	/** Colorize target **/
	shoutbox.insertColor = function(color,target){
		$('#shout_color_'+target).css('color','#'+color).val(color);
		$('#color_'+target).css('color','#'+color);
		$('#span_color_'+target).css('background-color','#'+color);
		if(target == 'robot'){
			$('#shout_name_robot').css('color','#'+color);
		}
	}
})(jQuery);
