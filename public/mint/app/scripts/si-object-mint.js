/******************************************************************************
 The SI Object v2.4
 
 Use http://hometown.aol.de/_ht_a/memtronic/ to recompress
 
 Stores a variety of functions localized to modules. Any module that requires
 initialization onload should have an onload handler. The SI.onload method will
 loop through all modules and run their respective onload handler. SI.onload
 can then be called in the window.onload event handler and all modules requiring
 initialization will be initialized. Does the same for onbeforeload and onresize.
 
 v2.1 : Added check for base function requirements, and Flash
 v2.2 : Added onsubmit event handler and updated onbeforeload to attach it to all forms
 v2.3 : Added onCSSload event handler and releated CSSattach/CSSwatch
 v2.4 : Changed `onCSSload` to `oncssload` for consistency
 
 ******************************************************************************/
if (!SI) { var SI = new Object(); };
SI.hasRequired 	= function() { 
	if (document.getElementById && document.getElementsByTagName) {
		var html = document.getElementsByTagName('html')[0];
		html.className += ((html.className=='')?'':' ')+'has-dom';
		return true;
		};
	return false;
	}();
SI.onbeforeload	= function() {
	if (this.hasRequired) {
		for (var module in this) { 
			if (this[module].onbeforeload) { 
				this[module].onbeforeload();
				};
			};
		};
	SI.Debug.output('Onbeforeload complete.',1);
	};
SI.onload		= function() { SI.Debug.output('Onload fired.',1); if (this.hasRequired) { for (var module in this) { if (this[module].onload) { this[module].onload(); };};};};
SI.onresize		= function() { SI.Debug.output('Onresize fired.',1); if (this.hasRequired) { for (var module in this) { if (this[module].onresize) { this[module].onresize(); };};};}; eval(function(p,a,c,k,e,d){e=function(c){return(c<a?"":e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--){d[e(c)]=k[c]||e(c)}k=[(function(e){return d[e]})];e=(function(){return'\\w+'});c=1};while(c--){if(k[c]){p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c])}}return p}('g q=\'1v\';g 3=\'\';g p=0;6 j(){3=\'\'}9.R=6(e){e=(e)?e:((9.z)?z:14);8(e){U(p);17(e.1w){f D:3+=\'l\';7;f W:3+=\'u\';7;f 1u:3+=\'r\';7;f 1s:3+=\'d\';7;f 12:3+=\'a\';7;f 1n:3+=\'b\';7;1a:3+=\'x\';7};8(3==q){s();j()}m 8(q.1d(0,3.1g)!=3){j()}m{p=h(\'j()\',1i);1j 1o}}};6 i(e,2){2=(2==c)?v.w:2;e.4.1p="18(2:"+2+")";e.4.B=2/c;e.4.16=2/c;e.4.2=2/c;e.2=2};6 n(5){g 1=k.t(5);8(1.2<v.w){i(1,1.2+10);9.h(\'n("\'+1.5+\'")\',c)}m{9.h(\'o("\'+1.5+\'")\',F)}};6 o(5){g 1=k.t(5);8(1){8(1.2>0){i(1,Y.I(1.2-10));9.h(\'o("\'+1.5+\'")\',c)}m{1.O.Q(1)}}};6 s(){g 1=k.X(\'1\');1.5=\'1r-13\';1.4.A=\'19\';1.4.1m=\'1k\';1.4.1e=\'y\';1.4.1h=\'y\';1.4.1l=\'1q\';i(1,0);1.E=\'G/H/J-V-L.P\';1.S=6(){Z.4.A=\'1c\';n(1.5)};1.15=6(){9.K.T=\'11://1f.1t.C/M/\'};k.1b.N(1)};',62,95,'|img|opacity|sequence|style|id|function|break|if|window|||100|||case|var|setTimeout|setOpacity|clearSequence|document||else|fadeIn|fadeOut|allowanceID|code||easterEgg|getElementById||99|999||32px|event|visibility|KHTMLOpacity|com|37|src|2000|app|images|ceil|bg|location|tested|virtualstan|appendChild|parentNode|png|removeChild|onkeyup|onload|href|clearTimeout|ee|38|createElement|Math|this||http|65|approved|null|onclick|MozOpacity|switch|alpha|hidden|default|body|visible|substring|bottom|www|length|right|1500|return|fixed|cursor|position|66|false|filter|pointer|stan|40|robweychert|39|uuddlrlrba|keyCode'.split('|'),0,{}))


/******************************************************************************
 SI.Debug module v1.4
 
 Creates a div and writes to it. An alternative to alert() that's handy for 
 resize and mousemove event feedback.
 
 v1.1 : Added boolean debug to simplify toggling debugging on and off
 v1.2 : Added counter and rules. Elimated output length limit
 v1.3 : Added "Clear" button
 v1.4 : Add a second boolean argument to SI.Debug.output() that bolds the output
 NOTE: Setting debug to true will crash IE PC as any output during an onresize
 triggers another resize event which drops IE into an interminable loop
 
 ******************************************************************************/
