(function(){
const historySize = 10;
const scrollHistorySize = 20;

function uriContainsParameter( href, name )
{
	if( -1 !== href.indexOf('&' + name + '=') ) return true;
	if( -1 !== href.indexOf('?' + name + '=') ) return true;
	return false;
}

// https://plainjs.com/javascript/ajax/send-ajax-get-and-post-requests-47/
function getAjax( url, funcSuccess )
{
	var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject('Microsoft.XMLHTTP');
	xhr.open( 'GET', url );
	xhr.onreadystatechange = function(){
		if( xhr.readyState <= 3 ) return;
		if( 200 == xhr.status ){
			funcSuccess( xhr.responseText, xhr.responseURL );
		}
		else {
			funcSuccess( xhr.responseText, xhr.responseURL );
		}
	};
	xhr.setRequestHeader( 'X-Requested-With', 'XMLHttpRequest' );
	xhr.send();
	return xhr;
}

function postAjax( url, data, funcSuccess )
{
	var params = typeof data == 'string' ? data : Object.keys(data).map(
		function(k){ return encodeURIComponent(k) + '=' + encodeURIComponent(data[k]) }
		).join('&');

	var xhr = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
	xhr.open( 'POST', url );
	xhr.onreadystatechange = function(){
		if( xhr.readyState <= 3 ) return;
		if( 200 == xhr.status ){
			funcSuccess( xhr.responseText, xhr.responseURL );
		}
		else {
			funcSuccess( xhr.responseText, xhr.responseURL );
		}
	};
	xhr.setRequestHeader( 'X-Requested-With', 'XMLHttpRequest' );
	xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );
	xhr.send( params );
	return xhr;
}

// example request
// postAjax('http://foo.bar/', 'p1=1&p2=Hello+World', function(data){ console.log(data); });
// postAjax('http://foo.bar/', { p1: 1, p2: 'Hello World' }, function(data){ console.log(data); });

function serializeForm( form )
{
	if( typeof form != 'object' ) return '';
	if( 'FORM' != form.nodeName ) return '';

	var field, l, s = [];
	var len = form.elements.length;
	for( var i=0; i < len; i++ ){
		field = form.elements[i];
		if( ! field.name ) continue;
		if( field.disabled ) continue;
		if( 'reset' == field.type ) continue;
		if( 'submit' == field.type ) continue;
		if( 'button' == field.type ) continue;
		// if( 'file' == field.type ) continue;

		if( 'select-multiple' == field.type ){
			l = form.elements[i].options.length; 
			for (var j=0; j<l; j++) {
				if(field.options[j].selected)
					s[s.length] = encodeURIComponent(field.name) + "=" + encodeURIComponent(field.options[j].value);
			}
		}
		else {
			if ( 'checkbox' == field.type ){
				if( ! field.checked ) continue;
			}
			if ( 'radio' == field.type ){
				if( ! field.checked ) continue;
			}
			s[s.length] = encodeURIComponent(field.name) + "=" + encodeURIComponent(field.value);
		}
	}

	return s.join('&').replace(/%20/g, '+');
}

function findLink( el )
{
	if( ('A' == el.tagName) && el.href ) return el;
	return el.parentElement ? findLink( el.parentElement ) : null;
}

function setLoader( el )
{
	el.classList.add( 'pw-loading' );
}

function unsetLoader( el )
{
	el.classList.remove( 'pw-loading' );
}

function canAccessLocalStorage()
{
	var test = 'plainware:testLocalStorage';
	try {
		localStorage.setItem( test, test );
		localStorage.removeItem( test );
		return true;
	}
	catch(e){
		return false;
	}
}

function loadFromHistory( url )
{
	if( ! canAccessLocalStorage() ) return null;
	if( ! historySize ) return null;

	var historyList = JSON.parse( localStorage.getItem('plainware-history') ) || [];
	for( var i = 0; i < historyList.length; i++ ){
		if( url == historyList[i].url ){
			return historyList[i];
			break;
		}
	}

	return null;
}

function saveScrollPosition( href, scrollY )
{
	if( ! canAccessLocalStorage() ) return;

	var newHistoryItem = { href:href, scroll: scrollY };

	var listHistory = JSON.parse( localStorage.getItem('plainware-scroll-history') ) || [];
	for( var i = 0; i < listHistory.length; i++ ){
		if( listHistory[i].href === newHistoryItem.href ){
			listHistory.splice( i, 1 );
			break;
		}
	}

	listHistory.push( newHistoryItem );
	while( listHistory.length > scrollHistorySize ){
		listHistory.shift();
	}

	while( listHistory.length > 0 ){
		try {
			localStorage.setItem( 'plainware-scroll-history', JSON.stringify(listHistory) );
			break;
		}
		catch(e){
			listHistory.shift(); // shrink the cache and retry
		}
	}

	// console.log( 'Save scroll: ' + newHistoryItem.href + ' => ' + newHistoryItem.scroll );
}

function findScrollPosition( href )
{
	if( ! canAccessLocalStorage() ) return 0;
	if( ! scrollHistorySize ) return 0;

	var listHistory = JSON.parse( localStorage.getItem('plainware-scroll-history') ) || [];
	for( var i = 0; i < listHistory.length; i++ ){
		if( document.location.href == listHistory[i].href ){
			// console.log( 'Got scroll: ' + listHistory[i].href + ' => ' + listHistory[i].scroll );
			return listHistory[i].scroll;
			break;
		}
	}

	return 0;
}

function saveToHistory( url, content )
{
	if( ! canAccessLocalStorage() ) return;
	if( ! historySize ) return;

	var newHistoryItem = { url:url, content: content, scroll: window.scrollY };
	// console.log( 'saveToHistory:' +  newHistoryItem.url );

	var historyList = JSON.parse( localStorage.getItem('plainware-history') ) || [];
	for( var i = 0; i < historyList.length; i++ ){
		if( historyList[i].url === url ){
			historyList.splice( i, 1 );
			break;
		}
	}

	historyList.push( newHistoryItem );
	while( historyList.length > historySize ){
		historyList.shift();
	}

	while( historyList.length > 0 ){
		try {
			localStorage.setItem( 'plainware-history', JSON.stringify(historyList) );
			break;
		}
		catch(e){
			historyList.shift(); // shrink the cache and retry
		}
	}
	// console.log( 'history size ' + historyList.length );
}

function ajaxContainer( $container )
{
	var parentTarget = null;
	var slugParamName = $container.getAttribute( 'data-pw-slug-param-name' );
	if( null === slugParamName ) slugParamName = 'p';
	var layoutParamName = $container.getAttribute( 'data-pw-layout-param-name' );
	if( null === layoutParamName ) layoutParamName = 'layout-';

	window.onpopstate = function( e ){
		var url = document.location.href;

		var storedItem = loadFromHistory( url );
		if( null !== storedItem ){
// console.log( 'has content for ' + url );
			setContent( $container, storedItem.content );
		}
		else {
// console.log( 'no content for ' + url );
			loadUrl( url, $container, $container );
		}
	};

	function convertUrl( href, layoutParamValue )
	{
	// tell to load partial only
		var glue = ( -1 !== href.indexOf('?') ) ? '&' : '?';
		href = href + glue + layoutParamName + '=' + layoutParamValue;

	// wordpress admin? change url to admin-ajax.php
		if( typeof ajaxurl !== 'undefined' ){
			if( -1 !== href.indexOf('admin.php?page=') ){
				href = href.replace( 'admin.php?page=', 'admin-ajax.php?action=' );
			}
		}

		return href;
	}

	function deconvertUrl( href )
	{
	// has layout param?
		var pos = href.indexOf( '?' + layoutParamName + '=' );
		if( -1 == pos ){
			var pos = href.indexOf( '&' + layoutParamName + '=' );
		}

		if( -1 !== pos ){
		// anything after our layout param?
			var pos2 = href.indexOf( '&', pos + 1 );
			if( -1 !== pos2 ){
				// href = href.substring( 0, pos ) + href.substring( pos2 );
				var href1 = href.substring( 0, pos );
				var href2 = href.substring( pos2 + 1 );
				var glue = ( -1 !== href1.indexOf('?') ) ? '&' : '?';
				href = href1 + glue + href2;
			}
			else {
				href = href.substring( 0, pos );
			}
		}

	// wordpress admin? change url from admin-ajax.php back to admin.php
		if( typeof ajaxurl !== 'undefined' ){
			if( -1 !== href.indexOf('admin-ajax.php?action=') ){
				href = href.replace( 'admin-ajax.php?action=', 'admin.php?page=' );
			}
		}

		return href;
	}

	function clickLink( e )
	{
		var a = findLink( e.target );
		if( null === a ) return;

	// target = _blank?
		var aTarget = a.getAttribute( 'target' );
		if( '_blank' == aTarget ) return

		var href = a.href;

	// contains our slug
		if( ! uriContainsParameter(href, slugParamName) ) return;

	// already contains layout param?
		if( uriContainsParameter(href, layoutParamName) ) return;

		e.preventDefault();

	// has data-pw-target parameter that may redefine the target?
		var customTarget = a.getAttribute( 'data-pw-target' );
		if( 'parent' != customTarget ){
			this.parentTarget = null;
		}

		var $customContainer = null;
		if( customTarget ){
			if( 'parent' == customTarget ){
				$customContainer = this.parentTarget;
			}
			else {
				if( customTarget.startsWith('closest:') ){
					const selector = customTarget.substring( 'closest:'.length );
					$customContainer = a.closest( selector );
					// console.log( $customContainer );
				}
				else {
					$customContainer = document.querySelector( customTarget );
				}
				this.parentTarget = $customContainer;
			}
// alert( $customContainer.innerHTML );
// return false;
		}

	// has data-pw-target parameter that may redefine the target?
		var keepScroll = a.hasAttribute( 'data-pw-keep-scroll' );
		if( keepScroll ){
			saveScrollPosition( href, window.scrollY );
		}

		if( $customContainer ){
			var partial = a.getAttribute( 'data-pw-partial' );
			if( ! partial ) partial = 'none';
			loadPartialUrl( href, $customContainer, partial );
		}
		else {
			loadUrl( href, $container, a );
		}
	}

	function loadPartialUrl( uri, el, partial )
	{
		var realUri = convertUrl( uri, partial );
		console.log( 'GET:' + realUri );

		var holder = el;
		while( holder ){
			if( holder == $container ) break;
			// console.log( holder );
			if( 'none' === window.getComputedStyle(holder).display ){
				holder.style.display = 'block';
			}
			holder = holder.parentNode;
		}

	// temporarily hide header in holder
		var holderHeader = el.parentNode.querySelector( 'header' );
		if( holderHeader ){
			holderHeader.style.display = 'none';
		}
		el.innerHTML = '';

		setLoader( el );

		getAjax( realUri, function(data, finalUri){
			if( realUri != finalUri ){
				uri = deconvertUrl( finalUri );
				// console.log( 'deconvert' + "\n" + finalUri + "\n" + uri );
			}

			unsetLoader( el );
			setContent( el, data );
			if( holderHeader ){
				holderHeader.style.display = 'block';
			}
		});

		return false;
	}

	function loadUrl( uri, el, srcEl )
	{
		saveScrollPosition( document.location.href, window.scrollY );

		var realUri = convertUrl( uri, 'ajax' );
console.log( 'GET:' + realUri );

		// var markEl = $container;
		var markEl = srcEl;
		setLoader( markEl );

		getAjax( realUri, function(data, finalUri){
		// after redirect?
// alert( realUri + ' VS ' + finalUri );
// console.log( 'after redirect: ' + "\n" + realUri + "\n" + finalUri );
			if( realUri != finalUri ){
				uri = deconvertUrl( finalUri );
console.log( 'deconvert' + "\n" + finalUri + "\n" + uri );
			}

			unsetLoader( markEl );
			setContent( el, data );

			if( window.history.pushState ){
				window.history.pushState( {}, null, uri );
				saveToHistory( uri, data );
			}

			var scrollY = findScrollPosition( uri );
			if( scrollY ){
				window.scroll( 0, scrollY );
			}
			else {
				let elHeight = el.offsetTop + el.offsetHeight;
				if( elHeight > window.innerHeight ){
					el.scrollIntoView();
				}
			}
		});

		return false;
	}

	function submitForm( e )
	{
		var href = e.target.getAttribute( 'action' );
		if( ! href ){
			href = document.location.href;
		}
		if( ! href ){
			console.log( 'no action uri for form' );
			return;
		}

		var noAjax = e.target.getAttribute( 'data-pw-noajax' );
		if( noAjax ) return;

		var withFileUpload = ( 'multipart/form-data' == e.target.getAttribute('enctype') ) ? true : false;
		if( withFileUpload ) return;

	// contains our slug
		if( ! uriContainsParameter(href, slugParamName) ) return;

		e.preventDefault();

		var realUri = convertUrl( href, 'ajax' );
		console.log( 'POST:' + realUri );

		// var markEl = $container;
		var markEl = e.target;
		setLoader( markEl );

		var data = serializeForm( e.target );
// console.log( data );
		postAjax( realUri, data, function(data, finalUri){
			if( realUri != finalUri ){
				uri = deconvertUrl( finalUri );
				// console.log( 'deconvert' + "\n" + finalUri + "\n" + uri );
			}

			unsetLoader( markEl );
			setContent( $container, data );

			if( window.history.pushState ){
// console.log( 'push ' + uri );
				window.history.pushState( {}, null, uri );
				// saveToHistory( uri, data );
			}

			var scrollY = findScrollPosition( uri );
			if( scrollY ){
				window.scroll( 0, scrollY );
			}
			else {
				$container.scrollIntoView();
			}
		});

		return false;
	};

	function setContent( el, content )
	{
		el.innerHTML = content;

	// inline script
		el.querySelectorAll('script').forEach( function(e){
			// alert( 'evaling' );
			eval( e.innerHTML );
		});

	// emit event
		var ev = new CustomEvent( 'pwLoaded', {el: el, content: content} );
		document.dispatchEvent( ev );
	}

	$container.addEventListener( 'click', clickLink, false );
	$container.addEventListener( 'submit', submitForm, false );
}

document.addEventListener( 'DOMContentLoaded', function()
{
	var containerList = document.getElementsByClassName( 'pw-ajax-container' );
	for( var ii = 0; ii < containerList.length; ii++ ){
		ajaxContainer( containerList[ii] );
	}
});

})();