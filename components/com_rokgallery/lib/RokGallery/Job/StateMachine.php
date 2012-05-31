<?php
/**
  * @version   $Id: StateMachine.php 39200 2011-06-30 04:31:21Z btowles $
  * @author    RocketTheme http://www.rockettheme.com
  * @copyright Copyright (C) 2007 - 2011 RocketTheme, LLC
  * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
  */

class RokGallery_Job_State extends RokCommon_State {

    public function Entry($fsm) {
    }

    public function Exit_($fsm) {
    }

    public function Cancel($fsm) {
        $this->Default_($fsm);
    }

    public function Canceled($fsm) {
        $this->Default_($fsm);
    }

    public function Complete($fsm) {
        $this->Default_($fsm);
    }

    public function Delete($fsm) {
        $this->Default_($fsm);
    }

    public function Deleted($fsm) {
        $this->Default_($fsm);
    }

    public function Error($fsm) {
        $this->Default_($fsm);
    }

    public function Pause($fsm) {
        $this->Default_($fsm);
    }

    public function Paused($fsm) {
        $this->Default_($fsm);
    }

    public function Ready($fsm) {
        $this->Default_($fsm);
    }

    public function Resume($fsm) {
        $this->Default_($fsm);
    }

    public function Run($fsm) {
        $this->Default_($fsm);
    }

    public function Default_($fsm) {
        if ($fsm->getDebugFlag() == true) {
            fwrite($fsm->getDebugStream(), "TRANSITION   : Default\n");
        }
        $state = $fsm->getState()->getName();
        $transition = $fsm->getTransition();
        $msg = "\n\tState: $state\n\tTransition: $transition";
        throw new TransitionUndefinedException($msg);
    }
}

class RokGallery_Job_StateMap_Default extends RokGallery_Job_State {

    public function Default_($fsm) {
        if ($fsm->getDebugFlag() == true) {
            fwrite($fsm->getDebugStream(), "TRANSITION   : RokGallery_Job_StateMap::\$Default->Default();\n");
        }
    }
    public function getTransitions() {
        return array(
            'Cancel' => 0,
            'Canceled' => 0,
            'Complete' => 0,
            'Default' => 2,
            'Delete' => 0,
            'Deleted' => 0,
            'Error' => 0,
            'Pause' => 0,
            'Paused' => 0,
            'Ready' => 0,
            'Resume' => 0,
            'Run' => 0,
        );
    }

}

class RokGallery_Job_StateMap_Prepping extends RokGallery_Job_StateMap_Default {

    public function Cancel($fsm) {
        $ctxt = $fsm->getOwner();
        if ($fsm->getDebugFlag() == true) {
            fwrite($fsm->getDebugStream(), "TRANSITION   : RokGallery_Job_StateMap::\$Prepping->Cancel();\n");
        }
        $fsm->getState()->Exit_($fsm);
        $fsm->clearState();
        $exception = NULL;
        try {
            $ctxt->cancelJob();
        }
        catch (Exception $exception) {}
        $fsm->setState(RokGallery_Job_StateMap::$Canceling);
        $fsm->getState()->Entry($fsm);
        if ($exception != NULL) {
            throw $exception;
        }
    }
    public function Ready($fsm) {
        $ctxt = $fsm->getOwner();
        if ($fsm->getDebugFlag() == true) {
            fwrite($fsm->getDebugStream(), "TRANSITION   : RokGallery_Job_StateMap::\$Prepping->Ready();\n");
        }
        $fsm->getState()->Exit_($fsm);
        $fsm->clearState();
        $exception = NULL;
        try {
            $ctxt->readyJob();
        }
        catch (Exception $exception) {}
        $fsm->setState(RokGallery_Job_StateMap::$Ready);
        $fsm->getState()->Entry($fsm);
        if ($exception != NULL) {
            throw $exception;
        }
    }
    public function getTransitions() {
        return array(
            'Cancel' => 1,
            'Canceled' => 0,
            'Complete' => 0,
            'Default' => 2,
            'Delete' => 0,
            'Deleted' => 0,
            'Error' => 0,
            'Pause' => 0,
            'Paused' => 0,
            'Ready' => 1,
            'Resume' => 0,
            'Run' => 0,
        );
    }

}

