/*
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
((function(){this.Job=new Class({Implements:[Options,Events],options:{url:"",id:null},initialize:function(a){this.setOptions(a);this.id=this.options.id;
this.queue=null;this.request=new Request({url:this.options.url,onSuccess:this.success.bind(this)});this.processRequest=new Request({url:this.options.url,onSuccess:this.success.bind(this)});
["create","ready","process","status","pause","resume","cancel","delete"].each(this.job.bind(this));},success:function(a){if(!JSON.validate(a)){return this.error("Invalid JSON response.");
}a=JSON.decode(a);if(a.status!="success"){return this.error(a.message);}if(this.action=="create"){this.id=a.payload.job;}this.fireEvent(this.action,[a,this.id]);
if(this.queue){this[this.queue].delay(10,this);this.queue=null;return this;}this.done(a);return this;},start:function(){this.fireEvent("start",this.id);
},complete:function(){this.fireEvent("complete",this.id);},done:function(a){this.fireEvent("done",[this.id,a]);},error:function(a){this.fireEvent("error",a);
},job:function(a){this[a]=function(){if(!this.id&&a!="create"){this.queue=a;return this.create();}this.start();this.fireEvent("before"+a.capitalize());
this.action=a;var b={model:"job",action:a};if(a!="create"){b.params=JSON.encode({id:this.id});}(a=="process"?this.request:this.processRequest)["send"]({data:b});
return this;};}.protect()});})());