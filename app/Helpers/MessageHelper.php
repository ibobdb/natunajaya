<?php

namespace App\Helpers;

class MessageHelper
{
  /**
   * Encode message content to ensure emoji and special characters are preserved
   * when processed by shell scripts
   * 
   * @param string $content Original message content
   * @return string Encoded content
   */
  public static function encodeContent($content)
  {
    // Base64 encode to preserve Unicode characters
    return base64_encode($content);
  }

  /**
   * Decode message content for display purposes in web interface
   * 
   * @param string $content Encoded message content
   * @return string Original message content
   */
  public static function decodeContent($content)
  {
    // Check if content appears to be base64 encoded
    if (preg_match('/^[A-Za-z0-9+\/]+={0,2}$/', $content)) {
      $decoded = base64_decode($content, true);
      if ($decoded !== false) {
        return $decoded;
      }
    }

    // If not base64 or decoding failed, return original content
    return $content;
  }
}
