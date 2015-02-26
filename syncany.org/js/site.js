	// Add tracker 
var local = window.location.hostname.match('\.lan$');
var t = document.getElementsByTagName('script')[0];		
var apihost = (local) ? 'http://api.syncany.lan/' : 'https://api.syncany.org/';

$(document).ready(function() {
	// tracker
	if (!local) {
		window.setTimeout("document.getElementsByTagName('body')[0].appendChild( getGoogleTracker() )", 3000);
		window.setTimeout("document.getElementsByTagName('body')[0].appendChild( getSyncanyTracker() )", 2000);
	}
	
	// Fancyboxes in examples
	$('#examples .fancybox').fancybox({
		scrolling: 'no',
		width: 700,
		helpers : {
			title: {
				type: 'inside'
			}
		},
		beforeLoad: function() {
			var titleElement = $(this.element).parent().find(".asciiprevdesc");

			if (titleElement) {
				this.title = titleElement.html();
			}
		}	
	});
	
	// "Scroll to anchor" links
	$(".scroll").click(function(event){		
		event.preventDefault();
		$('html,body').animate({scrollTop:$(this.hash).offset().top},1200);
		this.blur();
	});			
	
	// "Scroll to top" button
	var defaults = {
		containerID: 'toTop', // fading element id
		containerHoverID: 'toTopHover', // fading element hover id
		scrollSpeed: 1200,
		easingType: 'linear' 
	};

	$().UItoTop({ easingType: 'easeOutQuart' });	
	
	// Download links: Click-action on OS symbol
	$('#downloads a').click(function() {
		var this_link = $(this)[0];
		var div_instructions = $(this_link.hash);
	
		if (!$(this_link).hasClass('active')) {
			$('#downloads a').removeClass('active');
			$('.install_instructions').slideUp();
	
			div_instructions.slideDown();
			$(this_link).addClass('active');
		}
					
		return false;
	});

	// Download links: Show tab of current OS
	if (navigator.userAgent.indexOf('Windows') != -1) {
		$('#link_windows').addClass('active');
		$('#windows').show();
	}
	else if (navigator.userAgent.indexOf("Ubuntu") != -1 
		|| navigator.userAgent.indexOf("Linux Mint") != -1 
		|| navigator.userAgent.indexOf("Debian") != -1) {
		
		$('#link_debian').addClass('active');
		$('#debian').show();
	}
	else if (navigator.userAgent.indexOf("Arch Linux") != -1) {
		$('#link_arch').addClass('active');
		$('#arch').show();				
	}
	else if (navigator.userAgent.indexOf("OS X") != -1) {
		$('#link_mac').addClass('active');
		$('#mac').show();				
	}
	else {
		$('#link_other').addClass('active');
		$('#other').show();				
	}

	// Download links: Set target URL for big Windows "Download" link
	var is64bit = navigator.userAgent.indexOf("WOW64") != -1
		|| navigator.userAgent.indexOf("Win64") != -1 
		|| navigator.platform.indexOf("x86_64") != -1;					

	if (is64bit) {
		$('#link_windows_download').attr('href', 'https://syncany.org/r/latest-x86_64.exe');
		$('#link_windows_download').html('Syncany<br /><small>64-bit version</small>');
	}
	else {
		$('#link_windows_download').attr('href', 'https://syncany.org/r/latest-x86.exe');
		$('#link_windows_download').html('Syncany<br /><small>32-bit version</small>');
	}	
	
	// Autotype: Messages to appear
	var lineidx = 0;
	var lines = [
		{
			command: "sy plugin install webdav",
			comment: "Downloads and install the WebDAV plugin.{{enter}}{{enter}}Syncany supports many storage types, e.g.{{enter}}FTP, SFTP, WebDAV, Amazon S3, Samba, ..."
		},
		{
			command: "sy init",
			comment: "Creates a new repository using any plugin{{enter}}and generates a syncany://-link.{{enter}}{{enter}}The link contains the encrypted{{enter}}credentials to the repository."
		},				
		{
			command: "sy connect syncany://storage/1/cbyS6A...",
			comment: "Connects to an existing repository using{{enter}}a syncany://-link.{{enter}}{{enter}}The link can be safely shared with trusted{{enter}}friends or colleagues."
		},				
		{
			command: "sy up",
			comment: "Detects local changes and uploads them{{enter}}to the repository.{{enter}}{{enter}}Data is deduplicated and encrypted before{{enter}}uploading, so that it uses minimal space{{enter}}and your privacy is protected."
		},				
		{
			command: "sy down",
			comment: "Downloads changes by other users and{{enter}}applies them to your local machine."
		},				
		{
			command: "sy daemon start",
			comment: "Starts background daemon to automatically{{enter}}sync your files.{{enter}}{{enter}}The daemon can manage multiple Syncany{{enter}}folders and automatically detects{{enter}}remote and local changes."
		},
		{
			command: "sy restore --revision=2 de9329ca7",
			comment: "Restores an old version of a file to{{enter}}the local folder.{{enter}}{{enter}}Syncany stores old file versions that{{enter}}can be restored if needed. By default,{{enter}}up to 5 versions of each file are kept."
		},
		{
			command: "sy --help",
			comment: "There are a few other commands that{{enter}}might be interesting. Be sure to check{{enter}}out the --help page or navigate to the{{enter}}Syncany user guide."
		},												
	]
		
	
	// Autotype: Disable spellcheck (red underlining) and focusability
	$('#autotype1').focus(function() { $(this).blur(); });
	$('#autotype2').focus(function() { $(this).blur(); });

	$('#autotype1').attr('spellcheck', false);
	$('#autotype2').attr('spellcheck', false);
	
	// Autotype: After "command" is done, wait one second before explanation text starts
	$('#autotype1').bind('autotyped', function() { 
		setTimeout(function() {
			$('#autotype2').val('').autotype(lines[lineidx].comment, { delay: 40 });
		}, 1000);
	});				
		
	// Autotype: After "comment" is done, wait 2.5 seconds moving to the next command
	$('#autotype2').bind('autotyped', function() { 
		lineidx = (lineidx + 1) % lines.length; 
		
		setTimeout(function() {
			$('#autotype1').val('$ ').autotype(lines[lineidx].command, { delay: 40 });						
			$('#autotype2').val('');
		}, 2500);
	});

	// Autotype: Start with the first command
	if ($.isFunction($('#autotype1').val('$ ').autotype)) { // Only run if 'autotype' is there!
		$('#autotype1').val('$ ').autotype(lines[lineidx].command, { delay: 40 });
		$('#autotype2').val('');
	}
	
	// Examples previews scroller
	$('.asciiprevs').slick({
		dots: false,
		infinite: true,
		speed: 300,
		slidesToShow: 3,
		slidesToScroll: 1,
		autoplay: true,
		autoplaySpeed: 10000,
		responsive: [
			{
				breakpoint: 1120,
				settings: {
					slidesToShow: 2,
				}
			},
			{
				breakpoint: 715,
				settings: {
					slidesToShow: 1,
				}
			}
		]
	});	
});
		

// FUNCTIONS ///////////////////////////////////////////////////////////////////

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
