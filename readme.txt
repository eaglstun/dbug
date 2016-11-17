=== dbug ===
Contributors: postpostmodern
Donate link: http://www.heifer.org/
Tags: debug, error log
Requires at least: 4.0
Tested up to: 4.6.1
Stable tag: trunk

Simple error debugging and logging functions.

== Description ==
dbug() dlog() ddbug()

== Installation ==
1. Place entire /dbug/ directory to the /wp-content/plugins/ directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Set logging to screen or to to files, and the larget file size for logs
1. Write some code
1. Can't figure something out? dbug( $var )
1. Need to write yourself a note? dbug( $var, 'this is super cool' )
1. How many lines of backtrace do you need? dbug( $var, 'oh wow!', 25 )
1. Want to dbug and die? Use ddbug()

== Changelog ==

= 1.9.2 = 
Add composer info, autoload

= 1.9 =
Log viewer in admin

= 1.89 =
Slightly better class method information

= 1.88 =
Moved admin styles inline because of reasons

= 1.87 =
Improved default log filesize logic, added ddlog()

= 1.86 =
Registered css for mu-plugins correctly

= 1.85 =
Fixed offset in backtrace

= 1.81 =
Fixed sloppy error which disabled admin screen :(

= 1.8 =
Code cleanup, mostly in backtrace

= 1.74 =
Whoops, didn't add _htaccess.php to SVN :-p

= 1.73 =
Fixed creating .htaccess file when using mu-plugins

= 1.72 =
Set dbug to never show on screen if logs are turned on, added REQUEST_URI to all logs

= 1.7 =
Moved admin settings into separate class, cleanup of core code

= 1.55 = 
Fixed using float as argument $k in dbug

= 1.54 = 
Fixed deprecated argument in add_options_page()

= 1.53 = 
Fixed a bug in detecting MU

= 1.52 = 
Fixed a bug in logging integer keyed arrays

= 1.5 = 
Changed some things, I don't remember what

= 1.4 =
Better handling for MU / single blog. 
All options and settings for MU are now global, saved in blog #1 database.

= 1.32 =
Added ddbug() (dbug and die) 

= 1.31 =
fixed behavior in error handlers to return false, try /catch blocks work as expected. 

= 1.2 =
last bit of namespace pollution cleaned up
check for PHP 5
css tweaks

= 1.1 =
added preference for log filesize

= 1.08 =
code cleanup, reducing namespace pollution

= 1.0 =
yes, its here

== Upgrade Notice ==
dunno

== Frequently Asked Questions ==
= This doesn't do anything! =
well

= Why did you create dbug? =
Because I cant write code without it  

== Screenshots ==
1. dbug is delicious `/trunk/screenshot-1.png`
1. strict error reporting is great `/trunk/screenshot-2.png`

== dbug Basics ==

= Debugging =
Call with up to three arguments:
`<?php
	// output a variable
	dbug( $var );		
	
	// output a variable with a title			
	dbug( $debug, 'Testing' );
	
	// output a variable with a title and information from the last 6 steps from debug_backtrace
	dbug( $somevalue, 'Trying to figure some shit out', 6 );
}`

or use `ddbug` to dbug and die.

= Error Logging =
Call with up to three arguments:
`<?php
	// log a variable
	dlog( $val );
	
	// log a variable with a title
	dlog( $buggy, 'what is $buggy' );
	
	// log a variable with a title into the file 'bug_trap' 
	dlog( $somevalue, 'im desperate', 'bug_trap' );
}`

= Production / Development Environments = 
you should have them