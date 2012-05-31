/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){this.Tags.Slice=new Class({Extends:Tags,options:{classes:["dark"],url:"",data:{id:0},onEmptyList:function(){this.container.getElement(".oops").setStyle("display","block");
},onNonEmptyList:function(){this.container.getElement(".oops").setStyle("display","none");},onInsert:function(b,a){if(this.list.length&&!this.container.getElements(".tag").length){this.container.getElement(".oops").setStyle("display","none");
}b=new Elements(b.length?b:[b]);b.set("tween",{duration:"short"});b.inject(this.container).setStyle("opacity",0).fade("in");this.scrollbar.update().toBottom();
return this;},onErase:function(b,a){b=new Elements(b.length?b:[b]);b.set("tween",{duration:"short",onComplete:this.disposeTags.bind(this,b)});b.retrieve("tween").each(function(c){c.start("opacity",0);
});return this;},onInvalid:function(c){if(!c.length){return;}var a=this.input.retrieve("color")||this.input.getStyle("color"),b="#eb9191";this.input.set("tween",{link:"chain",duration:150,transition:"sine"});
this.input.tween("color",b).tween("color",a).tween("color",b).tween("color",a).tween("color",b).tween("color",a);},onFocus:function(a){this.scrollbar.update();
}},initialize:function(b,a){this.parent(b,a);var c=this.wrapper.getElements(".add-tag");if(c.length){c.setStyle("tabindex","-1").removeEvents().addEvent("click:stop",this.addNew.bind(this));
}this.input.store("color",this.input.getStyle("color"));var d=this.wrapper.getElement(".tags-list");if(this.wrapper.getElement(".gutter")){d.inject(d.getParent(),"before");
d.getNext().empty().dispose();}this.scrollbar=new Scrollbar(d,{fixed:true});},check:function(a){var b=this.getValues();return b.contains(a)||RokGallery.tags.getValues().contains(a);
},disposeTags:function(a){a.dispose();if(!this.list.length&&!this.container.getElements(".tag").length){this.container.getElement(".oops").setStyle("display","block");
}this.scrollbar.update();}});})());