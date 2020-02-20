<?php require_once("index.config.php"); ?>
<!DOCTYPE html>
<html>
  <head>
    <title><?php echo $config["title"];?></title>
    <meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" type="text/css" href="./style.css">
    <meta name="viewport" content="width=320, user-scalable=no, initial-scale=1, maximum-scale=1">
    
    <script src="https://code.jquery.com/jquery-3.4.0.min.js" integrity="sha256-BJeo0qm959uMBGb65z40ejJYGSgR7REI4+CW1fNKwOg=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script defer src="https://use.fontawesome.com/releases/v5.1.0/js/all.js" integrity="sha384-3LK/3kTpDE/Pkp8gTNp2gR/2gOiwQ6QaO7Td0zV76UFJVhqLl4Vl3KL1We6q6wR9" crossorigin="anonymous"></script>
  </head>
  <body>

    <div class="container">
      <div class="row">
        <div class="col-md-12">

<?php
$generatedUrls = array();
global $config;
if ($handle = opendir($config["localPath"])) {
  $entryTotal = 0;
  while (false !== ($entry = readdir($handle))) {
    if (pathinfo($entry, PATHINFO_EXTENSION) == "plist") {
      $entryTotal = 1;
      getGeneratedUrls($entry);
    }
  }
  closedir($handle);
}
// Plist parser
function parseValue( $valueNode ) {
  $valueType = $valueNode->nodeName;
  $transformerName = "parse_$valueType";
  if ( is_callable($transformerName) ) {
    return call_user_func($transformerName, $valueNode);
  }
  return null;
}
function parse_integer( $integerNode ) {
  return $integerNode->textContent;
}
function parse_string( $stringNode ) {
  return $stringNode->textContent;
}
function parse_date( $dateNode ) {
  return $dateNode->textContent;
}
function parse_true( $trueNode ) {
  return true;
}
function parse_false( $trueNode ) {
  return false;
}
function parse_dict( $dictNode ) {
  $dict = array();
  for (
    $node = $dictNode->firstChild;
    $node != null;
    $node = $node->nextSibling
  ) {
    if ( $node->nodeName == "key" ) {
      $key = $node->textContent;
      $valueNode = $node->nextSibling;
      while ( $valueNode->nodeType == XML_TEXT_NODE ) {
        $valueNode = $valueNode->nextSibling;
      }
      $value = parseValue($valueNode);
      $dict[$key] = $value;
    }
  }
  return $dict;
}
function parse_array( $arrayNode ) {
  $array = array();
  for (
    $node = $arrayNode->firstChild;
    $node != null;
    $node = $node->nextSibling
  ) {
    if ( $node->nodeType == XML_ELEMENT_NODE ) {
      array_push($array, parseValue($node));
    }
  }
  return $array;
}
function parsePlist( $path ) {
  $document = new DOMDocument();
  $document->load($path);
  $plistNode = $document->documentElement;
  $root = $plistNode->firstChild;
  while ( $root->nodeName == "#text" ) {
    $root = $root->nextSibling;
  }
  return parseValue($root);
}

function getGeneratedUrls($plistFile) {
  global $config, $generatedUrls;
  if ($config["parsePlist"]) {
    header ('Content-Type: text/html; charset=UTF-8');
    $plistContent = parsePlist($plistFile);

    $ipaUrl       = $plistContent["items"][0]["assets"][0]["url"];
    $name         = $plistContent["items"][0]["metadata"]["title"];
    $subtitle     = $plistContent["items"][0]["metadata"]["subtitle"];
    $version      = $plistContent["items"][0]["metadata"]["bundle-version"];
    $icon         = $plistContent["items"][0]["assets"][1]["url"];
    $plistUrl     = $config["baseUrl"] ."/". pathinfo($ipaUrl, PATHINFO_FILENAME) . ".plist";

    echo "
      <div class='my-2 p-relative bg-white shadow-1 blue-hover' style='width: 100%; overflow: hidden; border-radius: 1px;'>
        <img src='". $icon ."' alt='Man with backpack' class='d-block w-full'>
        <div class='px-2 py-2'>
          <p class='mb-0 small font-weight-medium text-uppercase mb-1 text-muted lts-2px'>". $version ."</p>
          <h1 class='ff-serif font-weight-normal text-black card-heading mt-0 mb-1' style='line-height: 1.75;direction: rtl;text-align: center;'>". $name ."</h1>
          <p class='mb-1' style='direction: rtl;text-align: right;'><strong>". $subtitle ."</strong></p>
        </div>
        <a href='itms-services://?action=download-manifest&url=". $plistUrl ."' class='text-uppercase mb-2 text-center btn btn-success' style='width: 100%;'><strong> تثبيت  | INSTALL </strong></a>
      </div>
    ";

  }
}

if($entryTotal == 0){
  echo "
      <div class='my-2 p-relative bg-white shadow-1 blue-hover' style='width: 100%; overflow: hidden; border-radius: 1px;'>
        <div class='px-2 py-2'>
          <p class='mb-0 small font-weight-medium text-uppercase mb-1 text-muted lts-2px'>". $config["title"] ."</p>
          <h1 class='ff-serif font-weight-normal text-black card-heading mt-0 mb-1' style='line-height: 1.75;direction: rtl;text-align: center;'>معليش 😱</h1>
          <p class='mb-1' style='direction: rtl;text-align: right;'><strong>شف ياطويل العمر، ي انه فيه مشكلة والتطبيقات ماهي طالعة أو فعلا التطبيقات محذوفة 🌚</strong><br>
        </div>
      </div>
  ";
}
?>

        </div>
      </div>
    </div>

  </body>
</html>