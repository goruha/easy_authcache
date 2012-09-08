backend default {
	.host = "127.0.0.1";
	.port = "80";
	.connect_timeout = 600s;
	.first_byte_timeout = 600s;
	.between_bytes_timeout = 600s;
}

include "./eac.vcl";

sub vcl_recv {
	// Normalize the Accept-Encoding header
	// as per: http://varnish-cache.org/wiki/FAQ/Compression
	if (req.http.Accept-Encoding) {
		if (req.url ~ "(?i)\.(flv|swf|mp3|mp4|m4a|ogg|mov|avi|wmv|jpe?g|png|gif|gz|tgz|bz2|tbz|mp3|ogg|eot|woff|ttf|htc)(\?.*)?$") {
			# No point in compressing these
			remove req.http.Accept-Encoding;
		}
		elsif (req.http.Accept-Encoding ~ "gzip") {
			set req.http.Accept-Encoding = "gzip";
		}
		else {
			# unkown algorithm
			remove req.http.Accept-Encoding;
		}
	}

	call eac_auth_recv;

	remove req.http.X-Forwarded-For;
	set req.http.X-Forwarded-For = client.ip;
	remove req.http.X-Real-Forwarded-For;
	set req.http.X-Real-Forwarded-For = client.ip;
	remove req.http.X-Varnish-Client-IP;
	set req.http.X-Varnish-Client-IP = client.ip;

	set req.grace = 6h;

	if (req.request == "PURGE"){
		if (!client.ip ~ internal){
			error 400 "Bad request.";
		}
		return(lookup);
	}

	if (!req.request ~ "GET|HEAD|PUT|POST|TRACE|OPTIONS|DELETE") {
		error 400 "Bad request";
	}

	if (req.request != "GET" && req.request != "HEAD") {
		return (pass);
	}

	if (req.url ~ "^/(cron|install|update)\.php") {
		if (!client.ip ~ internal) {
			error 401 "Restricted";
		}
		return(pass);
	}

	call eac_protected_recv;
	call eac_static_recv;
	call eac_pass_cache_recv;
	call eac_urls_pass_recv;
	call eac_cookie_pass_recv;


	unset req.http.Cookie;
	return (lookup);

} # vcl_recv

sub vcl_hash {
	hash_data(req.url);

	call eac_hash;

	if (req.http.host) {
		hash_data(req.http.host);
	}
	else {
		hash_data(server.ip);
	}

	return (hash);
} # vcl_hash

sub vcl_fetch {
	# compression, vcl_miss/vcl_pass unset compression from the backend
	if (req.http.Accept-Encoding) {
		set beresp.do_gzip = true;
	}

	#TTL
	call eac_global_ttl_fetch;
	set beresp.grace = 6h;

	call eac_static_fetch;

	call eac_esi_ttl_fetch;

	call eac_headers_fetch;

	unset beresp.http.Vary;

	return(deliver);
} # vcl_fetch

sub vcl_deliver {
	if (obj.hits > 0) {
		set resp.http.X-Cache = "HIT";
	}
	else {
		set resp.http.X-Cache = "MISS";
	}
	remove resp.http.X-Varnish-IP;
	unset resp.http.X-Varnish;
	unset resp.http.X-Powered-By;
	unset resp.http.X-Drupal-Cache;
	unset resp.http.X-TTL;
	unset resp.http.X-Generator;
	unset resp.http.server;
	unset resp.http.Via;

	return (deliver);
} # vcl_deliver

sub vcl_pass {
	set bereq.http.X-Real-IP = client.ip;
	set bereq.http.X-Forwarded-For = client.ip;
	# compression done in vcl_fetch
	unset bereq.http.accept-encoding;

	return (pass);
} # vcl_pass

sub vcl_miss {
	if (req.request == "PURGE") {
		error 404 "Not in cache.";
	}
	# compression done in vcl_fetch
	unset bereq.http.accept-encoding;

	return (fetch);
} # vcl_miss

sub vcl_hit {
	if (req.request == "PURGE") {
		#set obj.ttl = 0s;
		purge;
		error 200 "Purged.";
	}

	return (deliver);
} # vcl_hit

sub vcl_error {
	call eac_auth_error;
	call eac_bad_request_error;
	call eac_backend_server_error;
} # vcl_error

sub vcl_pipe {
	# compression done in vcl_fetch
	unset bereq.http.accept-encoding;
	# http://www.varnish-cache.org/ticket/451
	# This forces every pipe request to be the first one.
	set bereq.http.connection = "close";
} # vcl_pipe

