
Easy authcache
------------------------
Full documentation you can find here: http://drupal.org/node/916742

Installation
------------------------
To install Easy authcache
1.	Install required modules (common way - Authcache)
2.	Add Easy authcache module drupal contrib modules dir (usual /sites/all/modules). 
3.	Copy authcache_custom.php file from easy_authcache/_authcache/authcache_custom.php to authcache/ajax/  and change (authcache/ajax/authcache.php:str 42) to include  authcache_custom.php, if needed.
4.	Enable Easy authcache and it's plugins you need in drupal admin part url /admin/build/modules/list
5.	Config Easy authcache and/or it's plugns (some of them needs Easy Authcache UI module enabled to config).

Posible problems, risky cases and things you need to know.
----------------------
Do not forget about this risky cases
1.	Limit of url length
2.	Event races on ajax – js files/settings sync code
3.	Third party js files with wrong behaviours / or with out them can cause troubles wit h js
a.	Function attached several times to same dom element
b.	Unattached js to html getted wit h ajax
4.	Third party code that checked arguments in url with $_REUQEST array (wrong url context)
5.	 Wrong type of easy authcache result can cause bugs with replacing by js


Debug advices
----------------------
It use usefull to debug
1.	Enable authcache debug  /admin/settings/performance/authcache
2.	Comment strings (authcache/js/authcache.js:str 167) and (authcache/js/authcache.js:str 169). This will allow you to see js errors you get on authcache client callback
3.	Disable single easyauthcache request /admin/settings/performance/authcache/easyauthcache allow you to debug what easy authcache plugin breaks js.
4.	Using Firebug. 
5.	Adding var_dump() and die() in to easy_authcache plugins  hook_autcache_cached implementation.

Maintainers
-----------
Easy authcache developed:
Igor Rodionov
