<?php

namespace App\Config;

final class Header
{
  // --- Content Negotiation & Representation ---
  public const CONTENT_TYPE = 'Content-Type';
  public const CONTENT_LENGTH = 'Content-Length';
  public const CONTENT_ENCODING = 'Content-Encoding';
  public const CONTENT_LANGUAGE = 'Content-Language';
  public const ACCEPT = 'Accept';
  public const ACCEPT_CHARSET = 'Accept-Charset';
  public const ACCEPT_ENCODING = 'Accept-Encoding';
  public const ACCEPT_LANGUAGE = 'Accept-Language';

  // --- Authentication & Security ---
  public const AUTHORIZATION = 'Authorization';
  public const WWW_AUTHENTICATE = 'WWW-Authenticate';
  public const STRICT_TRANSPORT_SECURITY = 'Strict-Transport-Security';
  public const X_FRAME_OPTIONS = 'X-Frame-Options';
  public const X_XSS_PROTECTION = 'X-XSS-Protection';
  public const X_CONTENT_TYPE_OPTIONS = 'X-Content-Type-Options';
  public const CONTENT_SECURITY_POLICY = 'Content-Security-Policy';

  // --- CORS (Cross-Origin Resource Sharing) ---
  public const ACCESS_CONTROL_ALLOW_ORIGIN = 'Access-Control-Allow-Origin';
  public const ACCESS_CONTROL_ALLOW_CREDENTIALS = 'Access-Control-Allow-Credentials';
  public const ACCESS_CONTROL_ALLOW_HEADERS = 'Access-Control-Allow-Headers';
  public const ACCESS_CONTROL_ALLOW_METHODS = 'Access-Control-Allow-Methods';
  public const ACCESS_CONTROL_EXPOSE_HEADERS = 'Access-Control-Expose-Headers';
  public const ACCESS_CONTROL_MAX_AGE = 'Access-Control-Max-Age';
  public const ACCESS_CONTROL_REQUEST_HEADERS = 'Access-Control-Request-Headers';
  public const ACCESS_CONTROL_REQUEST_METHOD = 'Access-Control-Request-Method';
  public const ORIGIN = 'Origin';

  // --- Caching & Conditional Requests ---
  public const CACHE_CONTROL = 'Cache-Control';
  public const ETAG = 'ETag';
  public const EXPIRES = 'Expires';
  public const LAST_MODIFIED = 'Last-Modified';
  public const IF_MATCH = 'If-Match';
  public const IF_NONE_MATCH = 'If-None-Match';
  public const IF_MODIFIED_SINCE = 'If-Modified-Since';
  public const IF_UNMODIFIED_SINCE = 'If-Unmodified-Since';
  public const VARY = 'Vary';

  // --- Routing, Proxies & Tracking ---
  public const HOST = 'Host';
  public const LOCATION = 'Location';
  public const REFERER = 'Referer';
  public const USER_AGENT = 'User-Agent';
  public const FORWARDED = 'Forwarded';
  public const X_FORWARDED_FOR = 'X-Forwarded-For';
  public const X_FORWARDED_HOST = 'X-Forwarded-Host';
  public const X_FORWARDED_PROTO = 'X-Forwarded-Proto';

  // --- Cookies ---
  public const COOKIE = 'Cookie';
  public const SET_COOKIE = 'Set-Cookie';

  // --- Miscellaneous ---
  public const CONNECTION = 'Connection';
  public const SERVER = 'Server';
  public const DATE = 'Date';
  public const EXPECT = 'Expect';

  // Prevent instantiation
  private function __construct() {}
}
