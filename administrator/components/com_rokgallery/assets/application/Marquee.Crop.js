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
((function(){this.Marquee.Crop=new Class({Extends:Marquee,options:{autoHide:false,cropMode:true,contain:true,handleSize:8,preset:false,noHandlers:false,handleStyle:{border:"1px solid #000","background-color":"#ccc",opacity:0.75}},initialize:function(a,b){this.img=document.id(a);
if(this.img.get("tag")!="img"){return false;}var c=this.img.getCoordinates();this.container=new Element("div",{styles:{position:"relative",margin:"0 auto",width:c.width,height:c.height}}).inject(this.img,"after");
this.img.setStyle("display","none");b.p=this.container;this.imgClip=new Element("img",{src:this.img.get("src"),styles:{position:"absolute",top:0,left:0,width:c.width,height:c.height,padding:0,margin:0,"z-index":1}}).inject(this.container);
this.crop=new Element("img",{src:this.img.get("src"),styles:{position:"absolute",top:0,left:0,width:c.width,height:c.height,padding:0,margin:0,"z-index":this.options.zindex-1}}).inject(this.container);
this.parent(b);this.binds.handleMove=this.handleMove.bind(this);this.binds.handleEnd=this.handleEnd.bind(this);this.binds.handles={};this.handles={};this.handlesGrid={NW:[0,0],N:[0,1],NE:[0,2],W:[1,0],E:[1,2],SW:[2,0],S:[2,1],SE:[2,2]};
["NW","N","NE","W","E","SW","S","SE"].each(function(e){var d=this.handlesGrid[e];this.binds.handles[e]=this.handleStart.bind(this,e,d[0],d[1]);this.handles[e]=new Element("div",{styles:Object.merge({position:"absolute",display:"block",visibility:"hidden",width:this.options.handleSize,height:this.options.handleSize,overflow:"hidden",cursor:(e.toLowerCase()+"-resize"),"z-index":this.options.zindex+2},this.options.handleStyle)}).inject(this.box,"bottom");
},this);this.binds.drag=this.handleStart.bind(this,"DRAG",1,1);this.attach();this.setDefault();this.fireEvent("init");return this;},attach:function(){this.trigger.addEvent("mousedown",this.binds.start);
this.overlay.addEvent("mousedown",this.binds.drag);["NW","N","NE","W","E","SW","S","SE"].each(function(a){this.handles[a].addEvent("mousedown",this.binds.handles[a]);
},this);this.detached=false;},detach:function(){if(this.active){this.handleEnd();}this.trigger.removeEvent("mousedown",this.binds.start);this.overlay.removeEvent("mousedown",this.binds.drag);
["NW","N","NE","W","E","SW","S","SE"].each(function(a){this.handles[a].removeEvent("mousedown",this.binds.handles[a]);},this);this.detached=true;},setDefault:function(){if(!this.options.preset){return this.resetCoords();
}this.getContainCoords();this.getRelativeOffset();var b=this.coords.container,a=this.options.preset;this.coords.start={x:a[0],y:a[1]};this.active=true;
this.move({page:{x:a[2]+this.offset.left,y:a[3]+this.offset.top}});this.active=false;return this;},setCoords:function(a){a.each(function(d,c){a[c]=a[c].toInt();
});this.getContainCoords();this.getRelativeOffset();var b=this.coords.container;this.coords.start={x:a[0],y:a[1]};this.active=true;this.move({page:{x:a[2]+a[0]+this.offset.left,y:a[3]+a[1]+this.offset.top}});
this.active=false;return this;},handleStart:function(c,d,a,b){this.getContainCoords();this.getRelativeOffset();this.currentHandle={handle:c,row:d,col:a};
document.addEvents({mousemove:this.binds.handleMove,mouseup:this.binds.handleEnd});b.page.y-=this.offset.top;b.page.x-=this.offset.left;this.coords.hs={s:b.page,b:Object.merge(Object.clone(this.coords.box))};
this.active=true;if(this.scroller){this.scroller.start().attach();}},handleMove:function(a){var e=this.coords.box,g=this.coords.container,d=a.page,j=this.currentHandle,k=this.coords.start;
d.y-=this.offset.top;d.x-=this.offset.left;if(j.handle=="DRAG"){var i=this.coords.hs,f=d.x-i.s.x,b=d.y-i.s.y,h;e.y[0]=i.b.y[0]+b;e.y[1]=i.b.y[1]+b;e.x[0]=i.b.x[0]+f;
e.x[1]=i.b.x[1]+f;if((h=e.y[0]-g.y[0])<0){e.y[0]-=h;e.y[1]-=h;}if((h=e.y[1]-g.y[1])>0){e.y[0]-=h;e.y[1]-=h;}if((h=e.x[0]-g.x[0])<0){e.x[0]-=h;e.x[1]-=h;
}if((h=e.x[1]-g.x[1])>0){e.x[0]-=h;e.x[1]-=h;}this.removeDOMSelection();return this.refresh();}if(j.row==0&&e.y[1]<d.y){j.row=2;}if(j.row==2&&e.y[0]>d.y){j.row=0;
}if(j.col==0&&e.x[1]<d.x){j.col=2;}if(j.col==2&&e.x[0]>d.x){j.col=0;}if(j.row==0||j.row==2){k.y=(j.row)?e.y[0]:e.y[1];if(j.col==0){k.x=e.x[1];}if(j.col==1){k.x=e.x[0];
d.x=e.x[1];}if(j.col==2){k.x=e.x[0];}}if(!this.options.ratio){if(j.row==1){if(j.col==0){k.y=e.y[0];d.y=e.y[1];k.x=e.x[1];}else{if(j.col==2){k.y=e.y[0];
d.y=e.y[1];k.x=e.x[0];}}}}d.y+=this.offset.top;d.x+=this.offset.left;this.move(a);return this;},handleEnd:function(a){document.removeEvents({mousemove:this.binds.handleMove,mouseup:this.binds.handleEnd});
this.active=false;this.currentHandle=false;if(this.options.min&&(this.coords.w<this.options.min[0]||this.coords.h<this.options.min[1])){if(this.options.preset){this.setDefault();
}else{this.resetCoords();}}if(this.scroller){this.scroller.stop();}},end:function(a){if(!this.parent(a)){return false;}if(this.options.min&&(this.coords.w<this.options.min[0]||this.coords.h<this.options.min[1])){this.setDefault();
}if(this.scroller){this.scroller.stop();}return this;},resetCoords:function(){this.parent();this.coords.box={x:[0,0],y:[0,0]};this.hideHandlers();this.crop.setStyle("clip","rect(0px 0px 0px 0px)");
},showHandlers:function(){var c=this.coords.box;if(this.options.min&&(this.coords.w<this.options.min[0]||this.coords.h<this.options.min[1])){this.hideHandlers();
}else{var h=[],b=[],f=(this.options.handleSize/2)+1;for(var g=0,i=2;g<=i;g++){h[g]=((g==0)?0:((g==2)?c.y[1]-c.y[0]:(c.y[1]-c.y[0])/2))-f;b[g]=((g==0)?0:((g==2)?c.x[1]-c.x[0]:(c.x[1]-c.x[0])/2))-f;
}for(var e in this.handlesGrid){var a=this.handlesGrid[e],d=this.handles[e];if(!this.options.ratio||(a[0]!=1&&a[1]!=1)){if(this.options.min&&this.options.max){if((this.options.min[0]==this.options.max[0])&&(a[1]%2)==0){continue;
}if(this.options.min[1]==this.options.max[1]&&(a[0]%2)==0){continue;}}d.setStyles({visibility:"visible",top:h[a[0]],left:b[a[1]]});}}}},hideHandlers:function(){for(handle in this.handles){this.handles[handle].setStyle("visibility","hidden");
}},refresh:function(a){this.parent(a);var b=this.coords.box,c=this.coords.container;if(Browser.Engine.trident&&Browser.Engine.version<5&&this.currentHandle&&this.currentHandle.col===1){this.overlay.setStyle("width","100.1%").setStyle("width","100%");
}this.crop.setStyle("clip","rect("+(b.y[0])+"px "+(b.x[1])+"px "+(b.y[1])+"px "+(b.x[0])+"px )");if(!this.options.noHandlers){this.showHandlers();}},refreshClip:function(c,a,b){var d={width:c,height:a};
if(!c||!a){d={width:this.img.get("width"),height:this.img.get("height")};}$$([this.imgClip,this.crop]).set("width",d.width).set("height",d.height);$$([this.container,this.imgClip,this.crop,this.crop.getParent()]).setStyles({width:d.width,height:d.height});
this.refresh(b);}});})());