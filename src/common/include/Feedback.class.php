<?php

class Feedback {
    var $logs;
    function Feedback() {
        $this->logs = array();
    }
    function log($level, $msg) {
        $this->logs[] = array('level' => $level, 'msg' => $msg);
    }
    function fetch() {
        $html = '';
        $old_level = null;
        foreach($this->logs as $log) {
            if (!is_null($old_level) && $old_level != $log['level']) {
                $html .= '</ul>';
            }
            if (is_null($old_level) || $old_level != $log['level']) {
                $old_level = $log['level'];
                $html .= '<ul class="feedback_'. $log['level'] .'">';
            }
            $html .= '<li>'. $log['msg'] .'</li>';
        }
        if (!is_null($old_level)) {
            $html .= '</ul>';
        }
        return $html;
    }
    function display() {
        echo $this->fetch();
    }
}

?>
