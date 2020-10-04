/**
* @package		Breizh Shoutbox extension
* @copyright(c)	2018-2020 Sylver35  https://breizhcode.com
* @license		http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/** global: config */
/** global: bzhLang */
/** global: shoutbox */
var tpl = new Array(),uastring = navigator.userAgent,index,navigateur,version,is_ie = ((uastring.indexOf('msie') != -1) && (uastring.indexOf('opera') == -1)),headersContent = {'Cache-Control': 'private, no-cache, no-store, must-revalidate, proxy-revalidate','Pragma': 'no-cache'};
var timerIn,timerOnline,onCount = 0,$queryNb = 0,first = true,form_name = 'postform',text_name = 'chat_message',imgChargeOn = '<img src="'+config.extensionUrl+'images/run.gif" alt="" style="margin-right:15px;" />',imgLoadOn = '<img src="'+config.extensionUrl+'images/run2.gif" alt="" style="margin-right:15px;" />',imgTurnOn = '<img src="'+config.extensionUrl+'images/spinner.gif" alt="" style="margin-right:15px;" />',ajaxLoaderOn = '<img src="'+config.extensionUrl+'images/ajax_loader_2.gif" alt="" style="margin-right:15px;" />',imgLoader = '<img src="'+config.extensionUrl+'images/ajax_loader.gif" alt="" style="margin-right:15px;" />';

