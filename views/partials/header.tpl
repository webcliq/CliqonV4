<!DOCTYPE html>
<html lang="@($idiom)">
  <head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=0.8">
    <link rel="icon" type="image/ico" href="@($viewpath)img/favicon.ico">

    <meta name="author" content="Webcliq"/>
    <meta name="designer" content=”Webcliq”>
    <meta name="publisher" content=”Webcliq”>    
    <meta name="copyright" content="Webcliq"/>
    <meta name="format-detection" content="true"/>

    <!--Search Engine Optimization Meta Tags-->
    <title>@($cfg['site']['name'])</title>
    <meta name="description" content="@raw( Q::cCfg('site.description') )">
    <meta name="keywords" content="@raw( Q::cCfg('site.keywords') )">
    <meta name=”robots” content=”index,follow”>
    <meta name="revisit-after" content="7 days">
    <meta name="distribution" content="web">
    <meta name=”robots” content=”noodp”>
    
    <!--Optional Meta Tags-->
    <meta name="distribution" content="web">
    <meta name="web_author" content="">
    <meta name="rating" content="general">
    <meta name="rating" content="safe for kids">
    <meta name="subject" content="">
    <meta name="copyright" content="@raw( Q::cCfg('site.copyrightmessage') )">
    <meta name="reply-to" content="">
    <meta name="abstract" content="">
    <meta name=”city” content=””>
    <meta name=”country” content=””>
    <meta name="distribution" content="global">
    <meta name="classification" content="">
  
    <!--Meta Tags for HTML pages on Mobile-->
    <meta name="format-detection" content="telephone=yes"/>
    <meta name="HandheldFriendly" content="true"/> 
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    
    <!--http-equiv Tags-->
    <meta http-equiv="Content-Style-Type" content="text/css">
    <meta http-equiv="Content-Script-Type" content="text/javascript">
    
    <link href="@($viewpath)css/style.css" rel="stylesheet">
    <script type="text/javascript" src="@($includepath)js/basket.js"></script>


