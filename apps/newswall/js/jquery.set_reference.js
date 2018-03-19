$(window).load(function(){
  if($("body").attr("class")=="login") {
    parent.$(".pp_content").animate({"height": "-=144px"});
    parent.$("#pp_full_res iframe").animate({"height": "-=144px"});
  }
  new AjaxUpload('upload_button', {
    action: 'set_reference.php',
    data: {
      varreference : varreference
    },
    // Submit file after selection
    autoSubmit: true,
    onSubmit: function() {
      $.blockUI({
      css: {
                border: 'none',
                padding: '0',
                backgroundColor: 'transparent',
                opacity: .5,
                color: '#fff'},
      message: '<img src="images/ajax-loader.gif" />'
      });
    },
    onComplete: function(file, response){
      $.unblockUI();
      if(response=="success") {
        parent.$.prettyPhoto.close();
        parent.location.reload();
      }
      // this.disable();
    }
  });

  $('.refpic img[title]').qtip({
    style:    {name:'dark',background:'#333333',tip:true,border:{width:2,radius:5,color:'#333333'}},
    position: {corner:{target:'bottomLeft',tooltip:'topRight'},target: 'mouse', adjust: { mouse: true, screen: true }}
  });
  $(".refpic img.image").each(function(index) {
      ww=$(this).parent().innerWidth();
      wh=$(this).parent().innerHeight();
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
      $(this).css("margin",h+"px 0 0 "+w+"px");
  })
})

function setref(text,file) {
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
   { varreference: text, name: file },
   function(response) {
     $.unblockUI();
     if(response=="success") {
       parent.$.prettyPhoto.close();
       parent.location.reload();
     }
   }
 );
}

function delref(file) {
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
   { varreference: file, mode: "delete" },
   function(response) {
     $.unblockUI();
     if(response=="success") {
       self.location.reload();
     }
   }
 );
}

function unlinkref(file) {
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
   { varreference: file, mode: "unlink" },
   function(response) {
     $.unblockUI();
     if(response=="success") {
       self.location.reload();
     }
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
      parent.$(".pp_content").animate({"height": "+=144px"});
      parent.$("#pp_full_res iframe").animate({"height": "+=144px"}, function(){
        self.location.reload();
      });
     } else
       $.unblockUI();
   }
 );
}