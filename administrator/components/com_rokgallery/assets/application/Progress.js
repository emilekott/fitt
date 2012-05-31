/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){this.Progress=new Class({Implements:[Options,Events],options:{color:"#7f7f7f",bg:true,bgColor:"#fff"},initialize:function(c,b){if(Browser.ie8||Browser.ie7){return;
}var a=this.canvas=document.getElement(c);if(!a){return;}this.ctx=a.getContext("2d");this.setOptions(b);this.size={x:a.get("width").toInt(),y:a.get("height").toInt()};
Object.append(this.size,{halfx:this.size.x/2,halfy:this.size.y/2});this.start=0-Math.PI/2;this.end=this.start+((Math.PI*2)*0/100);this.ctx.fillStyle=this.options.color;
if(this.options.bg){this.createBackground();}},createBackground:function(){if(Browser.ie8||Browser.ie7){return;}var b=this.canvas.className.trim().clean();
var c=new Element("canvas."+b+"-bg",{width:this.size.x,height:this.size.y}).inject(this.canvas,"after"),a=c.getContext("2d");a.fillStyle=this.options.bgColor;
a.strokeStyle=this.options.color;a.beginPath();a.arc(this.size.halfx,this.size.halfy,this.size.halfx-1,0,Math.PI*2,true);a.closePath();a.fill();a.stroke();
},set:function(a){if(Browser.ie8||Browser.ie7){return;}a=a.toInt();this.end=this.start+((Math.PI*2)*a/100);this.ctx.clearRect(0,0,this.size.x,this.size.y);
this.ctx.moveTo(this.size.halfx,this.size.halfy);this.ctx.beginPath();this.ctx.arc(this.size.halfx,this.size.halfy,this.size.halfx-1,this.start,this.end,false);
this.ctx.lineTo(this.size.halfx,this.size.halfy);this.ctx.fill();this.ctx.closePath();},reset:function(){this.set(0);}});})());