<!DOCTYPE html>
<html lang="@($idiom)">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=0.8, shrink-to-fit=no">
    <link rel="icon" type="image/ico" href="@($viewpath)img/favicon.ico">
    <title>@(Q::cCfg('site.name'))</title>
    <meta content='width=device-width, initial-scale=0.8, maximum-scale=1.0, user-scalable=0, shrink-to-fit=yes' name='viewport' />

    <meta name="author" content="Webcliq"/>
    <meta name="copyright" content="Webcliq"/>
    <meta name="format-detection" content="true"/>
    <meta name="robots" content="noarchive"/>
    <meta name="intercoolerjs:use-data-prefix" content="true"/>
    <meta http-equiv="X-FRAME-OPTIONS" content="ALLOW">
    
    <link href="@($viewpath)css/style.css" rel="stylesheet">
    <script type="text/javascript" src="@($includepath)js/basket.js"></script>

    <style>
      .app-header.navbar .navbar-brand {
        background-size: @raw($admcfg['background-size']);
        background-color: @raw($admcfg['background-color']);
        background-image: url("@raw($admcfg['background-image'])");
      }
    </style>