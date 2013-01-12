<?php

namespace Psc\JS;

class ParseError extends \Psc\Exception {

  public static function withDetailContext($msg, $context, $detailContext) {
    return new static(sprintf("ParseError: %s. Context: %s near '%s'", $msg, $context, $detailContext));
  }

  public static function withContext($msg, $context) {
    return new static(sprintf("ParseError: %s. Context: %s", $msg, $context));
  }
  
  public static function inBranch($branchName, $token) {
    return new static(sprintf("ParseError: in Branch: %s ist %s unerwartet", $branchName, is_strign($token) ? $token : j_token_name($token)));
  }
}
?>