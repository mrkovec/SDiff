<?php

namespace mrkovec\sdiff;

// Stupid text diff
class SDiff {

  // compute diff for arbytrary object
  public static function getObjectDiff($a, $b, $retEqual = True)
  {
    ksort($a);
    ksort($b);
    $a = json_encode($a, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    $b = json_encode($b, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
    $diff = self::getClauseDiff($a, $b, $retEqual);
    if($diff["eq"] === 1) return null;
    return $diff["diff"];
  }

  public static function getClauseDiff($a, $b, $retEqual = True)
  {
    return self::getDiff($a, $b, [["separator" => "\n", "retEqual" => $retEqual],["separator" => " "],["separator" => ""]]);
  }

  public static function getWordDiff($a, $b, $retEqual = True)
  {
    return self::getDiff($a, $b, [["separator" => " ", "retEqual" => $retEqual],["separator" => ""]]);
  }

  public static function getCharDiff($a, $b, $retEqual = True)
  {
    return self::getDiff($a, $b, [["separator" => "", "retEqual" => $retEqual]]);
  }

  public static function getDiff($a, $b, $params = [["separator" => "", "retEqual" => True]])
  {
    if (!is_string($a) || !is_string($a)) throw new \Exception('not a string');

    if ($a === $b) {
      return ["eq" => 1, "diff" => $a];
    }

    $separator = "";
    if(isset($params[0]["separator"])) {
      $separator = $params[0]["separator"];
    }

    $retEqual = True;
    if(isset($params[0]["retEqual"])) {
      $retEqual = $params[0]["retEqual"];
    }


    if (count($params) === 0) return ["eq" => 0, "diff" => null];

    array_splice($params, 0, 1);

    if($separator) {
      $text1 = explode($separator, $a);
      $text2 = explode($separator, $b);
    } else {
      $text1 = preg_split('//u', $a, -1, PREG_SPLIT_NO_EMPTY);
      $text2 = preg_split('//u', $b, -1, PREG_SPLIT_NO_EMPTY);
    }

    $res = [];

    $numChar = max(count($text1),count($text2));
    $numEqual = 0;

    while (True) {
      if (count($text1) === 0) {
        foreach($text2 as $value) $res[] = "<ins>".$value."</ins>";
        break;
      }
      if (count($text2) === 0) {
        foreach($text1 as $value) $res[] = "<del>".$value."</del>";
        break;
      }

      if(count($params)>0) {
        $o = self::searchSimilar($text1[0], $text2, $params);
      } else {
        $o = self::searchEqual($text1[0], $text2, $params);
      }

      if($o["i"] === False) {
        $res[] = "<del>".$text1[0]."</del>";
      } else {
        if($o["i"] === 0) {
          array_splice($text2, 0, 1);
        } else {
          foreach(array_slice($text2, 0, $o["i"]) as $value) $res[] = "<ins>".$value."</ins>";
          array_splice($text2, 0, $o["i"]+1);
        }

        if($o["eq"] < 1) $res[] = $o["val"];
        elseif ($retEqual) $res[] = $o["val"];

        $numEqual += $o["eq"];
      }
      array_splice($text1, 0, 1);
    }
    return ["eq" => $numEqual/$numChar, "diff" => implode($res,$separator)];
  }


  private static function searchEqual($val, $array, $params) {
      $i = array_search($val, $array);
      $val = $array[$i];
      if ($i === False) $val="x";
    return ["i" => $i, "val" => $val, "eq" => 1];
  }

  private static function searchSimilar($val, $array, $params) {
    $maxEq = -1;
    $maxEqJ = -1;
    $maxEqDiff = [];
    for($j = 0; $j < count($array); $j++) {
      $diff = self::getDiff($val, $array[$j], $params);

      if($diff["eq"] === 1) {
        $maxEq = 1;
        $maxEqJ = $j;
        $maxEqDiff = $diff["diff"];
        break;
      }

      if(/*$diff["eq"] >= 0.5 &&*/ $diff["eq"] > $maxEq) {
        $maxEq = $diff["eq"];
        $maxEqJ = $j;
        $maxEqDiff = $diff["diff"];
      }
    }

    $diffval = "";
    if($maxEqJ<0) $i = False;
    else {
      $i = $maxEqJ;
      $diffval = $maxEqDiff;
    }
    return ["i" => $i, "val" => $diffval, "eq" => $diff["eq"]];
  }

   public static function formatHtml($json) {
      $tabcount = 0;
      $result = '';
      $inquote = false;
      // $ignorenext = false;
      $tab = "&nbsp;&nbsp;";
      $newline = "<br/>";

      for($i = 0; $i < strlen($json); $i++) {
          $char = $json[$i];
          // if ($ignorenext) {
          //     $result .= $char;
          //     $ignorenext = false;
          // } else {
              switch($char) {
                  case '{':
                      $tabcount++;
                      $result .= $char . $newline . str_repeat($tab, $tabcount);
                      break;
                  case '}':
                      $tabcount--;
                      $result = trim($result) . $newline . str_repeat($tab, $tabcount) . $char;
                      break;
                  case ',':
                      if ($inquote) $result .= $char;
                      else $result .= $char . $newline . str_repeat($tab, $tabcount);
                      break;
                  case '"':
                      $inquote = !$inquote;
                      $result .= $char;
                      break;
                  // case '\\':
                  //     if ($inquote) $ignorenext = true;
                  //     $result .= $char;
                  //     break;
                  default:
                      $result .= $char;
              }
          // }
      }
      return $result;
    }



}
