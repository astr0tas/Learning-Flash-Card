<?php

namespace App\Config;

final class ContentType
{
  // --- Application ---
  public const JSON = 'application/json';
  public const XML = 'application/xml';
  public const FORM_URLENCODED = 'application/x-www-form-urlencoded';
  public const JAVASCRIPT = 'application/javascript';
  public const PDF = 'application/pdf';
  public const ZIP = 'application/zip';
  public const OCTET_STREAM = 'application/octet-stream'; // Default for binary files/downloads
  public const GRAPHQL = 'application/graphql';
  public const LD_JSON = 'application/ld+json';

  // --- Text ---
  public const HTML = 'text/html';
  public const PLAIN = 'text/plain';
  public const CSS = 'text/css';
  public const CSV = 'text/csv';
  public const MARKDOWN = 'text/markdown';
  public const XML_TEXT = 'text/xml';

  // --- Multipart ---
  public const MULTIPART_FORM_DATA = 'multipart/form-data'; // Used for file uploads
  public const MULTIPART_BYTERANGES = 'multipart/byteranges';

  // --- Image ---
  public const JPEG = 'image/jpeg';
  public const PNG = 'image/png';
  public const GIF = 'image/gif';
  public const SVG = 'image/svg+xml';
  public const WEBP = 'image/webp';
  public const ICON = 'image/x-icon';

  // --- Audio / Video ---
  public const MPEG = 'audio/mpeg';
  public const OGG_AUDIO = 'audio/ogg';
  public const WAV = 'audio/wav';
  public const MP4 = 'video/mp4';
  public const WEBM = 'video/webm';

  // --- Fonts ---
  public const WOFF = 'font/woff';
  public const WOFF2 = 'font/woff2';
  public const TTF = 'font/ttf';

  // Prevent instantiation
  private function __construct() {}
}
