/**
* @package		Breizh Shoutbox extension
* @copyright(c)	2018-2025 Sylver35  https://breizhcode.com
* @license		http://opensource.org/licenses/gpl-license.php GNU Public License
*/

/** global: config */
/** global: bzhLang */
/** global: shoutbox */
var tpl = {'open':'<strong>&#187;</strong><span class="profile-shout">', 'close':'</span></a></span>', 'span':'<span title="">', 'return':'<br><br>', 'a':'<a onmouseover="shoutbox.iH(\'onTextUser\',this.title,false);" onmouseout="shoutbox.iH(\'onTextUser\',\'\',false);" class="tooltip pointer"', 'ext':' onclick="window.open(this.href);return false;" href="'};
var uastring = navigator.userAgent,index,navigateur,version,is_ie = ((uastring.indexOf('msie') != -1) && (uastring.indexOf('opera') == -1)),headersContent = {'Cache-Control': 'max-age=00002, private, no-cache, no-store, must-revalidate, proxy-revalidate','Pragma': 'no-cache'},dataRun = 'user='+config.userId+'&sort='+config.sortShoutNb,onCountCats = 0,onIdCats = 0,onSmilSort = 0;
var timerIn,timerOnline,timerCookies,onCount = 0,$queryNb = 0,first = true,form_name = 'postform',text_name = 'message',imgChargeOn = '<img src="'+config.extensionUrl+'images/run.gif" alt="" style="margin-right:15px;"/>',imgLoadOn = '<img src="'+config.extensionUrl+'images/run2.gif" alt="" style="margin-right:15px;"/>',imgTurnOn = '<img src="'+config.extensionUrl+'images/spinner.gif" alt="" style="margin-right:15px;"/>',ajaxLoaderOn = '<img src="'+config.extensionUrl+'images/ajax_loader_2.gif" alt="" style="margin-right:15px;"/>',imgLoader = '<img src="'+config.extensionUrl+'images/ajax_loader.gif" alt="" style="margin-right:15px;"/>';