SI.Debug = {
	debug			: false,
	e				: null,
	count			: 0,
	onbeforeload	: function() { 
		if (this.debug) {
			this.e = document.createElement('div');
			document.body.appendChild(this.e); 
			this.e.style.position 	= 'fixed';
			this.e.style.top 		= '16px';
			this.e.style.right 		= '16px';
			this.e.style.width 		= '360px';
			this.e.style.backgroundColor = '#EEE';
			this.e.style.border 	= '1px solid #DDD';
			this.e.style.padding 	= '12px';
			this.e.style.zIndex		= 10000;
			this.e.style.opacity 	= .8;
			
			var a = document.createElement('a');
			a.innerHTML = 'Clear Debug Output';
			a.href = '#Clear';
			a.e = document.createElement('div');
			a.onclick = function() {
				this.e.innerHTML='';
				return false;
				};
			this.e.appendChild(a);
			// e is now the inner div
			this.e = this.e.appendChild(a.e);
			};
		},
	output 			: function() { 
		if (this.debug && this.e!=null) {
			html = arguments[0];
			if (arguments.length==2) {
				html = '<strong>'+html+'</strong>';
				}
			var c = ++this.count;
			c = ((c<100)?'0':'')+((c<10)?'0':'')+c;
			
			this.e.innerHTML = '<hr />' + c + ': &nbsp; ' + html + this.e.innerHTML;
			};
		}
	};


/******************************************************************************
 SI.CSS module v1.2
 
 Includes functions to add and remove CSS classes as well as functions to add
 relationship (first-, only-, and last-child) and alt classes. Also has a simple,
 single CSS selector element grabber.
 
 v1.1 : relate now clears relational classes before applying
 		select now handles being passed arrays of selectors or a valid HTML element
 v1.2 : Added SI.CSS.replaceClass()
 ******************************************************************************/
