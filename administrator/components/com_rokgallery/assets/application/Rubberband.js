/*
 * RubberBand - A Rubberband selector
 *
 * @version		1.0
 * @license		MIT License
 * @author			Nathan White <http://www.nwhite.net/>
 *
 * @changes		Djamil Legato
 * @copyright		Authors
 */
((function(){Element.Properties.placement={set:function(b){this.store("placement",this.getCoordinates());},get:function(){return this.retrieve("placement");
}};var a=this.Rubberband=new Class({Implements:[Options,Events],options:{draggable:false,drag:{},triggers:[],elements:[],select:function(b){b.addClass("selected");
b.store("rubberbanded",true);},deselect:function(b){b.removeClass("selected");b.store("rubberbanded",false);},itemClick:function(b,c){if(!c.shift&&!c.meta&&!c.control){return;
}if(c.shift){if(document.selection&&document.selection.empty){document.selection.empty();}else{if(window.getSelection){sel=window.getSelection();if(sel&&sel.removeAllRanges){sel.removeAllRanges();
}}}}if(b.hasClass("selected")){this.selected.erase(b);this.options.deselect(b);}else{this.selected.include(b);this.options.select(b);}}},triggers:null,ignores:null,selected:[],index:[],box:null,initialize:function(b){this.setOptions(b);
this.triggers=typeOf(this.options.triggers)=="string"?document.getElements(this.options.triggers):this.options.triggers;this.elements=typeOf(this.options.elements)=="string"?document.getElements(this.options.elements):this.options.elements;
this.ignores=typeOf(this.options.ignores)=="string"?document.getElements(this.options.ignores):this.options.ignores;this.elements.each(this.add.bind(this));
this.box=new Element("div",{styles:{position:"absolute",border:"1px dotted #999",display:"none","z-index":100}}).inject(document.body);this.overlay=new Element("div",{styles:{opacity:0.4,height:"100%",width:"100%","background-color":"#5EA8F6"}}).inject(this.box);
this.bounds={trigger:{mousedown:this.start.bind(this)},document:{mousemove:this.move.bind(this),mouseup:this.end.bind(this)}};this.options.trigger=(!this.options.trigger)?document.body:document.id(this.options.trigger);
this.resetCoords();return this;},attach:function(){if(RokGallery.editPanel.isOpen||RokGallery.blocks.editFileSettings.isOpen){return this;}this.options.trigger.addEvents(this.bounds.trigger);
document.body.addEvents(this.bounds.document);this.detached=false;return this;},detach:function(){this.end();this.options.trigger.removeEvents(this.bounds.trigger);
document.body.removeEvents(this.bounds.document);this.detached=true;return this;},add:function(b,c){b.set("placement");b.store("itemClick",this.options.itemClick.bind(this,b));
b.addEvent("click",b.retrieve("itemClick"));this.index.push(b);},remove:function(b){b.eliminate("placement");b.removeClick("click",b.retrieve("itemClick"));
this.index.erase(b);this.elements.erase(b);this.selected.erase(b);this.refresh();},refresh:function(){this.index.each(function(b){b.set("placement");});
},start:function(c){var b=true;document.body.onselectstart=function(d){d=new Event(d).stop();return false;};this.ignores.each(function(d){if(d==c.target||d.getElement(c.target)){b=false;
}},this);if(c.meta||c.shift||c.control||!b){return this;}this.bActive=true;this.setStartCoords(c.page);this.selected.empty();this.fireEvent("start",[this.selected]);
return this;},end:function(b){if(!this.bActive){return false;}this.bActive=false;document.body.onselectstart=function(){};if(this.coords.w<5&&this.coords.h<5){this.index.each(this.checkNodes,this);
}if(this.coords.move.x||this.coords.move.y){this.fireEvent("end",[this.selected]);}this.resetCoords();return true;},move:function(b){if(this.bActive&&this.box.style.display==""){this.setMoveCoords(b.page);
this.index.each(this.checkNodes,this);var c;if(document.selection&&document.selection.empty){document.selection.empty();}else{if(window.getSelection){c=window.getSelection();
if(c&&c.removeAllRanges){c.removeAllRanges();}}}}},selectAll:function(){this.selected.empty();this.index.each(function(b){b.removeClass("selected");b.fireEvent("click",[{shift:true}]);
},this);},setStartCoords:function(b){this.coords.start=b;this.coords.w=0;this.coords.h=0;this.box.setStyles({display:"",top:this.coords.start.y,left:this.coords.start.x,"z-index":-100});
this.box.zindez=0;},setMoveCoords:function(b){if(!this.box.zindez){this.box.setStyle("z-index",100);this.box.zindez=1;}this.coords.move=b;this.coords.w=Math.abs(this.coords.start.x-this.coords.move.x);
this.coords.h=Math.abs(this.coords.start.y-this.coords.move.y);this.coords.top=this.coords.start.y>this.coords.move.y?this.coords.move.y:this.coords.start.y;
this.coords.left=this.coords.start.x>this.coords.move.x?this.coords.move.x:this.coords.start.x;this.coords.end={x:(this.coords.left+this.coords.w),y:(this.coords.top+this.coords.h)};
this.box.style.width=this.coords.w+"px";this.box.style.height=this.coords.h+"px";this.box.style.top=this.coords.top+"px";this.box.style.left=this.coords.left+"px";
},resetCoords:function(){this.coords={start:{x:0,y:0},move:{x:0,y:0},end:{x:0,y:0},w:0,h:0};this.box.setStyles({display:"none",top:0,left:0,width:0,height:0});
},checkNodes:function(b){var c=this.coords;var d=b.get("placement");if(Math.min(c.end.x,d.right)>Math.max(c.left,d.left)&&Math.max(c.top,d.top)<Math.min(c.end.y,d.bottom)){this.options.select(b);
this.selected.include(b);}else{this.options.deselect(b);this.selected.erase(b);}}});})());