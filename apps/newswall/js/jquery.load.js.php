<?php
  $vars=array(); if(file_exists("../variable.php.ini")) include("../variable.php.ini"); else include("../standard.php.ini");
  include("../functions.php");
?>
var aktiv;
var memtime;
var origx,origy,origw,origh;
var varsreload = <?php print $vars["reload"]; ?>;
var txtlink    = '<?php print t("Which image do you want to link to this title?"); ?>';
var txtsettings= '<?php print t("Settings"); ?>';
var txtshare=    '<?php print t("Share"); ?>';
$("html").css("overflow","hidden");

$(window).load(function(){

/* -- Hide area ---------------------------------------------------------- -- */
  $.blockUI({
    css: {
      border: 'none',
      padding: '0',
      backgroundColor: 'transparent',
      opacity: .5,
      color: '#fff'},
    message: '<img src="images/ajax-loader.gif" />'
  });

/* -- Timeline ----------------------------------------------------------- -- */
  $("#panel").append('<div id="timeline"></div>');

/* -- Panel buttons ------------------------------------------------------ -- */
  $("#panel").append('<div id="panedi" class="screenbutton"></div>');
  $("#panel").append('<div id="panclo" class="detailbutton"></div>');
  $("#panel").append('<div id="panweb" class="detailbutton"></div>');
  $("#panel").append('<div id="pansha" class="detailbutton"></div>');

/* -- Init search function ----------------------------------------------- -- */
  $("#panel").prepend('<form id="searchform" onsubmit="return false;"><input type="text" value="" id="searchbox"></form>');
  var runningRequest=false;
  var request;
  $('input#searchbox').keyup(function(e){
//    window.clearTimeout(aktiv);
    cleartimeline();
    e.preventDefault();
    var $q = $(this);
    if($q.val()=='' || $q.val().length<4){
      starttimer();
      $("#results").remove();
      $(".stripe li").removeClass("lowlight").removeClass("highlight");
      return false;
    }
    if(runningRequest) request.abort();
    runningRequest=true;
    request=$.post(
      "showresults.php",
      { q:$q.val() },
      function(response){
        showResults(response);
        runningRequest=false;
      },"json"
    );
    function showResults(data){
      $(".stripe li").removeClass("highlight").addClass("lowlight");
      $.each(data, function(i,item){
        $(item).removeClass("lowlight").addClass("highlight");
      });
      $("#results").remove();
      $("#searchform").append("<div id='results'> "+$(".highlight").length+" </div>");
      if(data.length==0) starttimer();
    }
  });

/* -- Adjust sizes ------------------------------------------------------- -- */
  $("#screen,#detail").css("height",$(window).height()-$("#panel").height()+"px");
  $('#screen').load('load.php', function() {
    $(".box").css("margin","10px auto 10px");
    $(".box .stripe").css("margin","10px 0 0 10px");
    if($.browser.msie) $(".box .stripe").css("margin-bottom","10px");
    $(".stripe ul li").css("margin-bottom","10px");

/* -- Adjust sizes ------------------------------------------------------- -- */
    origw=parseFloat($(".stripe ul li:last-child").width(),10);
    origh=parseFloat($(".stripe ul li:last-child").height(),10);
    bowi=origw
        +parseFloat($(".stripe ul li:last-child").css("margin-left"),10)
        +parseFloat($(".stripe ul li:last-child").css("margin-right"),10);
    liwi=Math.floor((parseFloat($(window).width(),10)-20-10)/bowi);
    $(".box,.stripe").css("width",(bowi*liwi+10)+"px");
    $(".stripe ul li .double").css("height",(origh)+"px");
    $(".stripe ul .content a[rel^='prettyPhoto']").removeAttr("onclick");
    $(".stripe ul .head a[rel^='prettyPhoto'],.stripe ul a.head[rel^='prettyPhoto'],.stripe ul .content a[rel^='prettyPhoto']").prettyPhoto({theme:'facebook'});
    $('.stripe ul .head img[title], .stripe ul .head a[title], .stripe ul a.head[title], .stripe ul .icon[title], .stripe ul .title[title], .stripe ul .content .text[title]').qtip({
      style:    {name:'dark',background:'#333333',tip:true,border:{width:2,radius:5,color:'#333333'}},
      position: {corner:{target:'bottomLeft',tooltip:'topRight'},target: 'mouse', adjust: { mouse: true, screen: true }}
    });
    $(".section a").click(function() {
      url=$(this).attr("href");
      if(url!="") loadsite(url,"");
      return false;
    });

    $(".stripe ul li:eq(0)").find(".head, .content").css("width",((origw+5)*2)+"px");
    $(".stripe ul li:eq(0)").find(".head, .content").css("height",((origh+5)*1)+"px");
    $(".stripe ul li:eq(0)").find(".head, .content").css("font-size","2em");
    $(".stripe ul li:eq(0)").find(".text").css("padding","10px");

/* -- Big box for big text ----------------------------------------------- -- */
    var boxwidth = $(".box,.stripe").width()-10;
    var boxposition = $(".box:first").position();
    $(".stripe ul li:gt(0) .content .text").each(function() {
        if($(this).text().length>150) {
          var position = $(this).parent().parent().parent().position();
          pl=position.left-boxposition.left-10;
          if(pl+bowi<boxwidth && $(this).parent().parent().parent().css("opacity")>.5) {
            $(this).parent().parent().parent().children(".head,.content").css("width",((origw+5)*2)+"px");
          }
        }
    });

/* -- Adjust image sizes ------------------------------------------------- -- */
    $(".stripe ul .headimg").each(function(index) {
      $(this).load(function() {
        ww=$(this).parent().parent().innerWidth();
        wh=$(this).parent().parent().innerHeight();
        wp=ww/wh;
        iw=$(this).width();
        ih=$(this).height();
        ip=iw/ih;
        if(wp<ip) {
          $(this).css("height",wh+"px");
        } else {
          $(this).css("width",ww+"px");
        }
        w=(ww-$(this).width())/2;
        h=(wh-$(this).height())/3;
        $(this).css("margin",h+"px 0 0 "+w+"px").css("opacity","1");
      })
    });
/* -- Show area ---------------------------------------------------------- -- */
    $(".box").nifty('transparent');
    if($(".stripe li").length<1) {
      $("#screen .box").hide();
    } else $("#searchbox").fadeIn();
    $.unblockUI();
    starttimer();
<?php if(!file_exists("../varfeeds.php.ini") && !file_exists("../varmails.php.ini")) echo"$.prettyPhoto.open('set_settings.php?iframe=true&width=400&height=216',txtsettings);"; ?>
  });

/* -- Navigation --------------------------------------------------------- -- */
  $("#panclo").click(function() {
    $(".detailbutton").fadeOut(function(){
      $("#results").fadeIn();
      $("#searchbox").fadeIn();
    });
    $("#screen").css("display","block");
    $("#detail").slideUp("slow");
    document.title="newswall";
//    window.clearTimeout(aktiv);
    cleartimeline();
    starttimer();
    return false;
  });
  $("#panweb").click(function(){
    url=$(this).data("url");
    if(url) {
      if($("#detailtext").css("display")=="none") {
        $("#detailweb").fadeOut(function(){
          $("#detailtext").fadeIn();
        })
      } else {
        if($("#detail iframe").length>0) {
          $("#detailtext").fadeOut(function(){
            $("#detailweb").fadeIn();
          })
        } else {
          $("#detail").append('<iframe id="detailweb" style="display:none;" />');
          $('#detailweb').css('width',$("#detail").width()).css('height',$("#detail").height());
          $('#detailweb').attr('src',url);
          $('#detailweb').load(function(){
            $("#detailtext").fadeOut(function(){
              $("#detailweb").fadeIn();
            })
          })
        }
      }
    }
    return false;
  });
  $("#panedi").click(function(){
    $.prettyPhoto.open('set_settings.php?iframe=true&width=400&height=216',txtsettings);
  });
  $("#pansha").click(function(){
    title=$(this).data("title");
    url=$(this).data("url");
    $.prettyPhoto.open('set_share.php?vartitle='+encodeURI(title)+'&varurl='+encodeURI(url)+'&iframe=true&width=420&height=50',txtshare,'');
  });
})

