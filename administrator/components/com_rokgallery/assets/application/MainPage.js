/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){this.MainPage=new Class({Implements:[Options,Events],options:{url:"",pageData:{page:3,items_per_page:6,filters:[]}},initialize:function(b,a){this.setOptions(a);
this.pageData=this.options.pageData;this.currentPage={page:1,items_per_page:12,filters:[]};this.bounds={click:this.load.bind(this)};this.boundsDoc={"keydown:keys(shift)":this.docKeyDown.bind(this),"keyup:keys(shift)":this.docKeyUp.bind(this)};
this.element=document.id(b)||document.getElement(b)||null;this.request=new Request({url:this.options.url,onSuccess:this.success.bind(this)});["getPage"].each(this.actions.bind(this));
this.attach();if(!RokGallerySettings.more_pages){this.detach();this.hideElement();}},showElement:function(){this.element.setStyle("display","block");},hideElement:function(){this.element.setStyle("display","none");
},docKeyDown:function(a){this.fireEvent("keyDown",[a,this.element]);},docKeyUp:function(a){this.fireEvent("keyUp",[a,this.element]);},attach:function(){if(this.attached){return;
}document.addEvents(this.boundsDoc);this.element.addEvents(this.bounds).removeClass("disabled");this.attached=true;},detach:function(){if(!this.attached){return;
}document.removeEvents(this.boundsDoc);this.element.removeEvents(this.bounds).addClass("disabled");this.attached=false;},load:function(a){if(this.request.isRunning()){return;
}this.attach();this.docKeyUp();this.showElement();this.element.addClass("loader");if(a&&a.shift){this.pageData.get_remaining=true;}else{this.pageData.get_remaining=false;
}this.setPageData(this.pageData);this.getPage(this.pageData);},refresh:function(a){this.attach();this.showElement();this.element.fade("in");document.getElements("#gallery-list .gallery-row").dispose();
if(a&&a+RokGallerySettings.total_items<=RokGallerySettings.items_per_page){this.currentPage.items_per_page=a+RokGallerySettings.total_items;}this.setPageData(this.currentPage);
this.request.cancel();this.load();},setPageData:function(a){this.pageData=a;this.pageData.filters=RokGallery.filters.filters;this.pageData.order=RokGallery.filters.order;
},success:function(a){if(!JSON.validate(a)){return this.error('<p class="error-intro">The response from the server had an invalid JSON string while trying to load more pages. Following is the reply.</p>'+a);
}a=JSON.decode(a);if(a.status!="success"){return this.error(a.message);}this.fireEvent(this.action,a);return this;},start:function(){this.fireEvent("start",this.id);
},complete:function(){this.fireEvent("complete",this.id);},error:function(a){this.fireEvent("error",a);},actions:function(a){this[a]=function(b){this.start();
this.fireEvent("before"+a.capitalize());this.action=a;var c={model:"mainpage",action:a};c.params=JSON.encode(b);this.request.send({data:c});return this;
};}.protect(),popup:function(a){var b={type:"warning",title:"Error",message:"",buttons:{ok:{show:false},cancel:{show:true,label:"close"}}};window.Popup.setPopup(Object.merge(b,a)).open();
}});})());