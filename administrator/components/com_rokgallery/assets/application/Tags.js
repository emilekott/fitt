/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){Event.definePseudo("stop",function(b,c,a){var e=b.value?b.value.replace(/\s/g,"").split(","):false;var d=a[0];if(!b.event.test(/^key.+/)||(e&&e.contains(d.key))){d.stop();
}c.apply(this,a);});this.Tags=new Class({Implements:[Events,Options],options:{input:".tags-add .add-input",container:".tags-list",list:".tags-list .tag",classes:[],layout:'<div class="tag-value"><span class="tag-name">{value}</span></div><div class="tag-erase"><span>x</span></div>',chars:(/^[\w\s-]+$/),onInsert:function(b,a){b.inject(this.container);
},onErase:function(b,a){if(b.length){b.each(this.list.erase.bind(this.list));}else{this.list.erase(b);}b.dispose();}},initialize:function(c,b){this.setOptions(b);
c=this.wrapper=document.getElement(c);if(!c){return this;}this.container=c.getElement(this.options.container);this.list=c.getElements(this.options.list);
this.input=c.getElement(this.options.input).removeEvents();this.bounds={click:this.toggle.bind(this),mouseenter:this.actions.mouseenter.bind(this),focus:this.actions.focus.bind(this),blur:this.actions.blur.bind(this),"click:relay(.tag-erase)":this.actions.deleteClick.bind(this)};
var a=Browser.Engine.trident?"keyup:stop":Browser.Engine.webkit?"keydown:stop":"keypress:stop";this.input.addEvent(a+"(enter)",function(d){if(d.key=="enter"){this.addNew();
}}.bind(this));this.list.forEach(function(d){if(!d.getElement(".tag-erase")){return;}d.set("tabindex",0);this.attachEvents(d);}.bind(this));this.container.addEvent(a+"(backspace, delete, enter, space)",function(d){if(d.key=="backspace"||d.key=="delete"){this.eraseActives();
}else{if(d.key=="enter"||d.key=="space"){this.toggle(d.target);}}}.bind(this));this.fireEvent("init");return this;},attachEvents:function(a){for(var b in this.bounds){a.addEvent(b,this.bounds[b].pass(a));
}},detachEvents:function(a){if(typeOf(a)=="element"){a=new Elements([a]);}a.each(function(b){for(var c in this.bounds){b.removeEvent(c,this.bounds[c].pass(b));
}},this);},actions:{mouseenter:function(a){return this.actions.blur(a);},focus:function(a){a.addClass("focus");if(instanceOf(this,Tags)){this.fireEvent("focus",a);
}},blur:function(a){a.removeClass("focus");if(instanceOf(this,Tags)){this.fireEvent("blur",a);}},deleteClick:function(a){this[a.hasClass("active")?"deactivate":"activate"](a);
this.erase(a);}},addNew:function(){var b=this.input.value.split(","),c=[],a=[];b.forEach(function(d){d=d.replace(/\s{1,},{1,}\s{1,},/g,"").clean().toLowerCase();
if(!this.check(d)&&d.length&&(this.options.chars).test(d)){this.input.get("tween").cancel();c.push(d);}else{a.push(d);}},this);if(c.length){this[c.length>1?"insertMany":"insert"](c);
}if(a.length){this.invalidate(a);}},insert:function(b,c){var a=new Element('div[class="tag"][tabindex=0]');this.options.classes.each(a.addClass.bind(a));
a.set("html",this.options.layout.replace("{value}",b));this.attachEvents(a);this.list.push(a);this.input.value="";return(!c)?this.fireEvent("insert",[a,b]):a;
},insertMany:function(a,c){var b=[];a.each(function(d){b.push(this.insert(d,true));},this);return(!c)?this.fireEvent("insert",[b,a]):a;},invalidate:function(a){return this.fireEvent("invalid",a);
},erase:function(a){this.detachEvents(a);this.fireEvent("erase",[a,this.getValue(a)]);this.list.erase(a);this.fireEvent("afterErase",[a,this.getValue(a)]);
return this;},eraseMany:function(a){this.detachEvents(a);this.fireEvent("erase",[a,this.getValues(a)]);a.each(this.list.erase.bind(this.list));this.fireEvent("afterErase",[a,this.getValues(a)]);
return this;},eraseActives:function(){var a=this.getActives();this[a.length>1?"eraseMany":"erase"](a);},toggle:function(a){return this[a.hasClass("active")?"deactivate":"activate"](a);
},activate:function(a){a.addClass("active");a.focus();this.fireEvent("activate",a);},deactivate:function(a){a.removeClass("active");this.fireEvent("deactivate",a);
},check:function(a){var b=this.getValues();return b.contains(a);},getValue:function(a){return this.list.indexOf(a)!=-1?a.getElement(".tag-name").get("text"):false;
},getValues:function(a){return((a&&a.length)?a:this.list).getElement(".tag-name").get("text");},getActives:function(){return this.list.filter(function(a){return a.hasClass("active")||a.hasClass("focus");
});}});})());