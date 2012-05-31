<?php
/**
  * @version   $Id: Status.php 39200 2011-06-30 04:31:21Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */
 
class RokGallery_Job_Status {
    public $state;
    public $status;
    public $percent;
    public $lastUpdate;
    public $type;
    public $html;


    /**
     * @param RokGallery_Model_Job $job
     * @return RokGallery_Job_Status
     */
    public function __construct(RokGallery_Model_Job $job, $html = '')
    {
        $this->state = $job->state;
        $this->status = $job->status;
        $this->percent = $job->percent;
        $this->lastUpdate = $job->updated_at;
        $this->type = $job->type;
        $this->html = $html;
    }
}