class RokGallery_Job_StateMap_Ready extends RokGallery_Job_StateMap_Default {

    public function Cancel($fsm) {
        $ctxt = $fsm->getOwner();
        if ($fsm->getDebugFlag() == true) {
            fwrite($fsm->getDebugStream(), "TRANSITION   : RokGallery_Job_StateMap::\$Ready->Cancel();\n");
        }
        $fsm->getState()->Exit_($fsm);
        $fsm->clearState();
        $exception = NULL;
        try {
            $ctxt->cancelJob();
        }
        catch (Exception $exception) {}
        $fsm->setState(RokGallery_Job_StateMap::$Canceling);
        $fsm->getState()->Entry($fsm);
        if ($exception != NULL) {
            throw $exception;
        }
    }
    public function Run($fsm) {
        $ctxt = $fsm->getOwner();
        if ($fsm->getDebugFlag() == true) {
            fwrite($fsm->getDebugStream(), "TRANSITION   : RokGallery_Job_StateMap::\$Ready->Run();\n");
        }
        $fsm->getState()->Exit_($fsm);
        $fsm->clearState();
        $exception = NULL;
        try {
            $ctxt->runJob();
        }
        catch (Exception $exception) {}
        $fsm->setState(RokGallery_Job_StateMap::$Running);
        $fsm->getState()->Entry($fsm);
        if ($exception != NULL) {
            throw $exception;
        }
    }
    public function getTransitions() {
        return array(
            'Cancel' => 1,
            'Canceled' => 0,
            'Complete' => 0,
            'Default' => 2,
            'Delete' => 0,
            'Deleted' => 0,
            'Error' => 0,
            'Pause' => 0,
            'Paused' => 0,
            'Ready' => 0,
            'Resume' => 0,
            'Run' => 1,
        );
    }

}

class RokGallery_Job_StateMap_Running extends RokGallery_Job_StateMap_Default {

    public function Cancel($fsm) {
        $ctxt = $fsm->getOwner();
        if ($fsm->getDebugFlag() == true) {
            fwrite($fsm->getDebugStream(), "TRANSITION   : RokGallery_Job_StateMap::\$Running->Cancel();\n");
        }
        $fsm->getState()->Exit_($fsm);
        $fsm->clearState();
        $exception = NULL;
        try {
            $ctxt->startCancelingJob();
        }
        catch (Exception $exception) {}
        $fsm->setState(RokGallery_Job_StateMap::$Canceling);
        $fsm->getState()->Entry($fsm);
        if ($exception != NULL) {
            throw $exception;
        }
    }
    public function Complete($fsm) {
        $ctxt = $fsm->getOwner();
        if ($fsm->getDebugFlag() == true) {
            fwrite($fsm->getDebugStream(), "TRANSITION   : RokGallery_Job_StateMap::\$Running->Complete();\n");
        }
        $fsm->getState()->Exit_($fsm);
        $fsm->clearState();
        $exception = NULL;
        try {
            $ctxt->completeJob();
        }
        catch (Exception $exception) {}
        $fsm->setState(RokGallery_Job_StateMap::$Completed);
        $fsm->getState()->Entry($fsm);
        if ($exception != NULL) {
            throw $exception;
        }
    }
    public function Error($fsm) {
        $ctxt = $fsm->getOwner();
        if ($fsm->getDebugFlag() == true) {
            fwrite($fsm->getDebugStream(), "TRANSITION   : RokGallery_Job_StateMap::\$Running->Error();\n");
        }
        $fsm->getState()->Exit_($fsm);
        $fsm->clearState();
        $exception = NULL;
        try {
            $ctxt->errorJob();
        }
        catch (Exception $exception) {}
        $fsm->setState(RokGallery_Job_StateMap::$Errored);
        $fsm->getState()->Entry($fsm);
        if ($exception != NULL) {
            throw $exception;
        }
    }
    public function Pause($fsm) {
        $ctxt = $fsm->getOwner();
        if ($fsm->getDebugFlag() == true) {
            fwrite($fsm->getDebugStream(), "TRANSITION   : RokGallery_Job_StateMap::\$Running->Pause();\n");
        }
        $fsm->getState()->Exit_($fsm);
        $fsm->clearState();
        $exception = NULL;
        try {
            $ctxt->startPausingJob();
        }
        catch (Exception $exception) {}
        $fsm->setState(RokGallery_Job_StateMap::$Pausing);
        $fsm->getState()->Entry($fsm);
        if ($exception != NULL) {
            throw $exception;
        }
    }
    public function getTransitions() {
        return array(
            'Cancel' => 1,
            'Canceled' => 0,
            'Complete' => 1,
            'Default' => 2,
            'Delete' => 0,
            'Deleted' => 0,
            'Error' => 1,
            'Pause' => 1,
            'Paused' => 0,
            'Ready' => 0,
            'Resume' => 0,
            'Run' => 0,
        );
    }

}

