// Add tracker 
var local = window.location.hostname.match('\.lan$');
var t = document.getElementsByTagName('script')[0];		
var apihost = (local) ? 'http://api.syncany.lan/' : 'http://api.syncany.org/';

$(document).ready(function() {
	// screenshots
	$("a[rel=screenshots]").fancybox({
		'transitionIn'		: 'elastic',
		'transitionOut'		: 'elastic',
		'titlePosition' 	: 'over',
		'titleFormat'		: function(title, currentArray, currentIndex, currentOpts) {
			colon = title.indexOf(':');
			title = (colon > 0) ? '<b>'+title.substring(0, colon)+'</b>'+title.substring(colon) : title;
			return '<span id="fancybox-title-over">' + title + '</span>';
		}
	});	
	
	// tracker
	if (!local) {
		window.setTimeout("document.getElementsByTagName('body')[0].appendChild( getExtremeTracker() )", 1000);
		window.setTimeout("document.getElementsByTagName('body')[0].appendChild( getGoogleTracker() )", 3000);
	}

		window.setTimeout("document.getElementsByTagName('body')[0].appendChild( getSyncanyTracker() )", 2000);
			
	// flattr			
	var s = document.createElement('script');
	s.type = 'text/javascript';
	s.async = true;
	s.src = 'http://api.flattr.com/js/0.6/load.js?mode=auto';
	t.parentNode.insertBefore(s, t);				
});
		



// FUNCTIONS ///////////////////////////////////////////////////////////////////

/* Extreme Tracking */
function getExtremeTracker() {
	var EXlogin    = 'sync0';
	var EXvsrv     = 's10';
	var EXdomain   = 'e1';

	// js 1.2
	EXs=screen;EXw=EXs.width;navigator.appName!="Netscape"?
	EXb=EXs.colorDepth:EXb=EXs.pixelDepth;

	// js 1.0
	navigator.javaEnabled()==1?EXjv="y":EXjv="n";
	EXd=document; EXw?"":EXw="na"; EXb?"":EXb="na";
	
	var imgObj = document.createElement("img");
	
	imgObj.width = 1;
	imgObj.height = 1;
	imgObj.style.display = 'none';
	imgObj.src = "http://"+EXdomain+".extreme-dm.com"+"/"+EXvsrv+".g?login="+EXlogin+"&"+"jv="+EXjv+"&j=y&srw="+EXw+"&srb="+EXb+"&"+"l="+escape(EXd.referrer);
	
	return imgObj;
}

/* Syncany Tracking */
function getSyncanyTracker() {
	var EXsite = 'syncany.org';

	// js 1.2
	EXs=screen;
	EXw=EXs.width; 
	EXh=EXs.height;
	navigator.appName!="Netscape"?EXb=EXs.colorDepth:EXb=EXs.pixelDepth;

	// js 1.0
	navigator.javaEnabled()==1?EXjv="1":EXjv="0";
	EXd=document; 
	
	var imgObj = document.createElement("img");
	
	imgObj.width = 1;
	imgObj.height = 1;
	imgObj.style.display = 'none';
	imgObj.src = apihost+"ping.php?s="+EXsite+"&j="+EXjv+"&w="+EXw+"&h="+EXh+"&d="+EXb+"&"+"r="+escape(EXd.referrer);
	
	return imgObj;
}

/* Google Analytics */
var _gaq = _gaq || [];
_gaq.push(['_setAccount', 'UA-23187925-1']);
_gaq.push(['_trackPageview']);

function getGoogleTracker() {
	var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
//	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
	return ga;
}
