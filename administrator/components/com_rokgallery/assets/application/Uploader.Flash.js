/*
 * FancyUpload - Flash FileReference Control
 *
 * @version		3.0
 * @license		MIT License
 * @author			Harald Kirschner <http://digitarald.de>
 * @author			Valerio Proietti, <http://mad4milk.net>
 *
 * @changes		Djamil Legato
 * @copyright		Authors
 */
((function(){this.Uploader.Flash=new Class({Extends:Swiff.Uploader,options:{verbose:false,appendCookieData:true,queued:1,limitSize:0,limitFiles:0,validateFile:Function.from(true),typeFilter:{"Images (*.jpg, *.jpeg, *.gif, *.png)":"*.jpg; *.jpeg; *.gif; *.png"},target:"files-browse",container:"files-browse",fileSizeMax:0,data:{model:"upload",action:"file",params:{}},onBeforeStart:function(){var a=this.options.data;
a.params=JSON.encode({id:this.job});this.setOptions({data:a});if(this.options.appendCookieData){this.appendCookieData();}},onLoad:function(){this.target.addEvents({click:function(){this.removeClass("hover").removeClass("down");
return false;},mouseenter:function(){this.addClass("hover").removeClass("down");},mouseleave:function(){this.removeClass("hover").removeClass("down");this.blur();
},mousedown:function(){this.addClass("down");this.focus();},mouseup:function(){this.removeClass("down");this.blur();}});this.clearall=new Element("span",{id:"files-clear",text:"clear all"}).inject(window.Popup.statusBar.getElement(".browse"),"after");
document.id("files-clear").addEvent("click",function(){this.remove();window.Popup.counter.getElement("span").set("text",0);this.overallProgress.reset();
var c=document.id("files-form").getChildren(),b;b=c.shift().setStyle("display","block");c=c.slice(0,c.length-1);new Elements(c).setStyle("display","none");
document.id("files-clear").setStyle("display","none");return false;}.bind(this));var a=window.Popup.statusBar.getElement(".ok").set("id","files-upload");
a.addEvent("click",function(){this.start();return false;}.bind(this));},onSelectFail:function(a){a.each(function(b){new Element("li",{"class":"validation-error",html:b.validationErrorMessage||b.validationError,title:MooTools.lang.get("Uploader","removeTitle"),events:{click:function(){window.Popup.reposition();
this.destroy();}}}).inject(this.list,"top");},this);},onFileSuccess:function(d,a){if(a.charCodeAt(0)==65279){for(var c=1,b=[];c<a.length;c++){b.push(a[c]);
}a=b.join("");}if(!JSON.validate(a)){d.element.addClass("file-failed");d.info.set("html","<strong>An error occured:</strong> Invalid JSON response.");this.scrollbar.update();
return this;}a=JSON.decode(a);if(a.status!="success"){d.element.addClass("file-failed");d.info.set("html","<strong>An error occured:</strong> "+a.message);
return this;}d.element.addClass("file-success");this.scrollbar.update();return this;},onFileComplete:function(b,a){this.uploaded++;this.setJobText("Uploading... ("+this.uploaded+"/"+this.fileList.length+")");
},onFail:function(a){switch(a){case"hidden":alert("To enable the embedded uploader, unblock it in your browser and refresh (see Adblock).");break;case"blocked":alert("To enable the embedded uploader, enable the blocked Flash movie (see Flashblock).");
break;case"empty":alert("A required file was not found, please be patient and we fix this.");break;case"flash":alert("To enable the embedded uploader, install the latest Adobe Flash plugin.");
}}},initialize:function(a,c,b){this.status=document.id(a);this.list=document.id(c);this.options.url=document.id("files-form").action;b.path=RokGallerySettings.application+"Swiff.Uploader.swf";
this.uploader=RokGallery.uploader;this.setOptions(b);this.scrollbar=new Scrollbar(c,{triggerElement:"#popup .content",gutter:true,wrapStyles:{"float":"right"}});
new Element("div.clr").inject(this.scrollbar.wrapper,"after");b.fileClass=b.fileClass||Uploader.Flash.File;b.fileSizeMax=b.limitSize||b.fileSizeMax;b.fileListMax=b.limitFiles||b.fileListMax;
this.parent(b);this.addEvents({load:this.render,select:this.onSelect,cancel:this.onCancel,start:this.onStart,queue:this.onQueue,complete:this.onComplete});
this.job=false;},setJobs:function(){window.Popup.popup.getElements(".button").setStyles({visibility:"hidden",position:"absolute"});this.clearall.setStyle("display","none");
this.jobsInfo=new Element("div.job-info").inject(window.Popup.popup.getElement(".statusbar .clr"),"before");window.Popup.popup.getElement(".loading").setStyle("display","block");
return this;},setJobText:function(a){this.jobsInfo.set("html",a);},createJob:function(){this.uploader.attachUnload("You are about to leave this page with an upload job in progress.\n\nAre you sure you want to continue?");
this.setJobs();this.uploader.job.create();},setReady:function(){this.uploader.job.ready();},render:function(){this.overallTitle=this.status.getElement(".overall-title");
this.currentText=this.status.getElement(".current-text");var a=this.status.getElement(".overall-progress");this.overallProgress=new Progress(a);this.updateOverall();
},start:function(){if(!this.fileList.length||!this.size){return this;}if(!this.job){return this.createJob();}this.uploaded=0;this.fireEvent("beforeStart");
this.remote("xStart");return this;},onSelect:function(){this.status.removeClass("status-browsing");this.status.setStyle("display","block");this.list.setStyle("display","block");
var b=document.id("files-form").getChildren(),a;a=b.shift().setStyle("display","none");b=b.slice(0,b.length-1);new Elements(b).setStyle("display","block");
document.id("files-clear").setStyle("display","inline-block");document.body.focus();},onCancel:function(){this.status.removeClass("file-browsing");},onStart:function(){this.status.addClass("file-uploading");
this.overallProgress.set(0);},onQueue:function(){this.updateOverall();window.Popup.reposition();this.scrollbar.update();},onComplete:function(){this.status.removeClass("file-uploading");
if(this.size){this.overallProgress.set(100);this.status.getElements("canvas").setStyle("display","none");this.status.getElement("#files-success").setStyle("display","block");
}else{this.overallProgress.set(0);this.status.getElements("canvas").setStyle("display","block");this.status.getElement("#files-success").setStyle("display","none");
}this.setReady();this.uploader.detachUnload();},updateOverall:function(){this.overallTitle.set("html",MooTools.lang.get("Uploader","progressOverall").substitute({total:Uploader.formatUnit(this.size,"b")})).store("total",Uploader.formatUnit(this.size,"b"));
if(!this.size){this.currentText.set("html","");}},upload:function(){this.start();},removeFile:function(){return this.remove();}});this.Uploader.Flash.File=new Class({Extends:Swiff.Uploader.File,render:function(){if(this.invalid){if(this.validationError){var a=MooTools.lang.get("Uploader","validationErrors")[this.validationError]||this.validationError;
this.validationErrorMessage=a.substitute({name:this.name,size:Uploader.formatUnit(this.size,"b"),fileSizeMin:Uploader.formatUnit(this.base.options.fileSizeMin||0,"b"),fileSizeMax:Uploader.formatUnit(this.base.options.fileSizeMax||0,"b"),fileListMax:this.base.options.fileListMax||0,fileListSizeMax:Uploader.formatUnit(this.base.options.fileListSizeMax||0,"b")});
}this.remove();return;}this.addEvents({start:this.onStart,progress:this.onProgress,complete:this.onComplete,error:this.onError,remove:this.onRemove});this.info=new Element("span",{"class":"file-info"});
this.canvas=new Element("canvas",{width:12,height:12,"class":"file-canvas"});this.element=new Element("li",{"class":"file file-"+Number.random(1,3)}).adopt(new Element("span",{"class":"file-name",html:MooTools.lang.get("Uploader","fileName").substitute(this)}),new Element("span",{"class":"file-size",html:"("+Uploader.formatUnit(this.size,"b")+")"}),new Element("a",{"class":"file-remove",href:"#",html:"<span>"+MooTools.lang.get("Uploader","remove")+"</span>",title:MooTools.lang.get("Uploader","removeTitle"),events:{click:function(){this.remove();
window.Popup.reposition();return false;}.bind(this)}}),this.info,this.canvas,new Element("div.clr")).inject(this.base.list);new Element("div.file-canvas-wrapper").wraps(this.canvas);
this.progressCanvas=new Progress(this.canvas);},validate:function(){window.Popup.reposition();return(this.parent()&&this.base.options.validateFile(this));
},onStart:function(){this.element.addClass("file-uploading");},onProgress:function(){this.base.overallProgress.set(this.base.percentLoaded);this.progressCanvas.set(this.progress.percentLoaded);
var a=this.base.overallTitle.retrieve("total");this.base.overallTitle.set("html",a+'<br /><span style="color: #999;">'+Uploader.formatUnit(this.base.bytesLoaded,"b")+"</span>");
this.base.currentText.set("html",this.base.percentLoaded+"% <br />"+((this.base.rate)?Uploader.formatUnit(this.progress.rate,"bps"):"- B"));},onComplete:function(){this.element.removeClass("file-uploading");
if(this.response.error){var b=MooTools.lang.get("Uploader","errors")[this.response.error]||"{error} #{code}";this.errorMessage=b.substitute(Object.append({name:this.name,size:Uploader.formatUnit(this.size,"b")},this.response));
var a=[this,this.errorMessage,this.response];this.fireEvent("error",a).base.fireEvent("fileError",a);}else{this.base.fireEvent("fileSuccess",[this,this.response.text||""]);
}},onError:function(){this.element.addClass("file-failed");var a=MooTools.lang.get("Uploader","fileError").substitute(this);this.info.set("html","<strong>"+a+":</strong> "+this.errorMessage);
},onRemove:function(){this.element.getElements("a").setStyle("visibility","hidden");this.element.fade("out").retrieve("tween").chain(function(){Element.destroy(this.element);
this.base.scrollbar.update();}.bind(this));window.Popup.counter.getElement("span").set("text",this.base.fileList.length);}});})());