class RokGallery_Job_StateMap_Pausing extends RokGallery_Job_StateMap_Default {

    public function Paused($fsm) {
        $ctxt = $fsm->getOwner();
        if ($fsm->getDebugFlag() == true) {
            fwrite($fsm->getDebugStream(), "TRANSITION   : RokGallery_Job_StateMap::\$Pausing->Paused();\n");
        }
        $fsm->getState()->Exit_($fsm);
        $fsm->clearState();
        $exception = NULL;
        try {
            $ctxt->pauseJob();
        }
        catch (Exception $exception) {}
        $fsm->setState(RokGallery_Job_StateMap::$Paused);
        $fsm->getState()->Entry($fsm);
        if ($exception != NULL) {
            throw $exception;
        }
    }
    public function getTransitions() {
        return array(
            'Cancel' => 0,
            'Canceled' => 0,
            'Complete' => 0,
            'Default' => 2,
            'Delete' => 0,
            'Deleted' => 0,
            'Error' => 0,
            'Pause' => 0,
            'Paused' => 1,
            'Ready' => 0,
            'Resume' => 0,
            'Run' => 0,
        );
    }

}

class RokGallery_Job_StateMap_Paused extends RokGallery_Job_StateMap_Default {

    public function Cancel($fsm) {
        $ctxt = $fsm->getOwner();
        if ($fsm->getDebugFlag() == true) {
            fwrite($fsm->getDebugStream(), "TRANSITION   : RokGallery_Job_StateMap::\$Paused->Cancel();\n");
        }
        $fsm->getState()->Exit_($fsm);
        $fsm->clearState();
        $exception = NULL;
        try {
            $ctxt->startCancelingJob();
        }
        catch (Exception $exception) {}
        $fsm->setState(RokGallery_Job_StateMap::$Canceling);
        $fsm->getState()->Entry($fsm);
        if ($exception != NULL) {
            throw $exception;
        }
    }
    public function Error($fsm) {
        $ctxt = $fsm->getOwner();
        if ($fsm->getDebugFlag() == true) {
            fwrite($fsm->getDebugStream(), "TRANSITION   : RokGallery_Job_StateMap::\$Paused->Error();\n");
        }
        $fsm->getState()->Exit_($fsm);
        $fsm->clearState();
        $exception = NULL;
        try {
            $ctxt->errorJob();
        }
        catch (Exception $exception) {}
        $fsm->setState(RokGallery_Job_StateMap::$Errored);
        $fsm->getState()->Entry($fsm);
        if ($exception != NULL) {
            throw $exception;
        }
    }
    public function Resume($fsm) {
        $ctxt = $fsm->getOwner();
        if ($fsm->getDebugFlag() == true) {
            fwrite($fsm->getDebugStream(), "TRANSITION   : RokGallery_Job_StateMap::\$Paused->Resume();\n");
        }
        $fsm->getState()->Exit_($fsm);
        $fsm->clearState();
        $exception = NULL;
        try {
            $ctxt->runJob();
        }
        catch (Exception $exception) {}
        $fsm->setState(RokGallery_Job_StateMap::$Running);
        $fsm->getState()->Entry($fsm);
        if ($exception != NULL) {
            throw $exception;
        }
    }
    public function getTransitions() {
        return array(
            'Cancel' => 1,
            'Canceled' => 0,
            'Complete' => 0,
            'Default' => 2,
            'Delete' => 0,
            'Deleted' => 0,
            'Error' => 1,
            'Pause' => 0,
            'Paused' => 0,
            'Ready' => 0,
            'Resume' => 1,
            'Run' => 0,
        );
    }

}

