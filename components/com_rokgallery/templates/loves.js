/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){if(typeof this.RokGallery=="undefined"){this.RokGallery={};}var a=this.RokGallery.Loves={actions:{love:"add",unlove:"remove"},scan:function(){var b=document.id(document.body).getElements(".action-love"),c=document.id(document.body).getElements(".action-unlove");
$$(b,c).each(function(e,d){var f=e.hasClass("action-love")?"love":"unlove";e.store("loveID",a.getID(e));e.store("loveAction",f);e.store("loveCount",a.getCount(e));
e.store("loveText",a.getText(e));e.store("loveRequest",a.buildRequest(e,f));e.addEvent("click",a.send.bind(a,e));},this);},getID:function(c){var b=c.getProperty("class"),d=null;
b=b.clean().trim();d=b.match(/id-([0-9]{1,})/i);if(d.length){d=d[1].toInt();}return d;},getCount:function(b){var c=document.body.getElements(".rg-item-loves-counter.id-"+b.retrieve("loveID"));
return c;},getText:function(b){return document.body.getElement(".action-text.id-"+b.retrieve("loveID"));},buildRequest:function(b,c){var d=b.retrieve("loveID");
return new Request.JSON({url:RokGallery.url||"",onRequest:function(){a.onRequest(b);},onSuccess:function(e){a.onSuccess(b,e);},data:{model:"loves",action:a.actions[c],params:JSON.encode({id:d})}});
},validate:function(b){b=b.replace(/\\(?:["\\\/bfnrt]|u[0-9a-fA-F]{4})/g,"@").replace(/"[^"\\\n\r]*"|true|false|null|-?\d+(?:\.\d*)?(?:[eE][+\-]?\d+)?/g,"]").replace(/(?:^|:|,)(?:\s*\[)+/g,"");
return(/^[\],:{}\s]*$/).test(b);},send:function(b){var c=b.retrieve("loveRequest"),e=b.retrieve("loveID"),d=b.retrieve("loveAction");if(c.running){return;
}c.send({data:{model:"loves",action:a.actions[d],params:JSON.encode({id:e})}});},onRequest:function(b){b.addClass("loading");},onSuccess:function(d,b){d.removeClass("loading");
var c=(typeof a=="string")?a.validate(b):true;if(!c){throw new Error("Invlid JSON response.");}else{if(b.status=="error"){throw new Error(b.message);return;
}var e=b.payload.loves,g=b.payload.text,f=b.payload.new_action;d.retrieve("loveCount").set("html",e);d.removeClass("action-love").removeClass("action-unlove");
d.addClass("action-"+f).store("loveAction",f);if(d.retrieve("loveText")){d.retrieve("loveText").set("html",g);}}}};window.addEvent("domready",a.scan);})());
