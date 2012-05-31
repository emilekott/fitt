<?php
 /**
  * @version   $Id: Job.php 39355 2011-07-02 18:33:01Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokGallery_Job
{
    const TYPE_CREATEGALLERY = 'Create Gallery';
    const TYPE_UPDATEGALLERY = 'Update Gallery';
    const TYPE_IMPORT = 'Import';
    const TYPE_TAGADDITION = 'Tag Addition';
    const TYPE_TAGREMOVAL = 'Tag Removal';


    const STATE_PREPPING = 'Prepping';
    const STATE_READY = 'Ready';
    const STATE_RUNNING = 'Running';
    const STATE_COMPLETE = 'Completed';
    const STATE_CANCELED = 'Canceled';
    const STATE_PAUSED = 'Paused';
    const STATE_PAUSING = 'Pausing';
    const STATE_CANCELING = 'Canceling';
    const STATE_ERRORED = 'Errored';

    /** @var RokGallery_Job_StateMachine */
    protected $_fsm;

    /** @var RokGallery_Job_Processor */
    protected $_processor;

    /** @var string */
    protected $_id;

    /** @var RokGallery_Model_Job */
    protected $_job;

    /**
     * @static
     * @param  $id
     * @return RokGallery_Job
     */
    public static function &get($id)
    {

        try
        {
            $job = new RokGallery_Job();
            $job->_load($id);
            return $job;
        } catch (Exception $e)
        {
            throw $e;
        }
    }

    /**
     * @static
     * @param  $type
     * @return rokGallery_Job
     */
    public static function &create($type)
    {
        try
        {
            $job = new RokGallery_Job();
            $job->_create($type);
            return $job;
        } catch (Exception $e)
        {
            throw $e;
        }
    }

    protected function __construct()
    {

    }

    protected function _load($id)
    {
        try
        {
            $this->_job = Doctrine_Core::getTable('RokGallery_Model_Job')->getSingle($id);
            if ($this->_job === false){
                throw new RokGallery_Job_Exception(rc__('ROKGALLERY_UNABLE_TO_FIND_ID',$id));
            }
            $this->_fsm = unserialize($this->_job->sm);
            $this->_fsm->setOwner($this);
            $this->_id = $this->_job->id;
            $this->_processor = RokGallery_Job_ProcessorFactory::factory($this);
        } catch (Exception $e)
        {
            throw $e;
        }
    }


    /**
     * @param RokGallery_Model_Job $job
     * @return RokGallery_Job
     *
     */
    protected function _create($type)
    {
        $tx = RokGallery_Doctrine::getConnection()->transaction;
        $tx->setIsolation('REPEATABLE READ');
        try
        {
            // Uncomment to send debug output to the apache error_log.
            //$this->_fsm->setDebugFlag(true);
            $this->_fsm = new RokGallery_Job_StateMachine($this);
            $tx->beginTransaction();
            $this->_job = new RokGallery_Model_Job();
            $this->_job->sm = serialize($this->_fsm);
            $this->_job->id = RokCommon_UUID::generate();
            $this->_job->type = $type;
            $this->_job->state = $this->getStateName();
            $this->_job->percent = 0;
            $this->_job->save();

            $this->_processor = RokGallery_Job_ProcessorFactory::factory($this);
            $this->_id = $this->_job->id;
            $tx->commit();
        }
        catch (Exception $e)
        {
            $tx->rollback();
            throw $e;
        }

    }

    public function saveCurrentState($state, $status = null, $percent = 0)
    {
        $tx = RokGallery_Doctrine::getConnection()->transaction;
        $tx->setIsolation('REPEATABLE READ');

        try
        {
            $tx->beginTransaction();
            $this->_job->state = $state;
            if ($percent !=0)
                $this->_job->percent = $percent;
            $this->_job->status = $status;
            $this->_job->sm = serialize($this->_fsm);
            $this->_job->save();
            $tx->commit();
        }
        catch (Exception $e)
        {
            $tx->rollback();
            throw $e;
        }
    }

    /**
     * @return RokGallery_Job_State
     */
    public function getFsm()
    {
        return $this->_fsm;
    }

    /**
     * gets the name of the current state the job is in
     * @return string
     */
    public function getStateName()
    {
        return preg_replace('/^RokGallery_Job_StateMap\./', '', $this->_fsm->getState()->getName());
    }

    public function Cancel($message=null, $percent=0)
    {
        $this->_fsm->Cancel();
        $this->saveCurrentState($this->getStateName(), $message, $percent=0);
    }

    public function Canceled($message=null, $percent=0)
    {
        $this->_fsm->Canceled();
        $this->saveCurrentState($this->getStateName(), $message, $percent=0);
    }

    public function Complete($message=null)
    {
        $this->_fsm->Complete();
        $this->saveCurrentState($this->getStateName(), $message, 100);
    }

    public function Delete($message=null, $percent=0)
    {
        $this->_fsm->Delete();
    }

    public function Deleted()
    {
        $this->_fsm->Deleted();
    }

    public function Error($message=null, $percent=0)
    {
        $this->_fsm->Error();
        $this->saveCurrentState($this->getStateName(), $message, $percent=0);
    }

    public function Pause($message=null, $percent=0)
    {
        $this->_fsm->Pause();
        $this->saveCurrentState($this->getStateName(), $message, $percent=0);
    }

    public function Paused($message=null, $percent=0)
    {
        $this->_fsm->Paused();
        $this->saveCurrentState($this->getStateName(), $message, $percent=0);
    }

    public function Ready($message=null, $percent=0)
    {
        $this->_fsm->Ready();
        $this->saveCurrentState($this->getStateName(), $message, $percent=0);
    }

    public function Resume($message=null, $percent=0)
    {
        $this->_fsm->Resume();
        $this->saveCurrentState($this->getStateName(), $message, $percent=0);
    }

    public function Run($message=null, $percent=0)
    {
        $this->_fsm->Run();
        $this->saveCurrentState($this->getStateName(), $message, $percent=0);
    }


    // Context Functions
    public function readyJob()
    {

    }

    public function cancelJob()
    {

    }

    public function runJob()
    {

    }

    public function pauseJob()
    {

    }

    public function completeJob()
    {

    }

    public function errorJob()
    {

    }

    public function startPausingJob()
    {

    }

    public function startCancelingJob()
    {

    }

    public function startDeletingJob()
    {
        $this->_job->delete();
    }

    public function deleteJob()
    {
        $this->_job->delete();
    }

    public function getStatus($html = '')
    {
        return new RokGallery_Job_Status($this->_job, $html);
    }


    // Job Helper Functions
    public function getProperties()
    {
        return unserialize($this->_job->properties);
    }

    public function setProperties($properties)
    {
        $this->_job->properties = serialize($properties);
    }

    public function getType()
    {
        return $this->_job->type;
    }

    public function save($status=null, $percent=0)
    {
        try
        {
            $this->saveCurrentState($this->getStateName(),$status, $percent);
        } catch (Exception $e)
        {
            throw $e;
        }
    }

    public function process()
    {
        $this->_processor->process();
    }


    public function refreshState()
    {
        $this->_job->refresh();
        $this->_fsm = unserialize($this->_job->sm);
        $this->_fsm->setOwner($this);
    }
    /**
     * @return string
     */
    public function getId()
    {
        return $this->_id;
    }

    public function &getJob()
    {
        return $this->_job;
    }


    public function shutdown()
    {
        $isError = false;
        if ($error = error_get_last()){
            switch($error['type']){
                case E_ERROR:
                case E_CORE_ERROR:
                case E_COMPILE_ERROR:
                case E_USER_ERROR:
                    $isError = true;
                    break;
            }
        }

        if ($isError){
            $this->Error("Whoops");
        }
    }


}
