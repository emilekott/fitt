/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){this.JobsManager=new Class({Extends:Job,options:{},initialize:function(b,a){this.setOptions(a);this.element=document.id(b)||document.getElement(b)||null;
if(!this.element){return;}this.parent();["get","clean","wipe"].each(this.jobs.bind(this));this.container=window.Popup.popup;this.bounds={click:this.open.bind(this)};
this.attach();},attach:function(){this.element.addEvents(this.bounds);},detach:function(){this.element.removeEvents(this.bounds);},open:function(){var a=window.Popup.popup.getElement(".jobs-info");
this.jobsInfo=a||new Element("div.jobs-info").inject(window.Popup.popup.getElement(".statusbar .clr"),"before");this.popup({type:"",title:"Jobs Manager",message:'<div class="jobs-loading">Retrieving the Jobs list...</div>'});
this.statusBar=window.Popup.statusBar;this.statusBar.getElement(".loading").setStyle("display","block");this.get();this.isOpen=true;},close:function(){this.statusBar.getElement(".loading").setStyle("display","none");
this.isOpen=false;},popup:function(a){var b={type:"warning",title:"Jobs Manager - Error",message:"",buttons:{ok:{show:false},cancel:{show:true,label:"close"}}};
window.Popup.setPopup(Object.merge(b,a)).open();},jobs:function(a){this[a]=function(){this.fireEvent("before"+a.capitalize());this.action=a;var b={model:"jobs",action:a};
this.request.send({data:b});return this;};}.protect()});})());