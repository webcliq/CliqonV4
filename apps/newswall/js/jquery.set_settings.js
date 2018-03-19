$(window).load(function(){
  if($("body").attr("class")=="login") {
    cn="loginsetting";
    parent.$(".pp_content").animate({"height": "-=160px"});
    parent.$("#pp_full_res iframe").animate({"height": "-=160px"});
  } else {
    cn=$(location).attr("hash").replace("#","")+"setting";
    if(cn=="setting") cn="feedssetting";
  }
  $("#calculate").css("opacity","0");
  $(".tabin").css("height",216-$(".feedline:first").outerHeight()+"px");

  $("#calculate").hide(function(){
    $("."+cn).css("font-weight","bold");
    $("#"+cn).height("auto");
    $("#"+cn).height(216-$(".feedline:first").outerHeight()+"px");
    $("#"+cn).show();
  });

  if(cn=="loginsetting") $("#panelpass").focus();
  if(cn=="feedssetting") $("#newfeedurl").focus();
  if(cn=="pop3setting")  $("#pop3setting input:first").select();
  if(cn=="imapsetting")  $("#imapsetting input:first").select();

  $(".feedline span").each(function() {
    $(this).click(function(){
      $(".tabin").hide();
      $(".feedline span").css("font-weight","normal");
      $(this).css("font-weight","bold");
      cn=$(this).attr("class");
      if(cn!="") {
        $("#"+cn).height("auto");
        $("#"+cn).height(216-$(".feedline:first").outerHeight()+"px");
        $("#"+cn).show();
        if(cn=="loginsetting") $("#panelpass").focus();
        if(cn=="feedssetting") $("#newfeedurl").focus();
        if(cn=="pop3setting")  $("#pop3setting input:first").select();
        if(cn=="imapsetting")  $("#imapsetting input:first").select();
      }
    });
  });
  $('*[title]').qtip({
    style:    {name:'dark',background:'#333333',tip:true,border:{width:2,radius:5,color:'#333333'}},
    position: {corner:{target:'bottomLeft',tooltip:'topRight'},target: 'mouse', adjust: { mouse: true, screen: true }}
  });
})

function delfeed(url) {
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
   "set_settings.php",
   { varfeed: url, mode: "delete" },
   function(response) {
     $.unblockUI();
     if(response=="success") {
       self.location.href="set_settings.php?"+Math.random()*999999+"#feeds";
     }
   }
 );
}
function addfeed() {
  url=$("#newfeedurl").val();
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
   "set_settings.php",
   { varfeed: url, mode: "add" },
   function(response) {
     $.unblockUI();
     if(response=="success") {
       $("#newfeedurl").val("http://");
       self.location.href="set_settings.php?"+Math.random()*999999+"#feeds";
     }
   }
 );
}
function delmail(mail,type) {
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
   "set_settings.php",
   { varmail: mail, mode: "delete" },
   function(response) {
     $.unblockUI();
     if(response=="success") {
       self.location.href="set_settings.php?"+Math.random()*999999+"#"+type;
     }
   }
 );
}
function addmail(type) {
  host=$("#newmailhost"+type).val();
  user=$("#newmailuser"+type).val();
  pass=$("#newmailpass"+type).val();
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
   "set_settings.php",
   { varmailhost: host, varmailuser: user, varmailpass: pass, varmailtype: type, mode: "add" },
   function(response) {
     $.unblockUI();
     if(response=="success") {
       window.location.href="set_settings.php?"+Math.random()*999999+"#"+type;
     }
   }
 );
}
function saveset() {
  varreload    =$("#varreload").val();
  varhighlight =$("#varhighlight").val();
  varrange     =$("#varrange").val();
  varmaxitems  =$("#varmaxitems").val();
  varfade      =$("#varfade").val();
  varpassword  =$("#varpassword").val();
  varpanelpass =$("#varpanelpass").val();
  varlanguage  =$("#varlanguage").val();
  vartheme     =$("#vartheme").val();
  varfeedsite  =$("#varfeedsite").val();
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
   "set_settings.php",
   { varreload:varreload,varhighlight:varhighlight,varrange:varrange,varmaxitems:varmaxitems,varfade:varfade,varpassword:varpassword,varpanelpass:varpanelpass,varlanguage:varlanguage,vartheme:vartheme,varfeedsite:varfeedsite,mode:"save" },
   function(response) {
     if(response=="success")
       parent.location.reload();
     else
       $.unblockUI();
   }
 );
}
function login() {
  varpanelpass=$("#panelpass").val();
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
   "set_settings.php",
   { varpanelpass:varpanelpass,mode:"login" },
   function(response) {
     if(response=="success") {
      $.unblockUI();
      $(".login").fadeOut();
      parent.$(".pp_content").animate({"height": "+=160px"});
      parent.$("#pp_full_res iframe").animate({"height": "+=160px"}, function(){
        self.location.reload();
      });
     } else
       $.unblockUI();
   }
 );
}
