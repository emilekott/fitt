/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){if(!this.Uploader){this.Uploader={};}this.Uploader=new Class({Implements:[Options,Events],options:{url:""},initialize:function(b,a){this.options.url=RokGallerySettings.url;
this.setOptions(a);this.type=UploaderSupport.check();if(!this.type){return;}if(this.type=="Flash"){var c=RokGallerySettings.session;this.setOptions({url:this.options.url+"&"+RokGallerySettings.token+"=1&"+c.name+"="+c.id});
}this.element=document.id(b)||document.getElement("element");this.bounds={click:this.load.bind(this)};this.job=new Job({url:RokGallery.url,onError:function(d){this.instance.setJobText(d);
}.bind(this),onBeforeCreate:function(){this.instance.setJobText("Creating Job...");}.bind(this),onCreate:function(d,e){this.instance.job=e;this.instance.setJobText("Uploading...");
this.instance.start();}.bind(this),onBeforeReady:function(){this.instance.setJobText("Waiting for the Job to be ready.");}.bind(this),onReady:function(d,f){this.instance.setJobText("Job is in ready state mode.");
window.Popup.popup.getElement(".loading").setStyle("display","none");var e=new Element("canvas",{width:18,height:18,"class":"job-canvas"});e.inject(this.instance.jobsInfo,"before");
this.instance.canvasWrapper=new Element("div.job-canvas-wrapper").wraps(e);this.job.progress=new Progress(e);this.job.process();}.bind(this),onBeforeProcess:function(){this.instance.setJobText("Processing the uploaded files.");
}.bind(this),onProcess:function(d){window.Popup.setPopup({buttons:{cancel:{label:"background"}}});window.Popup.popup.getElement(".button:last-child").setStyle("display","block");
if(RokGallery.uploader.type=="Flash"){window.Popup.popup.getElement(".button.cancel").setStyles({display:"block",position:"relative",visibility:"visible","float":"right"});
}this.timer=this.job.status.periodical(1000,this.job);this.job.status();}.bind(this),onBeforeStatus:function(){if(this.request.isRunning()){this.request.cancel();
}},onStatus:function(d){this.job.progress.set(d.payload.percent);this.instance.setJobText(d.payload.percent+"% - "+d.payload.status);if(d.payload.percent=="100"){clearTimeout(this.timer);
this.job.complete();}return this.job;}.bind(this),onComplete:function(e){clearTimeout(this.timer);this.job.request.cancel();window.Popup.setPopup({type:"success",buttons:{cancel:{label:"close"}}});
window.Popup.popup.getElement(".button:last-child").setStyle("display","block");if(RokGallery.uploader.type=="Flash"){window.Popup.popup.getElement(".button.cancel").setStyles({display:"block",position:"relative",visibility:"visible","float":"right"});
}var d=this.type=="HTML5"?this.instance.files.length:this.instance.fileList.length;this.instance.jobsInfo.dispose();this.instance.canvasWrapper.dispose();
window.Popup.popup.getElement(".loading").setStyle("display","none");RokGallery.loadMore.refresh(d);}.bind(this)});this.attach();},attach:function(){this.element.addEvents(this.bounds);
},detach:function(){this.element.removeEvents(this.bounds);},load:function(a){if(a){a.stop();}this.popup();this.instance=new Uploader[this.type]("files-status","files-list",this.options);
if(this.type=="HTML5"){window.Popup.popup.addEvents({dragover:function(){var b=this.form.getElement(".drop-info"),c=b.set("morph",{link:"cancel"});b.addClass("drag-over");
}.bind(this.instance),dragleave:function(){this.form.getElement(".drop-info").removeClass("drag-over");}.bind(this.instance),drop:function(){this.form.getElement(".drop-info").removeClass("drag-over");
}.bind(this.instance)});}},popup:function(){var b=window.Popup,d;d=this.type=="Flash"?"<p>Click the Browse button and select the images you want to upload.</p><p>Once you are ready to upload, click the Upload button and keep track of the progress bars</p>":'<div class="drop-info">Drag &amp; Drop Files Here</div>';
b.setPopup({type:"",title:"Files Upload",message:'<form action="'+this.options.url+'" method="post" enctype="multipart/form-data" id="files-form"><div id="files-empty-desc">'+d+'</div><div id="files-status"><div class="total-progress"><canvas class="overall-progress" height="25" width="25"></canvas><div id="files-success"></div></div><div class="overall-title"></div><div class="current-text"></div></div><div id="files-list"></div></form>',buttons:{ok:{show:true,label:"upload"},cancel:{show:true,label:"close"}},"continue":function(){}});
var c=b.statusBar.getElement("div.button.browse.custom");if(!c){c=new Element("div#files-browse.button.browse.custom",{text:"browse"}).inject(b.statusBar.getElement(".clr"),"before");
}var a=b.topBar.getElement("div.counter");if(!a){a=new Element("div.counter",{html:"<span>0</span> files"}).inject(b.topBar.getElement(".icon"),"after");
}a.set("tween",{duration:200,transition:"quad:in:out",link:"chain"});b.counter=a;b.popup.addEvent("close:once",function(){var e=document.id("files-clear");
a.dispose();document.id("files-browse").dispose();document.getElements("#popup .job-canvas-wrapper, #popup .job-info").dispose();if(e){e.dispose();}}.bind(this));
b.open();},onBeforeUnload:function(a){var b=b||window.event,a=a||"You are about to leave this page and any unsaved changed will be lost. Are you sure you want to continue?";
if(b){b.returnValue=a;}return a;},attachUnload:function(a){window.onbeforeunload=this.onBeforeUnload.pass(a);},detachUnload:function(){window.onbeforeunload=function(){};
}});window.addEvent("domready",function(){UploaderSupport.load(RokGallerySettings.application);});Object.append(this.Uploader,{STATUS_QUEUED:0,STATUS_RUNNING:1,STATUS_ERROR:2,STATUS_COMPLETE:3,STATUS_STOPPED:4,log:function(){if(window.console&&console.info){console.info.apply(console,arguments);
}},unitLabels:{b:[{min:1,unit:"B"},{min:1024,unit:"KB"},{min:1048576,unit:"MB"},{min:1073741824,unit:"GB"}],s:[{min:1,unit:"s"},{min:60,unit:"m"},{min:3600,unit:"h"},{min:86400,unit:"d"}]},formatUnit:function(a,h,b){var f=Uploader.unitLabels[(h=="bps")?"b":h];
var c=(h=="bps")?"/s":"";var e,d=f.length,j;if(a<1){return"0 "+f[0].unit+c;}if(h=="s"){var g=[];for(e=d-1;e>=0;e--){j=Math.floor(a/f[e].min);if(j){g.push(j+" "+f[e].unit);
a-=j*f[e].min;if(!a){break;}}}return(b===false)?g:g.join(b||", ");}for(e=d-1;e>=0;e--){j=f[e].min;if(a>=j){break;}}return(a/j).toFixed(2)+" "+f[e].unit+c;
}});})());