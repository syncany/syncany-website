<?php

	require("main.inc.php");
	
?>
<!DOCTYPE HTML>
<html>
<!--

Website layout based on:
	A Design by W3layouts
	Author: W3layout
	Author URL: http://w3layouts.com
	License: Creative Commons Attribution 3.0 Unported
	License URL: http://creativecommons.org/licenses/by/3.0/
	
Massively modified by:
	Pim Otte and Philipp Heckel
	
-->
<head>
	<title>Syncany - Secure file sync software for arbitrary storage backends</title>
	
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

	<!-- Site CSS and Fonts -->
	<link href='https://fonts.googleapis.com/css?family=Lato:100,300,400,700,900' rel='stylesheet' type='text/css'>

	<!-- fontawesome.io -->
	<link href="<?php echo $cdnhost; ?>fontawesome/4.0.3/css/font-awesome.min.css" rel="stylesheet">

	<!-- jQuery -->
	<script src="<?php echo $cdnhost; ?>fancybox/lib/jquery-1.10.1.min.js"></script>

	<!-- Live GitHub; http://ben.balter.com -->
	<script type="text/javascript" src="<?php echo $cdnhost; ?>js/github-sentences.js"></script> 
	<link rel="stylesheet" type="text/css" href="<?php echo $cdnhost; ?>css/github-main.css" />		

	<!-- fancybox: Add mousewheel plugin (this is optional) -->
	<script type="text/javascript" src="<?php echo $cdnhost; ?>fancybox/lib/jquery.mousewheel-3.0.6.pack.js"></script>

	<!-- fancybox: Add fancyBox -->
	<link rel="stylesheet" href="<?php echo $cdnhost; ?>fancybox/source/jquery.fancybox.css?v=2.1.5" type="text/css" media="screen" />
	<script type="text/javascript" src="<?php echo $cdnhost; ?>fancybox/source/jquery.fancybox.pack.js?v=2.1.5"></script>

	<!-- UItoTop (scroll to top button & dependencies) -->
	<script type="text/javascript" src="<?php echo $cdnhost; ?>js/jquery.easing.min.js"></script>		
	<script type="text/javascript" src="<?php echo $cdnhost; ?>js/move-top.js"></script>

	<!-- Autotype  -->
	<script type="text/javascript" src="<?php echo $cdnhost; ?>js/jquery.autotype.js"></script>

	<!-- Slick image slider  -->
	<script type="text/javascript" src="<?php echo $cdnhost; ?>/js/jquery-migrate-1.2.1.min.js"></script>    
    	<script type="text/javascript" src="<?php echo $cdnhost; ?>slick/slick.min.js"></script>
	<link rel="stylesheet" href="<?php echo $cdnhost; ?>slick/slick.css" type="text/css" media="screen" />

	<!-- This is our code! --> 
	<script type="text/javascript" src="<?php echo $cdnhost; ?>js/site.js?v=4"></script>	
	<link href="<?php echo $cdnhost; ?>css/style.css?v=4" rel="stylesheet" type="text/css" media="all" />
	<link rel="shortcut icon" href="<?php echo $cdnhost; ?>favicon.ico" />		
</head>

<body>

<!-- GitHub "Fork me" ribbon-->
<a class="githubribbon" href="https://github.com/syncany/syncany"><img style="position: absolute; top: 0; right: 0; border: 0;" src="https://camo.githubusercontent.com/365986a132ccd6a44c23a9169022c0b5c890c387/68747470733a2f2f73332e616d617a6f6e6177732e636f6d2f6769746875622f726962626f6e732f666f726b6d655f72696768745f7265645f6161303030302e706e67" alt="Fork me on GitHub" data-canonical-src="https://s3.amazonaws.com/github/ribbons/forkme_right_red_aa0000.png"></a>

<!-- header -->
<div class="header_bg">
	<div class="wrap">
		<div class="header">
			<div class="nav">
				<ul>
				   <li class="active"><a href="#home" class="scroll">Home</a></li>
				   <li><a href="#about" class="scroll">About</a></li>
				   <li><a href="#download" class="scroll">Download</a></li>
				   <li><a href="#news" class="scroll">News</a></li>
				   <li><a href="#examples" class="scroll">Examples</a></li>
				   <li><a href="#contact" class="scroll">Contact</a></li>
				 </ul>
			</div>

			<div class="topsocial">
				<a class="github" href="https://github.com/syncany/syncany"><i class="fa fa-github"></i></a>
				<a class="twitter" href="https://twitter.com/syncany"><i class="fa fa-twitter"></i></a>
				<a class="gplus" href="https://plus.google.com/u/0/communities/101993748229314848303"><i class="fa fa-google-plus"></i></a>
				<a class="youtube" href="https://www.youtube.com/channel/UCzegH3dpTK5HHQx6jJ5yhdQ"><i class="fa fa-youtube-play"></i></a>
				<a class="bitcoin" href="bitcoin:1626wjrw3uWk9adyjCfYwafw4sQWujyjn8?amount=0.05&amp;message=Syncany%20Donation"><i class="fa fa-bitcoin"></i></a>
			</div>	

			<div class="clear"> </div>
		</div>
	</div>
