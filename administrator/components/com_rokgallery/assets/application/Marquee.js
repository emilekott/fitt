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
((function(){this.Marquee=new Class({Implements:[Options,Events],active:false,options:{autoHide:true,cropMode:false,globalTrigger:false,min:false,max:false,ratio:false,contain:false,trigger:null,border:"#999",color:"#7389AE",opacity:0.3,zindex:10000},binds:{},initialize:function(b){this.setOptions(b);
this.box=new Element("div",{styles:{display:"none",position:"absolute","z-index":this.options.zindex}}).inject((this.container)?this.container:document.body);
this.overlay=new Element("div",{styles:{position:"relative",background:"url("+RokGallerySettings.images+"blank.gif)",height:"100%",width:"100%","z-index":this.options.zindex+1}}).inject(this.box);
this.mask=new Element("div",{styles:{position:"absolute","background-color":this.options.color,opacity:this.options.opacity,height:"100%",width:"100%","z-index":this.options.zindex-1}});
if(this.options.cropMode){this.mask.setStyle("z-index",this.options.zindex-2).inject(this.container);this.options.trigger=this.mask;}else{this.mask.inject(this.overlay);
}this.trigger=document.id(this.options.trigger);var c={position:"absolute",width:1,height:1,overflow:"hidden","z-index":this.options.zindex+1};if(this.options.border.test(/\.(jpe?g|gif|png)/)){c.backgroundImage="url("+this.options.border+")";
}else{var a="1px dashed "+this.options.border;}this.marchingAnts={};["left","right","top","bottom"].each(function(e,d){switch(e){case"left":style=Object.merge(Object.clone(c),{top:0,left:-1,height:"100%"});
break;case"right":style=Object.merge(Object.clone(c),{top:0,right:-1,height:"100%"});break;case"top":style=Object.merge(Object.clone(c),{top:-1,left:0,width:"100%"});
break;case"bottom":style=Object.merge(Object.clone(c),{bottom:-1,left:0,width:"100%"});break;}if(a){style["border-"+e]=a;}this.marchingAnts[e]=new Element("div",{styles:style}).inject(this.overlay);
},this);this.binds.start=this.start.bind(this);this.binds.move=this.move.bind(this);this.binds.end=this.end.bind(this);document.body.onselectstart=function(d){new Event(d).stop();
return false;};this.removeDOMSelection=(document.selection&&document.selection.empty)?function(){document.selection.empty();}:(window.getSelection)?function(){var d=window.getSelection();
if(d&&d.removeAllRanges){d.removeAllRanges();}}:Function.from(false);this.resetCoords();},attach:function(){this.trigger.addEvent("mousedown",this.binds.start);
this.detached=false;},detach:function(){if(this.active){this.end();}this.trigger.removeEvent("mousedown",this.binds.start);this.detached=true;},start:function(a){if((!this.options.autoHide&&a.target==this.box)||(!this.options.globalTrigger&&(this.trigger!=a.target))){return false;
}this.active=true;document.addEvents({mousemove:this.binds.move,mouseup:this.binds.end});this.resetCoords();if(this.options.contain){this.getContainCoords();
}if(this.container){this.getRelativeOffset();}this.setStartCoords(a.page);if(this.scroller){this.scroller.start().attach();}this.fireEvent("start");return true;
},move:function(h){if(!this.active){return false;}this.removeDOMSelection();var d=this.coords.start,a=h.page,g=this.coords.box={},j=this.coords.container;
if(this.container){a.y-=this.offset.top;a.x-=this.offset.left;}var i=this.flip={y:(d.y>a.y),x:(d.x>a.x)};g.y=(i.y)?[a.y,d.y]:[d.y,a.y];g.x=(i.x)?[a.x,d.x]:[d.x,a.x];
if(this.options.contain){if(g.y[0]<j.y[0]){g.y[0]=j.y[0];}if(g.y[1]>j.y[1]){g.y[1]=j.y[1];}if(g.x[0]<j.x[0]){g.x[0]=j.x[0];}if(g.x[1]>j.x[1]){g.x[1]=j.x[1];
}}if(this.options.max){if(g.x[1]-g.x[0]>this.options.max[0]){if(i.x){g.x[0]=g.x[1]-this.options.max[0];}else{g.x[1]=g.x[0]+this.options.max[0];}}if(g.y[1]-g.y[0]>this.options.max[1]){if(i.y){g.y[0]=g.y[1]-this.options.max[1];
}else{g.y[1]=g.y[0]+this.options.max[1];}}}if(this.options.ratio){var b=this.options.ratio;var e={x:(g.x[1]-g.x[0])/b[0],y:(g.y[1]-g.y[0])/b[1]};if(e.x>e.y){if(i.x){g.x[0]=g.x[1]-(e.y*b[0]);
}else{g.x[1]=g.x[0]+(e.y*b[0]);}}else{if(e.x<e.y){if(i.y){g.y[0]=g.y[1]-(e.x*b[1]);}else{g.y[1]=g.y[0]+(e.x*b[1]);}}}}this.refresh();return true;},refresh:function(a){var e=this.coords,b=this.coords.box,d=this.coords.container;
e.w=b.x[1]-b.x[0];e.h=b.y[1]-b.y[0];e.top=b.y[0];e.left=b.x[0];this.box.setStyles({display:"block",top:e.top,left:e.left,width:e.w,height:e.h});if(!a){this.fireEvent("resize",this.getRelativeCoords());
}},end:function(b){if(!this.active){return false;}this.active=false;document.removeEvents({mousemove:this.binds.move,mouseup:this.binds.end});if(this.options.autoHide){this.resetCoords();
}else{if(this.options.min){if(this.coords.w<this.options.min[0]||this.coords.h<this.options.min[1]){this.resetCoords();}}}var a=(this.options.autoHide)?null:this.getRelativeCoords();
if(this.scroller){this.scroller.stop();}this.fireEvent("complete",a);return true;},setStartCoords:function(a){if(this.container){a.y-=this.offset.top;a.x-=this.offset.left;
}this.coords.start=a;this.coords.w=0;this.coords.h=0;this.box.setStyles({display:"block",top:this.coords.start.y,left:this.coords.start.x});},resetCoords:function(){this.coords={start:{x:0,y:0},move:{x:0,y:0},end:{x:0,y:0},w:0,h:0};
this.box.setStyles({display:"none",top:0,left:0,width:0,height:0});this.getContainCoords();},getRelativeCoords:function(){var a=this.coords.box,d=Object.merge(Object.clone(this.coords.container)),b=this.coords;
if(!this.options.contain){d={x:[0,0],y:[0,0]};}return{x:((a.x[0]-d.x[0]).toInt()).limit(0,a.x[0]-d.x[0]),y:((a.y[0]-d.y[0]).toInt()).limit(0,a.y[0]-d.y[0]),w:(b.w).toInt(),h:(b.h).toInt()};
},getContainCoords:function(){var a=this.trigger.getCoordinates(this.container);this.coords.container={y:[a.top,a.top+a.height],x:[a.left,a.left+a.width]};
},getRelativeOffset:function(){this.offset=this.container.getCoordinates();},reset:function(){this.detach();}});})());