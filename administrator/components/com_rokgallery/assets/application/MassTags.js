/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){this.MassTags=new Class({Implements:[Options,Events],options:{url:""},initialize:function(a){this.setOptions(a);this.request=new Request({url:this.options.url,onSuccess:this.success.bind(this)});
["addTags","removeTags","getTagPopup"].each(this.actions.bind(this,false));},success:function(a){if(!JSON.validate(a)){return this.error("Invalid JSON response. ",a);
}a=JSON.decode(a);if(a.status!="success"){return this.error(a.message);}this.fireEvent(this.action,[a,this.id]);this.done(a);return this;},start:function(){this.fireEvent("start",this.id);
},done:function(a){this.fireEvent("done",[this.id,a]);},error:function(a){this.fireEvent("error",[a,this.action]);},actions:function(a,b){this[b]=function(c){this.start();
this.fireEvent("before"+b.capitalize());this.action=b;var d={model:"files",action:b};if(c){d.params=JSON.encode(c);}else{d.params=JSON.encode({});}this.request.send({data:d});
return this;};}.protect()});this.MassTagsManager=new Class({Extends:MassTags,options:{url:"",onGetTagPopup:function(a){window.Popup.statusBar.getElement(".loading").setStyle("display","none");
window.Popup.content.set("html",a.payload.html);window.Popup.content.getElement(".selected-files span").set("text",RokGallery.blocks.multiSelection.elements.length);
this.galleries={container:window.Popup.content.getElement(".galleries-list"),list:window.Popup.content.getElements(".galleries-dropdown li"),title:window.Popup.content.getElements(".galleries-list .title")};
this.radio=window.Popup.content.getElements("input[name=mass-tags-action]");this.input=window.Popup.content.getElement("#mass-tags-list");this.ids=new Elements(RokGallery.blocks.multiSelection.elements).retrieve("file-id");
this.build();},onBeforeAddTags:function(){window.Popup.statusBar.getElement(".loading").setStyle("display","block");},onAddTags:function(a){window.Popup.statusBar.getElement(".loading").setStyle("display","none");
RokGallery.loadMore.refresh();window.Popup.statusBar.getElement(".button.cancel").fireEvent("click");},onBeforeRemoveTags:function(){window.Popup.statusBar.getElement(".loading").setStyle("display","block");
},onRemoveTags:function(a){window.Popup.statusBar.getElement(".loading").setStyle("display","none");RokGallery.loadMore.refresh();window.Popup.statusBar.getElement(".button.cancel").fireEvent("click");
},onError:function(a,b){window.Popup.statusBar.getElement(".loading").setStyle("display","none");window.Popup.setPopup({type:"warning"});if(b=="getTagPopup"){window.Popup.statusBar.getElement(".button.ok").setStyle("display","none");
window.Popup.content.set("html",a);}else{window.Popup.statusBar.getElement(".tags-info").set("html",a);}}},initialize:function(a){this.parent(a);this.getTagPopup();
},build:function(){this.galleries.list.removeEvents("click").each(function(a){a.addEvent("click",function(){this.galleries.title.set("text",a.get("text"));
var b=a.get("data-tags")||"";this.galleries.container.addClass("hidden");this.input.set("value",b);document.addEvent("mousemove:once",function(){this.galleries.container.removeClass("hidden");
}.bind(this));}.bind(this));},this);},getRadioAction:function(){var a=null;this.radio.each(function(b){if(b.get("checked")){a=b.get("value");}},this);return a;
},popup:function(a){var b={type:"warning",title:"Galleries Manager - Error",message:"",buttons:{ok:{show:true,label:"save"},cancel:{show:true,label:"close"}}};
window.Popup.setPopup(Object.merge(b,a)).open();}});})());