(function($){  // Avoid conflicts with other libraries
	'use strict';

	shoutbox.onProgress = function(event){
		if(event.lengthComputable){
			shoutbox.iH('msg_txt','Progress: '+((event.loaded / event.total)*100)+'%',false);
			setTimeout(shoutbox.iH('msg_txt','',false),500);
		}
	};

	shoutbox.handle = function(e){
		var tmp;
		switch(e.name){
			case'E_USER_ERROR':
			case'E_CORE_ERROR':
				shoutbox.message(e.message,true,false,false);
			break;
			default:
				tmp = bzhLang['JS_ERR'];
				tmp += e.message;
				if(e.lineNumber){
					tmp += '\n'+bzhLang['LINE']+': ';
					tmp += e.lineNumber;
				}
				if(e.fileName){
					tmp += '\n'+bzhLang['FILE']+' : ';
					tmp += e.fileName;
				}
				shoutbox.message(tmp,true,false,false);
			break;
		}
		shoutbox.playSound(2,true);
		clearInterval(timerIn);
	};

	shoutbox.shoutInsertText = function(text,spaces){
		var textarea,form_name = 'postform',text_name = 'chat_message';
		textarea = document.forms[form_name].elements[text_name];
		if(spaces){
			text = ' '+text+' ';
		}
		if(!isNaN(textarea.selectionStart) && !is_ie){
			var sel_start = textarea.selectionStart;
			var sel_end = textarea.selectionEnd;
			mozWrap(textarea,text,'');
			textarea.selectionStart = sel_start+text.length;
			textarea.selectionEnd = sel_end+text.length;
		}else if(textarea.createTextRange && textarea.caretPos){
			if(baseHeight != textarea.caretPos.boundingHeight){
				textarea.focus();
				storeCaret(textarea);
			}
			var caret_pos = textarea.caretPos;
			caret_pos.text = (caret_pos.text.charAt(caret_pos.text.length-1) == ' ') ? caret_pos.text+text+' ' : caret_pos.text+text;
		}else{
			textarea.value = textarea.value+text;
		}
	};

	shoutbox.shoutPopup = function(popUrl,larg,haut,name){
		name = (name == '') ? '_popup' : name;
		window.open(popUrl.replace(/&amp;/g,'&'),name,'width='+larg+',height='+haut+',resizable=yes,toolbar=0,menubar=0,scrollbars=yes,statusbar=0,copyhistory=0,top=0,left=0');
		return false;
	};

	shoutbox.passwordSwitch = function(){
		if(!$('#passwordTxtShout').is(':visible')){
			$('#assist-icon-shout').removeClass('shout-icon-assist-hide').addClass('shout-icon-assist-show');
			$('#password_shout').addClass('off-screen').prop('aria-hidden', true).on('input', function(){$('#passwordTxtShout').val($('#password_shout').val());});
			$('#passwordTxtShout').val($('#password_shout').val()).show().prop('aria-hidden', false).on('input', function(){$('#password_shout').val($('#passwordTxtShout').val());});
			$('#assist-msg-shout').text(bzhLang['CACHE']);
			$('#assist-btn-shout').attr('title', bzhLang['CACHE']);
		}else{
			$('#assist-icon-shout').removeClass('shout-icon-assist-show').addClass('shout-icon-assist-hide');
			$('#passwordTxtShout').hide().prop('aria-hidden', true).off('input').off('input focus').off('blur');
			$('#password_shout').removeClass('off-screen').prop('aria-hidden', false).off('input', function(){$('#passwordTxtShout').val($('#password_shout').val());});
			$('#assist-msg-shout').text(bzhLang['AFFICHE']);
			$('#assist-btn-shout').attr('title', bzhLang['AFFICHE']);
		}
	};

	shoutbox.cookieShout = function(name,value,days){
		var expires = '';
		if(days){
			var date = new Date();
			date.setTime(date.getTime()+(days * 24 * 60 * 60 * 1000));
			expires = '; expires='+date.toGMTString();
		}
		document.cookie = config.cookieName+name+'='+shoutbox.encodeUtf8(value)+expires+config.cookieDomain+config.cookiePath;
	};

	shoutbox.getCookie = function(name){
		var nameEQ = config.cookieName+name+'=',ca = document.cookie.split(';');
		for(var i = 0; i < ca.length; i++){
			var c = ca[i];
			while(c.charAt(0) == ' '){
				c = c.substring(1,c.length);
			}
			if(c.indexOf(nameEQ) == 0){
				return shoutbox.decodeUtf8(c.substring(nameEQ.length,c.length));
			}
		}
		return false;
	};

	shoutbox.eraseCookie = function(name){
		shoutbox.cookieShout(name,'',-1);
	};

	shoutbox.goName = function(){
		$('#shoutnameyes').html(ajaxLoaderOn).show();
		var $username = $('#shoutname').val();
		if($username.length < config.minName || $username.length > config.maxName){
			setTimeout(shoutbox.goNameRed,1500);
		}else{
			shoutbox.cookieShout('shout-name',$username,60);
			setTimeout(shoutbox.goNameGreen,1500);
		}
	};

	shoutbox.goNameRed = function(){
		$('#shoutnameyes').html(bzhLang['USERNAME_EXPLAIN']).css({'color':'red','font-weight':'bold'});
	};

	shoutbox.goNameGreen = function(){
		$('#shoutnameyes').html(bzhLang['CHOICE_YES']).css({'color':'green','font-weight':'normal'});
	};

	shoutbox.createInput = function(sort){
		var css = sort ? 'shout-text-user' : 'inputbox',inputPost = shoutbox.cE('input','chat_message',css,'margin-'+config.direction+':6px;color:#9a9a9a;border-radius:3px;max-width:45%;width:'+config.widthPost,false,false,false,false);
		inputPost.name = 'chat_message';
		inputPost.spellcheck = true;
		inputPost.value = bzhLang['AUTO'];
		inputPost.onclick = function(){shoutbox.suppText('chat_message')};
		inputPost.onfocus = function(){shoutbox.suppText('chat_message')};
		inputPost.onblur = function(){shoutbox.addText('chat_message')};
		if(sort === false){
			inputPost.onkeypress = function(event){
				if(event.keyCode === 13){
					$('#postUser').click();
					event.returnValue = false;
					this.returnValue = false;
					return false;
				}
				return true;
			};
		}else{
			inputPost.onkeypress = function(event){
				if(event.keyCode === 13){
					$('#postAction').click();
					event.returnValue = false;
					this.returnValue = false;
					return false;
				}
				return true;
			};
		}
		return inputPost;
	};

	shoutbox.setRobot = function(){
		var value = shoutbox.getCookie('shout-robot'),setCookieBot = (value == '1') ? '0' : '1',onBot = (value == '1') ? 'on' : 'off',setBot = (value == '1') ? 'off' : 'on';
		shoutbox.cookieShout('shout-robot',setCookieBot,60);
		shoutbox.playSound(6,true);
		$('#onBot').val(setCookieBot);
		$('#iconBot').removeClass('button_shout_bot_'+onBot).addClass('button_shout_bot_'+setBot).attr('title', bzhLang['ROBOT_'+setBot.toUpperCase()]);
		onCount = 0;
		clearInterval(timerIn);
		shoutbox.loadMessages();
	};

	shoutbox.permutUser = function(sort){
		var inputPost = shoutbox.createInput(sort);
		$('#chat_message').remove();
		$('#span-post').remove();
		if(sort){
			var span = shoutbox.cE('span','span-post',false,'margin-'+config.direction+':6px;max-width:45%;display:inline-block;width:'+config.widthPost,false,false,false,false);
			$(inputPost).insertBefore('#postAction');
			$(span).insertBefore('#postUser');
		}else{
			$(inputPost).insertBefore('#postUser');
		}
	};

	shoutbox.message = function(msg, red, clearOn, reload){
		var colorMsg = red ? 'red' : 'green',tempsMsg = red ? 5000 : 3000,align = 'center',msgDisplay = '',span = '<span style="color:black;font-weight:bold;">',endSpan = ' : </span>',bR = '<br />';
		if(typeof msg === 'object'){
			msgDisplay = span+bzhLang['ERROR']+endSpan+msg['message'];
			msgDisplay += msg['line'] ? bR+span+bzhLang['LINE']+endSpan+msg['line'] : '';
			msgDisplay += msg['file'] ? bR+span+bzhLang['FILE']+endSpan+msg['file'] : '';
			msgDisplay += msg['content'] ? bR+span+bzhLang['DETAILS']+endSpan+msg['content'] : '';
			align = config.direction;
		}else{
			msgDisplay = msg;
		}
		if($('#msg_txt').length){
			$('#msg_txt').html('<p id="msg_p"></p>').show();
		}else{
			$('#shoutbox').html('<ul id="msg_txt" class="topiclist forums" style="height:40px;"><li id="msg_li" style="display:block;"><dl id="msg_dl" style="width:100%;"><dt id="msg_dt" class="row"><p id="msg_p"></p></dt></dl></li></ul>');
		}
		$('#msg_p').html(msgDisplay).css({'margin':'0.5em', 'text-align':align, 'color':colorMsg});
		if(clearOn){
			if(clearOn > 1){
				setTimeout(shoutbox.clearAfter, clearOn);
			}else{
				setTimeout(shoutbox.clearAfter, tempsMsg);
			}
		}
		if(reload){
			shoutbox.reloadAll(false);
		}
		$('#post_message').show();
	};

	shoutbox.reloadAll = function(timer){
		clearInterval(timerIn);
		if(timer){
			timerIn = setInterval(shoutbox.checkMessage, config.requestOn);
		}else{
			$('#openEdit').val(0);
			$('#nBErrors').val(0);
			shoutbox.setQuery();
			shoutbox.loadMessages();
		}
	};

	shoutbox.setError = function(nBn){
		nBn++;
		$('#nBErrors').val(nBn);
		if(nBn > 6){
			shoutbox.reloadAll(true);
		}
	};

	shoutbox.clearAfter = function(){
		shoutbox.iH('msg_txt','',2);
		$('#chat_message').css('background','');
	};

	shoutbox.cp = function(){
		return shoutbox.cE('span',false,'page-sep',false,false,false,bzhLang['SEPARATOR'],false);
	};

	shoutbox.cE = function(sort,id,className,cssText,title,type,innerHTML,alt){
		var onElement = document.createElement(sort);
		if(id){
			onElement.id = id;
		}
		if(className){
			onElement.className = className;
		}
		if(cssText){
			onElement.style.cssText = cssText;
		}
		if(title || title === ''){
			onElement.title = title;
		}
		if(type){
			onElement.type = type;
		}
		if(innerHTML){
			onElement.innerHTML = innerHTML;
		}
		if(alt){
			onElement.alt = alt;
		}
		return onElement;
	};

	shoutbox.cTN = function(e){
		return document.createTextNode(e);
	};

	shoutbox.iH = function(id,content,sort){
		if($('#'+id).length){
			if(sort !== false){
				shoutbox.sE(id,sort);
			}
			$('#'+id).html(content);
		}
	};

	shoutbox.sE = function(id,sort){
		if($('#'+id).length){
			switch(sort){
				case 1:
					$('#'+id).show();
				break;
				case 2:
					$('#'+id).hide();
				break;
				case 3:
					$('#'+id).css('display', 'inline');
				break;
				case 4:
					$('#'+id).css('display', 'inline-block');
				break;
			}
		}
	};

	shoutbox.setQuery = function(){
		$queryNb = 0;
		$('#nBQuery').val($queryNb);
	};

	shoutbox.trim = function(value){
		value = value.replace(/^\s+/,'').replace(/\s+$/,'');
		return value;
	};

	shoutbox.encodeUtf8 = function(string){
		return encodeURIComponent(string);
	};

	shoutbox.decodeUtf8 = function(string){
		return decodeURIComponent(string);
	};

	shoutbox.htmlEncode = function(value){
		return $('<div/>').text(value).html();
	};

	shoutbox.htmlDecode = function(value){
		return $('<div/>').html(value).text();
	};

	shoutbox.playSound = function(sort,force){
		var goSound = false;
		if(force){
			goSound = true;
		}else{
			if(config.isGuest){
				if(shoutbox.getCookie('shout-sound') == '1'){
					goSound = true;
				}
			}else if($('#onSound').val() == 1){
				goSound = true;
			}
		}
		if(goSound !== false){
			if($('#shoutAudio-'+sort).attr('title') !== 'off'){
				if($('#shoutAudio-'+sort).prop('paused')){
					$('#shoutAudio-'+sort).trigger('play');
				}else{
					$('#shoutAudio-'+sort).trigger('pause'); 
				}
			}
		}
	};

	shoutbox.shoutRules = function(){
		if(!$('#shout_rules').is(':visible')){
			$('#shout_rules').show();
		}
		$('#rules_on').html('<div style="text-align:center;">'+imgLoader+'</div>');
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.rulesUrl,
			data: 'user='+config.userId+'&sort='+config.sortShoutNb,
			cache: false,
			headers : headersContent,
			success: function(result){
				if(result.sort == 1){
					$('#rules_on').html(result.texte);
				}else{
					$('#rules_on').html('');
				}
			},
			error: function(){
				$('#shout_rules').hide();
				$('#rules_on').html('');
			}
		});
	};

	shoutbox.changePerso = function(idUser){
		$('#user_action').hide();
		$('#shout_bbcode').show();
		$('#button_shout_text').attr('title', bzhLang['BBCODE_CLOSE']);
		shoutbox.iH('shoutcheck',ajaxLoaderOn,false);
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.persoUrl,
			data: 'user='+idUser+'&sort='+config.sortShoutNb,
			cache: false,
			headers : headersContent,
			success: function(response){
				shoutbox.iH('shoutcheck','',false);
				if(response.error){
					shoutbox.message(response,true,5000,true);
					return;
				}
				if(response.id !== config.userId){
					response.name = bzhLang['CITE_ON']+': '+response.name;
					$('#user_inp_bbcode').val(response.id);
				}else{
					$('#user_inp_bbcode').val(0);
				}
				$('#h3userbbcode').html(response.name);
				$('#shout_text1').val(response.before);
				$('#shout_text2').val(response.after);
				$('#shoutexemple').html(response.message);
			},
			error: function(){
				$('#shout_bbcode').hide();
			}
		});
	};

	shoutbox.closePersoBbcode = function(){
		$('#shout_bbcode').hide();
		$('#user_inp_bbcode').val('');
		$('#h3userbbcode').html('');
		$('#shout_text1').val('');
		$('#shout_text2').val('');
		$('#shoutexemple').html('');
		$('#button_shout_text').attr('title',bzhLang['PERSO']);
	};

	shoutbox.deleteMessage = function(post_id){
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.deleteUrl,
			data: 'user='+config.userId+'&post='+post_id+'&sort='+config.sortShoutNb,
			cache: false,
			headers : headersContent,
			success: function(response){
				if(response.error){
					shoutbox.message(response,true,5000,true);
				}else if(response.type === 1){
					shoutbox.playSound(3,false);
					shoutbox.message(bzhLang['MSG_DEL_DONE'],false,800,true);
				}else if(response.type > 1){
					shoutbox.message(response.message,true,2000,true);
				}
			},
			error: function(){
				shoutbox.message(bzhLang['SERVER_ERR'],true,1,false);
			}
		});
	};

	shoutbox.purgeShout = function(purgeSort,robot){
		shoutbox.message(bzhLang['PURGE_PROCESS'],false,1,false);
		var ajaxUrl = robot ? config.purgeBotUrl : config.purgeUrl;
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: ajaxUrl,
			data: 'purge_sort='+purgeSort+'&sort='+config.sortShoutNb,
			cache: false,
			headers : headersContent,
			success: function(response){
				if(response.error){
					shoutbox.message(response,true,5000,true);
				}else if(response.type === 1){
					shoutbox.setQuery();
					shoutbox.playSound(3,false);
					var message = (response.nr > 1) ? bzhLang['MESSAGES'] : bzhLang['MESSAGE'];
					shoutbox.message(bzhLang['PURGE_PROCESS']+' - '+bzhLang['CITE_ON']+' '+response.nr+' '+message,false,1,false);
					$('#'+id).removeAttr('style');
					$('#shoutLast').val(0);
				}else if(response.type === 2){
					shoutbox.message(response.message,true,2000,true);
				}
			},
			error: function(){
				shoutbox.message(bzhLang['SERVER_ERR'],true,1,false);
			}
		});
	};

	shoutbox.suppText = function(sort){
		if($('#'+sort).val() == bzhLang['AUTO']){
			$('#'+sort).val('');
		}else{
			var onTextShout = $('#'+sort).val();
			onTextShout = onTextShout.replace(bzhLang['AUTO'],'');
			$('#'+sort).val(onTextShout);
		}
		$('#'+sort).css('color','black');
	};

	shoutbox.addText = function(sort){
		if($('#'+sort).val() == ''){
			$('#'+sort).val(bzhLang['AUTO']);
		}else if($('#'+sort).val() != bzhLang['AUTO']){
			var onTextShout = $('#'+sort).val();
			onTextShout = onTextShout.replace(bzhLang['AUTO'],'');
			$('#'+sort).val(onTextShout);
		}
		if($('#'+sort).val() == bzhLang['AUTO']){
			$('#'+sort).css('color','#9A9A9A');
		}else{
			$('#'+sort).css('color','black');
		}
	};

	shoutbox.closeAction = function(){
		shoutbox.permutUser(false);
		shoutbox.iH('h3user','',false);
		shoutbox.iH('shout_url','',false);
		shoutbox.iH('shout_avatar','',false)
		shoutbox.sE('user_action',2);
		$('#user_cite').val('');
		$('#user_inp').val('');
	};

	shoutbox.closeAll = function(){
		if($('#colour_shoutbox').length && $('#colour_shoutbox').is(':visible')){
			shoutbox.sE('colour_shoutbox',2);
			$('#color_shout1').attr('title', bzhLang['COLOR']);
		}
		if($('#shout_chars').length && $('#shout_chars').is(':visible')){
			shoutbox.sE('shout_chars',2);
			$('#chars01').attr('title', bzhLang['CHARS']);
		}
		if($('#shoutbox_posting').length && $('#shoutbox_posting').is(':visible')){
			shoutbox.sE('shoutbox_posting',2);
			$('#bbcodebutton').attr('title', bzhLang['BBCODES']);
		}
		if($('#shout_bbcode').length && $('#shout_bbcode').is(':visible')){
			shoutbox.sE('shout_bbcode',2);
			$('#button_shout_text').attr('title', bzhLang['PERSO']);
		}
	};

	shoutbox.onTime = function(){
		$queryNb++;
		$('#nBQuery').val($queryNb);
		var time = $queryNb * (config.requestOn / 1000),hours = Math.floor(time / 3600),minutes = Math.floor((time / 60) - (hours * 60)),seconds = time - (minutes * 60) - (hours * 3600),total;
		hours = hours ? ((hours < 10) ? '0'+hours+':' : hours+':') : '';
		minutes = (minutes < 10) ? '0'+minutes : minutes;
		seconds = (seconds < 10) ? '0'+seconds : seconds;
		total = hours+minutes+':'+seconds;
		$('#nBTemps').val(total);
		if(!config.sortPagin){
			$('#tempSpan').html(total);
		}
	};

	shoutbox.shoutReq = function(value1,value2,value3){
		if(($('#shout_text1').val() == '') && ($('#shout_text2').val() == '')){
			value1 = value2 = 1;
		}
		$('#shoutcheck').html(ajaxLoaderOn);
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.ubbcodeUrl,
			data: 'open='+shoutbox.encodeUtf8(value1)+'&close='+shoutbox.encodeUtf8(value2)+'&other='+value3+'&sort='+config.sortShoutNb,
			cache: false,
			headers : headersContent,
			success: function(response){
				$('#shoutcheck').html('<span id="shoutCheckSpan"></span>');
				if(response.error){
					shoutbox.message(response,true,5000,true);
					return;
				}
				var colorCheck = 'green';
				switch(response.type){
					case 1:
						$('#shout_text1').val('');
						$('#shout_text2').val('');
						$('#shoutexemple').html(response.text);
					break;
					case 2:
						colorCheck = 'red';
					break;
					case 3:
						$('#shout_text1').html(response.before);
						$('#shout_text2').html(response.after);
						$('#shoutexemple').html(response.text);
					break;
					case 4:
						colorCheck = 'blue';
						$('#shoutexemple').html(response.text);
					break;
					case 5:
						colorCheck = 'red';
					break;
				}
				$('#shoutCheckSpan').html(response.message).css('color',colorCheck);
			},
			error: function(){
				$('#shoutcheck').html('');
				shoutbox.message(bzhLang['SERVER_ERR'],true,1,false);
			}
		});
	};

	shoutbox.shoutOnline = function(){
		$('#online_shout').html(imgChargeOn).css('text-align','center');
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.onlineUrl,
			data: 'user='+config.userId+'&sort='+config.sortShoutNb,
			cache: false,
			headers : headersContent,
			success: function(response){
				$('#online_shout').html('<div id="online_shout1"></div><hr /><div id="online_shout2"></div>').css('text-align',config.direction);
				$('#online_shout1').html(response.title);
				$('#online_shout2').html(response.liste);
			},
			error: function(){
				clearTimeout(timerOnline);
				shoutbox.shoutOnline();
				timerOnline = setInterval(shoutbox.shoutOnline, 30000);
			}
		});
	};

	shoutbox.actionUser = function(id){
		if(isNaN(id)){
			shoutbox.message(bzhLang['SERVER_ERR'],true,1,false);
			return;
		}
		$('#user_shout').show();
		$('#user_action').show();
		$('#shout_url').attr('style','').show();
		shoutbox.sE('msg_user_shout',2);
		shoutbox.iH('shout_url',imgChargeOn,false);
		$('#user_cite').val('');
		$('#user_inp').val('');
		$('#shout_avatar').html('');
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.actUrl,
			data: 'user='+id+'&sort='+config.sortShoutNb,
			cache: false,
			headers : headersContent,
			success: function(response){
				if(response.error){
					shoutbox.message(response,true,5000,true);
					return;
				}
				if(response.t === 1){
					shoutbox.message(response,true,1,false);
					shoutbox.sE('user_action',2);
				}else if(response.type === 0){
					shoutbox.closeAction();
				}else if((typeof response.type !== undefined) && (typeof response.type !== 0)){
					if(response.type === 1){
						shoutbox.closeAction();
					}else if(response.type === 2){
						$('#h3user').html(response.username);
						$('#shout_url').html(response.message).css('color','red');
					}else if(response.type === 3){
						tpl = {'open':'<strong>&#187;</strong><span class="profile-shout">','close':'</span></a></span>','span':'<span title="">','return':'<br /><br />','a':'<a onmouseover="shoutbox.iH(\'onText\',this.title,false);" onmouseout="shoutbox.iH(\'onText\',\'\',false);" class="tooltip pointer"','ext':' onclick="window.open(this.href);return false;" href="'};
						$('#user_inp').val(response.id);
						$('#user_inp_sort').val(response.sort);
						$('#h3user').html(response.username);
						$('#shout_avatar').html('<span class="avatar-shout">'+response.avatar+'</span>');
						var content = '<br />';
						content += (response.foe) ? '<strong>&#187;</strong><span class="profile-shout" style="color:red">'+bzhLang['USER_IGNORE']+tpl['close']+tpl['return'] : '';
						content += (!response.foe && response.inp) ? tpl['open']+tpl['a']+response.url_message+tpl['close']+tpl['return'] : '';
						content += tpl['open']+tpl['a']+tpl['ext']+response.url_profile+tpl['close']+tpl['open']+tpl['a']+response.url_cite_m+tpl['close']+tpl['open']+tpl['a']+response.url_cite+tpl['close'];
						content += (response.retour) ? tpl['return'] : '';
						content += (response.url_admin) ? tpl['open']+tpl['a']+tpl['ext']+response.url_admin+tpl['close'] : '';
						content += (response.url_modo) ? tpl['open']+tpl['a']+tpl['ext']+response.url_modo+tpl['close'] : '';
						content += (response.url_ban) ? tpl['open']+tpl['a']+tpl['ext']+response.url_ban+tpl['close'] : '';
						content += (response.url_remove) ? tpl['return']+tpl['open']+tpl['a']+response.url_remove+tpl['close'] : '';
						content += (response.url_perso) ? tpl['return']+tpl['open']+tpl['a']+response.url_perso+tpl['close'] : '';
						content += '<br /><hr class="dotted" /><hr class="dotted" />';
						content += (response.inp) ? tpl['open']+tpl['a']+response.url_del_to+tpl['close']+tpl['return'] : '';
						content += (response.inp) ? tpl['open']+tpl['a']+response.url_del+tpl['close'] : '';
						content += (response.url_robot) ? tpl['return']+tpl['open']+tpl['a']+response.url_robot+tpl['close'] : '';
						$('#shout_url').html(content);
					}
				}else{
					shoutbox.sE('user_action',2);
					shoutbox.message(response,true,'',true);
				}
			},
			error: function(){
				shoutbox.sE('user_action',2);
				shoutbox.message(bzhLang['SERVER_ERR'],true,1,false);
			}
		});
	};

	shoutbox.replaceAll = function(str, find, replace){
		return str.replace(new RegExp(find, 'g'), replace);
	};

	shoutbox.sendUserAction = function(){
		if($('#chat_message').val() == '' || $('#chat_message').val() == bzhLang['AUTO']){
			alert(bzhLang['MESSAGE_EMPTY']);
			return;
		}else{
			$('#msg_txt').html(bzhLang['SENDING']);
			var ondata = 'user='+$('#user_inp').val()+'&pr='+$('#user_inp_sort').val()+'&sort='+config.sortShoutNb+'&message='+shoutbox.encodeUtf8($('#chat_message').val());
			shoutbox.permutUser(false);
			shoutbox.sE('msg_user_shout',2);
			shoutbox.sE('user_action',2);
			shoutbox.iH('shout_url',imgChargeOn,1);
			shoutbox.closeAll();
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: config.actPostUrl,
				data: ondata,
				cache: false,
				headers : headersContent,
				success: function(response){
					if(response.error){
						shoutbox.message(response,true,5000,true);
						return;
					}
					if(response.t == 1){
						shoutbox.message(response,true,'',true);
						shoutbox.permutUser(false);
					}else{
						$('#chat_message').val('');
						$('#user_inp').val('');
						if(response.type == 1){
							onCount = 0;
							shoutbox.playSound(4,false);
							shoutbox.message(bzhLang['POSTED'],false,800,true);
							shoutbox.setQuery();
							shoutbox.closeAction();
						}else if(response.type == 2){
							shoutbox.message(response.message,true,3000,true);
						}else{
							shoutbox.permutUser(false);
							$('#msg_txt').html('').hide();
						}
					}
				},
				error: function(){
					shoutbox.message(bzhLang['SERVER_ERR'],true,1,false);
				}
			});
		}
	};

	shoutbox.removeMsg = function(id){
		shoutbox.iH('shout_url',imgChargeOn,false);
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.actRemoveUrl,
			data: 'user='+id+'&pr='+$('#user_inp_sort').val()+'&sort='+config.sortShoutNb,
			cache: false,
			headers : headersContent,
			success: function(response){
				if(response.error){
					shoutbox.message(response,true,5000,true);
					return;
				}
				if(response.type == 1){
					shoutbox.setQuery();
					shoutbox.iH('shout_url',response.message,false);
				}else{
					shoutbox.sE('formuser',2);
				}
			},
			error: function(){
				shoutbox.iH('shout_url','',1);
				shoutbox.message(bzhLang['SERVER_ERR'],true,1,false);
			}
		});
	};

	shoutbox.delReqTo = function(id){
		shoutbox.iH('shout_url',imgChargeOn,false);
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.actDelToUrl,
			data: 'user='+id+'&pr='+$('#user_inp_sort').val()+'&sort='+config.sortShoutNb,
			cache: false,
			headers : headersContent,
			success: function(response){
				if(response.error){
					shoutbox.message(response,true,5000,true);
					return;
				}
				shoutbox.playSound(3,false);
				shoutbox.closeAction();
				shoutbox.message(response.message,false,1,true);
			},
			error: function(){
				shoutbox.closeAction();
				shoutbox.message(bzhLang['SERVER_ERR'],true,1,false);
			}
		});
	};

	shoutbox.delReq = function(id){
		shoutbox.iH('shout_url',imgChargeOn,false);
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.actDelUrl,
			data: 'user='+id+'&pr='+$('#user_inp_sort').val()+'&sort='+config.sortShoutNb,
			cache: false,
			headers : headersContent,
			success: function(response){
				if(response.error){
					shoutbox.message(response,true,5000,true);
					return;
				}
				shoutbox.playSound(3,false);
				shoutbox.closeAction();
				shoutbox.message(response.message,false,1,true);
			},
			error: function(){
				shoutbox.closeAction();
				shoutbox.message(bzhLang['SERVER_ERR'],true,1,false);
			}
		});
	};

	shoutbox.citeMsg = function(){
		onCount = 0;
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.citeUrl,
			data: 'user='+$('#user_inp').val()+'&sort='+config.sortShoutNb,
			cache: false,
			headers : headersContent,
			success: function(response){
				if(response.error){
					shoutbox.message(response,true,5000,true);
					return;
				}
				if(response.type === 0){
					shoutbox.message(response.message,true,1,false);
				}else if(response.type === 1){
					shoutbox.setQuery();
					shoutbox.closeAction();
					$('#user_cite').val(response.id);
					$('#chat_message').focus();
				}
			},
			error: function(){
				shoutbox.message(bzhLang['SERVER_ERR'],true,1,false);
			}
		});
	};

	shoutbox.citeMultiMsg = function(multiName,multiColor){
		if(multiColor){
			shoutbox.shoutInsertText('[b][color=#'+multiColor+']'+multiName+'[/color][/b]',true);
		}else{
			shoutbox.shoutInsertText('[b]'+multiName+'[/b]',true);
		}
		shoutbox.closeAction();
	};

	shoutbox.personalMsg = function(){
		onCount = 0;
		shoutbox.permutUser(true);
		$('#msg_user_shout').show();
		$('#user_cite').val('');
		$('#shout_url').html('');
		$('#shoutMsg').html('&nbsp;&nbsp;'+bzhLang['ACTION_MSG']+':');
		$('#chat_message').focus();
	};

	shoutbox.robotMsg = function(sortRobot){
		onCount = 0;
		shoutbox.permutUser(true);
		$('#msg_user_shout').show();
		shoutbox.sE('user_shout',2);
		shoutbox.iH('shoutMsg','&nbsp;&nbsp;'+bzhLang['MSG_ROBOT']+':',false);
		shoutbox.iH('h3user','',false);
		shoutbox.iH('shout_avatar','');
		$('#user_inp').val(1);
		$('#user_inp_sort').val(sortRobot);
		$('#chat_message').focus();
	};

	shoutbox.soundReq = function(){
		shoutbox.playSound(6,true);
		if(config.isGuest){
			if(shoutbox.getCookie('shout-sound') == '1'){
				var changec = '0',change = 0,soundClass = 'button_shout_sound_off',soundClassOut = 'button_shout_sound',title = bzhLang['SOUND'];
			}else{
				var changec = '1',change = 1,soundClass = 'button_shout_sound',soundClassOut = 'button_shout_sound_off',title = bzhLang['SOUND_OFF'];
			}
			shoutbox.cookieShout('shout-sound',changec,60);
			$('#onSound').val(change);
			$('#iconSound').removeClass(soundClassOut).addClass(soundClass).attr('title', title);
		}else{
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: config.soundUrl,
				data: 'sound='+$('#onSound').val()+'&user='+config.userId+'&sort='+config.sortShoutNb,
				cache: false,
				headers : headersContent,
				success: function(response){
					if(response.error){
						shoutbox.message(response,true,5000,true);
						return;
					}
					shoutbox.cookieShout('shout-sound',response.type,60);
					$('#onSound').val(response.type);
					$('#iconSound').removeClass(response.classOut).addClass(response.classIn).attr('title', response.title);
				},
				error: function(){
					shoutbox.message(bzhLang['SERVER_ERR'],true,1,false);
				}
			});
		}
	};

	shoutbox.runSmileys = function(smilSort,categorie){
		var urlSmil = smilSort ? config.smilUrl : config.smilPopUrl;
		$('#smilies').html('<div style="text-align:center;margin:25px auto;">'+imgLoadOn+bzhLang['LOADING']+'</div>').show();
		var dataOn = 'user='+config.userId+'&sort='+config.sortShoutNb+((categorie !== false) ? '&cat='+categorie : '');
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: urlSmil,
			data: dataOn,
			success: function(data){
				if(data.error){
					shoutbox.message(data,true,5000,true);
					shoutbox.sE('smilies_ul',2);
					$('#smilies').html('').hide();
					$('#iconSmilies').attr('title', bzhLang['SMILIES']);
					return;
				}
				var listeSmilies = '';
				if(typeof data.title !== 'undefined' && data.title){
					listeSmilies += '<h3 style="margin-top:2px;">'+data.title+'</h3>';
				}
				if(typeof data.emptyRow !== 'undefined' && data.emptyRow !== ''){
					listeSmilies += '<span style="color:red;font-weight:bold;">'+data.emptyRow+'</span>';
				}
				for(var i = 0; i < data.total; i++){
					var smilie = data.smilies[i];
					listeSmilies += '<a class="pointer" onclick="shoutbox.shoutInsertText(\''+smilie.code+'\',true);return false;" title="'+smilie.emotion+'">';
					listeSmilies += '<img class="smilies" src="'+data.url+smilie.image+'" alt="'+smilie.code+'" title="'+smilie.emotion+'" width="'+smilie.width+'" height="'+smilie.height+'"></a> ';
				}
				listeSmilies += '<div class="more-smiley"> ... ';
				if(data.nb_pop > 0 && smilSort){
					listeSmilies += '<a class="pointer tooltip" onclick="shoutbox.runSmileys(false,false);" style="margin:5px;" title="'+bzhLang['MORE_SMILIES_ALT']+'"><span title="">'+bzhLang['MORE_SMILIES']+'</span></a> ... ';
				}else if(!smilSort){
					listeSmilies += '<a class="pointer tooltip" onclick="shoutbox.runSmileys(true,false);" style="margin:5px;" title="'+bzhLang['LESS_SMILIES_ALT']+'"><span title="">'+bzhLang['LESS_SMILIES']+'</span></a> ... ';
				}
				if(config.creator){
					listeSmilies += '<a class="pointer tooltip" onclick="shoutbox.shoutPopup(config.creatorUrl,\'550\',\'570\',\'_phpbbsmiliescreate\');shoutbox.suppText(\'chat_message\');" style="margin: 5px;" title="'+bzhLang['CREATOR']+'"><span title="">'+bzhLang['CREATOR']+'</span></a> ... ';
				}
				if(config.category && typeof data.categories !== 'undefined'){
					listeSmilies += (data.title_cat !== 'undefined') ? '<h3 style="margin-top:8px;">'+data.title_cat+'</h3>' : '';
					for(var i = 0; i < data.categories.length; i++){
						var category = data.categories[i];
						var activeCat = (data.cat == category.cat_id) ? ' pagin_red' : '';
						listeSmilies += (i !== 0) ? ' - ' : '';
						listeSmilies += '<a class="pointer tooltip'+activeCat+'" onclick="shoutbox.runSmileys(false,'+category.cat_id+');" style="margin:5px;" title="'+category.cat_name+'"><span title="">'+category.cat_name+'</span></a>('+category.cat_nb+')';
					}
				}
				listeSmilies += '</div>';
				$('#smilies').html(listeSmilies);
			},
			error: function(){
				shoutbox.sE('smilies_ul',2);
				$('#smilies').html('').hide();
				$('#iconSmilies').attr('title', bzhLang['SMILIES']);
			}
		});
	};

	shoutbox.changePage = function(thisCount){
		shoutbox.setQuery();
		onCount = thisCount;
		$('#shout_messages').fadeOut(600,'linear').fadeIn(600,'linear');
		$('#msg_txt').hide();
		shoutbox.reloadAll(false);
	};

	shoutbox.openEdit = function(thisId){
		if($('#openEdit').val() == 1){
			shoutbox.playSound(2,true);
			alert(bzhLang['ONE_OPEN']);
			return;
		}
		clearInterval(timerIn);
		$('#openEdit').val(1);
		$('#post_message').show();
		$('#form'+thisId).css('padding', '0 0 5px 5px').show();
		shoutbox.sE('shout'+thisId,2);
		shoutbox.sE('editButton'+thisId,2);
		shoutbox.sE('infoButton'+thisId,2);
		shoutbox.sE('deleteButton'+thisId,2);
		shoutbox.sE('dtshout'+thisId,2);
		shoutbox.sE('ddshout'+thisId,2);
		$('#spa'+thisId).css('border', '0px none').html(bzhLang['SHOUT_EDIT']+': ');
		$('#input'+thisId).focus();
	};
	
	shoutbox.cancelMessage = function(thisId){
		$('#openEdit').val(0);
		$('#post_message').show();
		shoutbox.iH('spa'+thisId,'',false);
		shoutbox.sE('form'+thisId,2);
		shoutbox.sE('shout'+thisId,3);
		shoutbox.sE('editButton'+thisId,3);
		shoutbox.sE('dtshout'+thisId,3);
		shoutbox.sE('ddshout'+thisId,3);
		shoutbox.sE('infoButton'+thisId,3);
		shoutbox.sE('deleteButton'+thisId,3);
		shoutbox.closeAll();
		shoutbox.reloadAll(true);
	};

	shoutbox.editMessage = function(thisId,shoutId){
		shoutbox.sE('dtshout'+thisId,2);
		shoutbox.sE('ddshout'+thisId,2);
		shoutbox.sE('form'+thisId,2);
		shoutbox.iH('text'+thisId,bzhLang['SENDING_EDIT'],1);
		shoutbox.closeAll();
		var ondata = 'shout_id='+shoutId+'&sort='+config.sortShoutNb+'&chat_message='+shoutbox.encodeUtf8($('#input'+thisId).val());
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.editUrl,
			data: ondata,
			cache: false,
			headers : headersContent,
			success: function(response){
				if(response.error){
					shoutbox.message(response,true,5000,true);
					return;
				}else if(response.type === 1){
					shoutbox.message(response.message,true,2000,true);
				}else if(response.type === 2){
					shoutbox.playSound(5,false);
					$('#shout'+response.shout_id).html(response.texte);
					shoutbox.message(response.message,false,800,true);
				}
				$('#openEdit').val(0);
				shoutbox.setQuery();
				shoutbox.sE('msgbody'+response.shout_id,2);
				shoutbox.sE('shout'+response.shout_id,2);
				shoutbox.sE('editButton'+response.shout_id,3);
				shoutbox.sE('infoButton'+response.shout_id,3);
				shoutbox.sE('deleteButton'+response.shout_id,3);
				$('#post_message').show();
			}
		});
	};

	shoutbox.sendMessage = function(){
		if($('#chat_message').val() == bzhLang['AUTO'] || $('#chat_message').val() == ''){
			alert(bzhLang['MESSAGE_EMPTY']);
			return;
		}
		if(!config.limitPost && config.maxPost > 0){
			if($('#chat_message').val().length > config.maxPost){
				alert(bzhLang['TOO_BIG']+$('#chat_message').val().length+'\n'+bzhLang['TOO_BIG2']+config.maxPost);
				return;
			}
		}
		if(config.isGuest){
			if($('#shoutname').val() == ''){
				$('#shout_name').show();
				alert(bzhLang['CHOICE_NAME_ERROR']);
				return;
			}
			
		}
		shoutbox.setQuery();
		shoutbox.closeAll();
		shoutbox.iH('msg_txt','',false);
		$('#msg_txt').html(bzhLang['SENDING']);
		var ondata = 'user='+config.userId+'&sort='+config.sortShoutNb+'&chat_message='+shoutbox.encodeUtf8($('#chat_message').val());
		ondata += ($('#user_cite').val() !== '') ? '&cite='+$('#user_cite').val() : '&cite=0';
		if(config.isGuest){
			ondata += '&name='+shoutbox.encodeUtf8($('#shoutname').val());
		}
		$('#chat_message').attr('disabled', 'disabled').css('background', 'white url("'+config.extensionUrl+'images/ajax_loader.gif") no-repeat 90% 50%');
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.postUrl,
			data: ondata,
			cache: false,
			headers : headersContent,
			success: function(data){
				if(data.error){
					shoutbox.message(data,true,5000,true);
					return;
				}
				if(data.type == 2){
					shoutbox.message(data.message,true,2000,true);
				}else if(data.type == 1){
					shoutbox.message(data.message,false,800,true);
					shoutbox.playSound(4,false);
					$('#post_message').show();
					$('#user_cite').val('').attr('disabled');
					$('#user_inp').val('');
					$('#chat_message').val('');
				}else if(data.type == 10){
					shoutbox.message(data.message,true,1,true);
					shoutbox.playSound(2,false);
					$('#post_message').show();
					$('#user_cite').val('').attr('disabled');
					$('#user_inp').val('');
					$('#chat_message').val('');
				}else{
					shoutbox.reloadAll(false);
				}
				onCount = 0;
				$('#chat_message').removeAttr('disabled').focus();
			}
		});
	};

	shoutbox.setTimezone = function($timeOnMsg){
		var lastH = new Date($timeOnMsg * 1000),hour = lastH.getUTCHours(),minutes = lastH.getMinutes(),set12H = '';
		var operator = config.userTimezone.substring(0,1),zoneH = Number(config.userTimezone.substring(1,3)),zoneMin = Number(config.userTimezone.substring(4,6));
		var onHour = (operator == '+') ? hour + zoneH : hour - zoneH;
		minutes = minutes + zoneMin;
		if(minutes > 59){
			minutes = minutes - 60;
			onHour = onHour + 1;
		}
		if(config.dateFormat.indexOf('a') != -1){
			set12H = (onHour > 11) ? ' pm' : ' am';
			onHour = (onHour > 12) ? onHour - 12 : onHour;
		}else{
			onHour = (onHour < 10) ? '0'+onHour : onHour;
		}
		minutes = (minutes < 10) ? '0'+minutes : minutes;
		
		return onHour+':'+minutes+set12H;
	};

	shoutbox.refreshTime = function(){
		var isTime = Math.floor(new Date().getTime() / 1000),returnRefresh;
		$("#shout_messages span[name='time-shout']").each(function(){
			var $timeOnMsg = $(this).attr('time');
			if($timeOnMsg > (isTime - 23700)){
				var onMinute = Math.floor((isTime - $timeOnMsg) / 60);
				if(onMinute < 1){
					returnRefresh = bzhLang['DATETIME_0'];
				}else if(onMinute == 1){
					returnRefresh = bzhLang['DATETIME_1'].replace('%d',onMinute);
				}else if(onMinute > 1 && onMinute < 60){
					returnRefresh = bzhLang['DATETIME_2'].replace('%d',onMinute);
				}else if(onMinute >= 60){
					returnRefresh = bzhLang['DATETIME_3']+((config.dateFormat.indexOf(',') != -1) ? ', ' : ' ')+shoutbox.setTimezone($timeOnMsg);
				}
				$(this).html(returnRefresh);
			}
		});
	};

	shoutbox.checkMessage = function(){
		if($('#openEdit').val() == 1){
			clearInterval(timerIn);
			return;
		}
		if(config.sortShoutNb === 3 && !config.privOk){
			clearInterval(timerIn);
			return;
		}
		shoutbox.onTime();
		if(config.inactivity !== 0 && !config.isPriv){
			if($('#nBQuery').val() > config.inactivity){
				shoutbox.message(bzhLang['OUT_TIME'],true,false,false);
				clearInterval(timerIn);
				return;
			}
		}
		if(config.refresh){
			shoutbox.refreshTime();
		}
		var $onShoutLast = $('#shoutLast').val(),random = '?t='+Math.floor(Math.random() * 1000000);
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.checkUrl+random,
			data: 'sort='+config.sortShoutNb+'&on_bot='+$('#onBot').val(),
			cache: false,
			headers : headersContent,
			success: function(update){
				if(update.error){
					shoutbox.message(update,true,5000,false);
				}else if(update.t == 1){
					shoutbox.message(update,true,3000,false);
					shoutbox.reloadAll(true);
				}else if(update.t != $onShoutLast){
					if($onShoutLast != 0){
						/* A new message... */
						shoutbox.playSound(1,false);
					}
					$('#shoutLast').val(update.t);
					shoutbox.reloadAll(false);
				}
				/* else nothing to do, continue your work... */
			},
			error: function(update, statut, erreur){
				/* Just add nb errors and continue with silence */
				shoutbox.setError($('#nBErrors').val());
			}
		});
	};

	shoutbox.loadPagination = function(number){
		var totalPages = Math.ceil(number / config.perPage),onPage = Math.floor(onCount / config.perPage) + 1,direction = (config.direction == 'right') ? 'left' : 'right';
		if(totalPages < 2 || number < config.perPage){
			$('#divnr').hide();
		}else{
			$('#divnr').show();
			$('#linr').html('<span id="shout-pagin" class="shout-pagin"></span><span id="tempSpan" style="float:'+direction+';margin-'+direction+':5px;opacity:0.2;"></span>').show();
			var previousOn = shoutbox.cE('span',false,(onPage === 1) ? 'pagin_red' : 'pointer',false,bzhLang['PAGE']+'1',false,'1',false);
			if(onPage !== 1){
				previousOn.onclick = function(){shoutbox.changePage(0);};
				var previous = shoutbox.cE('span',false,'pointer',false,bzhLang['PREVIOUS'],false,bzhLang['PREVIOUS']+' ',false);
				previous.onclick = function(){shoutbox.changePage((onPage - 2) * config.perPage);};
				$(previous).appendTo($('#shout-pagin'));
			}
			$(previousOn).appendTo($('#shout-pagin'));
			var startCnt = Math.min(Math.max(1, onPage - 4),totalPages - 5),endCnt = (totalPages > 5) ? Math.max(Math.min(totalPages,onPage + 4),6) : totalPages;
			var startFor = (totalPages > 5) ? startCnt + 1 : 2,endFor = (totalPages > 5) ? endCnt - 1 : totalPages;
			$((startCnt > 1 && totalPages > 5) ? shoutbox.cTN(' ... ') : shoutbox.cp()).appendTo($('#shout-pagin'));
			for(var i = startFor; i < endCnt; i++){
				var nbOn = shoutbox.cE('span',false,(i === onPage) ? 'pagin_red' : 'pointer',false,bzhLang['PAGE']+i,false,i,false);
				if(i !== onPage){
					nbOn.c = (i - 1) * config.perPage;
					nbOn.onclick = function(){shoutbox.changePage(this.c);};
				}
				$(nbOn).appendTo($('#shout-pagin'));
				$((i < endFor) ? shoutbox.cp() : shoutbox.cTN('')).appendTo($('#shout-pagin'));
			}
			$((totalPages > 5) ? ((endCnt < totalPages) ? shoutbox.cTN(' ... ') : shoutbox.cp()) : shoutbox.cTN('')).appendTo($('#shout-pagin'));
			var nextOn = shoutbox.cE('span',false,(onPage === totalPages) ? 'pagin_red' : 'pointer',false,bzhLang['PAGE']+totalPages,false,totalPages,false),next = shoutbox.cE('span',false,false,false,false,false,false,false);
			if(onPage !== totalPages){
				nextOn.onclick = function(){shoutbox.changePage((totalPages - 1) * config.perPage);};
				next = shoutbox.cE('span',false,'pointer',false,bzhLang['NEXT'],false,' '+bzhLang['NEXT'],false);
				next.onclick = function(){shoutbox.changePage(onPage * config.perPage);};
			}
			$(nextOn).appendTo($('#shout-pagin'));
			$(next).appendTo($('#shout-pagin'));
		}
	};

	shoutbox.loadMessages = function(){
		if($('#openEdit').val() == 1){
			clearInterval(timerIn);
			return;
		}
		/* In case of auth modifications */
		if(config.sortShoutNb === 3 && !config.privOk){
			return;
		}
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.viewUrl,
			data: 'start='+onCount+'&l='+$('#shoutLast').val()+'&sort='+config.sortShoutNb+'&on_bot='+$('#onBot').val(),
			cache: false,
			headers : headersContent,
			success: function(datas){
				if(datas.error){
					shoutbox.message(datas,true,'',true);
					return;
				}else if(datas.last === 1){
					shoutbox.message(datas,true,1,false);
					shoutbox.iH('shout_messages',false);
					return;
				}
				$('#shout_messages').html('');
				if(datas.total === 0){
					if(config.postOk && first){
						$('#post_message').show();
						first = false;
					}
					$('#shout_messages').append('<div class="shout_centered">'+bzhLang['NO_MESSAGE']+'</div>');
					return;
				}
				if(!config.isRobot){
					shoutbox.loadPagination(datas.number);
				}
				$('#shoutLast').val(datas.last);
				var nowTime = Math.floor(new Date().getTime() / 1000),row = 1,i = 0;
				var okDelete = okEdit = okInfo = false;

				if(config.toBottom){
					/* Loop for messages from top to bottom */
					var listMessages = datas.messages;
				}else{
					/* Loop for messages from bottom to top */
					var listMessages = datas.messages.reverse();
				}

				/* Loop for messages */
				for(var i = 0; i < datas.total; i++){
					var post = listMessages[i];
					var okDelete = post.deletemsg,okEdit = post.edit,okInfo = post.showIp,okCite = (post.isUser && config.postOk && config.isUser && config.buttonCite) ? true : false;
					var li = shoutbox.cE('li','lishout'+i,'row row'+row+' bg'+row,false,false,false,false,false);
					var dl = shoutbox.cE('dl','dlshout'+i,false,false,false,false,false,false);
					row = (row === 1) ? 2 : 1;
					/* Construct the buttons : delete - edit - ip - cite */
					var deleteButton = shoutbox.cE('span','deleteButton'+i,false,false,false,false,false,false),editButton = shoutbox.cE('span','editButton'+i,false,false,false,false,false,false),infoButton = shoutbox.cE('span','infoButton'+i,false,false,false,false,false,false),citeButton = shoutbox.cE('span','citeButton'+i,false,false,false,false,false,false);
					if(okDelete){
						deleteButton = shoutbox.cE('input','deleteButton'+i,'button_shout_del button_shout_l',false,bzhLang['SHOUT_DEL'],'button',false,false);
						deleteButton.shoutId = post.shoutId;
						deleteButton.onclick = function(){
							if(confirm(bzhLang['DEL_SHOUT']+' message '+this.shoutId)){
								shoutbox.deleteMessage(this.shoutId);
							}
						};
					}else if(config.buttonsLeft){
						deleteButton = shoutbox.cE('input','deleteButton'+i,'button_shout_del_no button_shout_l',false,bzhLang['NO_SHOUT_DEL'],'button',false,false);
						deleteButton.onclick = function(){alert(bzhLang['NO_SHOUT_DEL'])};
					}
					if(okEdit){
						editButton = shoutbox.cE('input','editButton'+i,'button_shout_edit button_shout_l',false,bzhLang['SHOUT_EDIT'],'button',false,false);
						editButton.i = i;
						editButton.onclick = function(){shoutbox.openEdit(this.i)};
						var msg3 = shoutbox.cE('span','text'+i,'span-text-edit',false,false,false,false,false);
						var editForm = shoutbox.cE('form','form'+i,false,'display:none;',false,false,false,false);
						editForm.spellcheck = true;
						editForm.onsubmit = function(){return false};
						var spa = shoutbox.cE('span','spa'+i,false,false,false,false,false,false);
						var inputEdit = shoutbox.cE('input','input'+i,'input-text-edit',false,false,false,false,false);
						inputEdit.value = shoutbox.htmlDecode(post.msgPlain);
						inputEdit.i = i;
						inputEdit.onkeypress = function(event){
							if(event.keyCode == 13){
								$('#submit'+this.i).click();
								event.returnValue = false;
								this.returnValue = false;
								return false;
							}
						}
						var buttonEdit = shoutbox.cE('input','submit'+i,'button btnmain','',bzhLang['SHOUT_EDIT'],'button',false,false);
						buttonEdit.i = i;
						buttonEdit.shoutId = post.shoutId;
						buttonEdit.value = bzhLang['EDIT'];
						buttonEdit.onclick = function(){shoutbox.editMessage(this.i,this.shoutId)};
						var buttonCancel = shoutbox.cE('input',false,'button btnmain',false,bzhLang['CANCEL'],'button',false,false);
						buttonCancel.i = i;
						buttonCancel.value = bzhLang['CANCEL'];
						buttonCancel.onclick = function(){shoutbox.cancelMessage(this.i)};
					}else if(config.buttonsLeft){
						editButton = shoutbox.cE('input','editButton'+i,'button_shout_edit_no button_shout_l',false,bzhLang['NO_EDIT'],'button',false,false);
						editButton.onclick = function(){alert(bzhLang['NO_EDIT'])};
					}
					if(config.buttonIp){
						if(okInfo){
							infoButton = shoutbox.cE('input','infoButton'+i,'button_shout_ip button_shout_l',false,bzhLang['IP'],'button',false,false);
							infoButton.ip = post.shoutIp;
							infoButton.onclick = function(){alert(bzhLang['POST_IP']+'  '+this.ip)};
						}else if(config.buttonsLeft){
							infoButton = shoutbox.cE('input','infoButton'+i,'button_shout_ip_no button_shout_l',false,bzhLang['NO_IP_PERM'],'button',false,false);
							infoButton.onclick = function(){alert(bzhLang['NO_IP_PERM'])};
						}
					}
					if(okCite){
						citeButton = shoutbox.cE('input','citeButton'+i,'button_shout_cite button_shout_l',false,bzhLang['ACTION_CITE'],'button',false,false);
						citeButton.name = post.name;
						citeButton.colour = post.colour
						citeButton.onclick = function(){shoutbox.citeMultiMsg(this.name, this.colour)};
					}
					if(!okInfo && !okEdit && !okDelete && !okCite && !config.buttonsLeft){
						var dtt = shoutbox.cE('dt',false,false,'padding:0;display:inline;float:'+config.direction,false,false,false,false);
					}else{
						var dtt = shoutbox.cE('dt','dtshout'+i,'button_background'+config.endClassBg+' dtshout'+config.direction,false,false,false,false);
					}
					dtt.appendChild(deleteButton);
					dtt.appendChild(editButton);
					dtt.appendChild(infoButton);
					dtt.appendChild(citeButton);
					dl.appendChild(dtt);

					var spanNow = (post.timeMsg > (nowTime - 3700)) ? '<span name="time-shout" time="'+post.timeMsg+'">'+post.shoutTime+'</span>' : '<span name="no-time-shout">'+post.shoutTime+'</span>';
					var onAvatar = (post.avatar && post.avatar.length > 1) ? bzhLang['SHOUT_SEP']+'<span class="avatar-shout">'+post.avatar+'</span>' : '';
					var ddd = shoutbox.cE('dd','ddshout'+i,false,'width:auto',false,false,spanNow+onAvatar+bzhLang['SHOUT_SEP']+post.username+':',false);
					var dd = shoutbox.cE('dd','msgbody'+i,'msgbody'+config.direction,false,false,false,false,false);

					dd.appendChild(shoutbox.cE('span','shout'+i,'msg_shout',false,false,false,post.shoutText,false));
					if(okEdit){
						editForm.appendChild(spa);
						editForm.appendChild(inputEdit);
						editForm.appendChild(buttonEdit);
						editForm.appendChild(buttonCancel);
						dd.appendChild(editForm);
						dd.appendChild(msg3);
					}
					dl.appendChild(ddd);
					dl.appendChild(dd);
					li.appendChild(dl);
					$('#shout_messages').append(li);
				}
				if(config.toBottom){
					$('#shout_messages').scrollTop(0);
				}else{
					$('#shout_messages').scrollTop($('#shout_messages').prop('scrollHeight'));
				}
			},
			error: function(){
				clearInterval(timerIn);
				shoutbox.loadMessages();
				return;
			}
		});
		timerIn = setInterval(shoutbox.checkMessage, config.requestOn);
	};

	shoutbox.writeShoutbox = function(){
		try{
			if(config.sortShoutNb === 3 && !config.privOk){
				return;
			}

			$('#shout-1').html('<i class="icon fa-commenting fa-fw" aria-hidden="false"></i><a href="'+config.titleUrl+'" onclick="window.open(this.href);return false" title="'+bzhLang['TITLE']+'">'+bzhLang['TITLE']+'</a>').removeClass('shout-left-dt').addClass('shout-'+config.direction+'-dt');
			$('#shout-2').html(bzhLang['PRINT_VER']+'<i class="icon fa-info fa-fw" aria-hidden="false"></i>').removeClass('shout-left-dd').addClass('shout-'+config.direction+'-dd');
			$('#sortShoutNb').val(config.sortShoutNb);
			$('#onSound').val(config.enableSound);

			/* Load the cookies */
			if(config.isGuest){
				if(shoutbox.getCookie('shout-sound') === false){
					shoutbox.cookieShout('shout-sound',config.enableSound,60);
				}else{
					$('#onSound').val(shoutbox.getCookie('shout-sound'));
				}
				if(shoutbox.getCookie('shout-name') !== false){
					$('#shoutname').val(shoutbox.getCookie('shout-name'));
				}
			}
			if(shoutbox.getCookie('shout-robot') === false){
				shoutbox.cookieShout('shout-robot','1',60);
			}else{
				$('#onBot').val(shoutbox.getCookie('shout-robot'));
			}

			var shoutBarCss = (!config.barHaute) ? 'border-top:1px solid #00608F;' : 'border-bottom:1px solid #00608F;';
			var postingCssText = 'display:inline-block;padding:3px 0 3px 1px;vertical-align:middle;width:100%;';
			var postingStyle = 'height:auto;width:100%;overflow-wrap:break-word;'+(config.sortPagin ? 'margin-top:1px;float:'+config.direction : '');
			if(!config.postOk){
				shoutBarCss += (!config.sortPagin) ? 'text-align:center;padding:3px;border-bottom:1px solid #00608F;' : 'text-align:center;padding:3px;border-bottom:1px solid #00608F;';
				postingCssText = (!config.sortPagin) ? 'float:none;width:100%;' : 'padding-left:60px;float:'+config.direction+';width:100%;';
			}
			var base = shoutbox.cE('ul','base_ul','topiclist forums',false,false,false,false,false);
			var li = shoutbox.cE('li','shoutbar','button_background '+config.buttonBg,shoutBarCss,false,false,false,false);
			var dl = shoutbox.cE('dl','shoutdl',false,'width:100%;',false,false,false,false);
			var postingForm = shoutbox.cE('dt','post_message',false,postingCssText,false,false,false,false);
			var postingBox = shoutbox.cE('div','postingBox',false,postingStyle,false,false,false,false);

			var spanAudio = shoutbox.cE('span','audioShout','no_display',false,false,false,false,false);
			// 1 : New message, 2 : Error (default), 3 : Delete, 4 : Add message, 5 : Edit message, 6 : special auto sound
			var listSounds = [[1,'new',config.newSound],[2,'error',config.errorSound],[3,'del',config.delSound],[4,'add',config.addSound],[5,'edit',config.editSound],[6,'auto','discretion']];
			for(var i = 0; i < listSounds.length; i++){
				var sound = listSounds[i];
				var lecteur = shoutbox.cE('audio','shoutAudio-'+sound[0],false,false,sound[1],'audio/mpeg',false,false);
				if(sound[2] !== '1'){
					lecteur.src = config.extensionUrl+'sounds/'+sound[2]+'.mp3';
					lecteur.preload = 'auto';
				}else{
					lecteur.title = 'off';
				}
				spanAudio.appendChild(lecteur);
			}

			var activeSound = ($('#onSound').val() == 1) ? true : false,soundCss = activeSound ? '' : '_off',soundTitle = activeSound ? bzhLang['SOUND_OFF'] : bzhLang['SOUND'];
			var buttonSound = shoutbox.cE('input','iconSound','button_shout_sound'+soundCss+' button_shout','',soundTitle,'button',false,false);
			buttonSound.onclick = function(){shoutbox.soundReq()};
			var cssBot = ($('#onBot').val() == 1) ? 'on' : 'off',botTitle = bzhLang['ROBOT_'+cssBot.toUpperCase()];
			var buttonBot = shoutbox.cE('input','iconBot','button_shout_bot_'+cssBot+' shout_bot button_shout','',botTitle,'button',false,false);
			buttonBot.onclick = function(){shoutbox.setRobot()};

			if(!config.postOk){
				var printPermTitle = config.isGuest ? bzhLang['CLICK_HERE'] : bzhLang['NO_POST_PERM'];
				var printPerm = shoutbox.cE('a','printPerm','pointer',false,printPermTitle,false,printPermTitle,false);
				if(config.isGuest){
					printPerm.onclick = function(){
						if($('#shout_connect').is(':hidden')){
							$('#shout_connect').show();
							$('#printPerm').attr('title', bzhLang['DIV_CLOSE']);
						}else{
							$('#shout_connect').hide();
							$('#printPerm').attr('title', bzhLang['CLICK_HERE']);
						}
					};
				}
				if(config.sortPagin){
					var pagin = shoutbox.cE('dd','linr','pagination gensmall','margin-top:0px;padding:1px 0;height:auto;width:auto;float:right;border-left:0 none;',false,false,false,false);
					dl.appendChild(pagin);
				}
				postingForm.appendChild(postingBox);
				dl.appendChild(postingForm);
				li.appendChild(dl);
				base.appendChild(li);
				postingBox.appendChild(printPerm);
			}else{
				// Create the posting bar now
				var inputPost = shoutbox.createInput(false);
				postingBox.appendChild(inputPost);
				var postUser = shoutbox.cE('input','postUser','button btnmain','margin-'+config.direction+':6px;border-radius:4px;line-height:1.3;',bzhLang['POST_MESSAGE_ALT'],'button',false,false);
				postUser.value = bzhLang['POST_MESSAGE'];
				postUser.onclick = function(){shoutbox.sendMessage()};
				postingBox.appendChild(postUser);
				if(config.smiliesOk){
					var smiliesInput = shoutbox.cE('input','iconSmilies','button_shout_smile button_shout','margin-'+config.direction+':4px;',bzhLang['SMILIES'],'button',false,false);
					smiliesInput.onclick = function(){
						if($('#smilies').is(':visible')){
							$('#smilies_ul').hide();
							$('#smilies').html('').hide();
							$('#iconSmilies').attr('title', bzhLang['SMILIES']);
							shoutbox.addText('chat_message');
						}else{
							$('#iconSmilies').attr('title', bzhLang['SMILIES_CLOSE']);
							$('#smilies_ul').show();
							shoutbox.suppText('chat_message');
							shoutbox.runSmileys(true,false);
						}
					};
					postingBox.appendChild(smiliesInput);
				}else if(config.seeButtons){
					var smiliesInput = shoutbox.cE('input',false,'button_shout_smile_no button_shout','',bzhLang['NO_SMILIES'],'button',false,false);
					smiliesInput.onclick = function(){alert(bzhLang['NO_SMILIES'])};
					postingBox.appendChild(smiliesInput);
				}
				if(config.colorOk){
					var colored = shoutbox.cE('input','color_shout1','button_shout_color button_shout','',bzhLang['COLOR'],'button',false,false);
					colored.onclick = function(){
						if($('#colour_shoutbox').is(':visible')){
							$('#colour_shoutbox').hide();
							$('#color_shout1').attr('title', bzhLang['COLOR']);
							shoutbox.addText('chat_message');
						}else{
							$('#colour_shoutbox').show();
							$('#color_shout1').attr('title', bzhLang['COLOR_CLOSE']);
							shoutbox.suppText('chat_message');
						}
					};
					postingBox.appendChild(colored);
				}else if(config.seeButtons){
					var colored = shoutbox.cE('input','color_shout1','button_shout_color_no button_shout','',bzhLang['NO_COLOR'],'button',false,false);
					colored.onclick = function(){alert(bzhLang['NO_COLOR'])};
					postingBox.appendChild(colored);
				}
				if(config.charsOk){
					var chars = shoutbox.cE('input','chars01','button_shout_chars button_shout','',bzhLang['CHARS'],'button',false,false);
					chars.onclick = function(){
						if($('#shout_chars').is(':visible')){
							$('#shout_chars').hide();
							$('#char_shout1').html('');
							$('#chars01').attr('title', bzhLang['CHARS']);
						}else{
							var nbCols = (config.isMobile) ? 25 : ((config.sortShoutNb === 1) ? 29 : 38);
							$('#shout_chars').show();
							$('#char_shout1').html(shoutbox.specialCharShout(nbCols));
							shoutbox.mouseChar();
							$('#chars01').attr('title', bzhLang['CHARS_CLOSE']);
						}
					};
					postingBox.appendChild(chars);
				}else if(config.seeButtons){
					var chars = shoutbox.cE('input','chars01','button_shout_chars_no button_shout','',bzhLang['NO_CHARS'],'button',false,false);
					chars.onclick = function(){alert(bzhLang['NO_CHARS'])};
					postingBox.appendChild(chars);
				}
				if(config.bbcodeOk){
					var bbcode = shoutbox.cE('input','bbcodebutton','button_shout_img button_shout','',bzhLang['BBCODES'],'button',false,false);
					bbcode.onclick = function(){
						if($('#shoutbox_posting').is(':visible')){
							$('#shoutbox_posting').hide();
							$('#bbcodebutton').attr('title', bzhLang['BBCODES']);
						}else{
							$('#shoutbox_posting').show();
							$('#bbcodebutton').attr('title', bzhLang['BBCODES_CLOSE']);
						}
					};
					postingBox.appendChild(bbcode);
				}else if(config.seeButtons){
					var bbcode = shoutbox.cE('input','bbcodebutton','button_shout_img_no button_shout','',bzhLang['NO_BBCODES'],'button',false,false);
					bbcode.onclick = function(){alert(bzhLang['NO_BBCODE'])};
					postingBox.appendChild(bbcode);
				}
				if(config.sortShoutNb !== 1){
					if(config.popupOk){
						var popup = shoutbox.cE('input',false,'button_shout_popup button_shout','',bzhLang['POP'],'button',false,false);
						popup.onclick = function(){shoutbox.shoutPopup(config.popupUrl,config.popupWidth,config.popupHeight,'_popup');return false;};
					}else if(config.seeButtons){
						var popup = shoutbox.cE('input',false,'button_shout_popup_no button_shout','',bzhLang['NO_POP'],'button',false,false);
						popup.onclick = function(){alert(bzhLang['NO_POP'])};
					}
					postingBox.appendChild(popup);
					if(config.purgeOn){
						var purgeRobot = shoutbox.cE('input','purgeRobot','button_shout_robot button_shout','',bzhLang['PURGE_ROBOT_ALT'],'button',false,false);
						purgeRobot.onclick = function(){if(confirm(bzhLang['PURGE_ROBOT_BOX'])){shoutbox.purgeShout('purge_robot'+(config.isPriv ? '_priv' : ''),true)}};
						var purge = shoutbox.cE('input','purge','button_shout_purge button_shout','',bzhLang['PURGE_ALT'],'button',false,false);
						purge.onclick = function(){if(confirm(bzhLang['PURGE_BOX'])){shoutbox.purgeShout('purge'+(config.isPriv ? '_priv' : ''),false);}};
						postingBox.appendChild(purgeRobot);
						postingBox.appendChild(purge);
					}
				}
				if(config.isUser){
					if(config.sortShoutNb !== 3 && config.privOk){
						var priv = shoutbox.cE('input',false,'button_shout_priv button_shout','',bzhLang['PRIV'],'button',false,false);
						priv.onclick = function(){window.open(config.privUrl)};
						postingBox.appendChild(priv);
					}
					if(config.formatOk){
						var button_text = shoutbox.cE('input','button_shout_text','button_shout_text button_shout','',bzhLang['PERSO'],'button',false,false);
						button_text.onclick = function(){
							if($('#shout_bbcode').is(':visible')){
								shoutbox.closePersoBbcode();
							}else{
								shoutbox.changePerso(config.userId);
							}
						};
						postingBox.appendChild(button_text);
					}
					var button_config = shoutbox.cE('input',false,'button_shout_config button_shout','',bzhLang['CONFIG_OPEN'],'button',false,false);
					button_config.onclick = function(){shoutbox.shoutPopup(config.configUrl,'850','500','_popup')};
					postingBox.appendChild(button_config);
				}
				if(config.rulesOk){
					var rulesTitle = config.isPriv ? bzhLang['RULES_PRIV'] : bzhLang['RULES'],rulesTitleOn = config.rulesOpen ? bzhLang['RULES_CLOSE'] : rulesTitle;
					var div_rules = shoutbox.cE('input','buttonRules','button_shout_rules button_shout','',rulesTitleOn,'button',false,false);
					div_rules.onclick = function(){
						if($('#shout_rules').is(':visible')){
							$('#rules_on').html('');
							$('#shout_rules').hide();
							$('#buttonRules').attr('title', rulesTitle);
						}else{
							shoutbox.shoutRules();
							$('#buttonRules').attr('title', bzhLang['RULES_CLOSE']);
						}
					};
					postingBox.appendChild(div_rules);
				}
				if(config.onlineOk){
					var button_online = shoutbox.cE('input','buttonOnline','button_shout_online button_shout','',bzhLang['ONLINE'],'button',false,false);
					button_online.onclick = function(){
						if($('#shout_online').is(':visible')){
							$('#shout_online').hide();
							$('#buttonOnline').attr('title', bzhLang['ONLINE']);
							clearTimeout(timerOnline);
						}else{
							$('#shout_online').show();
							$('#buttonOnline').attr('title', bzhLang['ONLINE_CLOSE']);
							shoutbox.shoutOnline();
							timerOnline = setInterval(shoutbox.shoutOnline, 30000);
						}
					};
					postingBox.appendChild(button_online);
				}
				if(config.isGuest){
					var buttonConnect = shoutbox.cE('input','iconConnect','button_shout_connect button_shout','',bzhLang['CLICK_HERE'],'button',false,false);
					buttonConnect.onclick = function(){
						if($('#shout_connect').is(':hidden')){
							$('#shout_connect').show();
							$('#iconConnect').attr({'class':'button_shout_connect_on button_shout', 'title':bzhLang['DIV_CLOSE']});
						}else{
							$('#shout_connect').hide();
							$('#iconConnect').attr({'class':'button_shout_connect button_shout', 'title':bzhLang['CLICK_HERE']});
						}
					};
					var button_name = shoutbox.cE('input','iconName','button_shout_name button_shout','',bzhLang['CHOICE_NAME'],'button',false,false);
					button_name.onclick = function(){
						if($('#shout_name').is(':hidden')){
							$('#shout_name').show();
							$('#iconName').attr({'class':'button_shout_name_on button_shout', 'title':bzhLang['DIV_CLOSE']});
						}else{
							$('#shout_name').hide();
							$('#iconName').attr({'class':'button_shout_name button_shout', 'title':bzhLang['CHOICE_NAME']});
						}
						shoutbox.sE('shoutnameyes',2);
					};
					postingBox.appendChild(buttonConnect);
					postingBox.appendChild(button_name);
				}
				// End of create posting bar

				if(config.barHaute){
					postingForm.appendChild(postingBox);
					dl.appendChild(postingForm);
					li.appendChild(dl);
					base.appendChild(li);
				}
				if(config.barHaute && config.sortPagin){
					var pagin = shoutbox.cE('dd','linr','pagination gensmall','margin-top:0px;padding:4px;float:right;height:auto;width:auto;border:0;',false,false,false,false);
					dl.appendChild(pagin);
				}
				if($('#abbc3_buttons').length){
					$('#abbc3_buttons').css('margin', '0');
				}
			}
			postingBox.appendChild(buttonSound);
			postingBox.appendChild(buttonBot);
			postingBox.appendChild(spanAudio);
			var msg_txt = shoutbox.cE('div','msg_txt',false,'text-align:center;',false,false,false,false);
			base.appendChild(msg_txt);

			var divPosts = shoutbox.cE('div','shout_messages',false,'display:block;width:100%;overflow-x:hidden;overflow-y:scroll;position:relative;height:'+config.shoutHeight+'px;',false,false,'<div style="text-align:center;margin:50px auto;">'+imgChargeOn+bzhLang['LOADING']+'</div>',false);
			base.appendChild(divPosts);

			if(!config.barHaute && config.postOk){
				postingForm.appendChild(postingBox);
				dl.appendChild(postingForm);
				if(config.sortPagin){
					var pagin = shoutbox.cE('dd','linr','pagination','margin:0 0 3pt 0;padding:8pt 4pt 4pt;height:auto;width:auto;vertical-align:middle;backgroundImage:none;float:right;border-left:0;',false,false,false,false);
					dl.appendChild(pagin);
				}
				li.appendChild(dl);
				base.appendChild(li);
			}
			if(!config.sortPagin || !config.sortPagin && !config.postOk){
				var pagindiv = shoutbox.cE('div','divnr',false,false,false,false,false,false);
				var paginul = shoutbox.cE('ul','ulnr','topiclist forums','margin:0;',false,false,false,false);
				var paginli = shoutbox.cE('li','linr','pagination button_background '+config.buttonBg,'text-align:'+config.direction+';',false,false,false,false);
				paginul.appendChild(paginli);
				pagindiv.appendChild(paginul);
				base.appendChild(pagindiv);
			}
			$(base).appendTo($('#shoutbox'));
			/* Load the rules if wanted now but not in the popup */
			if(config.rulesOpen && config.sortShoutNb !== 1){
				shoutbox.shoutRules();
			}
			if(!config.barHaute){
				$('#shout_rules').css({'border-radius':'0', 'border-top':'1px solid darkgrey'}).insertAfter('#chat_form');
				$('#smilies_ul').css({'border-radius':'0', 'border-top':'1px solid darkgrey'}).insertAfter('#shoutbox');
				$('#shout_online').css({'border-radius':'0', 'border-top':'1px solid darkgrey'}).insertAfter('#chat_form');
				if($('#user_action').length){
					$('#user_action').css({'border-radius':'0', 'border-top':'1px solid darkgrey'}).insertAfter('#shoutbox');
				}
				if($('#colour_shoutbox').length){
					$('#colour_shoutbox').css({'border-radius':'0', 'border-top':'1px solid darkgrey'}).insertAfter('#shoutbox');
				}
				if($('#shout_chars').length){
					$('#shout_chars').css({'border-radius':'0', 'border-top':'1px solid darkgrey'}).insertAfter('#shoutbox');
				}
				if($('#shoutbox_posting').length){
					$('#shoutbox_posting').css({'border-radius':'0', 'border-top':'1px solid darkgrey'}).insertAfter('#shoutbox');
				}
				if($('#shout_bbcode').length){
					$('#shout_bbcode').css({'border-radius':'0', 'border-top':'1px solid darkgrey'}).insertAfter('#shoutbox');
				}
			}
			if(config.sortShoutNb === 1){
				$('.copyright').hide();
			}
			$('#audioShout audio').each(function(){
				$(this).attr('volume', 0.4);
			});
			/* Load the messages into the shoutbox now */
			shoutbox.loadMessages();
		}catch(e){
			shoutbox.handle(e);
			return;
		}
	};

	shoutbox.specialCharShout = function(nbCols){
		var chars = [['&euro;','&#8364;','euro sign'],['&cent;','&#162;','cent sign'],['&pound;','&#163;','pound sign'],['&curren;','&#164;','currency sign'],['&yen;','&#165;','yen sign'],['&copy;','&#169;','copyright sign'],['&reg;','&#174;','registered sign'],['&trade;','&#8482;','trade mark sign'],['&permil;','&#8240;','per mille sign'],['&micro;','&#181;','micro sign'],['&middot;','&#183;','middle dot'],['&bull;','&#8226;','bullet'],['&hellip;','&#8230;','three dot leader'],['&prime;','&#8242;','minutes / feet'],['&Prime;','&#8243;','seconds / inches'],['&sect;','&#167;','section sign'],['&para;','&#182;','paragraph sign'],['&szlig;','&#223;','sharp s / ess-zed'],['&lsaquo;','&#8249;','single left-pointing angle quotation mark'],['&rsaquo;','&#8250;','single right-pointing angle quotation mark'],['&laquo;','&#171;','left pointing guillemet'],['&raquo;','&#187;','right pointing guillemet'],['&lsquo;','&#8216;','left single quotation mark'],['&rsquo;','&#8217;','right single quotation mark'],['&ldquo;','&#8220;','left double quotation mark'],['&rdquo;','&#8221;','right double quotation mark'],['&bdquo;','&#8222;','double low-9 quotation mark'],['&le;','&#8804;','less-than or equal to'],['&ge;','&#8805;','greater-than or equal to'],['&ndash;','&#8211;','en dash'],['&mdash;','&#8212;','em dash'],['&macr;','&#175;','macron'],['&oline;','&#8254;','overline'],['&brvbar;','&#166;','broken bar'],['&iexcl;','&#161;','inverted exclamation mark'],['&iquest;','&#191;','turned question mark'],['&#9658;','&#9658;','triangle pointer'],['&tilde;','&#732;','small tilde'],['&deg;','&#176;','degree sign'],['&minus;','&#8722;','minus sign'],['&plusmn;','&#177;','plus-minus sign'],['&divide;','&#247;','division sign'],['&sup1;','&#185;','superscript one'],['&sup2;','&#178;','superscript two'],['&sup3;','&#179;','superscript three'],['&frac14;','&#188;','fraction one quarter'],['&frac12;','&#189;','fraction one half'],['&frac34;','&#190;','fraction three quarters'],['&fnof;','&#402;','function / florin'],['&int;','&#8747;','integral'],['&sum;','&#8721;','n-ary sumation'],['&infin;','&#8734;','infinity'],['&radic;','&#8730;','square root'],['&asymp;','&#8776;','almost equal to'],['&ne;','&#8800;','not equal to'],['&equiv;','&#8801;','identical to'],['&prod;','&#8719;','n-ary product'],['&not;','&#172;','not sign'],['&cap;','&#8745;','intersection'],['&part;','&#8706;','partial differential'],['&acute;','&#180;','acute accent'],['&ordf;','&#170;','feminine ordinal indicator'],['&ordm;','&#186;','masculine ordinal indicator'],['&dagger;','&#8224;','dagger'],['&Dagger;','&#8225;','double dagger'],['&Agrave;','&#192;','A - grave'],['&Aacute;','&#193;','A - acute'],['&Acirc;','&#194;','A - circumflex'],['&Atilde;','&#195;','A - tilde'],['&Auml;','&#196;','A - diaeresis'],['&Aring;','&#197;','A - ring above'],['&AElig;','&#198;','ligature AE'],['&Ccedil;','&#199;','C - cedilla'],['&Egrave;','&#200;','E - grave'],['&Eacute;','&#201;','E - acute'],['&Ecirc;','&#202;','E - circumflex'],['&Euml;','&#203;','E - diaeresis'],['&Igrave;','&#204;','I - grave'],['&Iacute;','&#205;','I - acute'],['&Icirc;','&#206;','I - circumflex'],['&Iuml;','&#207;','I - diaeresis'],['&ETH;','&#208;','ETH'],['&Ntilde;','&#209;','N - tilde'],['&Ograve;','&#210;','O - grave'],['&Oacute;','&#211;','O - acute'],['&Ocirc;','&#212;','O - circumflex'],['&Otilde;','&#213;','O - tilde'],['&Ouml;','&#214;','O - diaeresis'],['&Oslash;','&#216;','O - slash'],['&OElig;','&#338;','ligature OE'],['&Scaron;','&#352;','S - caron'],['&Ugrave;','&#217;','U - grave'],['&Uacute;','&#218;','U - acute'],['&Ucirc;','&#219;','U - circumflex'],['&Uuml;','&#220;','U - diaeresis'],['&Yacute;','&#221;','Y - acute'],['&Yuml;','&#376;','Y - diaeresis'],['&THORN;','&#222;','THORN'],['&atilde;','&#227;','a - tilde'],['&auml;','&#228;','a - diaeresis'],['&aring;','&#229;','a - ring above'],['&aelig;','&#230;','ligature ae'],['&ccedil;','&#231;','c - cedilla'],['&euml;','&#235;','e - diaeresis'],['&igrave;','&#236;','i - grave'],['&iacute;','&#237;','i - acute'],['&icirc;','&#238;','i - circumflex'],['&iuml;','&#239;','i - diaeresis'],['&eth;','&#240;','eth'],['&ntilde;','&#241;','n - tilde'],['&ograve;','&#242;','o - grave'],['&oacute;','&#243;','o - acute'],['&ocirc;','&#244;','o - circumflex'],['&otilde;','&#245;','o - tilde'],['&ouml;','&#246;','o - diaeresis'],['&oslash;','&#248;','o slash'],['&oelig;','&#339;','ligature oe'],['&scaron;','&#353;','s - caron'],['&ugrave;','&#249;','u - grave'],['&uacute;','&#250;','u - acute'],['&ucirc;','&#251;','u - circumflex'],['&uuml;','&#252;','u - diaeresis'],['&yacute;','&#253;','y - acute'],['&thorn;','&#254;','thorn'],['&yuml;','&#255;','y - diaeresis'],['&Beta;','&#914;','Beta'],['&Gamma;','&#915;','Gamma'],['&Delta;','&#916;','Delta'],['&Epsilon;','&#917;','Epsilon'],['&Zeta;','&#918;','Zeta'],['&Eta;','&#919;','Eta'],['&Theta;','&#920;','Theta'],['&Iota;','&#921;','Iota'],['&Kappa;','&#922;','Kappa'],['&Lambda;','&#923;','Lambda'],['&Mu;','&#924;','Mu'],['&Nu;','&#925;','Nu'],['&Xi;','&#926;','Xi'],['&Omicron;','&#927;','Omicron'],['&Pi;','&#928;','Pi'],['&Rho;','&#929;','Rho'],['&Sigma;','&#931;','Sigma'],['&Tau;','&#932;','Tau'],['&Upsilon;','&#933;','Upsilon'],['&Phi;','&#934;','Phi'],['&Chi;','&#935;','Chi'],['&Psi;','&#936;','Psi'],['&Omega;','&#937;','Omega'],['&alpha;','&#945;','alpha'],['&beta;','&#946;','beta'],['&gamma;','&#947;','gamma'],['&delta;','&#948;','delta'],['&epsilon;','&#949;','epsilon'],['&zeta;','&#950;','zeta'],['&eta;','&#951;','eta'],['&theta;','&#952;','theta'],['&iota;','&#953;','iota'],['&kappa;','&#954;','kappa'],['&lambda;','&#955;','lambda'],['&mu;','&#956;','mu'],['&nu;','&#957;','nu'],['&xi;','&#958;','xi'],['&omicron;','&#959;','omicron'],['&pi;','&#960;','pi'],['&rho;','&#961;','rho'],['&sigmaf;','&#962;','final sigma'],['&sigma;','&#963;','sigma'],['&tau;','&#964;','tau'],['&upsilon;','&#965;','upsilon'],['&phi;','&#966;','phi'],['&chi;','&#967;','chi'],['&psi;','&#968;','psi'],['&omega;','&#969;','omega'],['&larr;','&#8592;','leftwards arrow'],['&uarr;','&#8593;','upwards arrow'],['&rarr;','&#8594;','rightwards arrow'],['&darr;','&#8595;','downwards arrow'],['&harr;','&#8596;','left right arrow'],['&loz;','&#9674;','lozenge'],['&spades;','&#9824;','black spade suit'],['&clubs;','&#9827;','black club suit'],['&hearts;','&#9829;','black heart suit'],['&diams;','&#9830;','black diamond suit'],['&cong;','&#8773;','approximately equal to'],['&sim;','&#8764;','similar to'],['&isin;','&#8712;','element of'],['&notin;','&#8713;','not an element of'],['&ni;','&#8715;','contains as member'],['&and;','&#8743;','logical and'],['&or;','&#8744;','logical or'],['&cup;','&#8746;','union'],['&forall;','&#8704;','for all'],['&exist;','&#8707;','there exists'],['&empty;','&#8709;','diameter'],['&nabla;','&#8711;','backward difference'],['&lowast;','&#8727;','asterisk operator'],['&prop;','&#8733;','proportional to'],['&ang;','&#8736;','angle'],['&Alpha;','&#913;','Alpha'],['&alefsym;','&#8501;','alef symbol'],['&piv;','&#982;','pi symbol'],['&real;','&#8476;','real part symbol'],['&thetasym;','&#977;','theta symbol'],['&upsih;','&#978;','upsilon - hook symbol'],['&weierp;','&#8472;','Weierstrass p'],['&image;','&#8465;','imaginary part'],['&crarr;','&#8629;','carriage return'],['&lArr;','&#8656;','leftwards double arrow'],['&uArr;','&#8657;','upwards double arrow'],['&rArr;','&#8658;','rightwards double arrow'],['&dArr;','&#8659;','downwards double arrow'],['&hArr;','&#8660;','left right double arrow'],['&there4;','&#8756;','therefore'],['&sub;','&#8834;','subset of'],['&sup;','&#8835;','superset of'],['&nsub;','&#8836;','not a subset of'],['&sube;','&#8838;','subset of or equal to'],['&supe;','&#8839;','superset of or equal to'],['&oplus;','&#8853;','circled plus'],['&otimes;','&#8855;','circled times'],['&perp;','&#8869;','perpendicular'],['&sdot;','&#8901;','dot operator'],['&lceil;','&#8968;','left ceiling'],['&rceil;','&#8969;','right ceiling'],['&lfloor;','&#8970;','left floor'],['&rfloor;','&#8971;','right floor'],['&lang;','&#9001;','left-pointing angle bracket'],['&rang;','&#9002;','right-pointing angle bracket']];
		var i = 0, cols = 0,dataChars = '<div id="table-chars"><div class="row-chars">';
		for(i; i < chars.length; i++){
			if(cols == nbCols){
				dataChars += '</div><div class="row-chars">';
				cols = 0;
			}
			dataChars += '<span class="cell-chars" title="'+chars[i][2]+'">'+chars[i][0]+'</span>';
			cols++;
		}
		dataChars += '</div></div>';
		
		return dataChars;
	};

	shoutbox.mouseChar = function(){
		$('#table-chars span.cell-chars').each(function(){
			$(this).on({
				'click': function(){shoutbox.shoutInsertText($(this).html(),false)},
				'mouseover': function(){shoutbox.zoomChar($(this).html(),$(this).attr('title'))},
				'mouseout': function(){shoutbox.zoomChar('','')},
			});
		});
	};

	shoutbox.zoomChar = function(sign,title){
		$('#zoom').text(sign);
		$('#zoom2').text(title);
	};

})(jQuery);