class RokGallery_Job_StateMap_Canceling extends RokGallery_Job_StateMap_Default {

    public function Canceled($fsm) {
        $ctxt = $fsm->getOwner();
        if ($fsm->getDebugFlag() == true) {
            fwrite($fsm->getDebugStream(), "TRANSITION   : RokGallery_Job_StateMap::\$Canceling->Canceled();\n");
        }
        $fsm->getState()->Exit_($fsm);
        $fsm->clearState();
        $exception = NULL;
        try {
            $ctxt->cancelJob();
        }
        catch (Exception $exception) {}
        $fsm->setState(RokGallery_Job_StateMap::$Canceled);
        $fsm->getState()->Entry($fsm);
        if ($exception != NULL) {
            throw $exception;
        }
    }
    public function getTransitions() {
        return array(
            'Cancel' => 0,
            'Canceled' => 1,
            'Complete' => 0,
            'Default' => 2,
            'Delete' => 0,
            'Deleted' => 0,
            'Error' => 0,
            'Pause' => 0,
            'Paused' => 0,
            'Ready' => 0,
            'Resume' => 0,
            'Run' => 0,
        );
    }

}

class RokGallery_Job_StateMap_Errored extends RokGallery_Job_StateMap_Default {

    public function Delete($fsm) {
        $ctxt = $fsm->getOwner();
        if ($fsm->getDebugFlag() == true) {
            fwrite($fsm->getDebugStream(), "TRANSITION   : RokGallery_Job_StateMap::\$Errored->Delete();\n");
        }
        $fsm->getState()->Exit_($fsm);
        $fsm->clearState();
        $exception = NULL;
        try {
            $ctxt->deleteJob();
        }
        catch (Exception $exception) {}
        $fsm->setState(RokGallery_Job_StateMap::$Deleting);
        $fsm->getState()->Entry($fsm);
        if ($exception != NULL) {
            throw $exception;
        }
    }
    public function getTransitions() {
        return array(
            'Cancel' => 0,
            'Canceled' => 0,
            'Complete' => 0,
            'Default' => 2,
            'Delete' => 1,
            'Deleted' => 0,
            'Error' => 0,
            'Pause' => 0,
            'Paused' => 0,
            'Ready' => 0,
            'Resume' => 0,
            'Run' => 0,
        );
    }

}

class RokGallery_Job_StateMap_Canceled extends RokGallery_Job_StateMap_Default {