</div>

<div class="dark_bg" id="home">
	<div class="wrap">
	        <p><img src="<?php echo $cdnhost; ?>images/syncany-logo.png" /></p>
		<h2>Syncany</h2>

	        <p class="tagline">
			An open-source cloud storage and filesharing application.
			Securely synchronize your files to any kind of storage!
		</p>

		<a href="#about" class="scroll da-link">Learn more</a>
		<a href="#about" class="scroll"><span class="da-img"> </span></a>
		
		<div class="clear"> </div>
	</div>
</div>

<div class="light_bg" id="about">
	<div class="wrap">
		<h2>What is Syncany?</h2>

		<p>
			Syncany allows users to backup and share certain folders of their workstations
			using any kind of storage. Syncany is open-source and provides data encryption
			and incredible flexibility in terms of storage type and provider.
		</p>
		
		<div class="about-grids">
			<div class="about-item">
				<div class="about-icon"><i class="fa fa-refresh"></i></div> 
				<h3 class="about-heading">Sync your Files</h3>
				<p class="about-text">Backup your photo collection and share files with friends.</p>
			</div> 
			<div class="about-item">
				<div class="about-icon"><i class="fa fa-lock"></i></div> 
				<h3 class="about-heading">Encrypted</h3>
				<p class="about-text">Don't worry about your privacy. Files are encrypted before uploading.</p>
			</div> 
			<div class="about-item">
				<div class="about-icon"><i class="fa fa-cloud"></i></div> 
				<h3 class="about-heading">Any Storage</h3>
				<p class="about-text">Use any kind of storage - (S)FTP, WebDAV, and many more!</p>
			</div> 
		</div> 
		
		<div class="clear"> </div>
     	</div>
</div>

