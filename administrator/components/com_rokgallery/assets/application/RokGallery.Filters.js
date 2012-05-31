/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){String.implement({toBool:function(){return(/^true$/i).test(this);}});this.RokGallery.Filters=new Class({Implements:[Events,Options],options:{input:".filtering #filter-query",lists:".filtering .filters-list",apply:".filtering .filter-submit",sort:".filtering .filter-sort",container:".filters-generated .filters-wrapper",defaults:["tags","contains"],layout:'<span class="filter-query">{query}</span><span class="filter-types">{type}</span><span class="filter-operator">{operator}</span><span class="remove">x</span>'},initialize:function(a){this.setOptions(a);
this.input=document.getElement(this.options.input);this.lists=document.getElements(this.options.lists);this.apply=document.getElement(this.options.apply);
this.sort=document.getElement(this.options.sort);this.container=document.getElement(this.options.container);this.options.defaults=Array.combine(this.options.defaults,RokGallerySettings.order);
this.selected={};this.order={};this.listsKeys={};this.itemsKeys={};this.filters=[];this.bounds={apply:this.addFilter.bind(this),sort:this.sortFilters.bind(this)};
this.lists.each(this.attachList.bind(this));this.attach();this.options.defaults.each(function(c){var b=this.lists.getElement("li[data-key="+c+"]").clean();
if(b&&b[0]){b[0].fireEvent("click");}},this);},attach:function(){this.lists.each(function(c){this.listsKeys[c.get("data-key")]=c;c.removeClass("disabled");
var b=c.getElements("li");b.each(function(d){d.addEvent("click",this.itemClick.bind(this,d));},this);},this);var a=Browser.Engine.trident?"keyup:stop":Browser.Engine.webkit?"keydown:stop":"keypress:stop";
this.input.addEvent(a+"(enter)",function(b){if(b.key=="enter"){this.addFilter();}}.bind(this));this.input.removeClass("disabled").removeProperty("disabled");
this.apply.addEvent("click",this.bounds.apply);this.apply.setStyle("display","block");this.sort.addEvent("click",this.bounds.sort);this.sort.setStyle("display","block");
this.detached=false;},detach:function(){this.lists.each(function(b){b.addClass("disabled");var a=b.getElements("li");a.each(function(c){c.removeEvents("click");
},this);},this);this.input.removeEvents().addClass("disabled").setProperty("disabled","disabled").blur();this.apply.removeEvent("click",this.bounds.apply);
this.apply.setStyle("display","none");this.sort.removeEvent("click",this.bounds.sort);this.sort.setStyle("display","none");this.detached=true;},addFilter:function(){var a=Object.clone(this.selected),h=this,c;
if(this.input.getStyle("display")!="none"){a=Object.merge(a,{query:this.input.get("value")});}var b=Object.some(a,function(k,j){return k.length;});var f=this.checkExistance(a);
if(!b||!a.type||!a.operator||(typeOf(a.query)!="null"&&!a.query.length)){return;}if(f){return;}c=Object.clone(a);var i=new Element("div.filter-tag").setStyles({visibility:"hidden",opacity:0}),d=this.options.layout;
if(c.type=="gallery"){var e=this.listsKeys[c.type],g=e.getElement(".selected");if(!g){return;}c.query=g.get("text");}this.filters.push(a);d=d.substitute(c);
i.set("html",d);if(!c.query){i.getElement(".filter-query").setStyle("display","none");}i.getElement(".remove").addEvent("click",function(){if(h.detached){return;
}i.fade("out").get("tween").chain(function(){h.filters.erase(a);i.destroy();h.resetPage();if(!h.filters.length){h.container.getParent(".filters-generated").addClass("empty");
}});});if(this.filters.length){h.container.getParent(".filters-generated").removeClass("empty");}i.inject(this.container).fade("in");this.input.set("value","");
this.resetPage();},sortFilters:function(){var b=this.order.order_by,a=this.order.order_direction;this.resetPage();},checkExistance:function(a){var b=false;
this.filters.each(function(e){var d=[],c;Object.each(e,function(g,f){if(typeof a[f]!="undefined"){if(!a[f].length&&!e[f].length){d.push(true);}else{if(a[f]==e[f]){d.push(true);
}else{d.push(false);}}}else{d.push(false);}});c=true*d.every(Math.floor);b=b+c;});return b;},resetPage:function(){RokGallery.loadMore.attach();RokGallery.loadMore.element.setStyle("display","block").fade("in");
document.getElements("#gallery-list .gallery-row").dispose();RokGallery.loadMore.setPageData({page:1,items_per_page:12});RokGallery.loadMore.request.cancel();
RokGallery.loadMore.load();},attachList:function(b){var c=b.getElement("> .title"),d=b.getElement(".filters-dropdown"),a=b.getElements("li");if(!c.retrieve("original-value")){c.store("original-value",c.get("text"));
}b.removeClass("disabled");if(b.get("data-key")=="order_by"||b.get("data-key")=="order_direction"){this.order[b.get("data-key")]="";}else{this.selected[b.get("data-key")]="";
}a.forEach(function(h){var f=h.get("data-key");if(!f||!f.length||this.itemsKeys[f]){a.erase(h);return;}var i=(h.get("data-ignores")||"").clean(),g=(h.get("data-ignore-list")||"").clean(),e=h.get("data-input")||"ignore";
this.itemsKeys[f]=h;h.store("list",{list:b,title:c,dropdown:d,items:a});h.store("ignores",i.length?i.split(","):[]);h.store("ignore-list",g.length?g.split(","):[]);
h.store("show_input",e);},this);},itemClick:function(a){if(a.hasClass("disabled")){return;}var b=a.retrieve("list"),c=b.list.get("data-key");a.addClass("selected");
b.title.set("text",a.get("text"));b.items.filter(function(d){return d!=a;}.bind(this)).removeClass("selected");if(c=="type"){this.enableItems();this.enableLists();
this.disableItems(a.retrieve("ignores"));this.disableLists(a.retrieve("ignore-list"));}if(c=="order_by"||c=="order_direction"){this.order[c]=a.get("data-key").replace(/^order-/,"");
}else{this.selected[c=="gallery"?"query":c]=a.get("data-key");}if(a.retrieve("show_input")=="true"){this.input.setStyle("display","block").removeProperty("disabled");
}if(a.retrieve("show_input")=="false"){this.input.setStyle("display","none").setProperty("disabled","disabled");}},enableItems:function(a){(!a?Object:Array)["forEach"]((!a?this.itemsKeys:a),function(c,b){element=(!a?c:this.itemsKeys[c]);
element.removeClass("disabled");}.bind(this));return this;},enableLists:function(a){(!a?Object:Array)["forEach"]((!a?this.listsKeys:a),function(c,b){element=(!a?c:this.listsKeys[c]);
element.removeClass("disabled").setStyle("display","block");}.bind(this));return this;},disableItems:function(a){(!a?Object:Array)["forEach"]((!a?this.itemsKeys:a),function(d,c){var b=(!a?d:this.itemsKeys[d]);
if(b.hasClass("selected")){this.unselectList(b.retrieve("list").list);}b.addClass("disabled");}.bind(this));return this;},disableLists:function(a){(!a?Object:Array)["forEach"]((!a?this.listsKeys:a),function(d,c){var b=(!a?d:this.listsKeys[d]);
if(b.hasClass("selected")){this.unselectList(b.retrieve("list").list);}b.addClass("disabled").setStyle("display","none");}.bind(this));return this;},unselectList:function(b){var a=b.get("data-key"),c=b.getElement("> .title");
if(a=="order_by"||a=="order_direction"){this.order[a]="";}else{this.selected[a]="";}b.getElements("li").removeClass("selected");c.set("text",c.retrieve("original-value"));
return this;},refreshLists:function(){var a=0;this.lists.each(function(b){a+=b.getSize().x-2;},this);this.lists.getElement(".filters-dropdown").setStyle("width",a);
}});})());