(function($){  // Avoid conflicts with other libraries
	'use strict';

	shoutbox.onProgress = function(event){
		if(event.lengthComputable){
			var progress = (event.loaded / event.total) * 100;
			shoutbox.iH('msg_txt','Progress: '+progress+'%',false);
			setTimeout(shoutbox.iH('msg_txt','',false),500);
		}
	};

	shoutbox.handle = function(error){
		switch(error.name){
			case 'E_USER_ERROR':
			case 'E_CORE_ERROR':
				shoutbox.message(error.message,true,false,false);
			break;
			default:
				var message = bzhLang['JS_ERR'];
				message += error.message;
				if(error.lineNumber){
					message += '\n'+bzhLang['LINE']+': '+error.lineNumber;
				}
				if(error.fileName){
					message += '\n'+bzhLang['FILE']+' : '+error.fileName;
				}
				shoutbox.message(message,true,false,false);
			break;
		}
		shoutbox.playSound(2,true);
		clearInterval(timerIn);
	};

	shoutbox.message = function(message,red,clearOn,reload){
		var colorMsg = red ? 'red' : 'green',tempsMsg = red ? 5000 : 3000,align = 'center',msgDisplay = '',spanOpen = '<span style="color:black;font-weight:bold;">',spanEnd = ' : </span>',bR = '<br>';
		if(typeof message === 'object'){
			msgDisplay = spanOpen+bzhLang['ERROR']+spanEnd;
			msgDisplay += message['message'] ? message['message'] : '';
			msgDisplay += message['line'] ? bR+spanOpen+bzhLang['LINE']+spanEnd+message['line'] : '';
			msgDisplay += message['file'] ? bR+spanOpen+bzhLang['FILE']+spanEnd+message['file'] : '';
			msgDisplay += message['content'] ? bR+spanOpen+bzhLang['POST_DETAILS']+spanEnd+message['content'] : '';
			msgDisplay += message['MESSAGE_TITLE'] ? message['MESSAGE_TITLE'] : '';
			msgDisplay += message['MESSAGE_TEXT'] ? bR+message['MESSAGE_TEXT'] : '';
			align = config.direction;
			if(message['S_USER_NOTICE'] !== false || message['S_USER_WARNING'] !== false){
				clearInterval(timerIn);
			}
		}else{
			msgDisplay = message;
		}
		if($('#msg_txt').length){
			$('#msg_txt').html('<p id="msg_p"></p>').addClass('message-text').show();
		}else{
			$('#shoutbox').html('<ul id="msg_txt" class="topiclist forums" style="height:40px;"><li id="msg_li" style="display:block;"><dl id="msg_dl" style="width:100%;"><dt id="msg_dt" class="row"><p id="msg_p" class="message-text"></p></dt></dl></li></ul>');
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
			shoutbox.reloadAll(false,true);
		}
		$('#post_message').show();
	};

	shoutbox.shoutInsertText = function(text,spaces){
		var textarea,form_name = 'postform',text_name = 'message';
		textarea = document.forms[form_name].elements[text_name];
		text = (spaces) ? ' '+text+' ' : text;
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
		window.open(popUrl.replace(/&amp;/g,'&'),name,'width='+larg+',height='+haut+',location=0,status=0,resizable=yes,toolbar=0,menubar=0,scrollbars=yes,statusbar=0,copyhistory=0,top=0,left=0');
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

	shoutbox.loadCookies = function(enableSound,isGuest){
		if(isGuest){
			if(shoutbox.getCookie('shout-sound') === false){
				shoutbox.cookieShout('shout-sound',enableSound,60);
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
		var css = sort ? 'shout-text-user' : 'inputbox message-shout',inputPost = shoutbox.cE('input','message',css,'margin-'+config.direction+':6px;width:'+config.widthPost+'px;color:#9A9A9A;',false,false,false,false,'message',function(){shoutbox.suppText()});
		inputPost.value = bzhLang['AUTO'];
		inputPost.post = sort ? 'postAction' : 'postUser';
		inputPost.spellcheck = true;
		inputPost.onfocus = function(){shoutbox.suppText()};
		inputPost.onblur = function(){shoutbox.addText()};
		inputPost.onkeypress = function(event){
			if(event.keyCode === 13){
				$('#'+this.post).click();
				event.returnValue = false;
				this.returnValue = false;
				return false;
			}
			return true;
		};
		return inputPost;
	};

	shoutbox.setRobot = function(){
		var value = shoutbox.getCookie('shout-robot'),setCookieBot = (value == '1') ? '0' : '1',onBot = (value == '1') ? 'on' : 'off',setBot = (value == '1') ? 'off' : 'on';
		shoutbox.cookieShout('shout-robot',setCookieBot,60);
		shoutbox.playSound(6,true);
		$('#onBot').val(setCookieBot);
		$('#iconBot').removeClass('button_shout_bot_'+onBot).addClass('button_shout_bot_'+setBot).attr('title', bzhLang['ROBOT_'+setBot.toUpperCase()]);
		onCount = 0;
		shoutbox.loadMessages(true);
	};

	shoutbox.permutUser = function(sort){
		$('#message, #span-post').remove();
		var inputPost = shoutbox.createInput(sort);
		if(sort){
			var span = shoutbox.cE('span','span-post',false,'margin-'+config.direction+':6px;max-width:45%;display:inline-block;width:'+config.widthPost+'px;',false,false,false,false,false,false);
			$(inputPost).insertBefore('#postAction');
			$(span).insertBefore('#postUser');
			$('#postUser').hide();
		}else{
			$(inputPost).insertBefore('#postUser');
			$('#postUser').show();
		}
		$('#message').attr('data-tribute','true');
	};

	shoutbox.reloadAll = function(timer,setCount){
		clearInterval(timerIn);
		if(setCount){
			onCount = 0;
		}
		if(timer){
			timerIn = setInterval(shoutbox.checkMessage, config.requestOn);
		}else{
			$('#openEdit').val(0);
			$('#nBErrors').val(0);
			shoutbox.resetQuery();
			shoutbox.loadMessages();
		}
	};

	shoutbox.setError = function(nBn){
		nBn++;
		$('#nBErrors').val(nBn);
	};

	shoutbox.clearAfter = function(){
		$('#msg_txt').html('').removeClass('message-text').hide();
		$('#message').css('background','');
	};

	shoutbox.cp = function(){
		return shoutbox.cE('span',false,'page-sep',false,false,false,bzhLang['COMMA_SEPARATOR'],false,false,false,false);
	};

	shoutbox.cE = function(sort,id,className,cssText,title,type,innerHTML,alt,name,onClick){
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
		if(onClick){
			onElement.onclick = onClick;
		}
		onElement.name = (name) ? name : id;
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

	shoutbox.appendChildren = function(cible,listArgs){
		for(var i = 0; i < listArgs.length; i++){
			cible.appendChild(listArgs[i]);
		}
	};

	shoutbox.resetQuery = function(){
		$queryNb = 0;
		$('#nBQuery').val($queryNb);
	};

	shoutbox.addQuery = function(){
		$queryNb++;
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
		var play = false;
		if(force){
			play = true;
		}else{
			if(config.isGuest){
				if(shoutbox.getCookie('shout-sound') == '1'){
					play = true;
				}
			}else if($('#onSound').val() == 1){
				play = true;
			}
		}
		if(play !== false){
			if($('#shoutAudio-'+sort).attr('title') !== 'off'){
				$('#shoutAudio-'+sort).trigger('play');
			}
		}
	};

	shoutbox.replaceAll = function(str,find,replace){
		return str.replace(new RegExp(find, 'g'),replace);
	};

	shoutbox.infoCookies = function(){
		$('#questionCookies').attr({'onclick':'shoutbox.closeCookies();','title':bzhLang['DIV_CLOSE']});
		$('#i-question').removeClass('fa-question').addClass('fa-question-circle');
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.questionUrl,
			data: dataRun,
			cache: false,
			success: function(result){
				var timerCookies = setTimeout(shoutbox.closeCookies,20000), classDiv = config.barHaute ? 'message-text' : 'message-text-bottom';
				var data = '<h3>'+result.title+'</h3>';
				data += '<div class="shout-bold" style="margin:8px;text-align:left;">'+result.info+' :</div>';
				data += '<div style="margin:0 0 5px 12px;text-align:left;"><ul>';
				data += '<li>1. <span class="shout-bold">shout-robot</span> : '+result.robot+'</li>';
				data += '<li>2. <span class="shout-bold">shout-sound</span> : '+result.sound+'</li>';
				data += (result.name !== '') ? '<li>3. <span class="shout-bold">shout-name</span> : '+result.name+'</li>' : '';
				data += '</ul></div>';
				$('#msg_txt').html(data).addClass(classDiv).show();
			},
			error: function(){
				$('#questionCookies').attr({'onclick':'shoutbox.infoCookies();','title':bzhLang['COOKIES']});
				$('#i-question').removeClass('fa-question-circle').addClass('fa-question');
			}
		});
	};

	shoutbox.closeCookies = function(){
		clearTimeout(timerCookies);
		if($('#msg_txt').is(':visible')){
			var classDiv = config.barHaute ? 'message-text' : 'message-text-bottom';
			$('#msg_txt').html('').removeClass(classDiv).hide();
			$('#questionCookies').attr({'onclick':'shoutbox.infoCookies();','title':bzhLang['COOKIES']});
			$('#i-question').removeClass('fa-question-circle').addClass('fa-question');
		}
	};

	shoutbox.shoutRules = function(){
		if($('#shout_rules').is(':hidden')){
			$('#shout_rules').show();
		}
		$('#rules_on').html('<div style="text-align:center;">'+imgLoader+'</div>');
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.rulesUrl,
			data: dataRun,
			cache: false,
			headers : headersContent,
			success: function(result){
				if(result.sort == 1){
					$('#rules_on').html(result.texte);
				}else{
					$('#rules_on').html('');
				}
			},
			error: function(result,statut,erreur){
				$('#rules_on').html(result.responseText);
			}
		});
	};

	shoutbox.changePerso = function(idUser){
		$('#user_action').hide();
		$('#shout_bbcode').show();
		$('#button_shout_text').attr('title', bzhLang['DIV_BBCODE_CLOSE']);
		shoutbox.iH('shoutcheck',ajaxLoaderOn,false);
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.persoUrl,
			data: dataRun+'&other='+idUser,
			cache: false,
			headers : headersContent,
			success: function(response){
				shoutbox.iH('shoutcheck','',false);
				if(response.error){
					shoutbox.message(response,true,5000,true);
					return;
				}
				if(response.id !== config.userId){
					response.name = bzhLang['ACTION_CITE_ON']+': '+response.name;
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
		$('#user_inp_bbcode, #shout_text1, #shout_text2').val('');
		$('#h3userbbcode, #shoutexemple').html('');
		$('#button_shout_text').attr('title',bzhLang['PERSO']);
	};

	shoutbox.deleteMessage = function(post_id){
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.deleteUrl,
			data: dataRun+'&post='+post_id,
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
			error: function(result,statut,erreur){
				shoutbox.message(result.responseText,true,1,false);
			}
		});
	};

	shoutbox.purgeShout = function(purgeSort,robot){
		shoutbox.message(bzhLang['PURGE_PROCESS'],false,1,false);
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: robot ? config.purgeBotUrl : config.purgeUrl,
			data: dataRun+'&purge_sort='+purgeSort,
			cache: false,
			headers : headersContent,
			success: function(response){
				if(response.error){
					shoutbox.message(response,true,5000,true);
				}else if(response.type === 1){
					shoutbox.resetQuery();
					shoutbox.playSound(3,false);
					var message = (response.nr > 1) ? bzhLang['MESSAGES'] : bzhLang['MESSAGE'];
					shoutbox.message(bzhLang['PURGE_PROCESS']+' - '+bzhLang['ACTION_CITE_ON']+' '+response.nr+' '+message,false,1,false);
					$('#shoutLast').val(0);
				}else if(response.type === 2){
					shoutbox.message(response.message,true,2000,true);
				}
			},
			error: function(result,statut,erreur){
				shoutbox.message(result.responseText,true,1,false);
			}
		});
	};

	shoutbox.suppText = function(){
		if($('#message').val() == bzhLang['AUTO']){
			$('#message').val('');
		}else{
			var onTextShout = $('#message').val();
			onTextShout = onTextShout.replace(bzhLang['AUTO'],'');
			$('#message').val(onTextShout);
		}
		$('#message').css('color','black');
	};

	shoutbox.addText = function(){
		if($('#message').val() == ''){
			$('#message').val(bzhLang['AUTO']);
		}else if($('#message').val() != bzhLang['AUTO']){
			var onTextShout = $('#message').val().replace(bzhLang['AUTO'],'');
			$('#message').val(onTextShout);
		}
		if($('#message').val() == bzhLang['AUTO']){
			$('#message').css('color','#9A9A9A');
		}else{
			$('#message').css('color','black');
		}
	};

	shoutbox.closeAction = function(){
		if($('#span-post').length){
			shoutbox.permutUser(false);
		}
		shoutbox.iH('h3user','',false);
		shoutbox.iH('shout_url','',false);
		shoutbox.iH('shout_avatar','',false)
		shoutbox.sE('user_action',2);
		$('#user_cite,#user_inp,#user_inp_sort').each(function(){$(this).val('')});
	};

	shoutbox.closeColour = function(){
		shoutbox.sE('colour_shoutbox',2);
		$('#color_shout1').attr('title', bzhLang['COLOR']);
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

	shoutbox.shoutReq = function(value1,value2,value3){
		if(($('#shout_text1').val() == '') && ($('#shout_text2').val() == '')){
			value1 = value2 = 1;
		}
		$('#shoutcheck').html(ajaxLoaderOn);
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.ubbcodeUrl,
			data: dataRun+'&open='+shoutbox.encodeUtf8(value1)+'&close='+shoutbox.encodeUtf8(value2)+'&other='+value3,
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
						$('#shout_text1, #shout_text2').val('');
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
			error: function(response,statut,erreur){
				$('#shoutcheck').html(response.responseText);
			}
		});
	};

	shoutbox.shoutOnline = function(){
		$('#online_shout').html(imgChargeOn).css('text-align','center');
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.onlineUrl,
			data: dataRun,
			cache: false,
			headers : headersContent,
			success: function(response){
				$('#online_shout').html('<div id="online_shout1"></div><hr/><div id="online_shout2"></div>').css('text-align',config.direction);
				$('#online_shout1').html(response.title);
				$('#online_shout2').html(response.list);
			},
			error: function(response,statut,erreur){
				$('#online_shout').html(response.responseText);
			}
		});
	};

	shoutbox.actionUser = function(other){
		if(isNaN(other)){
			shoutbox.message(bzhLang['SERVER_ERR'],true,1,false);
			return;
		}
		$('#user_shout, #user_action').show();
		$('#shout_url').attr('style','').show();
		shoutbox.sE('msg_user_shout',2);
		shoutbox.iH('shout_url',imgChargeOn,false);
		$('#user_cite, #user_inp').val('');
		$('#shout_avatar').html('');
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.actUrl,
			data: dataRun+'&other='+other,
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
					shoutbox.message(response.message,true,3000,false);
				}else if((typeof response.type !== undefined) && (typeof response.type !== 0)){
					if(response.type === 1){
						shoutbox.closeAction();
					}else if(response.type === 2){
						$('#h3user').html(response.username);
						$('#shout_url').html(response.message).css('color','red');
					}else if(response.type === 3){
						$('#user_inp').val(response.id);
						$('#user_inp_sort').val(response.sort);
						$('#h3user').html(response.username);
						$('#shout_avatar').html('<span class="avatar-shout">'+response.avatar+'</span>');
						var content = '<br>';
						content += (response.foe) ? '<strong>&#187;</strong><span class="profile-shout" style="color:red;">'+bzhLang['USER_IGNORE']+tpl['close']+tpl['return'] : '';
						content += (!response.foe && response.inp) ? tpl['open']+tpl['a']+response.url_message+tpl['close']+tpl['return'] : '';
						content += tpl['open']+tpl['a']+tpl['ext']+response.url_profile+tpl['close']+tpl['open']+tpl['a']+response.url_cite_m+tpl['close']+tpl['open']+tpl['a']+response.url_cite+tpl['close'];
						content += (response.return) ? tpl['return'] : '';
						content += (response.url_admin) ? tpl['open']+tpl['a']+tpl['ext']+response.url_admin+tpl['close'] : '';
						content += (response.url_modo) ? tpl['open']+tpl['a']+tpl['ext']+response.url_modo+tpl['close'] : '';
						content += (response.url_ban) ? tpl['open']+tpl['a']+tpl['ext']+response.url_ban+tpl['close'] : '';
						content += (response.url_remove) ? tpl['return']+tpl['open']+tpl['a']+response.url_remove+tpl['close'] : '';
						content += (response.url_perso) ? tpl['return']+tpl['open']+tpl['a']+response.url_perso+tpl['close'] : '';
						content += (response.url_auth) ? tpl['return']+tpl['open']+tpl['a']+response.url_auth+tpl['close'] : '';
						content += (response.url_prefs) ? tpl['open']+tpl['a']+response.url_prefs+tpl['close'] : '';
						content += '<br><hr class="dotted"><hr class="dotted">';
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
			error: function(response,statut,erreur){
				$('#shout_url').html(response.responseText);
			}
		});
	};

	shoutbox.runAuth = function(other,userName){
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.authUrl,
			data: dataRun+'&other='+other+'&name='+userName,
			cache: false,
			headers : headersContent,
			success: function(response){
				var $data = $('#shout_avatar').html();
				$('#shout_avatar').html($data+'<h3 class="auth">'+response.username+'</h3>');
				var list = '<ul class="ul-auth">';
				for(var i = 0; i < response.nb; i++){
					list += (response.title[i] !== '') ? '<br><li class="auth-bold">'+response.title[i]+'</li>' : '';
					list += '<li class="li-auth">'+response.data[i]+'</li>';
				}
				list += '</ul>';
				$('#shout_url').html(list);
			},
			error: function(response,statut,erreur){
				$('#shout_url').html(response.responseText);
			}
		});
	};

	shoutbox.sendUserAction = function(){
		var textOn = $('#message').val();
		if(textOn == '' || textOn == bzhLang['AUTO']){
			alert(bzhLang['MESSAGE_EMPTY']);
			return;
		}else{
			$('#msg_txt').html(bzhLang['SENDING']);
			shoutbox.permutUser(false);
			shoutbox.sE('msg_user_shout',2);
			shoutbox.sE('user_action',2);
			shoutbox.iH('shout_url',imgChargeOn,1);
			shoutbox.closeAll();
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: config.actPostUrl,
				data: dataRun+'&other='+$('#user_inp').val()+'&message='+shoutbox.encodeUtf8(textOn),
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
						$('#message,#user_inp,#user_inp_sort').each(function(){$(this).val('')});
						if(response.type == 1){
							onCount = 0;
							shoutbox.playSound(4,false);
							shoutbox.message(bzhLang['POSTED'],false,800,true);
							shoutbox.resetQuery();
							shoutbox.closeAction();
						}else if(response.type == 2){
							shoutbox.message(response.message,true,3000,true);
						}else{
							shoutbox.permutUser(false);
							$('#msg_txt').html('').hide();
						}
					}
				},
				error: function(response,statut,erreur){
					$('#shout_url').html(response.responseText);
				}
			});
		}
	};

	shoutbox.removeMsg = function(other){
		shoutbox.iH('shout_url',imgChargeOn,false);
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.actRemoveUrl,
			data: dataRun+'&other='+other,
			cache: false,
			headers : headersContent,
			success: function(response){
				if(response.error){
					shoutbox.message(response,true,5000,true);
					return;
				}
				if(response.type === 1){
					shoutbox.resetQuery();
					shoutbox.iH('shout_url',response.message,false);
				}else{
					shoutbox.sE('formuser',2);
				}
			},
			error: function(response,statut,erreur){
				$('#shout_url').html(response.responseText);
			}
		});
	};

	shoutbox.delReqTo = function(other){
		shoutbox.iH('shout_url',imgChargeOn,false);
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.actDelToUrl,
			data: dataRun+'&other='+other,
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
			error: function(response,statut,erreur){
				$('#shout_url').html(response.responseText);
			}
		});
	};

	shoutbox.delReq = function(other){
		shoutbox.iH('shout_url',imgChargeOn,false);
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.actDelUrl,
			data: dataRun+'&other='+other,
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
			error: function(response,statut,erreur){
				$('#shout_url').html(response.responseText);
			}
		});
	};

	shoutbox.citeMsg = function(){
		onCount = 0;
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.citeUrl,
			data: dataRun+'&other='+$('#user_inp').val(),
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
					shoutbox.resetQuery();
					shoutbox.closeAction();
					$('#user_cite').val(response.id);
					$('#message').focus();
				}
			},
			error: function(response,statut,erreur){
				$('#shout_url').html(response.responseText);
			}
		});
	};

	shoutbox.citeMultiMsg = function(multiName,multiColor,closePanel){
		if(multiColor !== ''){
			shoutbox.shoutInsertText('[b][color=#'+multiColor+']'+multiName+'[/color][/b]',true);
		}else{
			shoutbox.shoutInsertText('[b]'+multiName+'[/b]',true);
		}
		if(closePanel){
			shoutbox.closeAction();
		}
	};

	shoutbox.personalMsg = function(){
		onCount = 0;
		shoutbox.permutUser(true);
		$('#msg_user_shout').show();
		$('#user_cite').val('');
		$('#shout_url').html('');
		$('#shoutMsg').html('&nbsp;&nbsp;'+bzhLang['ACTION_MSG']+':');
		$('#message').focus();
	};

	shoutbox.robotMsg = function(sortRobot){
		onCount = 0;
		shoutbox.permutUser(true);
		$('#msg_user_shout').show();
		$('#postUser').hide();
		shoutbox.sE('user_shout',2);
		shoutbox.iH('shoutMsg','&nbsp;&nbsp;'+bzhLang['MSG_ROBOT']+':',false);
		shoutbox.iH('h3user','',false);
		shoutbox.iH('shout_avatar','');
		$('#user_inp').val(1);
		$('#user_inp_sort').val(sortRobot);
		$('#message').focus();
	};

	shoutbox.soundReq = function(){
		shoutbox.playSound(6,true);
		if(config.isGuest){
			if(shoutbox.getCookie('shout-sound') == '1'){
				var changec = '0',change = 0,soundClass = 'button_shout_sound_off',soundClassOut = 'button_shout_sound',title = bzhLang['CLICK_SOUND_ON'];
			}else{
				var changec = '1',change = 1,soundClass = 'button_shout_sound',soundClassOut = 'button_shout_sound_off',title = bzhLang['CLICK_SOUND_OFF'];
			}
			shoutbox.cookieShout('shout-sound',changec,60);
			$('#onSound').val(change);
			$('#iconSound').removeClass(soundClassOut).addClass(soundClass).attr('title', title);
		}else{
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: config.soundUrl,
				data: dataRun+'&sound='+$('#onSound').val(),
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
				error: function(response,statut,erreur){
					shoutbox.message(response.responseText);
				}
			});
		}
	};

	shoutbox.runSmileys = function(smilSort,start,categorie){
		$('#smilies').html('<div style="text-align:center;margin:25px auto;">'+imgLoadOn+bzhLang['LOADING']+'</div>').show();
		if((onIdCats !== categorie) || (onSmilSort !== smilSort)){
			onCountCats = 0;onSmilSort = 0;start = 0;
		}
		onCountCats = start;
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: smilSort ? config.smilUrl : config.smilPopUrl,
			data: dataRun+'&start='+onCountCats+'&cat='+categorie,
			success: function(data){
				if(data.error){
					shoutbox.message(data,true,5000,true);
					shoutbox.sE('smilies_ul',2);
					$('#smilies').html('').hide();
					$('#iconSmilies').attr('title', bzhLang['SMILIES']);
					return;
				}
				onIdCats = categorie;
				onSmilSort = smilSort;
				var listeSmilies = '',isCats = typeof data.categories !== 'undefined';
				if(typeof data.title !== 'undefined' && data.title){
					listeSmilies += '<div id="title-smilies"><span>'+data.title+'</span></div><hr>';
				}
				if(typeof data.emptyRow !== 'undefined' && data.emptyRow !== ''){
					listeSmilies += '<span class="pagin_red">'+data.emptyRow+'</span>';
				}
				listeSmilies += '<div id="smilies-row">';
				for(var i = 0; i < data.total; i++){
					var smilie = data.smilies[i];
					listeSmilies += '<a class="pointer" onclick="shoutbox.shoutInsertText(\''+smilie.code+'\',true);return false;" title="'+smilie.emotion+'">';
					listeSmilies += '<img class="smilies" src="'+data.url+smilie.image+'" alt="'+smilie.code+'" title="'+smilie.emotion+'" width="'+smilie.width+'" height="'+smilie.height+'"></a> ';
				}
				listeSmilies += '</div><div id="smilies-pagin-div" class="action-bar bar-top litle-pagin"><div id="smilies-pagin" class="pagination"></div></div>';
				listeSmilies += '<div id="more-smiley" class="more-smiley"> ... ';
				if(data.nb_pop > 0 && onSmilSort){
					listeSmilies += '<a class="pointer tooltip" onclick="shoutbox.runSmileys(false,0,0);" style="margin:5px;" title="'+bzhLang['MORE_SMILIES_ALT']+'"><span title="">'+bzhLang['MORE_SMILIES']+'</span></a> ... ';
				}else if(!onSmilSort){
					listeSmilies += '<a class="pointer tooltip" onclick="shoutbox.runSmileys(true,0,0);" style="margin:5px;" title="'+bzhLang['LESS_SMILIES_ALT']+'"><span title="">'+bzhLang['LESS_SMILIES']+'</span></a> ... ';
				}
				if(config.creator){
					listeSmilies += '<a class="pointer tooltip" onclick="shoutbox.shoutPopup(config.creatorUrl,\'550\',\'570\',\'_phpbbsmiliescreate\');shoutbox.suppText();" style="margin: 5px;" title="'+bzhLang['CREATOR']+'"><span title="">'+bzhLang['CREATOR']+'</span></a> ... ';
				}
				if(config.category && isCats){
					listeSmilies += (data.title_cat !== 'undefined') ? '<h3 style="margin-top:8px;">'+data.title_cat+'</h3>' : '';
					for(var i = 0; i < data.categories.length; i++){
						var category = data.categories[i],activeCat = (data.cat == category.cat_id) ? ' pagin_red' : '';
						listeSmilies += (i !== 0) ? ' - ' : '';
						listeSmilies += '<a class="pointer tooltip'+activeCat+'" onclick="shoutbox.runSmileys(false,0,'+category.cat_id+');" style="margin:5px;" title="'+category.cat_name+'"><span title="">'+category.cat_name+'</span></a>('+category.cat_nb+')';
					}
				}
				listeSmilies += '</div>';
				$('#smilies').html(listeSmilies);
				shoutbox.paginationCats(data.pagination,isCats,onSmilSort);
			},
			error: function(result,statut,erreur){
				$('#smilies').html(result.responseText);
			}
		});
	};

	shoutbox.changePageCats = function(smilSort,countCats){
		onCountCats = countCats;
		shoutbox.runSmileys(smilSort,onCountCats,onIdCats);
	};

	shoutbox.paginationCats = function(pagination,isCats,smilSort){
		$('#smilies-pagin').html('');
		var totalPagesCats = Math.ceil(pagination / config.smiliesPerPage),onPageCats = Math.floor(onCountCats / config.smiliesPerPage) + 1,onPlus = (smilSort === true) ? true : false;
		if((totalPagesCats > 1) && (pagination > config.smiliesPerPage)){
			$('#smilies-pagin-div, #smilies-pagin').show();
			var items = [(onPageCats !== 1) ? shoutbox.cECats('a','previous'+onIdCats,'pointer',bzhLang['PREVIOUS'],bzhLang['PREVIOUS']+' ',function(){shoutbox.changePageCats(onPlus,(onPageCats - 2) * config.smiliesPerPage);},'button') : '',shoutbox.cECats('a','nb-1',(onPageCats === 1) ? 'pagin_red' : 'pointer',bzhLang['PAGE']+'1','1',(onPageCats !== 1) ? function(){shoutbox.changePageCats(onPlus,0);} : false,'button')];
			var startCnt = Math.min(Math.max(1, onPageCats - 4),totalPagesCats - 5),endCnt = (totalPagesCats > 5) ? Math.max(Math.min(totalPagesCats,onPageCats + 4),6) : totalPagesCats,startFor = (totalPagesCats > 5) ? startCnt + 1 : 2,endFor = (totalPagesCats > 5) ? endCnt - 1 : totalPagesCats;
			items.push((startCnt > 1 && totalPagesCats > 5) ? ' ... ' : shoutbox.cp());
			for(var i = startFor; i < endCnt; i++){
				items.push(shoutbox.cECats('a','nb-'+(i - 1) * config.smiliesPerPage,(i === onPageCats) ? 'pagin_red' : 'pointer',bzhLang['PAGE']+i,i,(i !== onPageCats) ? function(){shoutbox.changePageCats(onPlus,this.id.replace('nb-',''));} : false,'button'));
				items.push((i < endFor) ? shoutbox.cp() : '');
			}
			items.push((totalPagesCats > 5) ? ((endCnt < totalPagesCats) ? ' ... ' : shoutbox.cp()) : '');
			items.push(shoutbox.cECats('a','nb-fin',(onPageCats === totalPagesCats) ? 'pagin_red' : 'pointer',bzhLang['PAGE']+totalPagesCats,totalPagesCats,(onPageCats !== totalPagesCats) ? function(){shoutbox.changePageCats(onPlus,(totalPagesCats - 1) * config.smiliesPerPage);} : false,'button'),(onPageCats !== totalPagesCats) ? shoutbox.cECats('a','next'+onIdCats,'pointer',bzhLang['NEXT'],' '+bzhLang['NEXT'],function(){shoutbox.changePageCats(onPlus,onPageCats * config.smiliesPerPage);},'button') : shoutbox.cECats('span',false,false,false,false,false));
			if(isCats){
				$("#title-smilies").append(' '+bzhLang['SMILIES_PAGE_TITLE'].replace('%1$s',onPageCats).replace('%2$s',endCnt));
			}
			$('#smilies-pagin').append(items);
			$('a[name="button"]').attr('role', 'button');
		}else{
			$('#div-pagin, #smileys-pagin').hide();
		}
	};

	shoutbox.cECats = function(sort,id,className,title,innerHTML,onClick,name){
		var onElement = document.createElement(sort);
		if(id){
			onElement.id = id;
		}
		if(className){
			onElement.className = className;
		}
		if(title || title === ''){
			onElement.title = title;
		}
		if(innerHTML){
			onElement.innerHTML = innerHTML;
		}	
		if(onClick){
			onElement.onclick = onClick;
		}
		if(name){
			onElement.name = name;
		}
		return onElement;
	};

	shoutbox.changePage = function(thisCount){
		shoutbox.resetQuery();
		onCount = thisCount;
		$('#shout_messages').fadeOut(600,'linear').fadeIn(600,'linear');
		$('#msg_txt').hide();
		shoutbox.reloadAll(false,false);
	};

	shoutbox.openClosePerm = function(){
		if($('#shout_connect').is(':visible')){
			$('#shout_connect').hide();
			$('#printPerm').attr('title', bzhLang['CLICK_HERE']);
		}else{
			$('#shout_connect').show();
			$('#printPerm').attr('title', bzhLang['DIV_CLOSE']);
		}
	};

	shoutbox.openCloseSmilies = function(){
		if($('#smilies').is(':visible')){
			$('#smilies_ul').hide();
			$('#smilies').html('').hide();
			$('#iconSmilies').attr('title', bzhLang['SMILIES']);
			shoutbox.addText();
		}else{
			$('#iconSmilies').attr('title', bzhLang['SMILIES_CLOSE']);
			$('#smilies_ul').show();
			shoutbox.suppText();
			shoutbox.runSmileys(true,0,0);
		}
	};

	shoutbox.openCloseColor = function(){
		if($('#colour_shoutbox').is(':visible')){
			$('#colour_shoutbox').hide();
			$('#color_shout1').attr('title', bzhLang['COLOR']);
			shoutbox.addText();
		}else{
			$('#colour_shoutbox').show();
			$('#color_shout1').attr('title', bzhLang['COLOR_CLOSE']);
			shoutbox.suppText();
		}
	};

	shoutbox.openCloseChars = function(){
		if($('#shout_chars').is(':visible')){
			$('#shout_chars').hide();
			$('#char_shout1').html('');
			$('#chars01').attr('title', bzhLang['CHARS']);
		}else{
			var nbCols = (config.isMobile) ? 25 : (config.isPopup ? 29 : 38);
			$('#shout_chars').show();
			$('#char_shout1').html(shoutbox.specialCharShout(nbCols));
			shoutbox.mouseChar();
			$('#chars01').attr('title', bzhLang['CHARS_CLOSE']);
		}
	};

	shoutbox.openCloseBbcode = function(){
		if($('#shoutbox_posting').is(':visible')){
			$('#shoutbox_posting').hide();
			$('#bbcodebutton').attr('title', bzhLang['BBCODES']);
		}else{
			$('#shoutbox_posting').show();
			$('#bbcodebutton').attr('title', bzhLang['BBCODES_CLOSE']);
		}
	};

	shoutbox.openCloseRules = function(rulesTitle){
		if($('#shout_rules').is(':visible')){
			$('#rules_on').html('');
			$('#shout_rules').hide();
			$('#buttonRules').attr('title', rulesTitle);
		}else{
			shoutbox.shoutRules();
			$('#buttonRules').attr('title', bzhLang['RULES_CLOSE']);
		}
	};

	shoutbox.openCloseOnline = function(){
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

	shoutbox.openCloseConnect = function(){
		if($('#shout_connect').is(':hidden')){
			$('#shout_connect').show();
			$('#iconConnect').attr({'class':'button_shout_connect_on button_shout', 'title':bzhLang['DIV_CLOSE']});
		}else{
			$('#shout_connect').hide();
			$('#iconConnect').attr({'class':'button_shout_connect button_shout', 'title':bzhLang['CLICK_HERE']});
		}
	};

	shoutbox.openCloseName = function(){
		if($('#shout_name').is(':hidden')){
			$('#shout_name').show();
			$('#iconName').attr({'class':'button_shout_name_on button_shout', 'title':bzhLang['DIV_CLOSE']});
		}else{
			$('#shout_name').hide();
			$('#iconName').attr({'class':'button_shout_name button_shout', 'title':bzhLang['CHOICE_NAME']});
		}
		shoutbox.sE('shoutnameyes',2);
	};

	shoutbox.openEdit = function(thisId){
		if($('#openEdit').val() == 1){
			shoutbox.playSound(2,true);
			alert(bzhLang['ONLY_ONE_OPEN']);
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
		$('#spa'+thisId).css('border', '0px none').html(bzhLang['EDIT']+': ');
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
		shoutbox.reloadAll(true,false);
	};

	shoutbox.editMessage = function(thisId,shoutId){
		shoutbox.sE('dtshout'+thisId,2);
		shoutbox.sE('ddshout'+thisId,2);
		shoutbox.sE('form'+thisId,2);
		shoutbox.iH('text'+thisId,bzhLang['SENDING_EDIT'],1);
		shoutbox.closeAll();
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.editUrl,
			data: dataRun+'&shout_id='+shoutId+'&message='+shoutbox.encodeUtf8($('#input'+thisId).val()),
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
				shoutbox.resetQuery();
				shoutbox.sE('msgbody'+response.shout_id,2);
				shoutbox.sE('shout'+response.shout_id,2);
				shoutbox.sE('editButton'+response.shout_id,3);
				shoutbox.sE('infoButton'+response.shout_id,3);
				shoutbox.sE('deleteButton'+response.shout_id,3);
				$('#post_message').show();
			},
			error: function(response,statut,erreur){
				shoutbox.message(response.responseText,true,15000,true);
			}
		});
	};

	shoutbox.sendMessage = function(){
		var textSend = $('#message').val();
		if(textSend == bzhLang['AUTO'] || textSend == ''){
			shoutbox.message(bzhLang['MESSAGE_EMPTY'],true,5000,false);
			return;
		}
		if(!config.limitPost && config.maxPost > 0){
			if(textSend.length > config.maxPost){
				shoutbox.message(bzhLang['TOO_BIG']+textSend.length+'<br>'+bzhLang['TOO_BIG2']+config.maxPost,true,5000,false);
				return;
			}
		}
		var $message = shoutbox.encodeUtf8(textSend.replace(bzhLang['AUTO'],'')),ondata = dataRun+'&message='+$message;
		ondata += ($('#user_cite').val() !== '') ? '&cite='+$('#user_cite').val() : '&cite=0';
		if(config.isGuest){
			if($('#shoutname').val() == ''){
				$('#shout_name').show();
				shoutbox.message(bzhLang['CHOICE_NAME_ERROR'],true,7000,false);
				return;
			}
			ondata += '&name='+shoutbox.encodeUtf8($('#shoutname').val());
		}
		shoutbox.resetQuery();
		shoutbox.closeAll();
		shoutbox.iH('msg_txt','',false);
		$('#msg_txt').html(bzhLang['SENDING']);
		$('#message').css('background', 'white url("'+config.extensionUrl+'images/ajax_loader.gif") no-repeat 90% 50%');
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.postUrl,
			data: ondata,
			cache: false,
			headers: headersContent,
			success: function(data){
				if(data.error){
					shoutbox.message(data,true,5000,false);
					return;
				}
				if(data.type == 1){
					shoutbox.message(data.message,false,800,true);
					shoutbox.playSound(4,false);
					$('#post_message').show();
					$('#message,#user_inp,#user_cite').each(function(){$(this).val('')});
					$('#user_cite').attr('disabled');
				}else if(data.type == 2){
					shoutbox.message(data.message,true,2000,true);
				}else if(data.type == 10){
					shoutbox.message(data.message,true,1,true);
					shoutbox.playSound(2,false);
					$('#post_message').show();
					$('#message,#user_inp,#user_cite').each(function(){$(this).val('')});
					$('#user_cite').attr('disabled');
				}else{
					shoutbox.reloadAll(true,true);
				}
				onCount = 0;
				$('#message').focus();
			},
			error: function(response,statut,erreur){
				shoutbox.message(response.responseText,true,15000,false);
			}
		});
	};

	shoutbox.setTimezone = function($timeOnMsg){
		var lastH = new Date($timeOnMsg * 1000),hour = lastH.getUTCHours(),minutes = lastH.getMinutes(),set12H = '';
		var operator = config.userTimezone.substring(0,1),zoneH = Number(config.userTimezone.substring(1,3)),zoneMin = Number(config.userTimezone.substring(4,6)),onHour = (operator == '+') ? hour + zoneH : hour - zoneH,multiple;
		minutes = minutes + zoneMin;
		if(minutes > 59){
			multiple = Math.floor(minutes / 60);
			minutes = minutes - (multiple * 60);
			onHour = onHour + multiple;
		}
		if(config.dateFormat.indexOf('a') != -1){
			set12H = (onHour > 11) ? ' pm' : ' am';
			onHour = (onHour > 12) ? onHour - 12 : onHour;
		}
		onHour = (onHour < 10) ? '0'+onHour : onHour;
		minutes = (minutes < 10) ? '0'+minutes : minutes;

		return onHour+':'+minutes+set12H;
	};

	shoutbox.refreshTime = function(){
		var isTime = Math.floor(new Date().getTime() / 1000),virgule = (config.dateFormat.indexOf(',') != -1) ? ', ' : ' ';
		$("#shout_messages span[name='time-shout']").each(function(){
			var $timeOnMsg = $(this).attr('time');
			if($timeOnMsg > (isTime - 23700)){
				var onMinute = Math.floor((isTime - $timeOnMsg) / 60);
				if(onMinute < 1){
					$(this).html(bzhLang['DATETIME_0']);
				}else if(onMinute == 1){
					$(this).html(bzhLang['DATETIME_1'].replace('%d',onMinute));
				}else if(onMinute > 1 && onMinute < 60){
					$(this).html(bzhLang['DATETIME_2'].replace('%d',onMinute));
				}else if(onMinute >= 60){
					$(this).html(bzhLang['DATETIME_3']+virgule+shoutbox.setTimezone($timeOnMsg)).attr('name', 'no-time-out');
				}
			}
		});
	};

	shoutbox.onTime = function(){
		shoutbox.addQuery();
		var time = $queryNb * (config.requestOn / 1000),hours = Math.floor(time / 3600),minutes = Math.floor((time / 60) - (hours * 60)),seconds = time - (minutes * 60) - (hours * 3600);
		hours = hours ? ((hours < 10) ? '0'+hours : hours)+':' : '';
		minutes = ((minutes < 10) ? '0'+minutes : minutes)+':';
		seconds = (seconds < 10) ? '0'+seconds : seconds;
		$('#nBTemps').val(hours+minutes+seconds);
		$('#tempSpan').html(hours+minutes+seconds);
		if(config.refresh && $("#shout_messages span[name='time-shout']").length){
			shoutbox.refreshTime();
		}
	};

	shoutbox.checkMessage = function(){
		var $onShoutLast = $('#shoutLast').val(),$goBot = $('#onBot').val();
		/** In case of auth modifications and security issue **/
		if(config.isPriv && !config.privOk){
			$('#shout_messages, #shout-pagin').html('');
			clearInterval(timerIn);
			return;
		}
		if(($('#openEdit').val() == 1) || ($goBot === undefined) || ($onShoutLast === undefined)){
			clearInterval(timerIn);
			return;
		}
		shoutbox.onTime();
		if(config.inactivity > 0 && !config.isPriv){
			if($('#nBQuery').val() > config.inactivity){
				shoutbox.message(bzhLang['OUT_TIME'],true,false,false);
				clearInterval(timerIn);
				return;
			}
		}
		$.ajax({
			type: 'POST',
			dataType: 'json',
			url: config.checkUrl+'?r='+Math.floor(Math.random() * 1000000),
			data: dataRun+'&on_bot='+$goBot,
			cache: false,
			headers : headersContent,
			success: function(result){
				if(result.error || result.message){
					shoutbox.message(result.message,true,5000,false);
					clearInterval(timerIn);
				}else if(result.S_USER_NOTICE || result.S_USER_WARNING){
					shoutbox.message(result,true,0,false);
				}else if(result.last === 1){
					shoutbox.message(result,true,4000,false);
					shoutbox.reloadAll(true,false);
				}else if(result.last !== $onShoutLast){
					if($onShoutLast !== 0){
						/** A new message is coming... **/
						shoutbox.playSound(1,false);
					}
					$('#shoutLast').val(result.last);
					shoutbox.reloadAll(false,true);
				}
				/** else nothing to do, continue your work... **/
			},
			error: function(result,statut,erreur){
				/** Just add nb errors and continue with silence **/
				shoutbox.setError($('#nBErrors').val());
				/** But, if error persist, persit... **/
				if($('#nBErrors').val() > 5){
					$('#shout_messages').html(result.responseText);
					clearInterval(timerIn);
				}
			}
		});
	};

	shoutbox.loadPagination = function(total){
		var totalPages = Math.ceil(total / config.perPage),onPage = Math.floor(onCount / config.perPage) + 1;
		if((totalPages > 1) && (total > config.perPage)){
			var items = [(onPage !== 1) ? shoutbox.cE('a',false,'pointer',false,bzhLang['PREVIOUS'],false,bzhLang['PREVIOUS']+' ',false,'button',function(){shoutbox.changePage((onPage - 2) * config.perPage);}) : '',shoutbox.cE('a','nb-1',(onPage === 1) ? 'pagin_red' : 'pointer',false,bzhLang['PAGE']+'1',false,'1',false,'button',(onPage !== 1) ? function(){shoutbox.changePage(0);} : false)];
			var startCnt = Math.min(Math.max(1, onPage - 4),totalPages - 5),endCnt = (totalPages > 5) ? Math.max(Math.min(totalPages,onPage + 4),6) : totalPages,startFor = (totalPages > 5) ? startCnt + 1 : 2,endFor = (totalPages > 5) ? endCnt - 1 : totalPages;
			items.push((startCnt > 1 && totalPages > 5) ? ' ... ' : shoutbox.cp());
			for(var i = startFor; i < endCnt; i++){
				items.push(shoutbox.cE('a','nb-'+(i - 1) * config.perPage,(i === onPage) ? 'pagin_red' : 'pointer',false,bzhLang['PAGE']+i,false,i,false,'button',(i !== onPage) ? function(){shoutbox.changePage(this.id.replace('nb-',''));} : false));
				items.push((i < endFor) ? shoutbox.cp() : '');
			}
			items.push((totalPages > 5) ? ((endCnt < totalPages) ? ' ... ' : shoutbox.cp()) : '');
			items.push(shoutbox.cE('a',false,(onPage === totalPages) ? 'pagin_red' : 'pointer',false,bzhLang['PAGE']+totalPages,false,totalPages,false,'button',(onPage !== totalPages) ? function(){shoutbox.changePage((totalPages - 1) * config.perPage);} : false),(onPage !== totalPages) ? shoutbox.cE('a',false,'pointer',false,bzhLang['NEXT'],false,' '+bzhLang['NEXT'],false,'button',function(){shoutbox.changePage(onPage * config.perPage);}) : shoutbox.cE('span',false,false,false,false,false,false,false,false,false));
			$('#shout-pagin').html('').append(items);
			$('a[name="button"]').attr('role', 'button');
		}
	};

	shoutbox.loadMessages = async function(clear = false){
		if(clear){
			clearInterval(timerIn);
		}
		var $onShoutLast = $('#shoutLast').val(),$goBot = $('#onBot').val();
		/** In case of auth modifications and security issue **/
		if(config.isPriv && !config.privOk){
			$('#shout_messages, #shout-pagin').html('');
			clearInterval(timerIn);
			return;
		}
		if(($('#openEdit').val() == 1) || ($onShoutLast === undefined) || ($goBot === undefined)){
			clearInterval(timerIn);
			return;
		}
		try{
			const datas = await $.ajax({
				url: config.viewUrl,
				method: 'POST',
				dataType: 'json',
				cache: false,
				headers: headersContent,
				data: dataRun+'&start='+onCount+'&l='+$onShoutLast+'&on_bot='+$goBot,
			});
			shoutbox.responseMessage(datas);
		}catch(error){
			shoutbox.handleError(error);
		}
	}
	
	shoutbox.responseMessage = function(datas){
		if(datas.error){
			shoutbox.message(datas,true,'',false);
			return;
		}else if(datas.last === 1){
			shoutbox.message(datas,true,1,false);
			shoutbox.iH('shout_messages',false);
			return;
		}
		/** Fisrt, clear the shoutbox **/
		$('#shout_messages').html('');
		$('#shoutLast').val(datas.last);
		if(datas.total === 0){
			if(config.postOk && first){
				$('#post_message').show();
				first = false;
			}
			$('#shout_messages').append('<div class="shout_centered">'+bzhLang['NO_MESSAGE']+'</div>');
			return;
		}
		if(datas.message){
			$('#shout_messages').html(datas.message);
			clearInterval(timerIn);
			return;
		}

		/** Initialize variables **/
		var nowTime = Math.floor(new Date().getTime() / 1000),rowMessages = [],row = 1,okDelete = okEdit = okInfo = false;
		/** Loop for messages from top to bottom or bottom to top (normal or reverse row) **/
		var listMessages = (config.toBottom) ? datas.messages : datas.messages.reverse();

		/** Loop for messages **/
		for(var i = 0; i < datas.total; i++){
			var message = listMessages[i],okDelete = message.deletemsg,okEdit = message.edit,okInfo = message.showIp,okCite = (message.other && config.postOk && config.isUser && config.buttonCite) ? true : false,listButtons = [];
			var li = shoutbox.cE('li','lishout'+i,'row row'+row+' bg'+row,false,false,false,false,false,false,false),dl = shoutbox.cE('dl','dlshout'+i,'dlshout',false,false,false,false,false,false,false),dd = shoutbox.cE('dd','msgbody'+i,'ddshout msgbody'+config.direction,false,false,false,false,false,false,false);
			var spanNow = (message.timeMsg > (nowTime - 3700)) ? '<span name="time-shout" time="'+message.timeMsg+'">'+message.shoutTime+'</span>' : '<span name="no-time-shout">'+message.shoutTime+'</span>',onAvatar = (message.avatar && message.avatar.length > 1) ? bzhLang['SEP']+'<span class="avatar-shout">'+message.avatar+'</span>' : '';
			dd.appendChild(shoutbox.cE('span','shout'+i,'msg_shout',false,false,false,message.shoutText,false,false,false));
			row = (row === 1) ? 2 : 1;
			if(okDelete){
				listButtons.push(shoutbox.cE('input','deleteButton'+i,'button_shout_del button_shout_l',false,bzhLang['DEL'],'button',false,false,'deleteButton'+message.shoutId,function(){if(confirm(bzhLang['DEL_SHOUT']+' message '+this.name.replace('deleteButton',''))){shoutbox.deleteMessage(this.name.replace('deleteButton',''));}}));
			}else if(config.buttonsLeft){
				listButtons.push(shoutbox.cE('input','deleteButton'+i,'button_shout_del_no button_shout_l',false,bzhLang['NO_DEL'],'button',false,false,false,function(){alert(bzhLang['NO_DEL'])}));
			}
			if(okEdit){
				var editButton = shoutbox.cE('input','editButton'+i,'button_shout_edit button_shout_l',false,bzhLang['EDIT'],'button',false,false,'editButton'+message.shoutId,function(){shoutbox.openEdit(this.i)}),editForm = shoutbox.cE('form','form'+i,false,'display:none;',false,false,false,false,false,false),editMsg = shoutbox.cE('span','text'+i,'span-text-edit',false,false,false,false,false,false,false),inputEdit = shoutbox.cE('input','input'+i,'input-text-edit',false,false,false,false,false,false,false),spa = shoutbox.cE('span','spa'+i,false,false,false,false,false,false,false,false),buttonEdit = shoutbox.cE('input','submitEdit'+i,'button btnmain','',bzhLang['EDIT'],'button',false,false,'submitEdit'+message.shoutId,function(){shoutbox.editMessage(this.id.replace('submitEdit',''),this.name.replace('submitEdit',''))}),buttonCancel = shoutbox.cE('input','cancel'+i,'button btnmain',false,bzhLang['CANCEL'],'button',false,false,false,function(){shoutbox.cancelMessage(this.id.replace('cancel',''))});
				editForm.spellcheck = true;
				editForm.onsubmit = function(){return false};
				inputEdit.value = shoutbox.htmlDecode(message.msgPlain);
				inputEdit.onkeypress = function(event){if(event.keyCode === 13){$('#submitEdit'+this.i).click(); event.returnValue = false; this.returnValue = false; return false;}}
				buttonEdit.value = bzhLang['EDIT_MSG'];
				buttonCancel.value = bzhLang['CANCEL'];
				inputEdit.i = editButton.i = i;
				shoutbox.appendChildren(editForm,[spa,inputEdit,buttonEdit,buttonCancel]);
				shoutbox.appendChildren(dd,[editForm,editMsg]);
				listButtons.push(editButton);
			}else if(config.buttonsLeft){
				listButtons.push(shoutbox.cE('input','editButton'+i,'button_shout_edit_no button_shout_l',false,bzhLang['NO_EDIT'],'button',false,false,false,function(){alert(bzhLang['NO_EDIT'])}));
			}
			if(okInfo && config.buttonIp){
				listButtons.push(shoutbox.cE('input','infoButton'+i,'button_shout_ip button_shout_l',false,bzhLang['IP'],'button',false,false,'infoButton'+message.shoutIp,function(){alert(bzhLang['POST_IP']+'  '+this.name.replace('infoButton',''))}));
			}
			if(okCite){
				listButtons.push(shoutbox.cE('input','citeButton-'+message.name,'button_shout_cite button_shout_l',false,bzhLang['ACTION_CITE_M'],'button',false,false,message.colour ? message.colour : '',function(){shoutbox.citeMultiMsg(this.id.replace('citeButton-',''),this.name,false)}));
			}
			var dt = (!okInfo && !okEdit && !okDelete && !okCite && !config.buttonsLeft) ? shoutbox.cE('dt',false,false,'padding:0;display:inline;float:'+config.direction,false,false,false,false,false,false) : shoutbox.cE('dt','dtshout'+i,'button_background'+(config.endClassBg ? config.buttonBg : '')+' dtshout'+config.direction,false,false,false,false,false,false),user = shoutbox.cE('dd','ddshout'+i,'ddshout','width:auto',false,false,spanNow+onAvatar+bzhLang['SEP']+message.username+':',false,false,false);
			shoutbox.appendChildren(dt,listButtons);
			shoutbox.appendChildren(dl,[dt,user,dd]);
			li.appendChild(dl);
			/** Send the message in the row **/
			rowMessages.push(li);
		}

		/** Send row messages in the shoutbox **/
		if(config.toBottom){
			$('#shout_messages').append(rowMessages).scrollTop(0);
		}else{
			$('#shout_messages').append(rowMessages).scrollTop($('#shout_messages').prop('scrollHeight'));
		}

		/** Load the pagination **/
		if(!config.isRobot){
			shoutbox.loadPagination(datas.number);
		}

		/** Start running checking for new message **/
		if($('#nBErrors').val() < 5){
			timerIn = setInterval(shoutbox.checkMessage, config.requestOn);
		}else{
			clearInterval(timerIn);
		}
	}

	shoutbox.handleError = function(error){
		/** Just add nb errors and continue with silence **/
		shoutbox.setError($('#nBErrors').val());
		if($('#nBErrors').val() > 5){
			/** But, if error persist, persit... **/
			shoutbox.handle(error);
			clearInterval(timerIn);
		}
	}

	shoutbox.writeShoutbox = function(){
		try{
			$('#sortShoutNb').val(config.sortShoutNb);
			$('#onSound').val(config.enableSound);

			/** Load the cookies **/
			shoutbox.loadCookies(config.enableSound,config.isGuest);

			var postingItems = [],shoutBarCss = (!config.postOk) ? 'text-align:center;padding:3px;' : '',postingCssText = (config.postOk) ? 'display:block;padding:3px 0 3px 1px;width:100%;' : 'float:none;width:100%;',postingStyle = 'height:auto;width:100%;overflow-wrap:break-word;';
			shoutBarCss += (!config.barHaute) ? 'border-bottom:none;' : '';
			var base = shoutbox.cE('ul','base_ul','topiclist forums',false,false,false,false,false,false,false),postingLi = shoutbox.cE('li','shoutbar','button_background'+config.buttonBg,shoutBarCss,false,false,false,false,false,false),postingDl = shoutbox.cE('dl','shoutdl',false,'width:100%;',false,false,false,false,false,false),postingForm = shoutbox.cE('dt','post_message',false,postingCssText,false,false,false,false,false,false),postingBox = shoutbox.cE('div','postingBox',false,postingStyle,false,false,false,false,false,false);

			/** Create the posting bar **/
			if(config.postOk){
				postingItems = shoutbox.postingElements(postingItems);
			}else{
				postingItems.push(shoutbox.cE('a','printPerm','pointer',false,bzhLang[config.isGuest ? 'CLICK_HERE' : 'NO_POST_PERM'],false,bzhLang[config.isGuest ? 'CLICK_HERE' : 'NO_POST_PERM'],false,false,config.isGuest ? function(){shoutbox.openClosePerm()} : false));
			}

			var activeSound = ($('#onSound').val() == 1) ? true : false,soundCss = activeSound ? '' : '_off',soundTitle = bzhLang[activeSound ? 'CLICK_SOUND_OFF' : 'CLICK_SOUND_ON'],cssBot = ($('#onBot').val() == 1) ? 'on' : 'off',botTitle = bzhLang['ROBOT_'+cssBot.toUpperCase()]
			postingItems.push(shoutbox.cE('input','iconSound','button_shout_sound'+soundCss+' button_shout','',soundTitle,'button',false,false,false,function(){shoutbox.soundReq()}),shoutbox.cE('input','iconBot','button_shout_bot_'+cssBot+' shout_bot button_shout','',botTitle,'button',false,false,false,function(){shoutbox.setRobot()}),shoutbox.cE('a','questionCookies','pointer',false,bzhLang['COOKIES'],false,'<i id="i-question" class="icon fa-question fa-fw"></i><span></span>',false,false,function(){shoutbox.infoCookies()}));

			/** Audio players **/
			postingItems.push(shoutbox.createSpanAudio());

			shoutbox.appendChildren(postingBox,postingItems);
			postingForm.appendChild(postingBox);
			postingDl.appendChild(postingForm);
			postingLi.appendChild(postingDl);
			/** End of create posting bar **/

			/** Create the shoutbox body **/
			var items = [],posImgHori = config.shoutImgHori.replace('right','98%'),posImgVert = config.shoutImgVert.replace('top','1%'),shoutImg = config.shoutImg ? 'background: transparent url("'+config.shoutImg+'") no-repeat scroll '+posImgHori+' '+posImgVert+';' : '',direction = (config.direction == 'right') ? 'left' : 'right';
			var contentPagin = '<ul id="ulnr" class="topiclist forums" style="margin:0px;"><li id="linr" class="pagination button_background button_background'+config.buttonBg+'" style="text-align:'+config.direction+';"><span id="shout-pagin" class="shout-pagin"></span><span id="tempSpan" class="temp_span_'+direction+'"></span></li></ul>';
			items.push(postingLi);
			items.push(shoutbox.cE('div','msg_txt',false,false,false,false,false,false,false,false));
			items.push(shoutbox.cE('div','shout_messages','shout_message','height: '+config.shoutHeight+'px;'+shoutImg,false,false,'<div style="text-align:center;margin:50px auto;">'+imgChargeOn+bzhLang['LOADING']+'</div>',false,false,false));
			items.push(shoutbox.cE('div','divnr',false,false,false,false,contentPagin,false,false,false));
			shoutbox.appendChildren(base,items);
			$(base).appendTo($('#shoutbox'));
			$('#message').attr('data-tribute','true');
			/** End of create the shoutbox body **/

			/** Load the messages into the shoutbox now **/
			shoutbox.loadMessages();

			/** Load the rules if wanted but not in the popup **/
			if(config.rulesOpen && !config.isPopup){
				shoutbox.shoutRules();
			}

			/** Adjust the disposition of elements **/
			shoutbox.adjustDisposition();

		}catch(error){
			shoutbox.handle(error);
		}
	};

	shoutbox.postingElements = function(postingItems){
		postingItems.push(shoutbox.createInput(false));
		var postUser = shoutbox.cE('input','postUser','button btnmain','margin-'+config.direction+':6px;border-radius:4px;line-height:1.3;',bzhLang['POST_MESSAGE_ALT'],'button',false,false,'postUser',function(){shoutbox.sendMessage()});
		postUser.value = bzhLang['POST_MESSAGE'];
		postingItems.push(postUser);
		if(config.smiliesOk){
			postingItems.push(shoutbox.cE('input','iconSmilies','button_shout_smile button_shout','margin-'+config.direction+':4px;',bzhLang['SMILIES'],'button',false,false,'iconSmilies',function(){shoutbox.openCloseSmilies()}));
		}else if(config.seeButtons){
			postingItems.push(shoutbox.cE('input',false,'button_shout_smile_no button_shout','',bzhLang['NO_SMILIES'],'button',false,false,false,function(){alert(bzhLang['NO_SMILIES'])}));
		}
		if(config.colorOk){
			postingItems.push(shoutbox.cE('input','color_shout1','button_shout_color button_shout','',bzhLang['COLOR'],'button',false,false,false,function(){shoutbox.openCloseColor()}));
		}else if(config.seeButtons){
			postingItems.push(shoutbox.cE('input','color_shout1','button_shout_color_no button_shout','',bzhLang['NO_COLOR'],'button',false,false,false,function(){alert(bzhLang['NO_COLOR'])}));
		}
		if(config.charsOk){
			postingItems.push(shoutbox.cE('input','chars01','button_shout_chars button_shout','',bzhLang['CHARS'],'button',false,false,false,function(){shoutbox.openCloseChars()}));
		}else if(config.seeButtons){
			postingItems.push(shoutbox.cE('input','chars01','button_shout_chars_no button_shout','',bzhLang['NO_CHARS'],'button',false,false,false,function(){alert(bzhLang['NO_CHARS'])}));
		}
		if(config.bbcodeOk){
			postingItems.push(shoutbox.cE('input','bbcodebutton','button_shout_img button_shout','',bzhLang['BBCODES'],'button',false,false,false,function(){shoutbox.openCloseBbcode()}));
		}else if(config.seeButtons){
			postingItems.push(shoutbox.cE('input','bbcodebutton','button_shout_img_no button_shout','',bzhLang['NO_BBCODES'],'button',false,false,false,function(){alert(bzhLang['NO_BBCODE'])}));
		}
		if(!config.isPopup){
			if(config.popupOk){
				postingItems.push(shoutbox.cE('input',false,'button_shout_popup button_shout','',bzhLang['POP'],'button',false,false,false,function(){shoutbox.shoutPopup(config.popupUrl,config.popupWidth,config.popupHeight,'_popup');return false;}));
			}
			if(config.purgeOn){
				postingItems.push(shoutbox.cE('input','purgeRobot','button_shout_robot button_shout','',bzhLang['PURGE_ROBOT_ALT'],'button',false,false,false,function(){if(confirm(bzhLang['PURGE_ROBOT_BOX'])){shoutbox.purgeShout('purge_robot'+(config.isPriv ? '_priv' : ''),true)}}));
				postingItems.push(shoutbox.cE('input','purge','button_shout_purge button_shout','',bzhLang['PURGE_ALT'],'button',false,false,false,function(){if(confirm(bzhLang['PURGE_BOX'])){shoutbox.purgeShout('purge'+(config.isPriv ? '_priv' : ''),false);}}));
			}
		}
		if(!config.isPriv && config.privOk && config.isUser){
			postingItems.push(shoutbox.cE('input',false,'button_shout_priv button_shout','',bzhLang['PRIV'],'button',false,false,false,function(){window.open(config.privUrl)}));
		}
		if(config.formatOk && config.isUser){
			postingItems.push(shoutbox.cE('input','button_shout_text','button_shout_text button_shout','',bzhLang['PERSO'],'button',false,false,false,function(){if($('#shout_bbcode').is(':visible')){shoutbox.closePersoBbcode();}else{shoutbox.changePerso(config.userId);}}));
		}
		if(!config.isGuest){
			postingItems.push(shoutbox.cE('input',false,'button_shout_config button_shout','',bzhLang['CONFIG_OPEN'],'button',false,false,false,function(){shoutbox.shoutPopup(config.configUrl,'980','500','_popup')}));
		}
		if(config.rulesOk){
			var rulesTitle = bzhLang['RULES'+(config.isPriv ? '_PRIV' : '')];
			postingItems.push(shoutbox.cE('input','buttonRules','button_shout_rules button_shout','',config.rulesOpen ? bzhLang['RULES_CLOSE'] : rulesTitle,'button',false,false,false,function(){shoutbox.openCloseRules(rulesTitle)}));
		}
		if(config.onlineOk){
			postingItems.push(shoutbox.cE('input','buttonOnline','button_shout_online button_shout','',bzhLang['ONLINE'],'button',false,false,false,function(){shoutbox.openCloseOnline()}));
		}
		if(config.isGuest){
			postingItems.push(shoutbox.cE('input','iconName','button_shout_name button_shout','',bzhLang['CHOICE_NAME'],'button',false,false,false,function(){shoutbox.openCloseName()}));
			postingItems.push(shoutbox.cE('input','iconConnect','button_shout_connect button_shout','',bzhLang['CLICK_HERE'],'button',false,false,false,function(){shoutbox.openCloseConnect()}));
		}
		return postingItems;
	};

	shoutbox.createSpanAudio = function(){
		/** 1 : New message, 2 : Error (default), 3 : Delete, 4 : Add message, 5 : Edit message, 6 : special auto sound **/
		var listSounds = [[1,'new',config.newSound],[2,'error',config.errorSound],[3,'del',config.delSound],[4,'add',config.addSound],[5,'edit',config.editSound],[6,'auto','discretion']];
		var spanAudio = shoutbox.cE('span','audioShout','no_display',false,false,false,false,false,'audioShout',false),listLecteurs = [];
		for(var i = 0; i < listSounds.length; i++){
			var lecteur = shoutbox.cE('audio','shoutAudio-'+listSounds[i][0],false,false,listSounds[i][1],'audio/mpeg',false,false,'shoutAudio-'+listSounds[i][0],false);
			if(listSounds[i][2] !== '1'){
				lecteur.src = config.extensionUrl+'sounds/'+listSounds[i][2]+'.mp3';
				lecteur.preload = 'auto';
			}else{
				lecteur.title = 'off';
			}
			listLecteurs.push(lecteur);
		}
		shoutbox.appendChildren(spanAudio,listLecteurs);
		return spanAudio;
	}

	shoutbox.adjustDisposition = function(){
		$('#shout-1').html('<i class="icon fa-commenting fa-fw" aria-hidden="false"></i><a href="'+config.titleUrl+'" onclick="window.open(this.href);return false" title="'+bzhLang['TITLE']+'">'+bzhLang['TITLE']+'</a>').removeClass('shout-left-dt').addClass('shout-'+config.direction+'-dt');
		$('#shout-2').html(bzhLang['PRINT_VER']+'<i class="icon fa-info fa-fw" aria-hidden="false"></i>').removeClass('shout-left-dd').addClass('shout-'+config.direction+'-dd');
		if(!config.barHaute){
			var idList = ['shout_rules','smilies_ul','shout_online','user_action','colour_shoutbox','shout_chars','shoutbox_posting','shout_bbcode','shoutbar'];
			for(var i = 0; i < idList.length; i++){
				if($('#'+idList[i]).length){
					$('#'+idList[i]).css({'border-radius':'0', 'border-top':'1px solid darkgrey'}).insertBefore('#divnr');
				}
			}
			$('#msg_txt').insertAfter('#shout_messages');
		}
		if(config.isPopup){
			$('.copyright').hide();
		}
		if($('#format-buttons').length){
			$('.format-buttons').css({'margin':'0', 'padding':'10px 0 10px 0', 'text-align':'center'});
			$('.format-buttons .button').css('padding','4px');
			$('.format-buttons .button .icon').css('font-size','16px');
			$('.format-buttons select').css('font-size','1.1em');
		}else if($('#format-postingbuttons').length){
			$('#format-postingbuttons').css({'margin':'0', 'padding':'10px 0 10px 0', 'text-align':'center'});
			$('#format-postingbuttons .button').css('padding','4px');
			$('.format-postingbuttons .button .icon').css('font-size','16px');
			$('.format-postingbuttons select').css('font-size','1.1em');
		}
		if($('#iconConnect').length){
			$('#iconConnect').insertBefore('#questionCookies')
		}
		if($('#abbc3_buttons').length){
			$('#abbc3_buttons').css('margin','0');
		}
		$('#audioShout audio').each(function(){
			$(this).attr('type', 'audio/mpeg');
		});
	};

	shoutbox.specialCharShout = function(nbCols){
		var chars = [['&euro;','&#8364;','euro sign'],['&cent;','&#162;','cent sign'],['&pound;','&#163;','pound sign'],['&curren;','&#164;','currency sign'],['&yen;','&#165;','yen sign'],['&copy;','&#169;','copyright sign'],['&reg;','&#174;','registered sign'],['&trade;','&#8482;','trade mark sign'],['&permil;','&#8240;','per mille sign'],['&micro;','&#181;','micro sign'],['&middot;','&#183;','middle dot'],['&bull;','&#8226;','bullet'],['&hellip;','&#8230;','three dot leader'],['&prime;','&#8242;','minutes / feet'],['&Prime;','&#8243;','seconds / inches'],['&sect;','&#167;','section sign'],['&para;','&#182;','paragraph sign'],['&szlig;','&#223;','sharp s / ess-zed'],['&lsaquo;','&#8249;','single left-pointing angle quotation mark'],['&rsaquo;','&#8250;','single right-pointing angle quotation mark'],['&laquo;','&#171;','left pointing guillemet'],['&raquo;','&#187;','right pointing guillemet'],['&lsquo;','&#8216;','left single quotation mark'],['&rsquo;','&#8217;','right single quotation mark'],['&ldquo;','&#8220;','left double quotation mark'],['&rdquo;','&#8221;','right double quotation mark'],['&bdquo;','&#8222;','double low-9 quotation mark'],['&le;','&#8804;','less-than or equal to'],['&ge;','&#8805;','greater-than or equal to'],['&ndash;','&#8211;','en dash'],['&mdash;','&#8212;','em dash'],['&macr;','&#175;','macron'],['&oline;','&#8254;','overline'],['&brvbar;','&#166;','broken bar'],['&iexcl;','&#161;','inverted exclamation mark'],['&iquest;','&#191;','turned question mark'],['&#9658;','&#9658;','triangle pointer'],['&tilde;','&#732;','small tilde'],['&deg;','&#176;','degree sign'],['&minus;','&#8722;','minus sign'],['&plusmn;','&#177;','plus-minus sign'],['&divide;','&#247;','division sign'],['&sup1;','&#185;','superscript one'],['&sup2;','&#178;','superscript two'],['&sup3;','&#179;','superscript three'],['&frac14;','&#188;','fraction one quarter'],['&frac12;','&#189;','fraction one half'],['&frac34;','&#190;','fraction three quarters'],['&fnof;','&#402;','function / florin'],['&int;','&#8747;','integral'],['&sum;','&#8721;','n-ary sumation'],['&infin;','&#8734;','infinity'],['&radic;','&#8730;','square root'],['&asymp;','&#8776;','almost equal to'],['&ne;','&#8800;','not equal to'],['&equiv;','&#8801;','identical to'],['&prod;','&#8719;','n-ary product'],['&not;','&#172;','not sign'],['&cap;','&#8745;','intersection'],['&part;','&#8706;','partial differential'],['&acute;','&#180;','acute accent'],['&ordf;','&#170;','feminine ordinal indicator'],['&ordm;','&#186;','masculine ordinal indicator'],['&dagger;','&#8224;','dagger'],['&Dagger;','&#8225;','double dagger'],['&Agrave;','&#192;','A - grave'],['&Aacute;','&#193;','A - acute'],['&Acirc;','&#194;','A - circumflex'],['&Atilde;','&#195;','A - tilde'],['&Auml;','&#196;','A - diaeresis'],['&Aring;','&#197;','A - ring above'],['&AElig;','&#198;','ligature AE'],['&Ccedil;','&#199;','C - cedilla'],['&Egrave;','&#200;','E - grave'],['&Eacute;','&#201;','E - acute'],['&Ecirc;','&#202;','E - circumflex'],['&Euml;','&#203;','E - diaeresis'],['&Igrave;','&#204;','I - grave'],['&Iacute;','&#205;','I - acute'],['&Icirc;','&#206;','I - circumflex'],['&Iuml;','&#207;','I - diaeresis'],['&ETH;','&#208;','ETH'],['&Ntilde;','&#209;','N - tilde'],['&Ograve;','&#210;','O - grave'],['&Oacute;','&#211;','O - acute'],['&Ocirc;','&#212;','O - circumflex'],['&Otilde;','&#213;','O - tilde'],['&Ouml;','&#214;','O - diaeresis'],['&Oslash;','&#216;','O - slash'],['&OElig;','&#338;','ligature OE'],['&Scaron;','&#352;','S - caron'],['&Ugrave;','&#217;','U - grave'],['&Uacute;','&#218;','U - acute'],['&Ucirc;','&#219;','U - circumflex'],['&Uuml;','&#220;','U - diaeresis'],['&Yacute;','&#221;','Y - acute'],['&Yuml;','&#376;','Y - diaeresis'],['&THORN;','&#222;','THORN'],['&atilde;','&#227;','a - tilde'],['&auml;','&#228;','a - diaeresis'],['&aring;','&#229;','a - ring above'],['&aelig;','&#230;','ligature ae'],['&ccedil;','&#231;','c - cedilla'],['&euml;','&#235;','e - diaeresis'],['&igrave;','&#236;','i - grave'],['&iacute;','&#237;','i - acute'],['&icirc;','&#238;','i - circumflex'],['&iuml;','&#239;','i - diaeresis'],['&eth;','&#240;','eth'],['&ntilde;','&#241;','n - tilde'],['&ograve;','&#242;','o - grave'],['&oacute;','&#243;','o - acute'],['&ocirc;','&#244;','o - circumflex'],['&otilde;','&#245;','o - tilde'],['&ouml;','&#246;','o - diaeresis'],['&oslash;','&#248;','o slash'],['&oelig;','&#339;','ligature oe'],['&scaron;','&#353;','s - caron'],['&ugrave;','&#249;','u - grave'],['&uacute;','&#250;','u - acute'],['&ucirc;','&#251;','u - circumflex'],['&uuml;','&#252;','u - diaeresis'],['&yacute;','&#253;','y - acute'],['&thorn;','&#254;','thorn'],['&yuml;','&#255;','y - diaeresis'],['&Beta;','&#914;','Beta'],['&Gamma;','&#915;','Gamma'],['&Delta;','&#916;','Delta'],['&Epsilon;','&#917;','Epsilon'],['&Zeta;','&#918;','Zeta'],['&Eta;','&#919;','Eta'],['&Theta;','&#920;','Theta'],['&Iota;','&#921;','Iota'],['&Kappa;','&#922;','Kappa'],['&Lambda;','&#923;','Lambda'],['&Mu;','&#924;','Mu'],['&Nu;','&#925;','Nu'],['&Xi;','&#926;','Xi'],['&Omicron;','&#927;','Omicron'],['&Pi;','&#928;','Pi'],['&Rho;','&#929;','Rho'],['&Sigma;','&#931;','Sigma'],['&Tau;','&#932;','Tau'],['&Upsilon;','&#933;','Upsilon'],['&Phi;','&#934;','Phi'],['&Chi;','&#935;','Chi'],['&Psi;','&#936;','Psi'],['&Omega;','&#937;','Omega'],['&alpha;','&#945;','alpha'],['&beta;','&#946;','beta'],['&gamma;','&#947;','gamma'],['&delta;','&#948;','delta'],['&epsilon;','&#949;','epsilon'],['&zeta;','&#950;','zeta'],['&eta;','&#951;','eta'],['&theta;','&#952;','theta'],['&iota;','&#953;','iota'],['&kappa;','&#954;','kappa'],['&lambda;','&#955;','lambda'],['&mu;','&#956;','mu'],['&nu;','&#957;','nu'],['&xi;','&#958;','xi'],['&omicron;','&#959;','omicron'],['&pi;','&#960;','pi'],['&rho;','&#961;','rho'],['&sigmaf;','&#962;','final sigma'],['&sigma;','&#963;','sigma'],['&tau;','&#964;','tau'],['&upsilon;','&#965;','upsilon'],['&phi;','&#966;','phi'],['&chi;','&#967;','chi'],['&psi;','&#968;','psi'],['&omega;','&#969;','omega'],['&larr;','&#8592;','leftwards arrow'],['&uarr;','&#8593;','upwards arrow'],['&rarr;','&#8594;','rightwards arrow'],['&darr;','&#8595;','downwards arrow'],['&harr;','&#8596;','left right arrow'],['&loz;','&#9674;','lozenge'],['&spades;','&#9824;','black spade suit'],['&clubs;','&#9827;','black club suit'],['&hearts;','&#9829;','black heart suit'],['&diams;','&#9830;','black diamond suit'],['&cong;','&#8773;','approximately equal to'],['&sim;','&#8764;','similar to'],['&isin;','&#8712;','element of'],['&notin;','&#8713;','not an element of'],['&ni;','&#8715;','contains as member'],['&and;','&#8743;','logical and'],['&or;','&#8744;','logical or'],['&cup;','&#8746;','union'],['&forall;','&#8704;','for all'],['&exist;','&#8707;','there exists'],['&empty;','&#8709;','diameter'],['&nabla;','&#8711;','backward difference'],['&lowast;','&#8727;','asterisk operator'],['&prop;','&#8733;','proportional to'],['&ang;','&#8736;','angle'],['&Alpha;','&#913;','Alpha'],['&alefsym;','&#8501;','alef symbol'],['&piv;','&#982;','pi symbol'],['&real;','&#8476;','real part symbol'],['&thetasym;','&#977;','theta symbol'],['&upsih;','&#978;','upsilon - hook symbol'],['&weierp;','&#8472;','Weierstrass p'],['&image;','&#8465;','imaginary part'],['&crarr;','&#8629;','carriage return'],['&lArr;','&#8656;','leftwards double arrow'],['&uArr;','&#8657;','upwards double arrow'],['&rArr;','&#8658;','rightwards double arrow'],['&dArr;','&#8659;','downwards double arrow'],['&hArr;','&#8660;','left right double arrow'],['&there4;','&#8756;','therefore'],['&sub;','&#8834;','subset of'],['&sup;','&#8835;','superset of'],['&nsub;','&#8836;','not a subset of'],['&sube;','&#8838;','subset of or equal to'],['&supe;','&#8839;','superset of or equal to'],['&oplus;','&#8853;','circled plus'],['&otimes;','&#8855;','circled times'],['&perp;','&#8869;','perpendicular'],['&sdot;','&#8901;','dot operator'],['&lceil;','&#8968;','left ceiling'],['&rceil;','&#8969;','right ceiling'],['&lfloor;','&#8970;','left floor'],['&rfloor;','&#8971;','right floor'],['&lang;','&#9001;','left-pointing angle bracket'],['&rang;','&#9002;','right-pointing angle bracket']];
		var cols = 0,dataChars = '<div id="table-chars"><div class="row-chars">';
		for(var i = 0; i < chars.length; i++){
			if(cols === nbCols){
				dataChars += '</div><div class="row-chars">';
				cols = 0;
			}
			dataChars += '<span class="cell-chars" ><a id="char-'+i+'" name="char" title="'+chars[i][2]+'">'+chars[i][0]+'</a></span>';
			cols++;
		}
		dataChars += '</div></div>';

		return dataChars;
	};

	shoutbox.mouseChar = function(){
		$('#table-chars a[name=char]').each(function(){
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