<div class="dark_bg" id="download">
	<div class="wrap">
		<h2>Get it now!</h2>
	        
		<p>Excited? You can download a working <b>alpha version</b> here.</p>		
		
		<p class="divider">
			<span class="fa fa-star"></span>
			<span class="fa fa-star"></span>
			<span class="fa fa-star"></span>
		</p>
		
		<div id="downloads">
			<a id="link_debian" href="#debian"><img src="<?php echo $cdnhost; ?>images/os-ubuntu.png" /></a>
			<a id="link_arch" href="#arch"><img src="<?php echo $cdnhost; ?>images/os-arch.png" /></a>
			<a id="link_windows" href="#windows"><img src="<?php echo $cdnhost; ?>images/os-win.png" /></a>
			<a id="link_mac" href="#mac"><img src="<?php echo $cdnhost; ?>images/os-mac.png" /></a>
			<a id="link_docker" href="#docker"><img src="<?php echo $cdnhost; ?>images/os-docker.png" /></a>
			<a id="link_other" href="#other"><img src="<?php echo $cdnhost; ?>images/os-other.png" /></a>
			<a id="link_code" href="#code"><img src="<?php echo $cdnhost; ?>images/github.png" /></a>
		</div>		
				
		<div id="debian" class="install_instructions">
			<p>
				Debian/Ubuntu users can use our <a href="https://get.syncany.org/apt/">APT archive</a>:<br />
				<tt>curl -sL <a href="https://get.syncany.org/debian/">https://get.syncany.org/debian/</a> | sh</tt>
			</p>
			
			<p>
				Or install the .deb archives manually:
			
				<br />
				
				<img src="<?php echo $cdnhost; ?>images/os-ubuntu.png" style="width: 25px; height: 25px;" />
				<a class="dl-link" href="https://syncany.org/r/latest.deb">Syncany</a> <em>and</em>
	
				<img src="<?php echo $cdnhost; ?>images/os-ubuntu.png" style="width: 25px; height: 25px;" />
				<a class="dl-link" href="https://syncany.org/r/plugin-gui-latest-i386.deb">Syncany GUI (32-bit)</a>
				
				<br />
			
				<img src="<?php echo $cdnhost; ?>images/os-ubuntu.png" style="width: 25px; height: 25px;" />
				<a class="dl-link" href="https://syncany.org/r/plugin-gui-latest-amd64.deb">Syncany GUI (64-bit)</a>
			</p>						
		</div>
		
		<div id="arch" class="install_instructions">
			<p>
				Arch Linux users can use the <a href="https://aur.archlinux.org/packages/syncany/">AUR package</a>
				to install Syncany. An AUR helper like yaourt could help with this:
			</p>
			
			<p>
				<tt>yaourt -S syncany</tt>
			</p>
		</div>
		
		<div id="windows" class="install_instructions">
			<p>
				Windows users can use the our installer:
			</p>
			
			<a id="link_windows_download" href="https://syncany.org/r/latest-x86_64.exe" class="da-link2">
				<!-- Note: this is replaced by JS in site.js! -->
				
				<img src="<?php echo $cdnhost; ?>images/os-win.png" /><br />
				Syncany<br />
				<small>64-bit</small>
			</a> 
						
			<p>
				<img src="<?php echo $cdnhost; ?>images/os-win.png" style="width: 22px; height: 22px;" />
				<a class="dl-link" href="https://syncany.org/r/latest-x86.exe">Syncany (32-bit)</a> 
				
				&nbsp;
				
				<img src="<?php echo $cdnhost; ?>images/os-win.png" style="width: 22px; height: 22px;" />
				<a class="dl-link" href="https://syncany.org/r/latest-x86_64.exe">Syncany (64-bit)</a>
				
				<br />
				
				<img src="<?php echo $cdnhost; ?>images/os-win.png" style="width: 22px; height: 22px;" />
				<a class="dl-link" href="https://syncany.org/r/cli-latest.exe">Syncany (CLI only, 32/64-bit)</a>				
			</p>
		</div>

		<div id="mac" class="install_instructions">
			<p>
				Mac OSX users can install the .app.zip:
			</p>
			
			<a href="https://syncany.org/r/latest-x86_64.app.zip" class="da-link2">
				<img src="<?php echo $cdnhost; ?>images/os-mac.png" /><br />
				Syncany<br />
				<small>64-bit</small>
			</a> 
			
			<p>
				Or install the use our <a href="http://brew.sh/">Homebrew</a> formula:<br />
				<tt>brew install <a href="https://get.syncany.org/homebrew/syncany.rb">https://get.syncany.org/homebrew/syncany.rb</a></tt>
			</p>			
		</div>
		
		<div id="docker" class="install_instructions">
			<p>
				To try Syncany inside a Docker container, 
				you can use the <a href="https://registry.hub.docker.com/u/syncany/release/">syncany/release</a> repository:
			</p>
			
			<p>
				<tt>docker pull syncany/release</tt><br />
				<tt>docker run -ti syncany/release</tt>
			</p>
		</div>		

		<div id="other" class="install_instructions">
			<p>
				If there is no installer/package for your platform, you can still use Syncany by
				downloading and extracting the .zip/.tar.gz archive:
			</p>
			
			<p>
				<img src="<?php echo $cdnhost; ?>images/os-other.png" style="width: 22px; height: 22px;" />
				<a class="dl-link" href="https://syncany.org/r/latest.zip">Syncany (zip)</a>
			
				<img src="<?php echo $cdnhost; ?>images/os-other.png" style="width: 22px; height: 22px;" />
				<a class="dl-link" href="https://syncany.org/r/latest.tar.gz">Syncany (tar.gz)</a>
			</p>
		</div>
		
		<div id="code" class="install_instructions">
			<p>
				Syncany is open source and distributed under GPLv3.<br />
				To build it yourself, check out the code on <a href="https://github.com/syncany/syncany">GitHub</a>.
			</p>			
		</div>		
		
		<p class="divider" style="margin-top: 0">
			<span class="fa fa-star"></span>
			<span class="fa fa-star"></span>
			<span class="fa fa-star"></span>
		</p>


		<p style="max-width: 580px">
			Once you've installed Syncany, try out the follow commands and
			check out our awesome <a class="bold" href="https://www.syncany.org/r/userguide">User Guide</a>!
		</p>
		
		<div id="autotype" >
			<div>
				<textarea id="autotype1" class="codeline" rows="1"></textarea>
				<textarea id="autotype2" class="codecomment" rows="6"></textarea>
			</div>
		</div>
		
		<div class="clear"> </div>				
	</div>