window.onresize=function(){
  $("#screen,#detail").css("height",$(window).height()-$("#panel").height()+"px");
  $(".stripe ul li:gt(0)").children(".head,.content").css("width",origw+"px");
  bowi=parseFloat($(".stripe ul li:last-child").width(),10)
      +parseFloat($(".stripe ul li:last-child").css("margin-left"),10)
      +parseFloat($(".stripe ul li:last-child").css("margin-right"),10);
  liwi=Math.floor((parseFloat($(window).width(),10)-20-10)/bowi);
  $(".box,.stripe").css("width",(bowi*liwi+10)+"px");
  var boxwidth = $(".box,.stripe").width()-10;
  var boxposition = $(".box:first").position();
  $(".stripe ul li:gt(0) .content .text").each(function() {
    if($(this).text().length>150) {
      var position = $(this).parent().parent().position();
      pl=position.left-boxposition.left-10;
      if(pl+bowi<boxwidth && $(this).parent().parent().css("opacity")>.5) {
        $(this).parent().parent().children(".head,.content").css("width",((origw+5)*2)+"px");
      }
    }
  });
  $(".stripe ul .headimg").each(function(index) {
    ww=$(this).parent().parent().innerWidth();
    wh=$(this).parent().parent().innerHeight();
    wp=ww/wh;
    iw=$(this).width();
    ih=$(this).height();
    ip=iw/ih;
    if(wp<ip) {
      $(this).css("height",wh+"px");
      $(this).css("width","auto");
    } else {
      $(this).css("width",ww+"px");
      $(this).css("height","auto");
    }
    w=(ww-$(this).width())/2;
    h=(wh-$(this).height())/3;
    $(this).css("margin",h+"px 0 0 "+w+"px");
  });
  $(".box").nifty('transparent');
}

