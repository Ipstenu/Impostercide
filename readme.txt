=== Impostercide ===
Contributors: Ipstenu, skippy
Tags: comments, spoof, imposter, multisite, wpmu
Requires at least: 2.1
Tested up to: 4.0
Stable tag: 1.8
Donate link: https://store.halfelf.org/donate/

Prevent unauthenticated users from using a registered users' credentials when commenting.

== Description ==

Impostercide prevents unauthenticated users from "signing" a comment with a registered users email address or username.  There is no interface from the admin's end, no options to select.  Simply install, turn it on, and watch the spoofers get stopped.

**Misc**

* [Plugin Site](http://halfelf.org/plugins/impostercide/)
* [Donate](https://store.halfelf.org/donate/)

==Changelog==

= 1.8 = 
* 17 April, 2012 by Ipstenu
* Readme cleanup, fixing URLs etc.
* Internationalization

= 1.7 =
* 4 October 2011 by Ipstenu
* 3.3 compat check
* minor cleanup
* licencing

= 1.6.2 =
* 8 Dec 2010 by Ipstenu
* Removed the check on URL. Why? Cause sometimes people have the same URL as other.  Like when they share a site. (Sorry RonAndAndrea!)

= 1.6.1 =
* By Ipstenu
* Switched logged in check to is_user_logged_in() (thanks Chip!)
* Genericized the error message (again, Chip)

= 1.6 =
* By Ipstenu
* Re-released under GPL (per http://skippy.net/wordpress-plugins-discontinued#comment-8300 )
* Replaced `die()` with `wp_die()`
* Changed formatting to look 'pretty'

= 1.5 =
* July 2011 by Ipstenu
* Initial version by Ipstenu. All I did was change commenting and move it to a subfolder. (This was only ever released on my websites)

= 1.0 =
* 2005 Scott Merrill (skippy@skippy.net), discontinued in 2007.
* many thanks to Mark Jaquith for the name "Impostercide"

== Installation ==

No special instructions.

== Frequently Asked Questions ==

= Will this work on older versions of WordPress? =

This will work on WordPress from version 2.1 and up.

= Will this work on MultiSite? =

Again, yes.  I suggest putting it in `mu-plugins`, but it works fine both ways.

= Can this catch innocents? =

Yes, but ... well. The only person I've caught is someone who is, apparently, pathalogically incapable of remembering that he HAS an account on the site, and he needs to LOG IN with said account. He also claims to forget his password and that WordPress doesn't email it to him, so I'm pretty much chucking his complaint as a problem with the user, and not the tool.

== Screenshots ==
1. Error message when you try and post as a registered user
2. One of these things is not like the other...
