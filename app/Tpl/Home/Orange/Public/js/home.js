$(function(){
  current_url = "{:U('User/basic')}";
    /**头部导航切换**/
    $("#topMenu").find("li").click(function(){
      $(this).addClass("active")
      var menu = $(this).find("a").attr("href");
      if(menu != 'javascript:;'){
        $(menu).removeClass("hidden").addClass("show");
        $(menu).siblings("ul").removeClass("show").addClass("hidden");
      }
      $(this).siblings("li").removeClass("active");
    });

    /** set the defalut nav**/
    //$("nav-sidebar").collapse();
    /** default show the first nav **/
    $("li.link:first").parents("li").addClass("active");
    $("li.link:first").parents("li").find("i.fa-angle-right").removeClass("fa-angle-right").addClass("fa-angle-down");
    $("li.link:first").parents("ul").collapse("show");
    var url = $("li.link:first").data("href");
  loadMain(url);

    /** 右侧箭头效果 **/
    $(".nav-sidebar li").click(function(){
        $(this).find("i.fa-angle-right").removeClass("fa-angle-right").addClass("fa-angle-down");
        if(!$(this).siblings("li").hasClass("active")){
          $(this).siblings("li").find("i.fa-angle-down").removeClass("fa-angle-down").addClass("fa-angle-right");
        }
    });

    /** ajax load the main content **/
    $(".link").click(function(){
      if(!$(this).parents("li").hasClass("active")){
        /** 如果不是当前状态 **/
        $(this).parents("li").addClass("active");

        /** 定义激活状态的对象 **/
        var active = $(this).parents("li").siblings("li").filter(".active");
        active.removeClass("active");
        active.children("ul").collapse("hide");
      }
      var url = $(this).data('href');
      loadMain(url);
    });

  /** 绑定F5 **/
  document.onkeydown=function(event){
    var e = event || window.event || arguments.callee.caller.               arguments[0];
    if(e && e.keyCode==116){ // F5
      e.preventDefault();
      loadMain(0);
      }
    }

  /** 实时监听 **/
  $("#GMessage").hide();
  /*
  setInterval(function(){
    var msg = $("#GMessage");
    msg.load('message.log',function(data){
      if(data != ''){
        msg.fadeIn();
        window.setTimeout('$("#GMessage").fadeOut()',2000);
        $.get('push.php');
      }
    });
  },1000)*/
})

/** load main content **/
function loadMain(url){
  if(url == ''){
    url = current_url;
  }

  current_url = url;
  $(".main").load(url);

}

/** 发送表单 **/
function sendForm(url){
  var ids = $('input[name="id[]"]:checked').val();
  $.post(url,
         {'id':ids},
         function(data){
           calert(data.msg,data.code);
         },
         'json'
        );
}

/** post to the server **/
function editInfo(form){
  var selForm = $("#"+form);

  /** 文本编译器赋值 **/
  var resultInfo = $("#editor").html();
  $("#resultInfo").val(resultInfo);

  var data = selForm.serialize();
  var url = selForm.data('href');
  //var file = $("#imgPath").files;

  $.ajax({
    type: "POST",
    enctype: "multipart/form-data",
    url: url,
    data: data,
    success: function( data )
    {
      data = JSON.parse(data);
      if("undefined" == typeof data.code){data.code='0';}
      calert(data.msg, data.code);
    }
  });
  return false;
}

/** alert function **/
function calert(msg, status){
    switch (status){
    case '0':
        var status_name = 'alert-danger';
        break;
    case '1':
        var status_name = 'alert-success';
        break;
    }
    html = '<div class="alert '+status_name+'">'+msg+'<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button></div>'
    $(".main").prepend(html);
    window.setTimeout(function(){
      $(".alert").alert('close');
    }, 1500);
}

/** 调用图片选择 **/
function showImgBox(){
  $("#imgBox").dialog("open");
}