function loadrss(source,id) {
//  window.clearTimeout(aktiv);
  cleartimeline();
  if(source<0) url="showmail.php"; else url="showfeed.php";
  $.blockUI({
    css: {
      border: 'none',
      padding: '0',
      backgroundColor: 'transparent',
      opacity: .5,
      color: '#fff'},
    message: '<img src="images/ajax-loader.gif" />'
  });
  $('#detail').load(url+"?source="+source+"&id="+id,
    function() {
      $.unblockUI();
      $("#results").fadeOut();
      $("#searchbox").fadeOut(function(){
        if($("#panweb").data("url")=="") {
          if($("#pansha").data("url")!="") $("#pansha").fadeIn();
          $("#panclo").fadeIn();
        } else
          $(".detailbutton").fadeIn();
      });
      $("#detail").slideDown("slow", function() {
        $("#screen").css("display","none");
      });
    }
  );
}
function loadsite(url,title) {
//  window.clearTimeout(aktiv);
  cleartimeline();
  $.blockUI({
    css: {
      border: 'none',
      padding: '0',
      backgroundColor: 'transparent',
      opacity: .5,
      color: '#fff'},
    message: '<img src="images/ajax-loader.gif" />'
  });
  $('#detail').empty();
  $("#detail").append('<iframe id="detailweb" style="display:none;" />');
  $('#detailweb').css('width',$("#screen").width()).css('height',$("#screen").height());
  $('#detailweb').attr('src',url);
  $('#detailweb').load(function(){
      $("#detailweb").show();
      $.unblockUI();
      $("#results").fadeOut();
      $("#searchbox").fadeOut(function(){
        $("#pansha").data("title",encodeURI(title));
        $("#pansha").data("url",encodeURI(url));
        $("#pansha").fadeIn();
        $("#panclo").fadeIn();
      });
      $("#detail").slideDown("slow", function() {
        $("#screen").css("display","none");
        if(title!="") document.title="newswall: "+title;
      });
    }
  );
}
function setreference(text) {
  $.prettyPhoto.open('set_reference.php?varreference='+encodeURI(text)+'&iframe=true&width=420&height=200',txtlink,'');
}
function dropreference(text) {
  $.blockUI({
    css: {
      border: 'none',
      padding: '0',
      backgroundColor: 'transparent',
      opacity: .5,
      color: '#fff'},
    message: '<img src="images/ajax-loader.gif" />'
  });
  $.post(
   "set_reference.php",
   { varreference: text, mode: "droplink" },
   function(response) {
     $.unblockUI();
     if(response=="success") {
       self.location.reload();
     }
   }
 );
}
function starttimer() {
  var jetzt = new Date();
  memtime=(jetzt.getTime());
//  aktiv = window.setTimeout("location.reload()", varsreload * 60 * 1000); // without timeline
  aktiv = window.setInterval("timeline()",1000); // with timeline
}
function timeline() {
  panelwidth=$("#panel").width();
  var jetzt = new Date();
  dif=jetzt.getTime()-memtime;
  timerwidth=(dif/(varsreload*60*1000))*panelwidth;
  $("#timeline").animate({width:timerwidth});
  if(timerwidth>=panelwidth) {
    window.clearInterval(aktiv);
    location.reload();
  }
}
function cleartimeline() {
  $("#timeline").animate({width:0});
  window.clearInterval(aktiv);
}