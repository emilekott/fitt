/*
 * SqueezeBox - Expandable Lightbox
 *
 * Allows to open various content as modal,
 * centered and animated box.
 *
 * Dependencies: MooTools 1.2
 *
 * Inspired by
 *  ... Lokesh Dhakar	- The original Lightbox v2
 *
 * @version		1.1 rc4
 *
 * @license	MIT-style license
 * @author		Harald Kirschner <mail [at] digitarald.de>
 * @notes		Djamil Legato - Fixes for MooTools 1.3
 * @copyright	Author
 */
var SqueezeBox={presets:{onOpen:function(){},onClose:function(){},onUpdate:function(){},onResize:function(){},onMove:function(){},onShow:function(){},onHide:function(){},size:{x:600,y:450},sizeLoading:{x:200,y:150},marginInner:{x:20,y:20},marginImage:{x:50,y:75},handler:false,target:null,closable:true,closeBtn:true,zIndex:65555,overlayOpacity:0.7,classWindow:"",classOverlay:"squeezebox-overlay",overlayFx:{},resizeFx:{},contentFx:{},parse:"rel",parseSecure:false,shadow:true,document:null,ajaxOptions:{}},initialize:function(b){if(this.options){return this;
}this.presets=Object.merge(this.presets,b);this.doc=this.presets.document||document;this.options={};this.setOptions(this.presets).build();this.bound={window:this.reposition.bind(this),scroll:this.checkTarget.bind(this),close:this.close.bind(this),key:this.onKey.bind(this)};
this.isOpen=this.isLoading=false;return this;},build:function(){this.overlay=new Element("div",{id:"sbox-overlay",styles:{display:"none",zIndex:this.options.zIndex}});
this.win=new Element("div",{id:"sbox-window",styles:{display:"none",zIndex:this.options.zIndex+2}});this.win.close=this.close.bind(this);if(this.options.shadow){if(Browser.Engine&&Browser.Engine.webkit420){this.win.setStyle("-webkit-box-shadow","0 0 10px rgba(0, 0, 0, 0.7)");
}else{if(!Browser.ie6){var a=new Element("div",{"class":"sbox-bg-wrap"}).inject(this.win);var d=function(b){this.overlay.fireEvent("click",b);}.bind(this);
["n","ne","e","se","s","sw","w","nw"].each(function(b){new Element("div",{"class":"sbox-bg sbox-bg-"+b}).inject(a).addEvent("click",d);});}}}this.content=new Element("div",{id:"sbox-content"}).inject(this.win);
this.closeBtn=new Element("a",{id:"sbox-btn-close",href:"#"}).inject(this.win);this.fx={overlay:new Fx.Tween(this.overlay,Object.merge({onStart:Events.prototype.clearChain,duration:250,link:"cancel"},this.options.overlayFx)).set("opacity",0),win:new Fx.Morph(this.win,Object.merge({onStart:Events.prototype.clearChain,unit:"px",duration:750,transition:Fx.Transitions.Quint.easeOut,link:"cancel",unit:"px"},this.options.resizeFx)),content:new Fx.Tween(this.content,Object.merge({duration:250,link:"cancel"},this.options.contentFx)).set("opacity",0)};
document.id(this.doc.body).adopt(this.overlay,this.win);},assign:function(d,c){return(document.id(d)||$$(d)).addEvent("click",function(){return !SqueezeBox.fromElement(this,c);
});},open:function(j,i){this.initialize();if(this.element!=null){this.trash();}this.element=document.id(j)||false;this.setOptions(Object.merge(this.presets,i||{}));
if(this.element&&this.options.parse){var c=this.element.get(this.options.parse);if(c&&(c=JSON.decode(c,this.options.parseSecure))){this.setOptions(c);}}this.url=((this.element)?(this.element.get("href")):j)||this.options.url||"";
this.assignOptions();var b=b||this.options.handler;if(b){return this.setContent(b,this.parsers[b].call(this,true));}var a=false;return this.parsers.some(function(e,d){var f=e.call(this);
if(f){a=this.setContent(d,f);return true;}return false;},this);},fromElement:function(d,c){return this.open(d,c);},assignOptions:function(){this.overlay.set("class",this.options.classOverlay);
this.win.set("class",this.options.classWindow);if(Browser.Engine.trident4){this.win.addClass("sbox-window-ie6");}},close:function(c){var b=(typeOf(c)=="event");
if(b){c.stop();}if(!this.isOpen||(b&&!Function.from(this.options.closable).call(this,c))){return this;}this.fx.overlay.start("opacity",0).chain(this.toggleOverlay.bind(this));
this.win.setStyle("display","none");this.fireEvent("onClose",this.content);this.trash();this.toggleListeners();this.isOpen=false;return this;},trash:function(){this.element=this.asset=null;
this.content.empty();this.options={};this.setOptions(this.presets);this.callChain();},onError:function(){this.asset=null;this.setContent("string",this.options.errorMsg||"An error occurred");
},setContent:function(d,c){if(!this.handlers[d]){return false;}this.content.className="sbox-content-"+d;this.applyTimer=this.applyContent.delay(this.fx.overlay.options.duration,this,this.handlers[d].call(this,c));
if(this.overlay.retrieve("opacity")){return this;}this.toggleOverlay(true);this.fx.overlay.start("opacity",this.options.overlayOpacity);return this.reposition();
},applyContent:function(d,c){if(!this.isOpen&&!this.applyTimer){return;}this.applyTimer=clearTimeout(this.applyTimer);this.hideContent();if(!d){this.toggleLoading(true);
}else{if(this.isLoading){this.toggleLoading(false);}this.fireEvent("onUpdate",this.content,20);}if(d){if(["string","array"].contains(typeOf(d))){this.content.set("html",d);
}else{if(!(d!==this.content&&this.content.contains(d))){this.content.adopt(d);}}}this.callChain();if(!this.isOpen){this.toggleListeners(true);this.resize(c,true);
this.isOpen=true;this.fireEvent("onOpen",this.content);}else{this.resize(c);}},resize:function(g,f){this.showTimer=clearTimeout(this.showTimer||null);var i=this.doc.getSize(),e=this.doc.getScroll();
this.size=Object.merge((this.isLoading)?this.options.sizeLoading:this.options.size,g);var h={width:this.size.x,height:this.size.y,left:(e.x+(i.x-this.size.x-this.options.marginInner.x)/2).toInt(),top:(e.y+(i.y-this.size.y-this.options.marginInner.y)/2).toInt()};
this.hideContent();if(!f){this.fx.win.start(h).chain(this.showContent.bind(this));}else{this.win.setStyles(h).setStyle("display","");this.showTimer=this.showContent.delay(50,this);
}return this.reposition();},toggleListeners:function(d){var c=(d)?"addEvent":"removeEvent";this.closeBtn[c]("click",this.bound.close);this.overlay[c]("click",this.bound.close);
this.doc[c]("keydown",this.bound.key)[c]("mousewheel",this.bound.scroll);this.doc.getWindow()[c]("resize",this.bound.window)[c]("scroll",this.bound.window);
},toggleLoading:function(b){this.isLoading=b;this.win[(b)?"addClass":"removeClass"]("sbox-loading");if(b){this.fireEvent("onLoading",[this.win]);}},toggleOverlay:function(d){var c=this.doc.getSize().x;
this.overlay.setStyle("display",(d)?"":"none");this.doc.body[(d)?"addClass":"removeClass"]("body-overlayed");if(d){this.scrollOffset=this.doc.getWindow().getSize().x-c;
this.doc.body.setStyle("margin-right",this.scrollOffset);}else{this.doc.body.setStyle("margin-right","");}},showContent:function(){if(this.content.get("opacity")){this.fireEvent("onShow",[this.win]);
}this.fx.content.start("opacity",1);},hideContent:function(){if(!this.content.get("opacity")){this.fireEvent("onHide",[this.win]);}this.fx.content.cancel().set("opacity",0);
},onKey:function(a){switch(a.key){case"esc":this.close(a);case"up":case"down":return false;}},checkTarget:function(a){return a.target!==this.content&&this.content.contains(a.target);
},reposition:function(){var d=this.doc.getSize(),b=this.doc.getScroll(),c=this.doc.getScrollSize();this.overlay.setStyles({width:"100%",height:c.y,top:0,left:0,bottom:0,right:0});
this.win.setStyles({left:(b.x+(d.x-this.win.offsetWidth)/2-this.scrollOffset).toInt()+"px",top:(b.y+(d.y-this.win.offsetHeight)/2).toInt()+"px"});return this.fireEvent("onMove",[this.overlay,this.win]);
},removeEvents:function(b){if(!this.$events){return this;}if(!b){this.$events={};}else{if(this.$events[b]){this.$events[b]={};}}return this;},extend:function(b){return Object.append(this,b);
},handlers:new Hash(),parsers:new Hash()};SqueezeBox.extend(new Events()).extend(new Options()).extend(new Chain());SqueezeBox.parsers.extend({image:function(b){return(b||(/\.(?:jpg|png|gif)$/i).test(this.url))?this.url:false;
},clone:function(d){if(document.id(this.options.target)){return document.id(this.options.target);}if(this.element&&!this.element.parentNode){return this.element;
}var c=this.url.match(/#([\w-]+)$/);return(c)?document.id(c[1]):(d?this.element:false);},ajax:function(b){return(b||(this.url&&!(/^(?:javascript|#)/i).test(this.url)))?this.url:false;
},iframe:function(b){return(b||this.url)?this.url:false;},string:function(b){return true;}});SqueezeBox.handlers.extend({image:function(a){var e,d=new Image();
this.asset=null;d.onload=d.onabort=d.onerror=(function(){d.onload=d.onabort=d.onerror=null;if(!d.width){this.onError.delay(10,this);return;}var b=this.doc.getSize();
b.x-=this.options.marginImage.x;b.y-=this.options.marginImage.y;e={x:d.width,y:d.height};for(var c=2;c--;){if(e.x>b.x){e.y*=b.x/e.x;e.x=b.x;}else{if(e.y>b.y){e.x*=b.y/e.y;
e.y=b.y;}}}e.x=e.x.toInt();e.y=e.y.toInt();this.asset=document.id(d);d=null;this.asset.width=e.x;this.asset.height=e.y;this.applyContent(this.asset,e);
}).bind(this);d.src=a;if(d&&d.onload&&d.complete){d.onload();}return(this.asset)?[this.asset,e]:null;},clone:function(b){if(b){return b.clone();}return this.onError();
},adopt:function(b){if(b){return b;}return this.onError();},ajax:function(b){var a=this.options.ajaxOptions||{};this.asset=new Request.HTML(Object.merge({method:"get",evalScripts:false,onSuccess:function(f,e,h,g){this.applyContent(h);
if(a.evalScripts!==null&&a.evalScripts){$exec(g);}this.fireEvent("onAjax",[f,e,h,g]);this.asset=null;}.bind(this),onFailure:this.onError.bind(this)},this.options.ajaxOptions));
this.asset.send.delay(20,this.asset,{url:b});},iframe:function(b){this.asset=new Element("iframe",Object.merge({src:b,frameBorder:0,width:this.options.size.x,height:this.options.size.y},this.options.iframeOptions));
if(this.options.iframePreload){this.asset.addEvent("load",function(){this.applyContent(this.asset.setStyle("display",""));}.bind(this));this.asset.setStyle("display","none").inject(this.content);
return false;}return this.asset;},string:function(b){return b;}});SqueezeBox.handlers.url=SqueezeBox.handlers.ajax;SqueezeBox.parsers.url=SqueezeBox.parsers.ajax;
SqueezeBox.parsers.adopt=SqueezeBox.parsers.clone;