    public function Delete($fsm) {
        $ctxt = $fsm->getOwner();
        if ($fsm->getDebugFlag() == true) {
            fwrite($fsm->getDebugStream(), "TRANSITION   : RokGallery_Job_StateMap::\$Canceled->Delete();\n");
        }
        $fsm->getState()->Exit_($fsm);
        $fsm->clearState();
        $exception = NULL;
        try {
            $ctxt->deleteJob();
        }
        catch (Exception $exception) {}
        $fsm->setState(RokGallery_Job_StateMap::$Deleting);
        $fsm->getState()->Entry($fsm);
        if ($exception != NULL) {
            throw $exception;
        }
    }
    public function getTransitions() {
        return array(
            'Cancel' => 0,
            'Canceled' => 0,
            'Complete' => 0,
            'Default' => 2,
            'Delete' => 1,
            'Deleted' => 0,
            'Error' => 0,
            'Pause' => 0,
            'Paused' => 0,
            'Ready' => 0,
            'Resume' => 0,
            'Run' => 0,
        );
    }

}

class RokGallery_Job_StateMap_Completed extends RokGallery_Job_StateMap_Default {

    public function Delete($fsm) {
        $ctxt = $fsm->getOwner();
        if ($fsm->getDebugFlag() == true) {
            fwrite($fsm->getDebugStream(), "TRANSITION   : RokGallery_Job_StateMap::\$Completed->Delete();\n");
        }
        $fsm->getState()->Exit_($fsm);
        $fsm->clearState();
        $exception = NULL;
        try {
            $ctxt->startDeletingJob();
        }
        catch (Exception $exception) {}
        $fsm->setState(RokGallery_Job_StateMap::$Deleting);
        $fsm->getState()->Entry($fsm);
        if ($exception != NULL) {
            throw $exception;
        }
    }
    public function getTransitions() {
        return array(
            'Cancel' => 0,
            'Canceled' => 0,
            'Complete' => 0,
            'Default' => 2,
            'Delete' => 1,
            'Deleted' => 0,
            'Error' => 0,
            'Pause' => 0,
            'Paused' => 0,
            'Ready' => 0,
            'Resume' => 0,
            'Run' => 0,
        );
    }

}

class RokGallery_Job_StateMap_Deleting extends RokGallery_Job_StateMap_Default {

    public function Deleted($fsm) {
        $ctxt = $fsm->getOwner();
        if ($fsm->getDebugFlag() == true) {
            fwrite($fsm->getDebugStream(), "TRANSITION   : RokGallery_Job_StateMap::\$Deleting->Deleted();\n");
        }
        $fsm->getState()->Exit_($fsm);
        $fsm->clearState();
        $exception = NULL;
        try {
            $ctxt->deleteJob();
        }
        catch (Exception $exception) {}
        $fsm->setState(RokGallery_Job_StateMap::$Deleted);
        $fsm->getState()->Entry($fsm);
        if ($exception != NULL) {
            throw $exception;
        }
    }
    public function getTransitions() {
        return array(
            'Cancel' => 0,
            'Canceled' => 0,
            'Complete' => 0,
            'Default' => 2,
            'Delete' => 0,
            'Deleted' => 1,
            'Error' => 0,
            'Pause' => 0,
            'Paused' => 0,
            'Ready' => 0,
            'Resume' => 0,
            'Run' => 0,
        );
    }

}

class RokGallery_Job_StateMap_Deleted extends RokGallery_Job_StateMap_Default {

    public function getTransitions() {
        return array(
            'Cancel' => 0,
            'Canceled' => 0,
            'Complete' => 0,
            'Default' => 2,
            'Delete' => 0,
            'Deleted' => 0,
            'Error' => 0,
            'Pause' => 0,
            'Paused' => 0,
            'Ready' => 0,
            'Resume' => 0,
            'Run' => 0,
        );
    }

}

