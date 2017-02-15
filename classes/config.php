<?php
namespace assignsubmission_upchecker;

defined('MOODLE_INTERNAL') || die();

class config {
    public static $keys = [
        'maxfiles',
        'maxsizebytes',
        'caution',
        'example',
        'hint',
        'duedate',
        'storagelogin',
        'storagepassword',
        'lategrade',
        'checkurl',
        'fileparam',
        'restparams',
        'gradetype',
        'gradetag',
        'feedbacktag',
        'questionurl',
        'uploadfilename',
        'storagetype'
    ];

    public $data;

    public function __construct($data) {
        if ($data instanceof \assign_submission_upchecker) {
            foreach (self::$keys as $k) {
                $this->data[$k] = $data->get_config($k);
            }
        } else {
            foreach (get_object_vars($data) as $k => $v) {
                if (preg_match('/^assignsubmission_upchecker_(.*)/', $k, $m)) {
                    $this->data[$m[1]] = $v;
                }
            }
        }
    }

    public function __get($name) {
        if (isset($this->data[$name]))
            return $this->data[$name];
    }

    public function apply(\assign_submission_upchecker $upchecker) {
        foreach ($this->data as $k => $v) {
            $upchecker->set_config($k, $v);
        }
    }

    public function setDefaults(\MoodleQuickForm $form) {
        foreach (self::$keys as $k)
            $form->setDefault('assignsubmission_upchecker_'.$k, $this->data[$k]);
    }
}
