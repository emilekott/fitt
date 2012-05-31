/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){this.Scrollbar=new Class({Implements:[Options,Events],options:{chunk:20,fixed:false,gutter:true,injection:"after",direction:"vertical",wrap:true,effects:{duration:250,transition:"quad:in:out",link:"cancel"},wrapStyles:{},resizeWrapper:true,onShow:function(a,c,b){b.fade(0.7);
},onHide:function(a,c,b){b.fade(0);}},initialize:function(b,a){b=this.element=document.id(b);if(!b){return;}this.setOptions(a);this.bounds={trigger:{mouseenter:this.show.bind(this),mouseleave:this.hide.bind(this)},element:{mousewheel:this.mousewheel.bind(this)},drag:{mousedown:this.start.bind(this),mouseup:this.end.bind(this)},document:{mousemove:this.drag.bind(this),mouseup:this.end.bind(this)}};
if(!b.getStyle("position").test(/absolute|relative|fixed/i)){b.setStyle("position","relative");}if(this.options.direction=="vertical"){this.property={dir:"top",axis:"y",size:"height"};
}else{this.property={dir:"left",axis:"x",size:"width"};}if(this.options.wrap){var c=new Element("div",{styles:{position:"relative"}}).wraps(b);this.wrapper=c;
this.wrapper.store("custom",true);}else{this.wrapper=b.getParent();}this.wheelDir={horizontal:1,vertical:2};if(b.getStyle("width")){this.wrapper.setStyle("width",b.getStyle("width"));
}this.wrapper.setStyles(this.options.wrapStyles);this.triggerElement=document.getElement(this.options.triggerElement)||this.wrapper;this.scrollbar=new Element("div.scrollbar");
if(this.options.gutter){this.gutter=new Element("div.gutter."+this.options.direction).inject(b,"after").setStyle("opacity",0).setStyle(this.property.size,this.element.getSize()[this.property.axis]);
this.scrollbar.inject(this.gutter).setStyle(this.property.dir,0);this.gutter.setStyle(this.property.dir,this.gutter.getStyle(this.property.dir));this.gutter.set("tween",{duration:200});
this["stored"+this.property.dir.capitalize()]=0;}else{this.scrollbar.inject(this.element,"after");this["stored"+this.property.dir.capitalize()]=this.scrollbar.getStyle(this.property.dir).toInt()||0;
this.scrollbar.setStyle("opacity",0).setStyle(this.property.dir,this["stored"+this.property.dir.capitalize()]);}if(this.options.fixed){delete this.bounds.trigger;
this.show();}this.attach();this.update();},attach:function(){this.triggerElement.addEvents(this.bounds.trigger);this.element.addEvents(this.bounds.element);
(this.options.gutter?this.gutter:this.scrollbar).addEvents(this.bounds.element);this.scrollbar.addEvents(this.bounds.drag);this.scrollbar.store("detached",false);
return this;},detach:function(){this.triggerElement.removeEvents(this.bounds.trigger);this.element.removeEvents(this.bounds.element);(this.options.gutter?this.gutter:this.scrollbar).removeEvents(this.bounds.element);
this.scrollbar.removeEvents(this.bounds.drag);document.removeEvents(this.bounds.document);this.scrollbar.store("detached",true);return this;},update:function(){var b=this.scrollbar.retrieve("detached");
var d=this.dimensions={size:this.element.getSize()[this.property.axis],scrollSize:this.element.getScrollSize()[this.property.axis]};if(this.options.gutter){this.gutter.setStyle(this.property.size,d.size);
}if(this.wrapper.retrieve("custom")&&this.options.resizeWrapper){this.wrapper.setStyle("width",this.element.getStyle("width"));}if(d.scrollSize<=d.size){return(!b)?this.hide().detach():this.hide();
}if(b){this.attach();if(this.options.fixed){this.show();}}var a=this.ratio={content:d.size/d.scrollSize,scroll:d.scrollSize/(d.size-this["stored"+this.property.dir.capitalize()])};
var c=this.scrollbarSize=(d.size*a.content).limit(10,d.size);this.scrollbar.setStyle(this.property.size,c);this.updatePosition();return this;},updatePosition:function(){if(!this.ratio){return;
}var a=(this.element["scroll"+this.property.dir.capitalize()]/this.ratio.scroll).limit(this["stored"+this.property.dir.capitalize()]||0,(this.dimensions.size-this.scrollbarSize));
this.scrollbar.setStyle(this.property.dir,a);},show:function(){return this.fireEvent("show",[this.triggerElement,this.scrollbar,this.gutter||this.scrollbar]);
},hide:function(){return this.fireEvent("hide",[this.triggerElement,this.scrollbar,this.gutter||this.scrollbar]);},mousewheel:function(a){a.stop();var b=a.event.axis==this.wheelDir[this.options.direction],c;
if(Browser.Engine.webkit){c=a.event["wheelDelta"+this.property.axis.capitalize()];b=c;}if(Browser.ie||Browser.opera){c=a.event.wheelDelta;b=this.options.direction=="vertical"?c:false;
}if(b){this.element["scroll"+this.property.dir.capitalize()]-=a.wheel*this.options.chunk;}this.updatePosition();},start:function(a){a.stop();if(!this.coordinates){this.coordinates={mouse:{start:0,now:0},position:{start:0,now:0}};
}this.coordinates.mouse.start=a.page[this.property.axis];this.coordinates.position.start=this.scrollbar.getStyle(this.property.dir).toInt();this.triggerElement.removeEvents(this.bounds.trigger);
document.addEvents(this.bounds.document);},drag:function(a){a.stop();var b=this.coordinates;b.mouse.now=a.page[this.property.axis];b.position.now=(b.position.start+(b.mouse.now-b.mouse.start)).limit(0,(this.dimensions.size+this.scrollbarSize));
this.updatePosition();this.element["scroll"+this.property.dir.capitalize()]=b.position.now*this.ratio.scroll;},end:function(a){a.stop();document.removeEvents(this.bounds.document);
this.triggerElement.addEvents(this.bounds.trigger);if(!this.triggerElement.contains(a.target)&&!this.options.fixed){this.hide();}},scroll:function(){},toBottom:function(){this.element["scroll"+this.property.dir.capitalize()]=this.element["scroll"+this.property.size.capitalize()];
return this.updatePosition();}});})());