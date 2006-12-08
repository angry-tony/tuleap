<?php
/**
 * Copyright (c) Xerox Corporation, CodeX Team, 2001-2005. All rights reserved
 * 
 * $Id$
 *
 * ServerUpdate
 */

require_once('common/mvc/Controler.class.php');
require_once('common/include/HTTPRequest.class.php');
require_once('ServerUpdateViews.class.php');
require_once('ServerUpdateActions.class.php');

$GLOBALS['Language']->loadLanguageMsg('serverUpdate', 'serverupdate');

class ServerUpdate extends Controler {
    
    /**
     * The SVNUpdate Object that contains data for updating the server
     * 
     */
    var $svnupdate;
    
    var $themePath;
    
    function ServerUpdate($themePath) {
        session_require(array('group'=>'1','admin_flags'=>'A'));
        $this->svnupdate = new SVNUpdate($GLOBALS['codex_dir']);
        $this->themePath = $themePath;
    }
    
    function getSVNUpdate() {
        return $this->svnupdate;
    }
    function setSVNUpdate($svnupdate) {
        $this->svnupdate = $svnupdate;
    }
    
    function getThemePath() {
        return $this->themePath;
    }
    
    function request() {
        $request =& HTTPRequest::instance();
        
        if ($request->exist('view')) {
            switch ($request->get('view')) {
                case 'preferences':
                    $this->view = 'preferences';
                    break;
                case 'upgrades':
                    $this->view = 'scriptupgrades';
                    // If javascript is disabled, we let the user expand and collapse the executions details by using a reload page 
                    if ($request->exist('open')) {
                        // open is the current script that has just been clicked
                        // opens is all the scripts that are already expanded
                        $GLOBALS['open'] = $request->get('open');
                        $GLOBALS['opens'] = $request->get('opens');
                        if (strpos($GLOBALS['opens'], ",") === false) {
                            $opens = array();
                            if ($GLOBALS['opens'] != "") {
                                $opens[] = $GLOBALS['opens'];
                            }
                        } else {
                            $opens = explode(",", $GLOBALS['opens']);
                        }
                        // This is a tip to manage the expand/collapse in only one array
                        // if a script is already in the array of expanded scripts, we have to collapse it, and so remove it from the array
                        if (in_array($GLOBALS['open'], $opens)) {
                            $opens = array_diff($opens, array($GLOBALS['open']));
                        } else {
                            $opens[] = $GLOBALS['open'];
                        }
                        $GLOBALS['opens'] = implode(",", $opens);
                    }
                    break;
                default:
                    if ($this->svnupdate->getRepository() == "") {
                        $this->view = 'norepository';
                    } else {
                        if ($request->exist('sort')) {
                            $GLOBALS['sort'] = $request->get('sort');
                            if ($request->exist('level_op')) {
                                $GLOBALS['level_op'] = $request->get('level_op');
                            }
                            if ($request->exist('level')) {
                                $GLOBALS['level'] = $request->get('level');
                            }
                            if ($request->exist('manualupdate')) {
                                $GLOBALS['manualupdate'] = $request->get('manualupdate');
                            }
                        }
                        $this->view = 'browse';
                    }
                    break;
            }
        } else {
            if ($this->svnupdate->getRepository() == "") {
                $this->view = 'norepository';
            } else {
                $this->view = 'browse';
            }
        }
        
        if ($request->exist('action')) {
            switch ($request->get('action')) {
                case 'testupdate':
                    if ($request->exist('revision')) {
                        // We test if we are not going back (reverse to old revision)
                        // which is not allowed. To do this, it's better to use the console.
                        if ($this->svnupdate->getWorkingCopyRevision() >= $request->get('revision')) {
                            exit_error($GLOBALS['Language']->getText('plugin_serverupdate_update','UpdateFailed'), $GLOBALS['Language']->getText('plugin_serverupdate_update','NoReverseUpdate'));
                        } else {
                            $this->view = 'testUpdate';
                        }
                    }
                    break;
                case 'processUpdate':
                    if ($request->exist('revision')) {
                        if (is_numeric($request->get('revision'))) {
                            // We check that a reverse update to a older revision is not attempted
                            if ($this->svnupdate->getWorkingCopyRevision() >= $request->get('revision')) {
                                exit_error($GLOBALS['Language']->getText('plugin_serverupdate_update','UpdateFailed'), $GLOBALS['Language']->getText('plugin_serverupdate_update','NoReverseUpdate'));
                            } else {
                                $this->view = 'processUpdate';
                            }
                        }
                    }
                    break;
                case 'executescript':
                    if ($request->exist('scriptname')) {
                        $GLOBALS['scriptname'] = $request->get('scriptname');
                        $this->view = 'executescript';
                    }
                    break;
                default:
                    break;
            }
        }
    }
}


?>