class RokGallery_Job_StateMap {
    public static $Prepping;
    public static $Ready;
    public static $Running;
    public static $Pausing;
    public static $Paused;
    public static $Canceling;
    public static $Errored;
    public static $Canceled;
    public static $Completed;
    public static $Deleting;
    public static $Deleted;
    public static $Default_;
}
RokGallery_Job_StateMap::$Prepping = new RokGallery_Job_StateMap_Prepping('RokGallery_Job_StateMap.Prepping', 0);
RokGallery_Job_StateMap::$Ready = new RokGallery_Job_StateMap_Ready('RokGallery_Job_StateMap.Ready', 1);
RokGallery_Job_StateMap::$Running = new RokGallery_Job_StateMap_Running('RokGallery_Job_StateMap.Running', 2);
RokGallery_Job_StateMap::$Pausing = new RokGallery_Job_StateMap_Pausing('RokGallery_Job_StateMap.Pausing', 3);
RokGallery_Job_StateMap::$Paused = new RokGallery_Job_StateMap_Paused('RokGallery_Job_StateMap.Paused', 4);
RokGallery_Job_StateMap::$Canceling = new RokGallery_Job_StateMap_Canceling('RokGallery_Job_StateMap.Canceling', 5);
RokGallery_Job_StateMap::$Errored = new RokGallery_Job_StateMap_Errored('RokGallery_Job_StateMap.Errored', 6);
RokGallery_Job_StateMap::$Canceled = new RokGallery_Job_StateMap_Canceled('RokGallery_Job_StateMap.Canceled', 7);
RokGallery_Job_StateMap::$Completed = new RokGallery_Job_StateMap_Completed('RokGallery_Job_StateMap.Completed', 8);
RokGallery_Job_StateMap::$Deleting = new RokGallery_Job_StateMap_Deleting('RokGallery_Job_StateMap.Deleting', 9);
RokGallery_Job_StateMap::$Deleted = new RokGallery_Job_StateMap_Deleted('RokGallery_Job_StateMap.Deleted', 10);
RokGallery_Job_StateMap::$Default_ = new RokGallery_Job_StateMap_Default('RokGallery_Job_StateMap.Default_', -1);

class RokGallery_Job_StateMachine extends RokCommon_State_Context {

    protected $_owner;

    public function __sleep()
    {
        return array('_state', '_previous_state', '_state_stack', '_transition', '_debug_flag');
    }


    public function __construct($owner) {
        parent::__construct(RokGallery_Job_StateMap::$Prepping);
        $this->_owner = $owner;
    }

    public function Cancel() {
        $this->_transition = "Cancel";
        $this->getState()->Cancel($this);
        $this->_transition = NULL;
    }

    public function Canceled() {
        $this->_transition = "Canceled";
        $this->getState()->Canceled($this);
        $this->_transition = NULL;
    }

    public function Complete() {
        $this->_transition = "Complete";
        $this->getState()->Complete($this);
        $this->_transition = NULL;
    }

    public function Delete() {
        $this->_transition = "Delete";
        $this->getState()->Delete($this);
        $this->_transition = NULL;
    }

    public function Deleted() {
        $this->_transition = "Deleted";
        $this->getState()->Deleted($this);
        $this->_transition = NULL;
    }

    public function Error() {
        $this->_transition = "Error";
        $this->getState()->Error($this);
        $this->_transition = NULL;
    }

    public function Pause() {
        $this->_transition = "Pause";
        $this->getState()->Pause($this);
        $this->_transition = NULL;
    }

    public function Paused() {
        $this->_transition = "Paused";
        $this->getState()->Paused($this);
        $this->_transition = NULL;
    }

    public function Ready() {
        $this->_transition = "Ready";
        $this->getState()->Ready($this);
        $this->_transition = NULL;
    }

    public function Resume() {
        $this->_transition = "Resume";
        $this->getState()->Resume($this);
        $this->_transition = NULL;
    }

    public function Run() {
        $this->_transition = "Run";
        $this->getState()->Run($this);
        $this->_transition = NULL;
    }

    public function getState() {
        if ($this->_state == NULL) {
            throw new StateUndefinedException();
        }
        return $this->_state;
    }

    public function enterStartState() {
        $this->_state->Entry($this);
    }

    public function getOwner() {
        return $this->_owner;
    }

    public function setOwner(RokGallery_Job $owner){
        $this->_owner = $owner;
    }

}

/*
 Local variables:
  buffer-read-only: t
 End:
*/
