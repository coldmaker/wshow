// set the wechat share option
function wxshare() {
  var wxjs = WeixinJSBridge;
  wxjs.on("menu:share:appmessage", shareappmessage);
  wxjs.on("menu:share:weibo", shareweibo);
  wxjs.on("menu:share:timeline", sharetimeline);
  //wxjs.invoke("getnetworktype", {}, getnetworktype)
}

// get the client network type by weixinjsbridge
// function getnetworktype(wxjs) {
//   var b, c;
//   switch (a.err_msg) {
//   case "network_type:wwan":
//     b = 2e3;
//     break;
//   case "network_type:edge":
//     b = 3e3;
//     break;
//   case "network_type:wifi":
//     b = 4e3
//   }
//   c = new image,
//   c.onerror = c.onload = function () {
//     c = null
//   }
// }



// function share2qqblog() {
//   var b = window.shareData.share2qqBlog;
//   WeixinJSBridge.invoke("shareWeibo", {
//     content: a.isios ? b.content + b.link : b.content,
//     url: b.link
//   }, function () { })
// }

function share(type) {
  var data = window.shareData;
  WeixinJSBridge.invoke("sendAppMessage", {
    img_url: data.img,
    img_width: "640",
    img_height: "640",
    link: data.link,
    desc: data.content,
    title: data.title
  }, function (res) {
  })
}
// 发送给好友
function shareappmessage(){
  WeixinJSBridge.invoke('sendAppMessage', {
    "img_url": window.shareData.img,
    "img_width": "640",
    "img_height": "640",
    "link": window.shareData.link,
    "desc": window.shareData.content,
    "title": window.shareData.title
  }, function (res) {
  });
}
// 分享到朋友圈
function sharetimeline(){
  WeixinJSBridge.invoke('shareTimeline', {
    "img_url": window.shareData.img,
    "img_width": "640",
    "img_height": "640",
    "link": window.shareData.link,
    "desc": window.shareData.content,
    "title": window.shareData.title
  }, function (res) {
  });
}
// 分享到微博
function shareweibo(){
  WeixinJSBridge.invoke('shareWeibo', {
    "content": window.shareData.content,
    "url": window.shareData.link
  }, function (res) {
  });
}
// get the client agent:ios, android
function getclientagent(){
  var a = window.navigator.userAgent;
  return this.isAndroid =
    a.match(/(Android)\s+([\d.]+)/) || a.match(/Silk-Accelerated/) ? !0 : !1,
  this.isiPad = a.match(/iPad/) ? !0 : !1,
  this.isiPod = a.match(/(iPod).*OS\s([\d_]+)/) ? !0 : !1,
  this.isiPhone = !this.isiPad && a.match(/(iPhone\sOS)\s([\d_]+)/) ? !0 : !1,
  this.isios = this.isiPhone || this.isiPad || this.isiPod, this
}

window.shareData && document.addEventListener("WeixinJSBridgeReady", wxshare, !1);
