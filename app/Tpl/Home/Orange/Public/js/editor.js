$(function(){
  /** 编译器 **/
  function initToolbarBootstrapBindings() {
    var fonts = ['宋体,SimSun','微软雅黑,Microsoft YaHei','楷体,楷体_GB2312, SimKai','黑体, SimHei','隶书, SimLi','Serif', 'Sans', 'Arial', 'Arial Black', 'Courier', 'Courier New', 'Comic Sans MS', 'Helvetica', 'Impact', 'Lucida Grande', 'Lucida Sans', 'Tahoma', 'Times','Times New Roman', 'Verdana'],
        fontTarget = $('[title=字体]').siblings('.dropdown-menu');

    /** 循环输出字体 **/
    $.each(fonts, function (idx, fontName) {
      fontTarget.append($('<li><a data-edit="fontName ' + fontName +'" style="font-family:\''+ fontName +'\'">'+fontName + '</a></li>'));
    });

    /** 工具提示 **/
    $('a[title]').tooltip({container:'body'});

    /** 下拉输入框 **/
    $('.dropdown-menu input').click(function() {return false;})
      .change(function () {$(this).parent('.dropdown-menu').siblings('.dropdown-toggle').dropdown('toggle');})
      .keydown('esc', function () {this.value='';$(this).change();});

    /** 插入图片 **/
    $('[data-role=magic-overlay]').each(function () {
      var overlay = $(this), target = $(overlay.data('target'));
      overlay.css('opacity', 0).css('position', 'absolute').offset(target.offset()).width(target.outerWidth()).height(target.outerHeight());
    });
    $('#voiceBtn').hide();
    // if ("onwebkitspeechchange"  in document.createElement("input")) {
    //   var editorOffset = $('#editor').offset();
    //   $('#voiceBtn').css('position','absolute').offset({top: editorOffset.top, left: editorOffset.left+$('#editor').innerWidth()-35});
    // } else {
    //   $('#voiceBtn').hide();
    // }
  };

  /**  初始化编辑器工具栏 **/
  initToolbarBootstrapBindings();

  /** 初始化编辑器 **/
  $('#editor').wysiwyg();

  window.prettyPrint && prettyPrint();
})
