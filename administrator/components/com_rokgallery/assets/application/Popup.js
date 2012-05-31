/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){this.Popup=new Class({Implements:[Options,Events],options:{popup:"popup",overlay:"overlay"},initialize:function(a){this.setOptions(a);this.bounds=this.setBounds();
this.popup=document.id(this.options.popup).inject(document.body);this.overlay=document.id(this.options.overlay).inject(document.body);this.topBar=this.popup.getElement(".topbar");
this.content=this.popup.getElement(".content");this.statusBar=this.popup.getElement(".statusbar");this.setEvents();},setBounds:function(){return{popup:{open:this.showPopup.bind(this),close:this.hidePopup.bind(this),ok:this.continuePopup.bind(this)},overlay:{show:this.showOverlay.bind(this),hide:this.hideOverlay.bind(this)}};
},setEvents:function(){if(this.popup){this.reposition(true);this.popup.inject(this.overlay,"after");this.popup.setStyle("visibility","hidden");this.popup.set("morph",{duration:300,link:"cancel",transition:"expo:in"});
this.popup.addEvents({open:this.bounds.popup.open,close:this.bounds.popup.close,ok:this.bounds.popup.ok});}if(this.overlay){this.overlay.setStyles({visibility:"hidden",opacity:0,display:"block"});
this.overlay.addEvents({show:this.bounds.overlay.show,hide:this.bounds.overlay.hide});}window.addEvent("resize:throttle",this.reposition.bind(this));},attachEvents:function(){var a=this.popup.getElements(".button");
a.each(function(b){b.removeEvents("click");if(b.hasClass("close")||b.hasClass("cancel")){b.addEvent("click:once",this.close.bind(this));}if(b.hasClass("ok")){b.addEvent("click:once",this.popup.fireEvent.pass("ok",this.popup));
}},this);},showPopup:function(){this.popup.setStyles({visibility:"visible",opacity:1});},hidePopup:function(){this.popup.setStyles({visibility:"hidden",opacity:0});
},continuePopup:function(){if(this.continueFn){this.continueFn();}this.fireEvent("ok");},setContinue:function(a){this.continueFn=a.bind(this);},showOverlay:function(){this.overlay.setStyles({visibility:"visible",opacity:1});
},hideOverlay:function(){this.overlay.setStyles({visibility:"hidden",opacity:0});},open:function(){var a=window.getSize();if(a.y>=window.getScrollSize().y){document.getElements("html, body").setStyle("height","100%");
}else{document.getElements("html, body").setStyle("height","auto");}this.content.set("class","content");this.overlay.fireEvent("show");this.popup.fireEvent("open");
this.popup.store("open",true);this.attachEvents();return this;},close:function(){this.overlay.fireEvent("hide");this.popup.fireEvent("close");this.popup.store("open",false);
},reposition:function(c){if(!this.popup.retrieve("open")&&!c){return this.popup;}var f=this.popup.getSize(),b=this.overlay.getSize(),a=window.getSize();
if(a.y>=window.getScrollSize().y){document.getElements("html, body").setStyle("height","100%");}else{document.getElements("html, body").setStyle("height","auto");
}var e=a.y/2-(f.y/2),d=a.x/2-(f.x/2);return this.popup.setStyles({top:e.limit(5,a.y),left:d.limit(5,a.x)});},setPopup:function(b){this.continueFn=function(){};
this.setType("");if(!("size" in b)){this.setSize({width:400});this.reposition();}for(var a in b){this["set"+a.capitalize()](b[a]);}return this;},setButtons:function(d){this.popup.getElements(".button").setStyle("display","none");
for(var c in d){var b=d[c];var a=this.popup.getElement(".button."+c);a.setStyle("display",b.show?"block":"none");if(b.label){a.set("text",b.label);}}},setType:function(a){this.popup.className="popup "+a;
},setTitle:function(a){this.topBar.getElement("span:first-child").set("text",a);},setMessage:function(a){this.content.set("html",a);this.reposition();},setSize:function(b){var c={},a=this;
if(b&&typeOf(b)=="object"){for(var d in b){c[d]=b[d];}this.popup.setStyles(c);}this.reposition.delay(10,this);}});})());