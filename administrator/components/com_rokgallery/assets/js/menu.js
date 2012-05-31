/*
 * @copyright Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 *
 * JMenu javascript behavior
 *
 * @package Joomla
 * @since 1.5
 * @version 1.0
 * @notes MooTools 1.3 version by Djamil Legato
 */
var JMenu=new Class({initialize:function(c){var d=document.getElements("ul#menu li"),e,b,a;d.each(function(g,f){g.addEvents({mouseenter:function(){this.addClass("hover");
},mouseleave:function(){this.removeClass("hover");}});b=0;e=g.getElement("ul");if(!e){return;}a=e.getChildren().filter(function(h){return h.get("tag")=="li";
});a.each(function(k,j){var h=k.offsetWidth;b=(b>=h)?b:h;});a.setStyle("width",b);e.setStyle("width",b);});}});