</div>

<div class="light_bg" id="news">
	<div class="wrap">	
		<div class="newscol">
			<h2>News</h2>

			<p>
				<b>28 Dec 2014</b>: We've reached another milestone. The Syncany core feels more stable by the minute. We released 
					<a href="https://github.com/syncany/syncany/releases/tag/v0.2.0-alpha">v0.2.0-alpha</a>,
					<a href="https://github.com/syncany/syncany/releases/tag/v0.3.0-alpha">v0.3.0-alpha</a>,
					<a href="https://github.com/syncany/syncany/releases/tag/v0.4.0-alpha">v0.4.0-alpha</a>, 
				with v0.4.0-alpha being the best release we have ever had. The GUI (tray icon and 'New folder' wizard) works quite well. 
				You can see it in action on our <a href="https://www.youtube.com/channel/UCzegH3dpTK5HHQx6jJ5yhdQ">YouTube channel</a>. 
				We also made a couple of new plugins for 
					<a href="https://github.com/syncany/syncany-plugin-flickr">Flickr</a> (encode data in images, <a href="https://www.youtube.com/watch?v=zdUucWr3wKA">video here</a>),
					<a href="https://github.com/syncany/syncany-plugin-flickr">Dropbox</a>,
					<a href="https://github.com/syncany/syncany-plugin-swift">OpenStack Swift</a> and
					<a href="https://github.com/syncany/syncany-plugin-raid0">RAID0</a>.
				Beta is just around the corner!
			</p>

			<p>
				<b>20 Oct 2014</b>: In releases 
					<a href="https://github.com/syncany/syncany/releases/tag/v0.1.6-alpha">v0.1.6-alpha</a>,
					<a href="https://github.com/syncany/syncany/releases/tag/v0.1.7-alpha">v0.1.7-alpha</a>,
					<a href="https://github.com/syncany/syncany/releases/tag/v0.1.8-alpha">v0.1.8-alpha</a>,
					<a href="https://github.com/syncany/syncany/releases/tag/v0.1.9-alpha">v0.1.9-alpha</a>,
					<a href="https://github.com/syncany/syncany/releases/tag/v0.1.10-alpha">v0.1.10-alpha</a>,
					<a href="https://github.com/syncany/syncany/releases/tag/v0.1.11-alpha">v0.1.11-alpha</a> and
					<a href="https://github.com/syncany/syncany/releases/tag/v0.1.12-alpha">v0.1.12-alpha</a>,
				we have added a <a href="https://github.com/syncany/syncany-plugin-samba">Samba plugin</a>,
				a working REST/WS-based daemon, 
				a <a href="https://github.com/syncany/syncany-plugin-gui">first version of the GUI</a> (<a href="https://www.youtube.com/watch?v=eHoA5_8gRBc">cross-platform demo on YouTube</a>),
				and written an awesome <a href="https://syncany.org/r/userguide">user guide</a>. 
				We've also created an <a href="http://archive.syncany.org/">APT archive</a> for Debian/Ubuntu users, 
				and made a <a href="https://get.syncany.org/windows/">Windows installer</a> includes the GUI plugin.
				And of course, we've fixed countless bugs, slowly approaching stability. Unfortunately, we're still not there
				yet -- we still have <a href="https://github.com/syncany/syncany/labels/prio:high">some nasty bugs</a> ...
			</p>				

			<p>
				<b>25 Jun 2014</b>: Some updates before I go on vacation: stability, an already implemented basic daemon, a preview of the REST/WebSocket-API-enabled daemon and a web interface. Check out the details <a href="https://github.com/syncany/syncany/wiki/News">on the news site</a>.
			</p>

			<p>
				Older news are available in the <a href="https://github.com/syncany/syncany/wiki/News">news archive</a>.
			</p>
		</div>
		
		<div class="newscol">
			<h2>Activity on GitHub</h2>
			
			<ul id="github-widget" data-type="events" data-org="syncany" data-repo="syncany" data-limit="8"></ul>			
			<script type="text/javascript" src="<?php echo $cdnhost; ?>js/github-widgets.js"></script> 
		</div>
				
		<div class="clear"> </div>				
	</div>
</div>

