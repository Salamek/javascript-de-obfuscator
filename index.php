<?php

class deo
{
  private $content;
  public function __construct($content)
  {
    $this->content = $content;
  }
  
  function decode_crap($code)
  {
    return preg_replace_callback(
        "/\\\(x)?([0-9a-f]{2,3})/i",
        function($m){
            return chr($m[1] ? hexdec($m[2]) : octdec($m[2]));
        },
        $code
    );
  }
  
  
  private function findListName()
  {
    $reg = '/var\s(.+)(?:\s|.)?=(?:\s|.)?\[/i';
    $matches = array();
    preg_match($reg, $this->content, $matches);
    if (count($matches) != 2)
    {
      throw new Exception('List Name not found, it should be on start of script, eg var _0xfe5b=');
    }
    
    return trim($matches[1]);
  }
  
  private function parseList($listName)
  {
    $reg = '/var\s'.$listName.'(?:\s|.)?=(?:\s|.)?\[(.+)\];/i';
    $matches = array();
    preg_match($reg, $this->content, $matches);
    
    if (count($matches) != 2)
    {
      throw new Exception('List not found, it should be on start of script, eg var _0xfe5b=["mess"]');
    }
    
    $items = explode(',', $matches[1]);
    foreach ($items AS &$item)
    {
      $item = $this->decode_crap(str_replace('"', '',trim($item)));
    }
        
    return $items;
  }
  
  private function decode($list, $listName)
  {
    # Decode from list
    $reg = '/'.$listName.'\[(\d*)\]/i';
    $matches = array();
    preg_match_all($reg, $this->content, $matches);
    
    $content = $this->content;
    
    foreach($matches[0] AS $k => $name)
    {
      $content = str_replace($name, '\''.$list[$matches[1][$k]].'\'', $content);
    }
    
    #Decode function calling ['trigger'] -> .trigger
    $reg = '/\[\'(.+?)\'\]/i';
    $matches = array();
    preg_match_all($reg, $content, $matches);
    

    foreach($matches[0] AS $k => $name)
    {
      $content = str_replace($name, '.'.$matches[1][$k], $content);
    }
    
    #Remove list from string
    $reg = '/var\s'.$listName.'(?:\s|.)?=(?:\s|.)?\[(.+)\];/i';
    $content = preg_replace($reg, '', $content);
    
    #Decode any crap in the code
    $content = $this->decode_crap($content);
    
    return $content;
  }
  
  
  public function decompile()
  {
    $listName = $this->findListName();
    $list = $this->parseList($listName);
    
    return $this->decode($list, $listName);
  }
}

$obfuscated = 'var _0xfe5b=["\x53\x61\x79\x48\x65\x6C\x6C\x6F","\x47\x65\x74\x43\x6F\x75\x6E\x74","\x4D\x65\x73\x73\x61\x67\x65\x20\x3A\x20","\x59\x6F\x75\x20\x61\x72\x65\x20\x77\x65\x6C\x63\x6F\x6D\x65\x2E"];function NewObject(_0x24c9x2){var _0x24c9x3=0;this[_0xfe5b[0]]=function(_0x24c9x4){_0x24c9x3++;alert(_0x24c9x2+_0x24c9x4);};this[_0xfe5b[1]]=function(){return _0x24c9x3};}var obj= new NewObject(_0xfe5b[2]);obj.SayHello(_0xfe5b[3]);';
$deobfuscated = '';
$error = '';
if (isset($_POST) && array_key_exists('obfuscated', $_POST))
{
  try
  {
    $obfuscated = $_POST['obfuscated'];
    $deo = new deo($obfuscated);

    $deobfuscated = $deo->decompile();
  }
  catch (Exception $ex)
  {
    $error = $ex->getMessage();
  }
  
}



?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Javascript DE-Obfuscator (of javascriptobfuscator.com)">
    <meta name="author" content="Adam Schubert">
    <meta name="keywords" content="deobfuscator, Javascript, javascriptobfuscator">

    <title>Bare - Start Bootstrap Template</title>

    <!-- Bootstrap Core CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
    body {
        padding-top: 70px;
        /* Required padding for .navbar-fixed-top. Remove if using .navbar-static-top. Change if height of navigation changes. */
    }
    </style>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body>

    <!-- Page Content -->
    <div class="container">
      <?php
      if($error)
      {
        echo '<div class="alert alert-danger">'.$error.'</div>';
      }
      ?>
      <div class="row">
        <div class="col-lg-12 text-center">
          <h1> Javascript DE-Obfuscator (of javascriptobfuscator.com)</h1>
        </div>
      </div>
        <div class="row">
          <form method="post">
            <div class="col-lg-5">
              <textarea class="form-control" name="obfuscated" style="height:600px" placeholder="Obfuscated by javascriptobfuscator.com"><?php echo $obfuscated ?></textarea>
              <a href="#" class="btn btn-primary btn-block run">RUN!</a>
            </div>
            <div class="col-lg-1">
              <button type="submit" class="btn btn-success">Magic <i class="glyphicon glyphicon-menu-right"></i></button>
            </div>
            <div class="col-lg-5">
              <textarea class="form-control" style="height:600px" placeholder="DE-Obfuscated"><?php echo $deobfuscated ?></textarea>
              <a href="#" class="btn btn-primary btn-block run">RUN!</a>
            </div>
          </form>
        </div>
      <div class="row">
        <div class="col-lg-12 text-center">
          <p class="text-success"> This Javascript DE-Obfuscator is created for deobfuscation of code generated by javascriptobfuscator.com.</p>
          <p class="text-warning"> Source on <a href="https://github.com/Salamek/javascript-de-obfuscator">https://github.com/Salamek/javascript-de-obfuscator</a>.</p>
          <p class="text-danger"> I'm unable to recover variable names (information is lost by obfuscation), that is on you. You also need some 3rd party tool to fix indentation.</p>
        </div>
      </div>
        <!-- /.row -->

    </div>
    <!-- /.container -->

    <!-- jQuery Version 1.11.1 -->
    <script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>

    <script>
    $(document).ready(function(){
      $('.run').click(function(){
        eval($(this).prev('textarea').val());
      });
    });
    </script>
    
</body>

</html>
