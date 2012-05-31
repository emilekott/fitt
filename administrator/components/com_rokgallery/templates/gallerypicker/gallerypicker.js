/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){this.GalleryPicker=new Class({Implements:[Options,Events],options:{url:""},initialize:function(b,a){this.setOptions(a);this.element=document.id(b)||document.getElement(b)||null;
this.type="";this.activeTab="filelist";this.inputs={file_id:this.element.getElement("input[type=hidden][name=file_id]"),gallery_id:this.element.getElement("input[type=hidden][name=gallery_id]")};
this.back=this.element.getElement(".back-button").setStyle("display","none");this.instructions=this.element.getElement(".instructions").setStyle("display","none");
this.ajax=new Request.HTML({url:RokGallerySettings.modal_url,evalScripts:false,onRequest:this.clickRequest.bind(this),onSuccess:this.clickSuccess.bind(this)});
this.tabs=this.element.getElements("#gallerypicker-tabs li");this.panels=new Elements(this.tabs.get("data-panel").map(function(c,d){return this.element.getElement(".panel."+c);
},this));this.loader=new GalleryPickerMainPage(document.id("load-more"),{url:RokGallerySettings.url,pageData:{page:RokGallerySettings.next_page,items_per_page:RokGallerySettings.items_per_page,filters:[],composite:{context:"com_rokgallery.gallerypicker",layout:"default_file"}}});
this.loader.addEvent("getPage",this.refreshLinks.bind(this));this.loader.element.addEvent("click",function(){this.type="files";}.bind(this));this.files=this.element.getElements("li.file");
this.galleries=this.element.getElements("li.gallery");this.slices=this.element.getElements("li.slice");this.menuitems=this.element.getElements("a.menu-item");
this.attachLists();this.attachTabs();this.attachBack();},attachLists:function(){this.files.each(this.attachFile.bind(this));this.galleries.each(this.attachGallery.bind(this));
this.slices.each(this.attachSlice.bind(this));this.menuitems.each(this.attachMenuItem.bind(this));},attachFile:function(a){var b;a.addEvent("click",function(){if(a.hasClass("total-slices-0")){return false;
}this.type="files";b=a.get("data-id").replace("file-","");this.inputs.file_id.set("value",b);this.ajax.send({data:{file_id:b}});}.bind(this));},attachGallery:function(a){var b;
a.addEvent("click",function(){if(a.hasClass("total-slices-0")){return false;}this.type="gallery";b=a.get("data-id").replace("gallery-","");this.inputs.gallery_id.set("value",b);
this.ajax.send({data:{gallery_id:b}});}.bind(this));},attachSlice:function(d){var b=d.getElement(".jinsert_action"),c=d.getElements("select"),a="";d.addEvent("click",function(){this.type="slice";
}.bind(this));b.addEvent("click",function(){if(c.length){a=this.buildString(c);}else{a=b.get("data-display")||"";}var e={width:b.get("data-width"),height:b.get("data-height")},f=b.get("data-minithumb");
if(RokGallerySettings.textarea){window.parent.jInsertEditorText(a,RokGallerySettings.textarea);window.parent.SqueezeBox.close();}else{if(RokGallerySettings.inputfield){if(typeof window.parent.GalleryPickerInsertText!="undefined"){window.parent.GalleryPickerInsertText(RokGallerySettings.inputfield,a,e,f);
}else{window.parent.document.getElementById(RokGallerySettings.inputfield).value=a;}window.parent.SqueezeBox.close();}}}.bind(this));},attachMenuItem:function(c){var a="",b={open:c.get("data-opentag")||"",close:c.get("data-closetag")||"",display:c.get("data-display")||""};
c.addEvent("click",function(d){d.stop();a=this.buildMenuItemString(b);if(RokGallerySettings.textarea){window.parent.jInsertEditorText(a,RokGallerySettings.textarea);
window.parent.SqueezeBox.close();}else{if(RokGallerySettings.inputfield){if(typeof window.parent.GalleryPickerInsertText!="undefined"){window.parent.GalleryPickerInsertText(RokGallerySettings.inputfield,a);
}else{window.parent.document.getElementById(RokGallerySettings.inputfield).value=a;}window.parent.SqueezeBox.close();}}}.bind(this));},buildMenuItemString:function(b){var a=b.open+b.display+b.close;
return a.replace(/\'/g,'"');},buildString:function(b){var a="",c="";b.each(function(d){var e={open:d.getSelected().get("data-opentag")||"",close:d.getSelected().get("data-closetag")||"",display:d.getSelected().get("data-display")||""};
if(!c.length&&e.close.length){c=e.close;}a+=e.open+e.display;});a+=c;return a.replace(/\'/g,'"');},attachTabs:function(){this.tabs.each(function(b,a){b.addEvent("click",function(){var c=this.panels[a];
this.activeTab=b.get("data-panel");this.type=b.get("data-type");this.tabs.removeClass("active");this.panels.setStyle("display","none");b.addClass("active");
c.setStyle("display","block");if(c.getElement(".slice")){$$(this.back,this.instructions).setStyle("display","block");}else{$$(this.back,this.instructions).setStyle("display","none");
}}.bind(this));},this);},attachBack:function(){this.back.addEvent("click",function(){var b=this.activeTab.replace("list","_id"),a={};this.inputs[b].set("value",0);
a[b]=0;a.page=this.loader.currentPage.page;this.type=this.getActiveTab();$$(this.back,this.instructions).setStyle("display","none");this.ajax.send({data:a});
}.bind(this));},clickRequest:function(){var b=(this.type=="slice")?this.getActiveTab():this.type,c="gallerypicker-"+b+"list",a=document.id(c);if(this.type=="files"){this.loader.element.setStyle("display","none");
}a.empty().getParent(".panel").addClass("loader");},clickSuccess:function(a,i,f){var h=(this.type=="slice")?this.getActiveTab():this.type,d=new Element("div",{styles:{display:"none"}}),g="gallerypicker-"+h+"list",b=document.id(g),c;
b.getParent(".panel").removeClass("loader");d.inject(document.body,"top").set("html",f);c=d.getElement("#"+g).getChildren();b.adopt(c);d.dispose();if(b.getElement(".slice")){$$(this.back,this.instructions).setStyle("display","block");
this.type="slice";}else{this.type=this.getActiveTab();$$(this.back,this.instructions).setStyle("display","none");}this.refreshLinks();if(this.type=="files"){if(b.getElement(".slice")){this.loader.element.setStyle("display","none");
}else{var e=this.loader.element.retrieve("display");this.loader[e=="none"?"hideElement":"showElement"]();}}},getActiveTab:function(){return document.id("gallerypicker-tabs").getElement(".active").get("data-type");
},refreshLinks:function(){var c=(this.type=="slice")?this.getActiveTab():this.type,d="gallerypicker-"+c+"list",a=document.id(d);if(!a){return;}var b=a.getChildren();
switch(this.type){case"slice":b.removeEvents("click").each(this.attachSlice.bind(this));break;case"gallery":b.removeEvents("click").each(this.attachGallery.bind(this));
break;case"files":default:b.removeEvents("click").each(this.attachFile.bind(this));}}});this.GalleryPickerMainPage=new Class({Extends:MainPage,options:{onKeyDown:function(b,a){a.addClass("load-all").getElement("span.text").set("html","load all");
},onKeyUp:function(b,a){a.removeClass("load-all").getElement("span.text").set("html","load more");},onGetPage:function(b){this.element.removeClass("loader");
var f=b.payload;if(!f.more_pages){this.detach();this.element.fade("out").get("tween").chain(function(){this.hideElement();}.bind(this));}var d=(f.next_page)?f.next_page-1:this.currentPage.page+1;
this.currentPage={page:d,items_per_page:RokGallerySettings.items_per_page};this.setPageData({page:f.next_page,items_per_page:RokGallerySettings.items_per_page,filters:[],composite:{context:"com_rokgallery.gallerypicker",layout:"default_file"}});
RokGallerySettings.total_items=f.total_items;var c=b.payload.html,e=new Element("ul").set("html",c),a=e.getChildren();document.id("gallerypicker-fileslist").adopt(a);
e.dispose();return this;},onError:function(a){this.element.removeClass("loader");document.getElement(".panel.filelist").empty().set("html",'<div class="error"><h2>An error occurred: </h2>'+a+"</div>");
}},initialize:function(b,a){this.parent(b,a);this.currentPage={page:1,items_per_page:RokGallerySettings.items_per_page,filters:[]};},showElement:function(){this.element.store("display","block");
this.parent();},hideElement:function(){this.element.store("display","none");this.parent();},setPageData:function(a){this.pageData=a;}});})());