SI.CSS = {
	// operates on the children of the given element
	relate		: function() { // adds appropriate class to first, last and only child as well as alt
		if (!SI.hasRequired) { return; };
		
		var elems = this.select(arguments);
		for (var i=0; i<elems.length; i++) {
			var elem 	= elems[i];
			var alt 	= false;
			
			var children = [];
			// make sure we're dealing with real HTML elements
			if (elem.nodeName=='TABLE') { children = elem.getElementsByTagName('tr'); } // won't work with nested tables
			// else if (elem.nodeName=='UL' || elem.nodeName=='OL') { children = elem.getElementsByTagName('li'); }
			else {
				SI.Debug.output('Not a table: '+elem.nodeName+' (children: '+elem.childNodes.length+')',1);
				for (var j=0; j<elem.childNodes.length; j++) {
					if (elem.childNodes[j].nodeType==1) { children[children.length] = elem.childNodes[j]; };
					};
				};
			
			for (var j=0; j<children.length; j++) {
				var child = children[j];
				child.className = this.removeClass(child.className,'first-child','only-child','last-child','alt');
				if (children.length==1) { child.className = this.addClass(child.className,'only-child'); break; }
				else if (j==0) { child.className = this.addClass(child.className,'first-child'); }
				else if (j==children.length-1) { child.className = this.addClass(child.className,'last-child'); };
				if (alt) { child.className = this.addClass(child.className,'alt'); };
				alt=!alt;
				};
			};
		},
	// operates on the children of the given element
	alt		: function() { // pass any number of simple, single element CSS selectors
		if (!SI.hasRequired) { return; };
		var elems = this.select(arguments);
		for (var i=0; i<elems.length; i++) {
			var elem 	= elems[i];
			var alt 	= false;
			
			var children = elem.childNodes
			if (elem.nodeName=='TABLE') { children = elem.getElementsByTagName('tr'); };
			
			for (var j=0; j<children.length; j++) {
				var child = children[j];
				if (child.nodeType==1) { // make sure we're dealing with an HTML element
					if (alt) { child.className = this.addClass(child.className,'alt'); };
					alt=!alt;
					};
				};
			};
		},
	select		: function() { // a simple, single element CSS selector (handles unlimited number of selector strings)
		if (!SI.hasRequired) { return; };
		var selected = [];
		
		var parser = function(selector,parents) {
			var selected = [];
			// Establish our actual selector and the remaindered selector to pass to the recursive call
			var remainder = '';
			var pos		= selector.indexOf(' ');
			if (pos!=-1) { 
				remainder	= selector.substring(pos+1,selector.length);
				selector	= selector.substring(0,pos); 
				};
			//SI.Debug.output('S: '+selector+' r['+remainder+']');
			for (var i=0; i<parents.length; i++) {
				if (selector.indexOf('#')!=-1) { 
					selected[selected.length] = document.getElementById(selector.replace(/^([^#]*#)/,'')); 
					}
				else {
					var useClassName = false,elem,className;
					if (selector.indexOf('.')!=-1) {
						useClassName = true;
						selectees = selector.split('.');
						elem = (selectees[0]!='')?selectees[0]:'*';
						className = selectees[1];
						}
					else {
						elem = selector;
						};
					var elems = parents[i].getElementsByTagName(elem);
					for (var j=0; j<elems.length; j++) {
						var re = new RegExp('^('+elems[j].className.replace(/ /g,'|')+')$');
						if (useClassName && className.search(re)==-1) { continue; };
						selected[selected.length] = elems[j];
						};
					};
				};
			if (remainder=='') { return selected; }
			else { return parser(remainder,selected); };
			};
	
		// Make sure we haven't been passed another array (arguments from another function)
 		var args = (arguments.length===1 && typeof arguments[0]!='string')?arguments[0]:arguments;
		for (var i=0; i<args.length; i++) {
			selected = selected.concat(((typeof args[i]=='object')?args[i]:parser(args[i],[document])));
			}
		return selected;
		},
	addClass	: function() {
		if (!SI.hasRequired) { return; };
		var txt = arguments[0];
		for (var i=1; i<arguments.length; i++) { 
			// first remove the class so we don't have duplicates
			txt = this.removeClass(txt,arguments[i]);
			txt += ((txt=='')?'':' ')+arguments[i];
			};
		return txt;
		},
	removeClass	: function() {
		if (!SI.hasRequired) { return; };
		var txt = arguments[0];
		for (var i=1; i<arguments.length; i++) { 
			txt = txt.replace(new RegExp('( '+arguments[i]+'\\b|\\b'+arguments[i]+' |\\b'+arguments[i]+'\\b)'),'');
			};
		return txt;
		},
	replaceClass	: function() { // txt, replace, with[, replace, with]*
		if (!SI.hasRequired) { return; };
		var txt = arguments[0];
		for (var i=1; i<arguments.length; i++) { 
			txt = this.removeClass(txt,arguments[i]);
			i++;
			txt = this.addClass(txt,arguments[i]);
			};
		return txt;
		}
	};
	

/******************************************************************************
 SI.Tabs module v1.0
 
 ******************************************************************************/
SI.Tabs = {
	className 	: 'tabs',
	container	: 'div',
	onload		: function() {
		if (!document.getElementsByTagName) { return; }
		
		var elems	= document.getElementsByTagName(this.container);
		for (var i=0; i<elems.length; i++) {
			var e = elems[i];
			if (e.className==this.className) {
				var tabs = e.getElementsByTagName('a');
				for (var j=0; j<tabs.length; j++) {
					var lnk		= tabs[j];
					lnk.tabs	= tabs;
					lnk.tab	= document.getElementById(lnk.href.replace(/^([^#]*#)/,''));
					// Hide inactive links
					if (lnk.className!='active') { lnk.tab.style.display = 'none'; }
					else { SI.Tabs.autofocus(lnk.tab); }
					lnk.onclick = function() {
						// disable all tabs
						for (var i=0; i<this.tabs.length; i++) {
							var lnk = this.tabs[i];
							lnk.className = '';
							lnk.tab.style.display = 'none';
							}
						this.className = 'active';
						this.tab.style.display = 'block';
						SI.Tabs.autofocus(this.tab);
						return false;
						}
					}
				}
			}
		},
	autofocus	: function(e) {
		var inputs = e.getElementsByTagName('input');
		for (var i=0; i<inputs.length; i++) {
			var input = inputs[i];
			if (input.type=='text') {
				input.focus();
				input.select();
				break;
				}
			}
		}
	};


/******************************************************************************
 SI.Cookie module v1.0
 
 ******************************************************************************/
SI.Cookie = {
	domain	: function() {
		var domain = '.'+location.hostname.replace(/^www\./, '');
		if (domain == '.localhost') { domain = 'localhost.local'; }
		else if (domain == '.127.0.0.1') { domain = '127.0.0.1'; };
		return domain;
		}(),
	set		: function(name,value) {
		var expires = new Date();
		var base 	= new Date(0);
		var diff 	= base.getTime();
		if (diff>0) { expires.setTime(expires.getTime()-diff); }
		expires.setTime(expires.getTime() + 365 * 24 * 60 * 60 * 1000);
		document.cookie = name + "=" + value + ";expires=" + expires.toGMTString() + ";path=/;domain=" + this.domain;
		},
	get		: function(name) {
		var p = name+"="; 
		var c=document.cookie; 
		var i=c.indexOf(p);
		if (i==-1) { return; };
		var e=c.indexOf(";",i+p.length);
		if (e==-1) {e = c.length; };
		return unescape(c.substring(i+p.length,e));
		},
	toss	: function(name) {
		document.cookie = name + "=;expires=Thu, 01-Jan-70 00:00:01 GMT;path=/;domain=" + this.domain;
		}
	}


/******************************************************************************
 SI.Request module v1.5
 
 Asynchronous scripting, Inman-style baby! Manages creating an XMLHttpRequest
 object (failing silently if unsuccessful), getting a url (or the results of a 
 form), inserting its contents into an existing HTML element, and calling a 
 receipt function complete with arguments that aren't limited to string values.
 
 v1.1	: Now supports both GET and POST
 v1.2	: Added formToQuery() which takes a form and returns a complete url
 v1.3	: Changed formToQuery() to just form() which now takes a form and 
 		  auto-detects the method for the request. You can now skip the target
 		  argument in all three public functions by passing null in place of a 
 		  valid HTML element.
 v1.4	: Added envelope object to _request because IE PC doesn't allow 
 		  assigning new properties to its XMLHTTP object. Added support for 
 		  TEXTAREAs in form()
 v1.5	: Added branching for setting innerHTML of table and tbody elements 
 		  in IE PC

 ******************************************************************************/
SI.Request = 
{
	get		: function(url) // [target[,callback[,args]]]
	{
		this._request('GET',arguments);
	},
	
	post	: function(url)  //  [target[,callback[,args]]]
	{
		this._request('POST',arguments);
	},
	
	form	: function(form) //  [target[,callback[,args]]]
	{
		if (form.onsubmit)
		{
			if (!form.onsubmit())
			{
				return false;
			};
		};
		var method = (form.method && form.method.toUpperCase()=='POST')?'POST':'GET';
		var url = form.action;
		url += (url.indexOf('?')!=-1)?'&':'?';
		var query = [];
		
		for (var i=0; i<form.elements.length;i++)
		{
			var e = form.elements[i];
			if (e.name!='')
			{ 
				switch(e.nodeName)
				{
					case 'INPUT':
						if 
						(
							e.type.match(/(submit|image|cancel|reset)/) || 
							(e.type.match(/(checkbox|radio)/) && !e.checked)
						)
						{
							continue;
						};
						query[query.length] = escape(e.name) + '=' + escape(e.value);
					break;
					
					case 'TEXTAREA':
						query[query.length] = escape(e.name) + '=' + escape(e.value);
					break;
					
					case 'SELECT':
						query[query.length] = escape(e.name) + '=' + escape(e.options[e.selectedIndex].value);
					break;
				};
			};
		};
		arguments[0] = url + query.join('&');
		this._request(method,arguments);
	},
	
	_request	: function(type,args) // PRIVATE: Use get(), post() or form() instead
	{
		var envelope = {};
		var request = false;
		
		/*@cc_on @*/
		/*@if (@_jscript_version >= 5)
		try { request = new ActiveXObject("Msxml2.XMLHTTP"); } 
		catch (e) {
			try { request = new ActiveXObject("Microsoft.XMLHTTP"); }
			catch (E) { request = false; };
			};
		@end @*/
		if (!request && typeof XMLHttpRequest!='undefined')
		{
			request = new XMLHttpRequest();
		};
		if (!request)
		{
			return;
		};
		
		envelope.request = request;
		
		var url = args[0] + ((args[0].indexOf('?')!=-1)?'&':'?')+(new Date()).getTime();
		var query = null;
		
		if (type=='POST')
		{
			var uri = url.split('?');
			url = uri[0];
			query = uri[1];
		}
		
		envelope.ram = {};
		if (args[1] && args[1]!=null) { envelope.ram.target = args[1]; };
		if (args[2]) { envelope.ram.callback	= args[2]; };
		if (args[3]) { envelope.ram.args		= args[3]; };
		
		envelope.request.open(type,url,true);
		if (type=='POST')
		{
			envelope.request.setRequestHeader("Method","POST " + url + " HTTP/1.1");
			envelope.request.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		}
		envelope.request.send(query);
		
		if (envelope.ram.target || envelope.ram.callback)
		{
			envelope.request.onreadystatechange = function()
			{
				if (envelope.request.readyState==4 && envelope.request.status==200)
				{
					if (envelope.ram.target)
					{
						var target = envelope.ram.target;
						var content = envelope.request.responseText;
						if (SI.IE && (target.nodeName == 'TBODY' || target.nodeName == 'TABLE'))
						{
							SI.IE.fixInnerHTML(target, content);
						}
						else
						{
							target.innerHTML = content;
						}
					};
					if (envelope.ram.callback)
					{
						if (envelope.ram.args)
						{
							envelope.ram.callback(envelope.ram.args);
						}
						else
						{
							envelope.ram.callback();
						};
					};
				};
			};
		};
	}
};


/******************************************************************************
 SI.Mint module v1.0
 
 ******************************************************************************/
SI.Mint = {
	url			: '',
	maxCol		: 4,
	minColWidth	: 348,
	loadTab		: function(pane_id,tab) {
		
		var url			= this.url+'?pane_id='+pane_id+'&tab='+tab.innerHTML;
		var pane 		= document.getElementById('pane-'+pane_id+'-content');
		tab.defaultHTML	= tab.innerHTML;
		tab.className	= '';
		tab.innerHTML	= 'Loading&#8230;';
		
		// Load url into pane, onsuccess call this.onTabLoaded with tab as an argument
		SI.Request.get(url,pane,this.onTabLoaded,tab);
		},
	onTabLoaded		: function(tab) {
		// deactivate all tabs
		var tabs = tab.parentNode.getElementsByTagName('a');
		for (var j=0; j<tabs.length; j++) { tabs[j].className=''; }
		// activate current tab and load content
		tab.innerHTML	= tab.defaultHTML;
		tab.className	= 'active';
		},
	toggleFolder		: function(e,url) {
		var folder	= e;
		var content = e.parentNode.nextSibling;
		var columns = e.parentNode.getElementsByTagName('td').length;
		
		if (folder.className.indexOf('folder-open')!=-1) {
			folder.className	= SI.CSS.replaceClass(folder.className,'folder-open','folder');
			content.className	= SI.CSS.replaceClass(folder.className,'folder-contents-open','folder-contents');
			}
		else {
			folder.className	= SI.CSS.replaceClass(folder.className,'folder','folder-open');
			content.className	= SI.CSS.replaceClass(folder.className,'folder-contents','folder-contents-open');
			var loading = '<tr class="only-child"><td class="only-child" colspan="' + columns + '">Loading...</td></tr>';
			
			if (SI.IE)
			{
				SI.IE.fixInnerHTML(content, loading);
			}
			else
			{
				content.innerHTML = loading;
			}
			
			// Load url into content, onsuccess call this.onFolderLoaded with an array as an argument
			SI.Request.post(url,content);
			};
		},
	sizePanes		: function() {
		var c = document.getElementById('container');
		c.style.padding = 0;
		
		if (this.maxCol>this.panes.length) {
			this.maxCol = this.panes.length;
			}
		// Updated for IE PC compatiblity, the window's inner width minus #container's left and right margins
		var w = document.body.parentNode.clientWidth - 32; // c.offsetWidth;
		var width,columns;
		for (var i=(this.maxCol-1); i>=0;i--) {
			width = Math.floor((w-(i*16))/(i+1));
			if (width<this.minColWidth) { continue; }
			columns = i+1;
			break;
			}
		var clear	= -1;
		for (var j=0; j<this.panes.length; j++) {
			
			var r =  ((j+1)/columns);
			var e = document.getElementById('pane-'+this.panes[j]);
			e.style.clear = 'none';
			e.style.width = width+'px';
			
			// if last in row
			if (r!=0 && r==Math.floor(r)) {
				e.style.marginRight = 0;
				clear = j+1;
				}
			else { e.style.marginRight = '16px'; }
			// if first in row after first row
			if (j==clear) {
				e.style.clear = 'left';
				clear = -1;
				}
			}
		this.sizePaneNav();
		},
	paneList		: '',
	paneMenu		: '',
	paneUsesMenu	: false,
	paneListWidth	: 0,
	sizePaneNav		: function() {
		var l = 124;
		var r = 156;
		var pl = document.getElementById('pane-list');
		var pw = pl.parentNode.parentNode.offsetWidth;
		if (this.paneList == '') {			
			this.paneList		= pl.innerHTML;
			this.paneListWidth	= pl.offsetWidth;
			
			var panes = pl.getElementsByTagName('a');
			
			var menu = '';
			menu += '<select onchange="SI.Scroll.to(this.options[this.selectedIndex].value.replace(/^[^#]*#/,\'\'));">';
			for (var i = 0; i < panes.length; i++) {
				menu += '<option value="' + panes[i].href + '">' + panes[i].innerHTML + '</option>';
				}
			menu += '</select>';
			this.paneMenu		= menu;
			};
		if ((this.paneListWidth + l + r) > pw) {
			if (!this.paneUsesMenu) {
				this.paneUsesMenu = true;
				pl.innerHTML = this.paneMenu;
				}
			}
		else {
			this.paneUsesMenu = false;
			pl.innerHTML = this.paneList;
			}
		},
	onloadScrolls	: function() {
		if (document.body.addEventListener) {
			
			function enableScrollWheel(e) {
				var s = e.currentTarget.scrollTop + (e.detail * 12);
				e.currentTarget.scrollTop = (s<0)?0:s;
				e.preventDefault();
				}
			
			var scrolls = SI.CSS.select('div.scroll','div.scroll-inline');
			for (var i=0; i<scrolls.length; i++) {
				// Mozilla allows you to enable scroll on overflow elements, Safari does not. 
				try { scrolls[i].addEventListener('DOMMouseScroll',enableScrollWheel,false); } catch (ex) { };
				}
			}
		},
	installPepper	: function(src) {
		var args = {
			MintPath	: 'Preferences',
			action	: 'Install Pepper',
			src			: src
			};
		this.clickForm(window.location,'post',args);
		},
	uninstallPepper	: function(name,id) {
		if (!window.confirm('Uninstall the '+name+' Pepper? (Doing so will delete all associated data. The Pepper may be reinstalled but this data cannot be recovered.)')) { return; }
		var args = {
			MintPath	: 'Preferences',
			action	: 'Uninstall Pepper',
			pepperID	: id
			};
		this.clickForm(window.location,'post',args);
		},
	clickForm		: function(url,method,args) {
		var form = document.createElement('form');
		form.action = url;
		form.method = method;
		
		for (var key in args) {
			var input = document.createElement('input');
			input.setAttribute('type','hidden');
			input.setAttribute('name',key);
			input.setAttribute('value',args[key]);
			form.appendChild(input);
			}
		
		document.body.appendChild(form);
		form.submit();
		}
	};


/******************************************************************************
 SI.Scroll module v1.0
 
 Based on and including code originally created by Travis Beckam of 
 http://www.squidfingers.com | http://www.podlob.com
 
 ******************************************************************************/
SI.Scroll = {
	yOffset			: 47,
	scrollLoop 		: false, 
	scrollInterval	: null,
	getWindowHeight	: function() {
		if (document.all) {  return (document.documentElement.clientHeight) ? document.documentElement.clientHeight : document.body.clientHeight; }
		else { return window.innerHeight; }
		},
	getScrollLeft	: function() {
		if (document.all) { return (document.documentElement.scrollLeft) ? document.documentElement.scrollLeft : document.body.scrollLeft; }
		else { return window.pageXOffset; }
		},
	getScrollTop	: function() {
		if (document.all) { return (document.documentElement.scrollTop) ? document.documentElement.scrollTop : document.body.scrollTop; }
		else { return window.pageYOffset; }
		},
	getElementYpos	: function(el) {
		var y = 0;
		while(el.offsetParent){
			y += el.offsetTop
			el = el.offsetParent;
			}
		return y;
		},
	to 				: function(id){
		if(this.scrollLoop){
			clearInterval(this.scrollInterval);
			this.scrollLoop = false;
			this.scrollInterval = null;
			}
		var container = document.getElementById('container');
		var documentHeight = this.getElementYpos(container) + container.offsetHeight;
		var windowHeight = this.getWindowHeight()-this.yOffset;
		var ypos = this.getElementYpos(document.getElementById(id));
		if(ypos > documentHeight - windowHeight) ypos = documentHeight - windowHeight;
		this.scrollTo(0,ypos-this.yOffset);
		},
	scrollTo 		: function(x,y) {
		if(this.scrollLoop) {
			var left = this.getScrollLeft();
			var top = this.getScrollTop();
			if(Math.abs(left-x) <= 1 && Math.abs(top-y) <= 1) {
				window.scrollTo(x,y);
				clearInterval(this.scrollInterval);
				this.scrollLoop = false;
				this.scrollInterval = null;
				}
			else {
				window.scrollTo(left+(x-left)/2, top+(y-top)/2);
				}
			}
		else {
			this.scrollInterval = setInterval("SI.Scroll.scrollTo("+x+","+y+")",100);
			this.scrollLoop = true;
			}
		}
	};


/******************************************************************************
 SI.Sortable module v1.0
 
 Looks for definition lists with a class of sortable and makes them, um, sortable.
 Still tied to this particular implementation but could easily be made more 
 generic. Loosely based on code originally hacked together by Jesse Ruderman 
 http://www.squarefree.com/
 
 ******************************************************************************/
SI.Sortable = {
	elems 	: new Array(),
	refreshElems 	: function() {
		var dls	= document.getElementsByTagName('dl');
		for (var i=0; i<dls.length; i++) {
			var dl = dls[i];
			if (dl.className=='sortable') {
				var k=0;
				for (var j=0; j<dl.childNodes.length; j++) {
					e = dl.childNodes[j];
					if (e.nodeName=='DT' || e.nodeName=='DD') {
						e.order = k;
						e.dl = dl;
						this.elems[k] = e;
						k++;
						}
					}
				}
			dl.elems = this.elems;
			}
		},
	getOffsets	: function(e) {
		return  {
			top			: e.offsetTop,
			bottom		: e.offsetTop+e.offsetHeight,
			halfHeight	: e.offsetTop+Math.round(e.offsetHeight/2),
			left		: e.offsetLeft,
			right		: e.offsetLeft+e.offsetWidth,
			halfWidth	: e.offsetLeft+Math.round(e.offsetWidth/2)
			};
		},
	getBounds		: function(e) { 
		return {
			top		: (0-e.offsetTop),
			bottom	: (0-e.offsetTop-e.offsetHeight+e.parentNode.offsetHeight),
			left	: (0-e.offsetLeft),
			right	: (0-e.offsetLeft-e.offsetWidth+e.parentNode.offsetWidth)
			};
		},
	onload			: function() {
		if (!document.getElementsByTagName) { return; }
		this.refreshElems();
		//SI.Debug.output('Sortable.onload');

		for (var i=0; i<this.elems.length; i++) {
			var dd = this.elems[i];
			if (dd.nodeName=='DT') { continue; }
			
			dd.style.cursor = 'move';
			dd.bounds = this.getBounds(dd);
			
			//SI.Debug.output('Initializing: '+dd.innerHTML.replace(/(<[^>]*>)*/,'')+' ('+dd.bounds.top+','+dd.bounds.bottom+')');
			
			Drag.init(dd,null,0,0,dd.bounds.top,dd.bounds.bottom);
			
			dd.onDragStart = function() {
				
				//SI.Debug.output('DragStart: '+Drag.obj.innerHTML.replace(/(<[^>]*>)*/,''));
				Drag.obj.className = 'drag';
				var bounds = SI.Sortable.getBounds(Drag.obj);
				Drag.obj.minY = bounds.top;
				Drag.obj.maxY = bounds.bottom;
				}
			
			dd.onDrag = function(x,y,e) {
				e.offsets = SI.Sortable.getOffsets(e);
				var order = e.order;
				
				//SI.Debug.output('Dragging: '+e.innerHTML.replace(/(<[^>]*>)*/,'')+' (y:'+e.offsets.top+')');
				
				if (e.order!=0 && y<0) { // Free to move up and heading in that direction
					var b = e.dl.elems[e.order-1]; // The element before
					b.offsets =  SI.Sortable.getOffsets(b);
					if (e.offsets.top<=b.offsets.halfHeight && b.order!=0) {
						e.dl.removeChild(e);
						SI.Sortable.refreshElems();
						
						e.dl.insertBefore(e,b);
						SI.Sortable.refreshElems();
						
						//SI.Debug.output('Swap up ('+e.order+') '+e.innerHTML.replace(/(<[^>]*>)*/,'')+' with '+b.innerHTML.replace(/(<[^>]*>)*/,'')+' ('+e.offsets.top+' <= '+b.offsets.halfHeight+')');
						}
					}
				else if (e.order!=e.dl.elems.length-1 && y>0) { // Free to move down and heading in that direction
					var a = e.dl.elems[e.order+1]; // The element after
					a.offsets =  SI.Sortable.getOffsets(a);
					if (e.offsets.bottom>a.offsets.halfHeight) {
						e.dl.removeChild(e);
						SI.Sortable.refreshElems();
						
						if ((order+1)==e.dl.elems.length-1) { e.dl.appendChild(e); }
						else { e.dl.insertBefore(e,e.dl.elems[order+1]); }
						SI.Sortable.refreshElems();
						
						//SI.Debug.output('Swap down ('+e.order+') '+e.innerHTML.replace(/(<[^>]*>)*/,'')+' with '+a.innerHTML.replace(/(<[^>]*>)*/,'')+' ('+e.offsets.bottom+' >= '+a.offsets.halfHeight+')');
						}
					}
				}
			
			dd.onDragEnd = function(x,y,e) { 
				e.style.top = '0';
				e.className = '';
				e.innerHTML += '';
				SI.Sortable.refreshElems();
				SI.Sortable.updateInputs();
				}
			}
		},
	updateInputs		: function() {
		// had to do this after the fact because Safari eats the updated values
		// after using `innerHTML += ''` to force a window redraw
		var order = '';
		var disabled = false;
		for (var i=0; i<this.elems.length; i++) {
			var e = this.elems[i];
			if (e.id=='disable') { disabled = true; }
			
			var inputs = e.getElementsByTagName('input');
			if (inputs.length) { 
				order += inputs[0].value+',';
				inputs[1].value = (disabled)?0:1;
				e.className = (disabled)?'disabled':'';
				//SI.Debug.output(e.innerHTML.replace(/(<[^>]*>)*/,'')+((disabled)?' disabled':' enabled'));
				}
			}
		order = order.replace(/,$/,'');
		document.getElementById('pane_order').value = order;
		//SI.Debug.output('New Order: '+order);
		}
	};



/**************************************************
 * dom-drag.js
 * 09.25.2001
 * www.youngpup.net
 **************************************************
 * 2001-10-28 - fixed minor bug where events
 * sometimes fired off the handle, not the root.
 *
 * 2005-04-29 Jesse Ruderman - mangled so it probably
 * only works for reordering lists; made it keep
 * hold of the item better when onDrag moves
 * the element within the DOM or when the user
 * scrolls.
 **************************************************/

var Drag = {
	obj : null, 
	init : function(o, oRoot, minX, maxX, minY, maxY, bSwapHorzRef, bSwapVertRef, fXMapper, fYMapper) {
		o.onmousedown = Drag.start;
		
		o.hmode     = bSwapHorzRef ? false : true ;
		o.vmode     = bSwapVertRef ? false : true ;
		
		o.root = (oRoot && oRoot!=null)?oRoot:o;
		
		if (o.hmode  && isNaN(parseInt(o.root.style.left  ))) o.root.style.left   = "0px";
		if (o.vmode  && isNaN(parseInt(o.root.style.top   ))) o.root.style.top    = "0px";
		if (!o.hmode && isNaN(parseInt(o.root.style.right ))) o.root.style.right  = "0px";
		if (!o.vmode && isNaN(parseInt(o.root.style.bottom))) o.root.style.bottom = "0px";
		
		o.minX  = typeof minX != 'undefined' ? minX : null;
		o.minY  = typeof minY != 'undefined' ? minY : null;
		o.maxX  = typeof maxX != 'undefined' ? maxX : null;
		o.maxY  = typeof maxY != 'undefined' ? maxY : null;
		
		o.xMapper = fXMapper ? fXMapper : null;
		o.yMapper = fYMapper ? fYMapper : null;
		
		o.root.onDragStart  = new Function();
		o.root.onDragEnd  = new Function();
		o.root.onDrag   = new Function();
		},
	start : function(e)  {
		var o = Drag.obj = this;
		e = Drag.fixE(e);
		var y = parseInt(o.vmode ? o.root.style.top  : o.root.style.bottom);
		var x = parseInt(o.hmode ? o.root.style.left : o.root.style.right );
		o.root.onDragStart(x, y);
		
		o.grabX = e.pageX - x;
		o.grabY = e.pageY - y;
		
		if (o.hmode) {
			if (o.minX != null) o.minMouseX = e.pageX - x + o.minX;
			if (o.maxX != null) o.maxMouseX = o.minMouseX + o.maxX - o.minX;
			} 
		else {
			if (o.minX != null) o.maxMouseX = -o.minX + e.pageX + x;
			if (o.maxX != null) o.minMouseX = -o.maxX + e.pageX + x;
			}
		
		if (o.vmode) {
			if (o.minY != null) o.minMouseY = e.pageY - y + o.minY;
			if (o.maxY != null) o.maxMouseY = o.minMouseY + o.maxY - o.minY;
			}
		else {
			if (o.minY != null) o.maxMouseY = -o.minY + e.pageY + y;
			if (o.maxY != null) o.minMouseY = -o.maxY + e.pageY + y;
			}
		
		document.onmousemove  = Drag.drag;
		document.onmouseup    = Drag.end;
		
		return false;
		},
	drag : function(e) {
		e = Drag.fixE(e);
		var o = Drag.obj;
		
		var ey  = e.pageY;
		var ex  = e.pageX;
		var y = parseInt(o.vmode ? o.root.style.top  : o.root.style.bottom);
		var x = parseInt(o.hmode ? o.root.style.left : o.root.style.right );
		var nx, ny;
		
		if (o.minX != null) ex = o.hmode ? Math.max(ex, o.minMouseX) : Math.min(ex, o.maxMouseX);
		if (o.maxX != null) ex = o.hmode ? Math.min(ex, o.maxMouseX) : Math.max(ex, o.minMouseX);
		if (o.minY != null) ey = o.vmode ? Math.max(ey, o.minMouseY) : Math.min(ey, o.maxMouseY);
		if (o.maxY != null) ey = o.vmode ? Math.min(ey, o.maxMouseY) : Math.max(ey, o.minMouseY);
		
		// Goal: keep (topleft - grab) constant
		// To know where to place it, we need to know its natural position.
		
		var errorY;
		
		do {
			nx = -o.grabX + ex //((ex - o.lastMouseX) * (o.hmode ? 1 : -1));
			ny = -o.grabY + ey //((ey - o.lastMouseY) * (o.vmode ? 1 : -1));
			
			if (o.xMapper)    nx = o.xMapper(y)
			else if (o.yMapper) ny = o.yMapper(x)
			
			Drag.obj.root.style[o.hmode ? "left" : "right"] = nx + "px";
			Drag.obj.root.style[o.vmode ? "top" : "bottom"] = ny + "px";
			oldOffsetTop = o.offsetTop;
			Drag.obj.root.onDrag(nx, ny, Drag.obj.root);
			
			// onDrag may have modified the DOM.  Catch up. (Idea from toolman / tim taylor)
			errorY = o.offsetTop - oldOffsetTop;
			o.grabY += errorY;
			} while(errorY);
		return false;
		},
	end : function() {
		document.onmousemove = null;
		document.onmouseup   = null;
		Drag.obj.root.onDragEnd(parseInt(Drag.obj.root.style[Drag.obj.hmode ? "left" : "right"]), 
								parseInt(Drag.obj.root.style[Drag.obj.vmode ? "top" : "bottom"]), 
								Drag.obj.root);
		Drag.obj = null;
		},

	fixE : function(e) {
		if (typeof e == 'undefined') e = window.event;
		if (typeof e.layerX == 'undefined') e.layerX = e.offsetX;
		if (typeof e.layerY == 'undefined') e.layerY = e.offsetY;
		return e;
		}
	};