<div class="dark_bg" id="examples">
	<div class="wrap">
		<h2 id="Examples">Examples</h2>
		
		<p>Enough with the marketing. Here's how Syncany looks like in the wild.</p>
	
		<div class="asciiprevs">
			<div>
				<div class="asciiprev">
					<a class="fancybox fancybox.iframe" href="video.php?v=4"><div class="img-for-overlay" style="background:url(<?php echo $cdnhost; ?>images/ascii4.png)"><img src="<?php echo $cdnhost; ?>images/play-button-overlay.png"></span></div></a>
					<span class="asciiprevdesc">
						<b>Syncany in action</b>: Creating a new repository, connecting to it and syncing files
						with the graphical user interface.
					</span>
				</div>
			</div>
		
			<div>				
				<div class="asciiprev">
					<a class="fancybox" rel="examples" href="<?php echo $cdnhost; ?>images/syncany-tray2-menu-uploading.png"><img src="<?php echo $cdnhost; ?>images/syncany-tray2-menu-uploading-small.png" /></a>
					<span class="asciiprevdesc">
						<b>Tray menu</b>: The tray icon and menu always keep you up to date - for all of your
						sync folders.
					</span>
				</div>	
			</div>	
								
			<div>				
				<div class="asciiprev">
					<a class="fancybox" rel="examples" href="<?php echo $cdnhost; ?>images/syncany-tray3-notification.png"><img src="<?php echo $cdnhost; ?>images/syncany-tray3-notification-small.png" /></a>
					<span class="asciiprevdesc">
						<b>Notifications</b>: Nice notifications let you know when other users have added, 
						changed or deleted files.						
					</span>
				</div>	
			</div>	
		
			<div>				
				<div class="asciiprev">
					<a class="fancybox" rel="examples" href="<?php echo $cdnhost; ?>images/syncany-init1-select-action.png"><img src="<?php echo $cdnhost; ?>images/syncany-init1-select-action-small.png" /></a>
					<span class="asciiprevdesc">
						<b>Adding new folders</b>: Creating new repositories and connecting to existing ones
						is easy with the 'New folder' wizard.
					</span>
				</div>	
			</div>	
						
			<div>				
				<div class="asciiprev">
					<a class="fancybox" rel="examples" href="<?php echo $cdnhost; ?>images/syncany-init2-local-folder.png"><img src="<?php echo $cdnhost; ?>images/syncany-init2-local-folder-small.png" /></a>
					<span class="asciiprevdesc">
						<b>Multiple sync folders</b>: Syncany lets you sync any (<em>get it?</em>) folder, not just a
						single one. 
					</span>
				</div>	
			</div>	

			<div>				
				<div class="asciiprev">
					<a class="fancybox" rel="examples" href="<?php echo $cdnhost; ?>images/syncany-init3-select-storage.png"><img src="<?php echo $cdnhost; ?>images/syncany-init3-select-storage-small.png" /></a>
					<span class="asciiprevdesc">
						<b>Any backend</b>: Use any backend storage, without having to worry about your privacy. We encrypt before uploading.
					</span>
				</div>	
			</div>	
			
			<div>				
				<div class="asciiprev">
					<a class="fancybox" rel="examples" href="<?php echo $cdnhost; ?>images/syncany-init4-settings-ftp.png"><img src="<?php echo $cdnhost; ?>images/syncany-init4-settings-ftp-small.png" /></a>
					<span class="asciiprevdesc">
						<b>Storage settings</b>: Depending on the storage backend, Syncany displays relevant settings.
					</span>
				</div>	
			</div>				
						
			<div>				
				<div class="asciiprev">
					<a class="fancybox" rel="examples" href="<?php echo $cdnhost; ?>images/syncany-init6-choose-password.png"><img src="<?php echo $cdnhost; ?>images/syncany-init6-choose-password-small.png" /></a>
					<span class="asciiprevdesc">
						<b>Symmetric encryption</b>: Files are encrypted before upload, with AES-128 and Twofish-128.
					</span>
				</div>	
			</div>		
			
			<div>				
				<div class="asciiprev">
					<a class="fancybox" rel="examples" href="<?php echo $cdnhost; ?>images/syncany-init7-finished-link.png"><img src="<?php echo $cdnhost; ?>images/syncany-init7-finished-link-small.png" /></a>
					<span class="asciiprevdesc">
						<b>Sharing</b>: Once a repo is created, it can be shared among friends and colleagues 
						via a <tt>syncany://</tt>-link.
					</span>
				</div>	
			</div>						
						
			<div>
				<div class="asciiprev">
					<a class="fancybox fancybox.iframe" rel="examples" href="video.php?v=1"><div class="img-for-overlay" style="background:url(<?php echo $cdnhost; ?>images/ascii1.png)"><img src="<?php echo $cdnhost; ?>images/play-button-overlay.png"></span></div></a>
					<span class="asciiprevdesc">
						<b>Creating a repo via CLI</b>: Creating a new repository via the command line is very fast with the
						<tt>sy init</tt> command. 
					</span>
				</div>
			</div>
			
			<div>
				<div class="asciiprev">
					<a class="fancybox fancybox.iframe" rel="examples" href="video.php?v=2"><div class="img-for-overlay" style="background:url(<?php echo $cdnhost; ?>images/ascii2.png)"><img src="<?php echo $cdnhost; ?>images/play-button-overlay.png"></span></div></a>
					<span class="asciiprevdesc">
						<b>Connecting to a repo via CLI</b>: Other clients can connect to the repository and sync files manually with 
						<tt>sy up</tt> and <tt>sy down</tt>.
					</span>
				</div>
			</div>			
		</div>
		
		<p class="faq">
			Still no idea what we're talking about?<br />
			Check out the <a href="https://www.syncany.org/r/userguide">User Guide</a>!
		</p>
	
		<div class="clear"> </div>		
     	</div>
