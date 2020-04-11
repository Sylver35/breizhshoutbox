/**
* By Sylver35 for Breizh Shoutbox
* Colors palettes and Picolor 
*/

function colorShout(){
	var r = 0, g = 0, b = 0, first = true, color = '', list = ['00','40','80','BF','FF'];
	var data = '<div id="table-color1">';
	for(r = 0; r < 5; r++){
		data += '<div class="row-chars">';
		for(g = 0; g < 5; g++){
			for(b = 0; b < 5; b++){
				first = (g === 0 && b === 0) ? true : false;
				color = String(list[r])+String(list[g])+String(list[b]);
				data += first ? '' : '<span class="cell-separate"></span>';
				data += '<span class="cell-colors" style="background:#'+color+';" title="'+color+'"></span>';
			}
		}
		data += '</div>';
		data += '<div class="row-chars row-separate"></div>';
	}
	data += '</div>';
	$('#shoutcolor1').html(data);
	$('#table-color1 span.cell-colors').each(function(){
		$(this).on('click', function(){bbfontstyle('[color=#'+$(this).attr('title')+']','[/color]')});
	});
}

function colorShoutLarge(){
	var r = 0, g = 0, b = 0, first = true, color = '', list = ['00','20','40','60','80','BF','CC','DA','FF'];
	var data = '<div id="table-color2">';
	for(r = 0; r < 9; r++){
		data += '<div class="row-chars">';
		for(g = 0; g < 6; g++){
			for(b = 0; b < 6; b++){
				first = (g === 0 && b === 0) ? false : true;
				color = String(list[r])+String(list[g])+String(list[b]);
				data += !first ? '' : '<span class="cell-separate"></span>';
				data += '<span class="cell-colors" style="background:#'+color+';" title="'+color+'"></span>';
			}
		}
		data += '</div>';
		data += '<div class="row-chars row-separate"></div>';
	}
	data += '</div>';
	$('#shoutcolor2').html(data);
	$('#table-color2 span.cell-colors').each(function(){
		$(this).on('click', function(){bbfontstyle('[color=#'+$(this).attr('title')+']','[/color]')});
	});
}

function activeColor(div){
	if(div == 'shoutcolor1'){
		colorShout();
		changeClass('shoutcolor1',true,false);
		changeClass('shoutcolor2',false,true);
		changeClass('shoutcolor3',false,false);
	}else if(div == 'shoutcolor2'){
		colorShoutLarge();
		changeClass('shoutcolor1',false,true);
		changeClass('shoutcolor2',true,false);
		changeClass('shoutcolor3',false,false);
	}else if(div == 'shoutcolor3'){
		changeClass('shoutcolor1',false,true);
		changeClass('shoutcolor2',false,true);
		changeClass('shoutcolor3',true,false);
	}else{
		changeClass('shoutcolor1',false,true);
		changeClass('shoutcolor2',false,true);
		changeClass('shoutcolor3',false,false);
	}
}

function changeClass(div, sort, content){
	if(sort){
		$('#'+div).removeClass('no_display').addClass('displayblock');
	}else{
		$('#'+div).removeClass('displayblock').addClass('no_display');
	}
	if(content){
		$('#'+div).html('');
	}
}

