<?php
/**
 * Created by JetBrains PhpStorm.
 * User: goruha
 * Date: 9/9/12
 * Time: 1:48 AM
 * To change this template use File | Settings | File Templates.
 */
acl internal {
	"127.0.0.1";
	"localhost";
}



sub eac_auth_recv {
	set req.http.X-Acl = "1";
	if (req.http.X-Acl == "1") {
		if (! client.ip ~ internal) {
			if (! req.http.Authorization == "Basic ZnR2ZW46cmVnaW9ucw==") {
				error 401 "Restricted";
			}
		}
	}	
}



sub eac_auth_error {
	if (obj.status == 401) {
		set obj.http.Content-Type = "text/html; charset=utf-8";
		set obj.http.WWW-Authenticate = "Basic realm=Secured";
		synthetic {"
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
 "http://www.w3.org/TR/1999/REC-html401-19991224/loose.dtd">
<HTML>
  <HEAD>
    <TITLE>Error</TITLE>
    <META HTTP-EQUIV='Content-Type' CONTENT='text/html;'>
  </HEAD>
  <BODY><H1>401 Unauthorized (varnish).</H1></BODY>
</HTML>
		"};
		return (deliver);
	}
}



sub eac_bad_request_error {
	if (obj.status == 400) {
		set obj.http.Content-Type = "text/html; charset=utf-8";
		synthetic {"
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
  <head>
    <title>400 Bad request</title>
  </head>
  <body>
    <h1>Error 400 Bad request</h1>
    <p>Bad request</p>
  </body>
</html>
		"};

	return (deliver);
	}
}



sub eac_backend_server_error {
	if (obj.status >= 500 && obj.status <= 505) {
		set obj.http.Content-Type = "text/html; charset=utf-8";
		synthetic {"
<?xml version="1.0" encoding="utf-8"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
 "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
  <head>
    <title>Internal server error</title>
  </head>
  <body>
    <h1>Internal server error</h1>
  </body>
</html>
		"};

	return (deliver);
	}
}



sub eac_hash {
	set req.http.EAC-ROLE-SESSION-ID = regsub( req.http.Cookie, "^.*?EACSESS(.{32})=([^;]*);*.*$", "\1\2" );
	hash_data(req.http.EAC-ROLE-SESSION-ID);

	set req.http.EAC-ESI-SESSION-ID = regsub( req.http.Cookie, "^.*?EACESISESS(.{32})=([^;]*);*.*$", "\1\2" );
	if (req.url ~ "^/esi/") {
		hash_data(req.http.EAC-ESI-SESSION-ID);
	}

}



sub eac_protected_recv {
	if (req.url ~ "(?i)\.(module|info|inc|profile|engine|test|po|txt|theme|svn|git|tpl(\.php)?)(\?.*|)$"
	&& !req.url ~ "(?i)robots\.txt"
	) {
		if (!client.ip ~ internal) {
			error 400 "Bad request";
		}
	}
}



sub eac_static_cache_recv {
	if (req.url ~ "(?i)\.(jpeg|jpg|png|gif|ico|swf|js|css|txt|html|htm|pdf|tar|gz|gzip|bz2)(\?.*|)$") {
		return (lookup);
	}
}



sub eac_static_pass_recv {
	if (req.url ~ "(?i)\.(jpeg|jpg|png|gif|ico|swf|js|css|txt|html|htm|pdf|tar|gz|gzip|bz2)(\?.*|)$") {
		return (pass);
	}
}



sub eac_urls_pass_recv {
	if (req.url ~ "^/user"
	|| req.url ~ "^/admin"
	|| req.url ~ "^/logout"
	) {
		return(pass);
	}
}



sub eac_cookie_pass_recv {
	if (req.http.Cookie) {
		set req.http.Cookie = ";" + req.http.Cookie;
		set req.http.Cookie = regsuball(req.http.Cookie, "; +", ";");
		set req.http.Cookie = regsuball(req.http.Cookie, ";(drupal_uid|SESS[a-z0-9]+)=", "; \1=");
		set req.http.Cookie = regsuball(req.http.Cookie, ";[^ ][^;]*", "");
		set req.http.Cookie = regsuball(req.http.Cookie, "^[; ]+|[; ]+$", "");

		if (req.http.Cookie == "") {
			unset req.http.Cookie;
		}
		else {
			return (pass);
		}
	}
}



sub eac_global_ttl_fetch {
	set beresp.ttl = 60m;
}



sub eac_static_fetch {
	if (req.url ~ "(?i)\.(jpeg|jpg|png|gif|ico|swf|js|css|txt|html|htm|pdf)(\?.*|)$") {
		unset beresp.http.set-cookie;
		return(deliver);
	}
	else {
		set beresp.do_esi = true;
		remove beresp.http.expires;
	}
}



sub eac_esi_ttl_fetch {
	if (req.url ~ "^/esi") {
		#unset beresp.http.set-cookie;
		set beresp.do_gunzip = true;
		set beresp.ttl = 10m;
	}
}



sub eac_headers_fetch {
	if (beresp.ttl <= 0s) {
		set beresp.http.X-Cacheable = "NO:Not Cacheable";
	}
	elsif (req.http.Cookie ~ "(SESS[a-z0-9]+|drupal_uid)") {
		set beresp.http.X-Cacheable = "NO:Got Session";
		return(hit_for_pass);
	}
	elsif ( beresp.http.Cache-Control ~ "private") {
		set beresp.http.X-Cacheable = "NO:Cache-Control=private";
		return(hit_for_pass);
	}
	elsif ( beresp.ttl < 1s ) {
		set beresp.ttl   = 5s;
		set beresp.grace = 5s;
		set beresp.http.X-Cacheable = "YES:FORCED";
	}
	else {
		set beresp.http.X-Cacheable = "YES";
	}
}

