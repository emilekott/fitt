/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){this.Tags.Ajax=new Class({Extends:Tags,options:{classes:["dark"],url:"",data:{id:0},onInsert:function(b,a){var c=Object.clone(this.options.data.insert),d={params:JSON.encode({id:this.options.data.id,tags:Array.from(a)})};
c={data:Object.merge(c,d)};this.insertBind=this.insertSuccess.bind(this,b,a);this.fireEvent("beforeInsert");this.ajax.addEvent("onSuccess:once",this.insertBind);
this.ajax.send(c);},onErase:function(b,a){var c=Object.clone(this.options.data.erase),d={params:JSON.encode({id:this.options.data.id,tags:Array.from(a)})};
c={data:Object.merge(c,d)};this.eraseBind=this.eraseSuccess.bind(this,b,a);this.fireEvent("beforeErase");b.addClass("loader");this.ajax.addEvent("onSuccess:once",this.eraseBind);
this.ajax.send(c);},onInvalid:function(c){if(!c.length){return;}var a=this.input.retrieve("color")||this.input.getStyle("color"),b="#eb9191";this.input.set("tween",{link:"chain",duration:150,transition:"sine"});
this.input.tween("color",b).tween("color",a).tween("color",b).tween("color",a).tween("color",b).tween("color",a);},onFocus:function(a){this.scrollbar.update();
}},initialize:function(b,a){this.parent(b,a);var c=this.wrapper.getElements(".add-tag");if(c.length){c.setStyle("tabindex","-1").removeEvents().addEvent("click:stop",this.addNew.bind(this));
}this.input.store("color",this.input.getStyle("color"));var d=this.wrapper.getElement(".tags-list");this.scrollbar=new Scrollbar(d,{fixed:true});this.ajax=new Request({url:this.options.url,method:"post",link:"ignore"});
},insert:function(a,b){if(this.ajax.isRunning()){return this;}return this.parent(a,b);},insertMany:function(a,b){if(this.ajax.isRunning()){return this;
}return this.parent(a,b);},erase:function(a){if(this.ajax.isRunning()){return this;}return this.parent(a);},eraseMany:function(a){if(this.ajax.isRunning()){return this;
}return this.parent(a);},insertSuccess:function(c,b,a){this.ajax.removeEvent("onSuccess:once",this.insertBind);if(!JSON.validate(a)){return this.popup({title:"Add Tag - Invalid Response",message:'<p class="error-intro">The response from the server had an invalid JSON string while adding Tags. Following is the reply.</p>'+a});
}a=JSON.decode(a);if(a.status!="success"){return this.popup({title:"Add Tag - Error",message:a.message});}if(this.list.length){this.container.getElement(".oops").setStyle("display","none");
}c=new Elements(c.length?c:[c]);c.set("tween",{duration:"short"});c.inject(this.container).setStyle("opacity",0).fade("in");this.scrollbar.update().toBottom();
this.fireEvent("afterInsertSuccess",[a,c,b]);return this;},eraseSuccess:function(c,b,a){this.ajax.removeEvent("onSuccess:once",this.eraseBind);if(!JSON.validate(a)){return this.popup({title:"Remove Tag - Invalid Response",message:'<p class="error-intro">The response from the server had an invalid JSON string while removing Tags. Following is the reply.</p>'+a});
}a=JSON.decode(a);if(a.status!="success"){return this.popup({title:"Remove Tag - Error",message:a.message});}c=new Elements(c.length?c:[c]);c.set("tween",{duration:"short",onComplete:this.disposeTags.bind(this,c)});
c.retrieve("tween").each(function(d){d.start("opacity",0);});this.fireEvent("afterEraseSuccess",[a,c,b]);return this;},disposeTags:function(a){a.dispose();
if(!this.list.length){this.container.getElement(".oops").setStyle("display","block");}this.scrollbar.update();},popup:function(a){var b={type:"warning",title:"Error",message:"",buttons:{ok:{show:false},cancel:{show:true,label:"close"}}};
window.Popup.setPopup(Object.merge(b,a)).open();}});})());