var picolor = {
	dir : '',
	bindClass : 'color',
	binding : true,
	preloading : false,
	install : function(){
		picolor.addEvent(window,'load',picolor.init);
	},
	init : function(){
		if(picolor.binding){
			picolor.bind();
		};
		if(picolor.preloading){
			picolor.preload();
		}
	},
	getDir : function(){
		return config.extensionUrl+'images/jscolor/';
	},
	detectDir : function(){
		var base = location.href;
		var e = document.getElementsByTagName('base');
		for(var i = 0;i < e.length;i++){
			if(e[i].href){
				base=e[i].href;
			}
		}
		var e = document.getElementsByTagName('script');
		for(var i = 0;i < e.length;i++){
			if(e[i].src && /(^|\/)colors_palettes\.js([?#].*)?$/i.test(e[i].src)){
				var src = new picolor.URI(e[i].src);
				var srcAbs = src.toAbsolute(base);
				srcAbs.path = srcAbs.path.replace(/[^\/]+$/,'');
				srcAbs.query = null;
				srcAbs.fragment = null;
				return srcAbs.toString();
			}
		}
		return false;
	},
	bind : function(){
		var matchClass = new RegExp('(^|\\s)('+picolor.bindClass+')\\s*(\\{[^}]*\\})?','i');
		var e = document.getElementsByTagName('input');
		for(var i = 0; i < e.length; i++){
			var m;
			if(!e[i].color && e[i].className && (m=e[i].className.match(matchClass))){
				var prop = {};
				if(m[3]){
					try{
						eval('prop='+m[3]);
					}catch(eInvalidProp){}
				}
				e[i].color = new picolor.color(e[i],prop);
			}
		}
	},
	preload : function(){
		for(var fn in picolor.imgRequire){
			if(picolor.imgRequire.hasOwnProperty(fn)){
				picolor.loadImage(fn);
			}
		}
	},
	images : {
		pad : [ 181,101 ],sld : [ 16,101 ],cross : [ 15,15 ],arrow : [ 7,11 ]
	},
	imgRequire : {},
	imgLoaded : {},
	requireImage : function(filename){
		picolor.imgRequire[filename] = true;
	},
	loadImage : function(filename){
		if(!picolor.imgLoaded[filename]){
			picolor.imgLoaded[filename] = new Image();
			picolor.imgLoaded[filename].src=picolor.getDir()+filename;
		}
	},
	fetchElement : function(mixed){
		return typeof mixed === 'string' ? document.getElementById(mixed) : mixed;
	},
	addEvent : function(el,evnt,func){
		if(el.addEventListener){
			el.addEventListener(evnt,func,false);
		}else if(el.attachEvent){
			el.attachEvent('on'+evnt,func);
		}
	},
	fireEvent : function(el,evnt){
		if(!el){
			return;
		}
		if(document.createEvent){
			var ev = document.createEvent('HTMLEvents');
			ev.initEvent(evnt,true,true);
			el.dispatchEvent(ev);
		}else if(document.createEventObject){
			var ev = document.createEventObject();
			el.fireEvent('on'+evnt,ev);
		}else if(el['on'+evnt]){
			el['on'+evnt]();
		}
	},
	getElementPos : function(e){
		var e1 = e,e2 = e,x = 0,y = 0;
		if(e1.offsetParent){
			do{
				x += e1.offsetLeft;y += e1.offsetTop;
			}
			while(e1 = e1.offsetParent);
		};
		while((e2 = e2.parentNode) && e2.nodeName.toUpperCase() !== 'BODY'){
			x -= e2.scrollLeft;
			y -= e2.scrollTop;
		}
		return [x,y];
	},
	getElementSize : function(e){
		return [e.offsetWidth,e.offsetHeight];
	},
	getRelMousePos : function(e){
		var x = 0,y = 0;
		if (!e){
			e = window.event;
		}
	if (typeof e.offsetX === 'number'){
		x = e.offsetX;
		y = e.offsetY;
	}else if (typeof e.layerX === 'number'){
		x = e.layerX;
		y = e.layerY;
	}
	return{x: x,y: y};
	},
	getViewPos : function(){
		if(typeof window.pageYOffset === 'number'){
			return [window.pageXOffset,window.pageYOffset];
		}else if(document.body && (document.body.scrollLeft || document.body.scrollTop)){
			return [document.body.scrollLeft,document.body.scrollTop];
		}else if(document.documentElement && (document.documentElement.scrollLeft || document.documentElement.scrollTop)){
			return [document.documentElement.scrollLeft,document.documentElement.scrollTop];
		}else{
			return [0,0];
		}
	},
	getViewSize : function(){
		if(typeof window.innerWidth === 'number'){
			return [window.innerWidth,window.innerHeight];
		}else if(document.body && (document.body.clientWidth || document.body.clientHeight)){
			return [document.body.clientWidth,document.body.clientHeight];
		}else if(document.documentElement && (document.documentElement.clientWidth || document.documentElement.clientHeight)){
			return [document.documentElement.clientWidth,document.documentElement.clientHeight];
		}else{
			return [0,0];
		}
	},
	URI : function(uri){
		this.scheme = null;
		this.authority = null;
		this.path = '';
		this.query = null;
		this.fragment = null;
		this.parse = function(uri){
			var m = uri.match(/^(([A-Za-z][0-9A-Za-z+.-]*)(:))?((\/\/)([^\/?#]*))?([^?#]*)((\?)([^#]*))?((#)(.*))?/);
			this.scheme = m[3] ? m[2] : null;
			this.authority = m[5] ? m[6] : null;
			this.path = m[7];
			this.query = m[9] ? m[10] : null;
			this.fragment = m[12] ? m[13] : null;
			return this;
		};
		this.toString = function(){
			var result = '';
			if(this.scheme !== null){
				result = result+this.scheme+':';
			}
			if(this.authority !== null){
				result=result+'//'+this.authority;
			}
			if(this.path !== null){
				result=result+this.path;
			}
			if(this.query !== null){
				result=result+'?'+this.query;
			}
			if(this.fragment !== null){
				result = result+'#'+this.fragment;
			}
			return result;
		};
		this.toAbsolute = function(base){
			var base = new picolor.URI(base);
			var r = this;
			var t = new picolor.URI;
			if(base.scheme === null){
				return false;
			}
			if(r.scheme !== null && r.scheme.toLowerCase() === base.scheme.toLowerCase()){
				r.scheme=null;
			}
			if(r.scheme !== null){
				t.scheme=r.scheme;
				t.authority=r.authority;
				t.path=removeDotSegments(r.path);
				t.query=r.query;
			}else{
				if(r.authority !== null){
					t.authority=r.authority;
					t.path=removeDotSegments(r.path);
					t.query=r.query;
				}else{
					if(r.path === ''){
						t.path=base.path;
						if(r.query !== null){
							t.query=r.query;
						}else{
							t.query=base.query;
						}
					}else{
						if(r.path.substr(0,1) === '/'){
							t.path=removeDotSegments(r.path);
						}else{
							if(base.authority !== null && base.path === ''){
								t.path='/'+r.path;
							}else{
								t.path=base.path.replace(/[^\/]+$/,'')+r.path;
							}
							t.path=removeDotSegments(t.path);
						}
						t.query=r.query;
					}
					t.authority=base.authority;
				}
				t.scheme=base.scheme;
			}
			t.fragment=r.fragment;
			return t;
		};
		function removeDotSegments(path){
			var out = '';
			while(path){
				if(path.substr(0,3) === '../' || path.substr(0,2) === './'){
					path = path.replace(/^\.+/,'').substr(1);
				}else if(path.substr(0,3) === '/./' || path === '/.'){
					path = '/'+path.substr(3);
				}else if(path.substr(0,4) === '/../' || path === '/..'){
					path = '/'+path.substr(4);
					out = out.replace(/\/?[^\/]*$/,'');
				}else if(path === '.' || path === '..'){
					path = '';
				}else{
					var rm = path.match(/^\/?[^\/]*/)[0];
					path = path.substr(rm.length);
					out = out+rm;
				}
			}
			return out;
		};
		if(uri){
			this.parse(uri);
		}
	},
	color : function(target,prop){
		this.required=true;
		this.adjust=true;
		this.hash=false;
		this.caps=true;
		this.slider=true;
		this.valueElement=target;
		this.styleElement=target;
		this.hsv=[0,0,1];
		this.rgb=[1,1,1];
		this.pickerOnfocus=true;
		this.pickerMode='HSV';
		this.pickerPosition='top';
		this.pickerButtonHeight=22;
		this.pickClose=false;
		this.pickerCloseText=bzhLang['SHOUT_CLOSE'];
		this.pickerButtonColor='ButtonText';
		this.pickerFace=12;
		this.pickerFaceColor='ThreeDFace';
		this.pickerBorder=1;
		this.pickerBorderColor='ThreeDHighlight ThreeDShadow ThreeDShadow ThreeDHighlight';
		this.pickerInset=2;
		this.pickerInsetColor='ThreeDShadow ThreeDHighlight ThreeDHighlight ThreeDShadow';
		this.pickerZIndex=10000;
		for(var p in prop){
			if(prop.hasOwnProperty(p)){
				this[p]=prop[p];
			}
		};
		this.hidePicker=function(){
			if(isPickerOwner()){
				removePicker();
			}
		};
		this.showPicker=function(){
			if(!isPickerOwner()){
				var tp=picolor.getElementPos(target);
				var ts=picolor.getElementSize(target);
				var vp=picolor.getViewPos();
				var vs=picolor.getViewSize();
				var ps=getPickerDims(this);
				var a,b,c;
				switch(this.pickerPosition.toLowerCase()){
					case 'left':
						a=1;b=0;c=-1;
					break;
					case 'right':
						a=1;b=0;c=1;
					break;
					case 'top':
						a=0;b=1;c=-1;
					break;
					default:
						a=0;b=1;c=1;
					break;
				};
				var l=(ts[b]+ps[b])/2;
				var pp=[ -vp[a]+tp[a]+ps[a] > vs[a]?(-vp[a]+tp[a]+ts[a]/2 > vs[a]/2 && tp[a]+ts[a]-ps[a] >= 0?tp[a]+ts[a]-ps[a] : tp[a]) : tp[a],-vp[b]+tp[b]+ts[b]+ps[b]-l+l*c > vs[b]?(-vp[b]+tp[b]+ts[b]/2 > vs[b]/2 && tp[b]+ts[b]-l-l*c >= 0?tp[b]+ts[b]-l-l*c : tp[b]+ts[b]-l+l*c) : (tp[b]+ts[b]-l+l*c >= 0?tp[b]+ts[b]-l+l*c : tp[b]+ts[b]-l-l*c)];
				drawPicker(pp[a],pp[b]);
			}
		};
		this.importColor=function(){
			if(!valueElement){
				this.exportColor();
			}else{
				if(!this.adjust){
					if(!this.fromString(valueElement.value,leaveValue)){
						styleElement.style.backgroundColor=styleElement.jscStyle.backgroundColor;styleElement.style.color=styleElement.jscStyle.color;
						this.exportColor(leaveValue | leaveStyle);
					}
				}else if(!this.required && /^\s*$/.test(valueElement.value)){
					valueElement.value='';
					styleElement.style.backgroundColor=styleElement.jscStyle.backgroundColor;
					styleElement.style.color=styleElement.jscStyle.color;
					this.exportColor(leaveValue | leaveStyle);
				}else if(this.fromString(valueElement.value)){
				}else{
					this.exportColor();
				}
			}
		};
		this.exportColor=function(flags){
			if(!(flags & leaveValue) && valueElement){
				var value=this.toString();
				if(this.caps){
					value=value.toUpperCase();
				}
				if(this.hash){
					value='#'+value;
				};
				valueElement.value=value;
			};
			if(!(flags & leaveStyle) && styleElement){
				styleElement.style.backgroundColor = '#'+this.toString();
				styleElement.style.color = 0.213*this.rgb[0] + 0.715*this.rgb[1] + 0.072*this.rgb[2] < 0.5?'#FFF' : '#000';
			};
			if(!(flags & leavePad) && isPickerOwner()){
				redrawPad();
			};
			if(!(flags & leaveSld) && isPickerOwner()){
				redrawSld();
			}
		};
		this.fromHSV=function(h,s,v,flags){
			h<0 && (h=0) || h>6 && (h=6);s<0 && (s=0) || s>1 && (s=1);v<0 && (v=0) || v>1 && (v=1);
			this.rgb=HSV_RGB(h === null?this.hsv[0] : (this.hsv[0]=h),s === null?this.hsv[1] : (this.hsv[1]=s),v === null?this.hsv[2] : (this.hsv[2]=v));
			this.exportColor(flags);
		};
		this.fromRGB=function(r,g,b,flags){
			r<0 && (r=0) || r>1 && (r=1);g<0 && (g=0) || g>1 && (g=1);b<0 && (b=0) || b>1 && (b=1);
			var hsv=RGB_HSV(r === null?this.rgb[0] : (this.rgb[0]=r),g === null?this.rgb[1] : (this.rgb[1]=g),b === null?this.rgb[2] : (this.rgb[2]=b));
			if(hsv[0] !== null){
				this.hsv[0]=hsv[0];
			};
			if(hsv[2] !== 0){
				this.hsv[1]=hsv[1];
			};
			this.hsv[2]=hsv[2];
			this.exportColor(flags);
		};
		this.fromString=function(hex,flags){
			var m=hex.match(/^\W*([0-9A-F]{3}([0-9A-F]{3})?)\W*$/i);
			if(!m){
				return false;
			}else{
				if(m[1].length === 6){
					this.fromRGB(parseInt(m[1].substr(0,2),16) / 255,parseInt(m[1].substr(2,2),16) / 255,parseInt(m[1].substr(4,2),16) / 255,flags);
				}else{
					this.fromRGB(parseInt(m[1].charAt(0)+m[1].charAt(0),16) / 255,parseInt(m[1].charAt(1)+m[1].charAt(1),16) / 255,parseInt(m[1].charAt(2)+m[1].charAt(2),16) / 255,flags);
				};
				return true;
			}
		};
		this.toString=function(){
			return ((0x100 | Math.round(255*this.rgb[0])).toString(16).substr(1) + (0x100 | Math.round(255*this.rgb[1])).toString(16).substr(1) + (0x100 | Math.round(255*this.rgb[2])).toString(16).substr(1));
		};
		function RGB_HSV(r,g,b){
			var n=Math.min(Math.min(r,g),b);
			var v=Math.max(Math.max(r,g),b);
			var m=v-n;if(m === 0){
				return [ null,0,v ];
			};
			var h=r === n?3+(b-g)/m : (g === n?5+(r-b)/m : 1+(g-r)/m);
			return [ h === 6?0:h,m/v,v ];
		};
		function HSV_RGB(h,s,v){
			if(h === null){
				return [ v,v,v ];
			};
			var i=Math.floor(h);
			var f=i%2?h-i : 1-(h-i);
			var m=v*(1-s);
			var n=v*(1-s*f);
			switch(i){
				case 6:
				case 0: 
				return [v,n,m];
				case 1: 
				return [n,v,m];
				case 2: 
				return [m,v,n];
				case 3: 
				return [m,n,v];
				case 4: 
				return [n,m,v];
				case 5: 
				return [v,m,n];
			}
		};
		function removePicker(){
			delete picolor.pick.owner;
			document.getElementsByTagName('body')[0].removeChild(picolor.pick.boxB);
		};
		function drawPicker(x,y){
			if(!picolor.pick){
				picolor.pick ={
					box : document.createElement('div'),boxB : document.createElement('div'),pad : document.createElement('div'),padB : document.createElement('div'),padM : document.createElement('div'),sld : document.createElement('div'),sldB : document.createElement('div'),sldM : document.createElement('div'),btn : document.createElement('div'),btnS : document.createElement('span'),btnT : document.createTextNode(THIS.pickerCloseText)
				};
				for(var i=0,segSize=4;i<picolor.images.sld[1];i+=segSize){
					var seg=document.createElement('div');
					seg.style.height=segSize+'px';
					seg.style.fontSize='1px';
					seg.style.lineHeight='0';
					picolor.pick.sld.appendChild(seg);
				};
				picolor.pick.sldB.appendChild(picolor.pick.sld);
				picolor.pick.box.appendChild(picolor.pick.sldB);
				picolor.pick.box.appendChild(picolor.pick.sldM);
				picolor.pick.padB.appendChild(picolor.pick.pad);
				picolor.pick.box.appendChild(picolor.pick.padB);
				picolor.pick.box.appendChild(picolor.pick.padM);
				picolor.pick.btnS.appendChild(picolor.pick.btnT);
				picolor.pick.btn.appendChild(picolor.pick.btnS);
				picolor.pick.box.appendChild(picolor.pick.btn);
				picolor.pick.boxB.appendChild(picolor.pick.box);
			};
			var p=picolor.pick;
			p.box.onmouseup = p.box.onmouseout=function(){
				target.focus();
			};
			p.box.onmousedown=function(){
				abortBlur=true;
			};
			p.box.onmousemove=function(e){
				if (holdPad || holdSld){
					holdPad && setPad(e);
					holdSld && setSld(e);
					if (document.selection){
						document.selection.empty();
					}else if (window.getSelection){
						window.getSelection().removeAllRanges();
					}
				}
			};
			p.padM.onmouseup = p.padM.onmouseout=function(){
				if(holdPad){
					holdPad=false;
					picolor.fireEvent(valueElement,'change');
				}
			};
			p.padM.onmousedown=function(e){
				holdPad=true;
				setPad(e);
			};
			p.sldM.onmouseup = p.sldM.onmouseout=function(){
				if(holdSld){
					holdSld=false;
					picolor.fireEvent(valueElement,'change');
				}
			};
			p.sldM.onmousedown=function(e){
				holdSld=true;
				setSld(e);
			};
			var dims=getPickerDims(THIS);
			p.box.style.width=dims[0]+'px';
			p.box.style.height=dims[1]+'px';
			p.boxB.style.position='absolute';
			p.boxB.style.clear='both';
			p.boxB.style.left=x+'px';
			p.boxB.style.top=y+'px';
			p.boxB.style.zIndex=THIS.pickerZIndex;
			p.boxB.style.border=THIS.pickerBorder+'px solid';
			p.boxB.style.borderColor=THIS.pickerBorderColor;
			p.boxB.style.background=THIS.pickerFaceColor;
			p.pad.style.width=picolor.images.pad[0]+'px';
			p.pad.style.height=picolor.images.pad[1]+'px';
			p.padB.style.position='absolute';
			p.padB.style.left=THIS.pickerFace+'px';
			p.padB.style.top=THIS.pickerFace+'px';
			p.padB.style.border=THIS.pickerInset+'px solid';
			p.padB.style.borderColor=THIS.pickerInsetColor;
			p.padM.style.position='absolute';
			p.padM.style.left='0';
			p.padM.style.top='0';
			p.padM.style.width=THIS.pickerFace+2*THIS.pickerInset+picolor.images.pad[0]+picolor.images.arrow[0]+'px';
			p.padM.style.height=p.box.style.height;p.padM.style.cursor='crosshair';
			p.sld.style.overflow='hidden';
			p.sld.style.width=picolor.images.sld[0]+'px';
			p.sld.style.height=picolor.images.sld[1]+'px';
			p.sldB.style.display=THIS.slider?'block' : 'none';
			p.sldB.style.position='absolute';
			p.sldB.style.right=THIS.pickerFace+'px';
			p.sldB.style.top=THIS.pickerFace+'px';
			p.sldB.style.border=THIS.pickerInset+'px solid';
			p.sldB.style.borderColor=THIS.pickerInsetColor;
			p.sldM.style.display=THIS.slider?'block' : 'none';
			p.sldM.style.position='absolute';
			p.sldM.style.right='0';
			p.sldM.style.top='0';
			p.sldM.style.width=picolor.images.sld[0]+picolor.images.arrow[0]+THIS.pickerFace+2*THIS.pickerInset+'px';
			p.sldM.style.height=p.box.style.height;
			try{
				p.sldM.style.cursor='pointer';
			}catch(eOldIE){
				p.sldM.style.cursor='hand';
			};
			function setBtnBorder(){
				var insetColors=THIS.pickerInsetColor.split(/\s+/);
				var pickerOutsetColor=insetColors.length < 2?insetColors[0] : insetColors[1]+' '+insetColors[0]+' '+insetColors[0]+' '+insetColors[1];
				p.btn.style.borderColor=pickerOutsetColor;
			};
			p.btn.style.display=THIS.pickClose?'block':'none';
			p.btn.style.position='absolute';
			p.btn.style.left=THIS.pickerFace+'px';
			p.btn.style.bottom=THIS.pickerFace+'px';
			p.btn.style.padding='0 15px';
			p.btn.style.height='18px';
			p.btn.style.border=THIS.pickerInset+'px solid';
			setBtnBorder();
			p.btn.style.color=THIS.pickerButtonColor;
			p.btn.style.font='12px sans-serif';
			p.btn.style.textAlign='center';
			try{
				p.btn.style.cursor='pointer';
			}catch(eOldIE){
				p.btn.style.cursor='hand';
			};
			p.btn.onmousedown=function (){
				THIS.hidePicker();
			};
			p.btnS.style.lineHeight=p.btn.style.height;
			switch(modeID){
				case 0:
					var padImg='ps.webp';
				break;
				case 1:
					var padImg='pv.webp';
				break;
			};
			p.padM.style.backgroundImage="url('"+picolor.getDir()+"croix.gif')";
			p.padM.style.backgroundRepeat="no-repeat";
			p.sldM.style.backgroundImage="url('"+picolor.getDir()+"arrow.gif')";
			p.sldM.style.backgroundRepeat="no-repeat";
			p.pad.style.backgroundImage="url('"+picolor.getDir()+padImg+"')";
			p.pad.style.backgroundRepeat="no-repeat";
			p.pad.style.backgroundPosition="0 0";
			redrawPad();
			redrawSld();
			picolor.pick.owner=THIS;
			document.getElementsByTagName('body')[0].appendChild(p.boxB);
		};
		function getPickerDims(o){
			var dims=[2*o.pickerInset+2*o.pickerFace+picolor.images.pad[0] + (o.slider?2*o.pickerInset+2*picolor.images.arrow[0]+picolor.images.sld[0] : 0),o.pickClose?4*o.pickerInset+3*o.pickerFace+picolor.images.pad[1]+o.pickerButtonHeight:2*o.pickerInset+2*o.pickerFace+picolor.images.pad[1]];
			return dims;
		};
		function redrawPad(){
			switch(modeID){
				case 0:
					var yComponent=1;
				break;
				case 1:
					var yComponent=2;
				break;
			};
			var x=Math.round((THIS.hsv[0]/6)*(picolor.images.pad[0]-1));
			var y=Math.round((1-THIS.hsv[yComponent])*(picolor.images.pad[1]-1));
			picolor.pick.padM.style.backgroundPosition = (THIS.pickerFace+THIS.pickerInset+x-Math.floor(picolor.images.cross[0]/2))+'px ' + (THIS.pickerFace+THIS.pickerInset+y-Math.floor(picolor.images.cross[1]/2))+'px';
			var seg=picolor.pick.sld.childNodes;
			switch(modeID){
				case 0:
					var rgb=HSV_RGB(THIS.hsv[0],THIS.hsv[1],1);
					for(var i=0;i<seg.length;i+=1){
						seg[i].style.backgroundColor='rgb('+(rgb[0]*(1-i/seg.length)*100)+'%,'+(rgb[1]*(1-i/seg.length)*100)+'%,'+(rgb[2]*(1-i/seg.length)*100)+'%)';
					}
				break;
				case 1:
					var rgb,s,c=[ THIS.hsv[2],0,0 ];
					var i=Math.floor(THIS.hsv[0]);
					var f=i%2?THIS.hsv[0]-i : 1-(THIS.hsv[0]-i);
					switch(i){
						case 6:
						case 0:
							rgb=[0,1,2];
						break;
						case 1:
							rgb=[1,0,2];
						break;
						case 2:
							rgb=[2,0,1];
						break;
						case 3:
							rgb=[2,1,0];
						break;
						case 4:
							rgb=[1,2,0];
						break;
						case 5:
							rgb=[0,2,1];
						break;
					};
					for(var i=0;i<seg.length;i+=1){
						s=1-1/(seg.length-1)*i;c[1]=c[0]*(1-s*f);c[2]=c[0]*(1-s);seg[i].style.backgroundColor='rgb('+(c[rgb[0]]*100)+'%,'+(c[rgb[1]]*100)+'%,'+(c[rgb[2]]*100)+'%)';
					}
				break;
			}
		};
		function redrawSld(){
			switch(modeID){
				case 0:
					var yComponent=2;
				break;
				case 1:
					var yComponent=1;
				break;
			};
			var y=Math.round((1-THIS.hsv[yComponent])*(picolor.images.sld[1]-1));picolor.pick.sldM.style.backgroundPosition = '0 '+(THIS.pickerFace+THIS.pickerInset+y-Math.floor(picolor.images.arrow[1]/2))+'px';
		};
		function isPickerOwner(){
			return picolor.pick && picolor.pick.owner === THIS;
		};
		function blurTarget(){
			if(valueElement === target){
				THIS.importColor();
			};
			if(THIS.pickerOnfocus){
				THIS.hidePicker();
			}
		};
		function blurValue(){
			if(valueElement !== target){
				THIS.importColor();
			}
		};
		function setPad(e){
			var mpos=picolor.getRelMousePos(e);
			var x=mpos.x-THIS.pickerFace-THIS.pickerInset;
			var y=mpos.y-THIS.pickerFace-THIS.pickerInset;
			switch(modeID){
				case 0:
					THIS.fromHSV(x*(6/(picolor.images.pad[0]-1)),1-y/(picolor.images.pad[1]-1),null,leaveSld);
				break;
				case 1:
					THIS.fromHSV(x*(6/(picolor.images.pad[0]-1)),null,1-y/(picolor.images.pad[1]-1),leaveSld);
				break;
			}
		};
		function setSld(e){
			var mpos=picolor.getRelMousePos(e);
			var y=mpos.y-THIS.pickerFace-THIS.pickerInset;
			switch(modeID){
				case 0:
					THIS.fromHSV(null,null,1-y/(picolor.images.sld[1]-1),leavePad);
				break;
				case 1:
					THIS.fromHSV(null,1-y/(picolor.images.sld[1]-1),null,leavePad);
				break;
			}
		};
		var THIS=this;
		var modeID=this.pickerMode.toLowerCase() === 'hvs'? 1 : 0;
		var abortBlur=false;
		var valueElement=picolor.fetchElement(this.valueElement),styleElement=picolor.fetchElement(this.styleElement);
		var holdPad=false,holdSld=false;
		var leaveValue=1<<0,leaveStyle=1<<1,leavePad=1<<2,leaveSld=1<<3;
		picolor.addEvent(target,'focus',function(){if(THIS.pickerOnfocus){THIS.showPicker();}});
		picolor.addEvent(target,'blur',function(){if(!abortBlur){window.setTimeout(function(){abortBlur || blurTarget();abortBlur=false;},0);}else{abortBlur=false;}});
		if(valueElement){
			var updateField=function(){
				THIS.fromString(valueElement.value,leaveValue);
			};
			picolor.addEvent(valueElement,'keyup',updateField);
			picolor.addEvent(valueElement,'input',updateField);
			picolor.addEvent(valueElement,'blur',blurValue);
			valueElement.setAttribute('autocomplete','off');
		};
		if(styleElement){
			styleElement.jscStyle ={backgroundColor : styleElement.style.backgroundColor,color : styleElement.style.color};
		};
		switch(modeID){
			case 0:
				picolor.requireImage('ps.webp');
			break;
			case 1:
				picolor.requireImage('pv.webp');
			break;
		};
		picolor.requireImage('croix.gif');
		picolor.requireImage('arrow.gif');
		this.importColor();
	}
};
picolor.install();

function validPicker(){
	if($('#shout_user1').attr('disabled') === false){
		var form_name = 'formuser',text_name = 'shout_user1';
	}else{
		var form_name = 'postform',text_name = 'chat_message';
	}
	shoutbox.suppText(text_name);
	bbfontstyle('[color=#'+$('#pick').val()+']','[/color]');
}
