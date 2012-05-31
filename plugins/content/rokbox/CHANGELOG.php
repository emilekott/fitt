<?php
/**
 * RokBox Content Plugin
 *
 * @package		Joomla
 * @subpackage	RokBox Content Plugin
 * @copyright Copyright (C) 2009 RocketTheme. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see RT-LICENSE.php
 * @author RocketTheme, LLC
 *
 */
 
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
?>

1. Copyright and disclaimer
----------------


2. Changelog
------------
This is a non-exhaustive changelog for RokBox Content, inclusive of any alpha, beta, release candidate and final versions.

Legend:

* -> Security Fix
# -> Bug Fix
+ -> Addition
^ -> Change
- -> Removed
! -> Note

----------- 1.7 Release [01-Nov-2010] -----------

31-Oct-2010 Djamil Legato
# Stopped RokBox from loading in Admin

----------- 1.6 Release [30-Jun-2010] -----------

30-Jun-2010 Djamil Legato
+ Added wrappers elements for albums. Useful for different styling
+ Added new attribute to specify custom thumbs size (ie, thumbsize=|100 200|)
# Fixed thumbnails html output to provide widths and heights

----------- 1.5 Release [18-Nov-2009] -----------

18-Nov-2009 Brian Towles
# Added bug fix for PHP 5.3

----------- 1.4 Release [26-June-2009] ------------

26-Jun-2009 Djamil Legato
# Suppressed a possible warning

----------- 1.3 Release [18-Jun-2009] ------------

18-Jun-2009 Djamil Legato
# Folders containing others files type rather than just images were breaking RokBox
# Wildcards syntax now strips out all non-images files
# Check on the foreach loop that in same rare scenarios where throwing notices
+ New option that let you try to load remote images size (Note: This could slow down your site a bit)

16-Jun-2009 Djamil Legato
+ Built-in RokModule check

----------- 1.2 Release [18-Feb-2009] ------------

18-Feb-2009 Djamil Legato
+ Module support, you can load an element from the page with a specific id right into RokBox ({rokbox module=|login|}{/rokbox})
# Fixed the lowercase extension limitation, now uppercase are considered good too
# Fixed a thumbnail directory issue

----------- 1.1 Release [05-Jun-2008] ------------

05-Jun-2008 Djamil Legato
+ Auto calculation for images width and height when no size specified. 
+ Support for wildcards by Dennis Pleiter in local path, to load a whole directory of images with just one line ({rokbox album=|myalbum|}images/stories/food/*{/rokbox}) and auto thumb creation! In conjunction with wildcards you can also use thumbcount=|number| option, to determine how many thumbs show for that folder.

----------- 1.0 Release [13-May-2008] ------------

13-May-2008 Djamil Legato
! Initial release. 

--------- 0.3 Release [03-May-2009] ---------

03-May-2008 Djamil Legato
# Various bug fixes

--------- 0.2 Release [30-April-2009] ---------

30-Apr-2008 Djamil Legato
+ PHP4 compatibility
# Validation errors fixed

----------- 0.1 Release [29-April-2009] -----------

29-Apr-2008 Djamil Legato
! Initial release. 
----------- Initial Changelog Creation -----------