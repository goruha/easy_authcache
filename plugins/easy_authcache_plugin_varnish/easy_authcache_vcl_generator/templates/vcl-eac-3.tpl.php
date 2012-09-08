acl internal {
<?php print $ips; ?>

}

sub eac_auth_recv {
<?php if ($http_auth) : ?>
  if (! client.ip ~ internal) {
	  if (! req.http.Authorization == "Basic <?php print $http_auth_encode; ?>") {
		  error 401 "Restricted";
		}
	}
<?php endif;?>

}



sub eac_auth_error {
	if (obj.status == 401) {
		set obj.http.Content-Type = "text/html; charset=utf-8";
		set obj.http.WWW-Authenticate = "Basic realm=Secured";
		synthetic {"
<?php print $http_auth_wrong_html; ?>
"};
		return (deliver);
	}
}



sub eac_bad_request_error {
	if (obj.status == 400) {
		set obj.http.Content-Type = "text/html; charset=utf-8";
		synthetic {"
<?php print $wrong_request_html; ?>

"};
	return (deliver);
	}
}



sub eac_backend_server_error {
	if (obj.status >= 500 && obj.status <= 505) {
		set obj.http.Content-Type = "text/html; charset=utf-8";
		synthetic {"
<?php print $backend_error_html; ?>

"};

	return (deliver);
	}
}



sub eac_hash {
	set req.http.EAC-ROLE-SESSION-ID = regsub( req.http.Cookie, "^.*?<?php print $eac_prefix; ?>SESS(.{32})=([^;]*);*.*$", "\1\2" );
	hash_data(req.http.EAC-ROLE-SESSION-ID);

	set req.http.EAC-ESI-SESSION-ID = regsub( req.http.Cookie, "^.*?<?php print $esi_prefix; ?>SESS(.{32})=([^;]*);*.*$", "\1\2" );
	if (req.url ~ "^/<?php print $esi_path; ?>/") {
		hash_data(req.http.EAC-ESI-SESSION-ID);
	}

}



sub eac_protected_recv {
	if (req.url ~ "<?php print $files_hide; ?>"
	&& !req.url ~ "<?php print $files_show; ?>"
	) {
		if (!client.ip ~ internal) {
			error 400 "Bad request";
		}
	}
}



sub eac_static_recv {
	if (req.url ~ "<?php print $files_cache; ?>") {
		return (<?php print $files_cache_action; ?>);
	}
}



sub eac_pass_cache_recv {
	if (req.url ~ "^/esi/") {
		return(pass);
	}
	if (!req.url ~ "^/esi/") {
		return(pass);
	}
}



sub eac_urls_pass_recv {
	if (<?php print $ulrs_path?>) {
		return(pass);
	}
}



sub eac_cookie_pass_recv {
	if (req.http.Cookie) {
		set req.http.Cookie = ";" + req.http.Cookie;
		set req.http.Cookie = regsuball(req.http.Cookie, "; +", ";");
		set req.http.Cookie = regsuball(req.http.Cookie, ";(<?php print $cookies_pass; ?>)=", "; \1=");
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
	set beresp.ttl = <?php print $page_ttl; ?>;
}



sub eac_static_fetch {
	if (req.url ~ "<?php print $files_cache; ?>") {
		unset beresp.http.set-cookie;
		return(deliver);
	}
	else {
		set beresp.do_esi = true;
		remove beresp.http.expires;
	}
}



sub eac_esi_ttl_fetch {
	if (req.url ~ "^/<?php print $esi_path; ?>") {
		#unset beresp.http.set-cookie;
		set beresp.do_gunzip = true;
		set beresp.ttl = <?php print $esi_ttl; ?>;
	}
}



sub eac_headers_fetch {
	if (beresp.ttl <= 0s) {
		set beresp.http.X-Cacheable = "NO:Not Cacheable";
	}
	elsif (req.http.Cookie ~ "(<?php print $cookies_pass; ?>)") {
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

