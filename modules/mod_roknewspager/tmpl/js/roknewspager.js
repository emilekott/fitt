/*
 * @author RocketTheme, LLC http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
var RokNewsPagerStorage=[];var RokNewsPager=new Class({version:1.5,Implements:[Options,Events],options:{autoupdate:true,delay:3000,accordion:false},initialize:function(b){var a=this;
this.setOptions(b);this.storage=RokNewsPagerStorage;this.elements=$$(".roknewspager");this.modules=this.elements.getParent(".roknewspager-wrapper").getParent();
this.spinners=[];this.arrows={prev:[],next:[]};this.pages=[];this.current=[];this.overlay=[];this.effects=[];this.ajax=[];this.timer=[];this.accordions=[];
this.elements.each(function(f,e){var c=f.getParent(".roknewspager-wrapper").getParent().getElement(".roknewspager-pages");this.pages.push(c);this.arrows.prev.push(!c?null:c.getElement(".roknewspager-prev"));
this.arrows.next.push(!c?null:c.getElement(".roknewspager-next"));this.spinners.push(!c?null:c.getElement(".roknewspager-spinner"));this.ajax.push(new Request({url:this.storage[e].url,method:"get"}));
this.overlay.push(new Element("div",{"class":"roknewspager-overlay"}).setStyles({opacity:0,display:"none",position:"absolute",top:0,left:0}).inject(f,"before"));
f.getParent().setStyle("overflow","hidden");this.effects.push(new Fx.Tween(this.overlay[e],{duration:200}).set("opacity",1));this.current.push(0);this.accordions.push(false);
this.events(e);this.ajax[e].addEvent("onComplete",function(g){this.complete(g,e);}.bind(this));if(RokNewsPagerStorage[e].autoupdate){this.ajax[e].addEvent("onRequest",function(){$clear(this.timer[e]);
}.bind(this));this.modules[e].addEvents({mouseenter:function(){$clear(this.timer[e]);}.bind(this),mousemove:function(){$clear(this.timer[e]);}.bind(this),mouseleave:function(){$clear(this.timer[e]);
this.timer[e]=this.next.periodical(RokNewsPagerStorage[e].delay,this,e);}.bind(this)});this.timer[e]=this.next.periodical(RokNewsPagerStorage[e].delay,this,e);
}var d=f.getElements(".roknewspager-h3").getParent();if(RokNewsPagerStorage[e].accordion){this.accordions[e]=new Fx.Accordion(f.getElements(".roknewspager-toggle"),f.getElements(".roknewspager-div"),{initialDisplayFx:false,opacity:false,onActive:function(g){if(this.togglers.length){$$(this.togglers).removeClass("roknewspager-toggle-active");
f.getChildren().removeClass("active");}g.addClass("roknewspager-toggle-active");var h=this.togglers.indexOf(g);f.getChildren()[h].addClass("active");}},f);
}},this);},complete:function(a,b){if(RokNewsPagerStorage[b].autoupdate){$clear(this.timer[b]);this.timer[b]=this.next.periodical(RokNewsPagerStorage[b].delay,this,b);
}this.overlay[b].setStyles({width:this.elements[b].getSize().x+this.elements[b].getParent().getStyle("padding-left").toInt()+this.elements[b].getParent().getStyle("padding-right").toInt(),height:(Browser.Engine.trident4)?this.elements[b].getSize().x:3000});
var c=new Element("div").set("html",a);if(!RokNewsPagerStorage[b].accordion){c.getElements(".roknewspager > li").inject(this.elements[b].empty());}else{c.getElement(".roknewspager").getChildren().inject(this.elements[b].empty());
}this.hideSpinner(b);this.effects[b].start("opacity",0);},events:function(b){if(!this.pages[b]){return;}var a=this.pages[b].getElements("li");a.each(function(d,c){d.addEvent("click",function(){if(d.hasClass("active")){return;
}if(!c){this.arrows.prev[b].addClass("roknewspager-prev-disabled").removeClass("roknewspager-prev");this.arrows.next[b].addClass("roknewspager-next").removeClass("roknewspager-next-disabled");
}else{if(c==a.length-1){this.arrows.next[b].addClass("roknewspager-next-disabled").removeClass("roknewspager-next");this.arrows.prev[b].addClass("roknewspager-prev").removeClass("roknewspager-prev-disabled");
}else{this.arrows.prev[b].addClass("roknewspager-prev").removeClass("roknewspager-prev-disabled");this.arrows.next[b].addClass("roknewspager-next").removeClass("roknewspager-next-disabled");
}}this.showSpinner(b);this.pages[b].getElements("li").removeClass("active");d.addClass("active");this.current[b]=c;this.overlay[b].setStyles({opacity:0.9,display:"block"});
var e=this.elements[b].getChildren().length*c;this.ajax[b].options.url=this.storage[b].url.replace("_OFFSET_",e);this.ajax[b].get();}.bind(this));},this);
this.arrows.prev[b].addClass("roknewspager-prev-disabled").removeClass("roknewspager-prev");this.arrows.prev[b].addEvent("click",function(){var c=this.current[b];
if(RokNewsPagerStorage[b].autoupdate){$clear(this.timer[b]);}this.arrows.next[b].addClass("roknewspager-next").removeClass("roknewspager-next-disabled");
if(!c||c-1==0){this.arrows.prev[b].addClass("roknewspager-prev-disabled").removeClass("roknewspager-prev");}if(a[c-1]){a[c-1].fireEvent("click");}}.bind(this));
this.arrows.next[b].addEvent("click",function(){var c=this.current[b];if(RokNewsPagerStorage[b].autoupdate){$clear(this.timer[b]);}this.arrows.prev[b].addClass("roknewspager-prev").removeClass("roknewspager-prev-disabled");
if(c==a.length-1||c+1==a.length-1){this.arrows.next[b].addClass("roknewspager-next-disabled").removeClass("roknewspager-next");}if(a[c+1]){a[c+1].fireEvent("click");
}}.bind(this));},showSpinner:function(a){return this.spinners[a].setStyle("display","block");},hideSpinner:function(a){if(RokNewsPagerStorage[a].accordion){this.accordionInit(a);
}return this.spinners[a].setStyle("display","none");},add:function(a){return this.storage.push($merge(this.options,a));},next:function(a){if(this.current[a]==this.pages[a].getElements("li").length-1){this.current[a]=-1;
}this.arrows.next[a].fireEvent("click");},accordionInit:function(a){this.accordions[a].elements=[];this.accordions[a].togglers=[];this.accordions[a].previous=-1;
this.elements[a].getElements(".roknewspager-h3").getParent().each(function(d,c){var e=d.getFirst(),b=d.getFirst().getElement(".roknewspager-toggle"),f=d.getLast();
this.accordions[a].addSection(b,f);e.inject(d);b.inject(e);f.inject(d);},this);this.accordions[a].display(0,false);}});window.addEvent("domready",function(){new RokNewsPager();
});