</div>			
				
<div class="light_bg" id="contact">
	<div class="wrap">	
		<div class="newscol">
			<h2 id="Contact">Contact</h2>

			<p>Syncany is distributed under the GPLv3 open source license.
			It is actively developed by <a href="https://github.com/binwiederhier">Philipp C. Heckel</a> and 
			<a href="https://github.com/syncany/syncany/graphs/contributors">many others</a>. 
			Feel free to contact us:</p>

			<ul class="contactlist">
				<li>Contact us on 
					<a class="socialb twitter" href="https://twitter.com/syncany" title="Twitter"><i class="fa fa-twitter"></i> Twitter</a>, 
					<a class="socialb gplus" href="https://plus.google.com/u/0/communities/101993748229314848303" title="Google+"><i class="fa fa-google-plus"></i> Google+</a> or 
					<a class="socialb github" href="https://github.com/syncany/syncany" title="GitHub"><i class="fa fa-github"></i> GitHub</a>
				</li>
				<li>Join the <a href="https://launchpad.net/~syncany-team">mailing list on Launchpad</a></li>
				<li>Ask us a question in the <a href="https://webchat.freenode.net/?channels=syncany">#syncany IRC channel</a> (Freenode)</li>
				<li>If that doesn't help, <a href="mailto:team@syncany.org">contact the project core team</a></li>				
			</ul>					
		</div>
		
		<div class="newscol">
			<h2 id="Contributing">Contributing <a href="https://github.com/syncany/syncany"><i class="fa fa-github"></i></a></h2>		
						
			<p>
				Want to help? Or just build it yourself? For information about building, development, documentation, screencasts
				and diagrams, please check out the <a href="https://github.com/syncany/syncany">source code</a> and the 
				<b><a href="https://github.com/syncany/syncany/wiki">Syncany wiki page</a></b>. It'll hopefully give you all
				the information you need!
			</p>
			
			<p style="padding-bottom: 7px">
				If you're not in a position to help out but you still want to support us,
				you can buy us a coffee or a beer.
			</p>
	
			<p>
				<a href="donate.html" title="Donate via PayPal"><img src="<?php echo $cdnhost; ?>images/paypal.png" /></a>		
				<a href="https://flattr.com/thing/290043/Syncany" title="Flattr Syncany"><img src="https://api.flattr.com/button/flattr-badge-large.png" alt="Flattr this" title="Flattr this" border="0" /></a>
				<a href="bitcoin:1626wjrw3uWk9adyjCfYwafw4sQWujyjn8?amount=0.05&amp;message=Syncany%20Donation" title="Donate some Bitcoins"><img src="<?php echo $cdnhost; ?>images/bitcoin.png" /></a>						
				<a href="https://www.gittip.com/binwiederhier/" title="A little tip with GitTip"><img src="<?php echo $cdnhost; ?>images/gittip.png" /></a>								
			</p>
		</div>		
		
		<div class="clear"> </div>				
	</div>
</div>

<div class="footer-bottom">
	<div class="wrap">
		<div class="copy">
			<p class="copy">Original website template by <a href="http://w3layouts.com">w3layouts</a>. Thanks for the love!</p>
		</div>
		
		<a href="#" id="toTop" style="display: block;"><span id="toTopHover" style="opacity: 1;"> </span></a>
		<script src="<?php echo $cdnhost; ?>js/jquery.scrollTo.js"></script>
	</div>
</div>

</body>
</html>
