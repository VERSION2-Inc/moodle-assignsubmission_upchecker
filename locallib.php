<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * This file contains the definition for the library class for file submission plugin
 *
 * This class provides all the functionality for the new assign module.
 *
 * @package assignsubmission_upchecker
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use assignsubmission_upchecker\config;
use assignsubmission_upchecker\upchecker;
use assignsubmission_upchecker\remote_grading;
use assignsubmission_upchecker\dropbox;

require_once $CFG->libdir . '/questionlib.php';

require_once($CFG->libdir.'/eventslib.php');

defined('MOODLE_INTERNAL') || die();

// File areas for file submission assignment.
define('ASSIGNSUBMISSION_UPCHECKER_MAXFILES', 20);
define('ASSIGNSUBMISSION_UPCHECKER_MAXSUMMARYFILES', 5);
define('ASSIGNSUBMISSION_UPCHECKER_FILEAREA', 'submission_files');

/**
 * Library class for file submission plugin extending submission plugin base class
 *
 * @package   assignsubmission_upchecker
 * @copyright 2012 NetSpot {@link http://www.netspot.com.au}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class assign_submission_upchecker extends assign_submission_plugin {
    private $data;

    /**
     * Get the name of the file submission plugin
     * @return string
     */
    public function get_name() {
        return get_string('upchecker', 'assignsubmission_upchecker');
    }

    /**
     * Get file submission information from the database
     *
     * @param int $submissionid
     * @return mixed
     */
    private function get_upchecker_submission($submissionid) {
        global $DB;
        return $DB->get_record('assignsubmission_upchecker', array('submission'=>$submissionid));
    }

    /**
     * Get the default setting for file submission plugin
     *
     * @param MoodleQuickForm $mform The form to add elements to
     * @return void
     */
    public function get_settings(MoodleQuickForm $mform) {
        global $CFG, $COURSE;

        $defaultmaxfilesubmissions = $this->get_config('maxfiles');
        $defaultmaxsubmissionsizebytes = $this->get_config('maxsizebytes');

        $settings = array();
        $options = array();
        for ($i = 1; $i <= ASSIGNSUBMISSION_UPCHECKER_MAXFILES; $i++) {
            $options[$i] = $i;
        }

        $name = get_string('maxfilessubmission', 'assignsubmission_upchecker');
        $mform->addElement('select', 'assignsubmission_upchecker_maxfiles', $name, $options);
        $mform->addHelpButton('assignsubmission_upchecker_maxfiles',
                              'maxfilessubmission',
                              'assignsubmission_upchecker');
        $mform->setDefault('assignsubmission_upchecker_maxfiles', $defaultmaxfilesubmissions);
        $mform->disabledIf('assignsubmission_upchecker_maxfiles', 'assignsubmission_upchecker_enabled', 'notchecked');

        $choices = get_max_upload_sizes($CFG->maxbytes,
                                        $COURSE->maxbytes,
                                        get_config('assignsubmission_upchecker', 'maxbytes'));

        $settings[] = array('type' => 'select',
                            'name' => 'maxsubmissionsizebytes',
                            'description' => get_string('maximumsubmissionsize', 'assignsubmission_upchecker'),
                            'options'=> $choices,
                            'default'=> $defaultmaxsubmissionsizebytes);

        $name = get_string('maximumsubmissionsize', 'assignsubmission_upchecker');
        $mform->addElement('select', 'assignsubmission_upchecker_maxsizebytes', $name, $choices);
        $mform->addHelpButton('assignsubmission_upchecker_maxsizebytes',
                              'maximumsubmissionsize',
                              'assignsubmission_upchecker');
        $mform->setDefault('assignsubmission_upchecker_maxsizebytes', $defaultmaxsubmissionsizebytes);
        $mform->disabledIf('assignsubmission_upchecker_maxsizebytes',
                           'assignsubmission_upchecker_enabled',
                           'notchecked');

        // PG 問題項目
        $f = $mform;
        $pfx = 'assignsubmission_upchecker_';
        $enabledelm = 'assignsubmission_upchecker_enabled';
        $textareaattr = ['cols' => 50, 'rows' => 6];

        $f->addElement('textarea', $pfx.'caution', upchecker::str('caution'), $textareaattr);
        $f->disabledIf($pfx.'caution', $enabledelm, 'notchecked');
        $f->addElement('textarea', $pfx.'example', upchecker::str('example'), $textareaattr);
        $f->disabledIf($pfx.'example', $enabledelm, 'notchecked');
        $f->addElement('textarea', $pfx.'hint', upchecker::str('hint'), $textareaattr);
        $f->disabledIf($pfx.'hint', $enabledelm, 'notchecked');
//         $f->addElement('date_time_selector', $pfx.'duedate', upchecker::str('limitdatetime'),
//             ['optional' => true]);
//         $mform->addElement('select', 'permitlate',
//                 get_string('afterlimit', 'qtype_upchecker'),
//                 array(1 => get_string('accept', 'qtype_upchecker'),
//                         0 => get_string('notaccept',
//                                 'qtype_upchecker')));
//         $f->setType('permitlate', PARAM_INT);

        $gradeopts = question_bank::fraction_options();
        $f->addElement('select', $pfx.'lategrade',
                get_string('limitpoint', 'qtype_upchecker'),
                $gradeopts);
        $f->disabledIf($pfx.'lategrade', $enabledelm, 'notchecked');

//         $f->setType('lategrade', PARAM_NUMBER);

        $f->addElement('text', $pfx.'checkurl',
                get_string('checkurl', 'qtype_upchecker'),
                array('size' => 60));
        $f->setType($pfx.'checkurl', PARAM_URL);
        $f->addHelpButton($pfx.'checkurl', 'checkurl', 'qtype_upchecker');
        $f->disabledIf($pfx.'checkurl', $enabledelm, 'notchecked');

        $f->addElement('text', $pfx.'fileparam',
                get_string('filepostname', 'qtype_upchecker'));
        $f->setType($pfx.'fileparam', PARAM_TEXT);
        $f->addHelpButton($pfx.'fileparam', 'filepostname', 'qtype_upchecker');
        $f->disabledIf($pfx.'fileparam', $enabledelm, 'notchecked');

        $f->addElement('text', $pfx.'restparams',
                get_string('restparams', 'qtype_upchecker'),
                array('size' => 60));
        $f->setType($pfx.'restparams', PARAM_TEXT);
        $f->addHelpButton($pfx.'restparams', 'restparams', 'qtype_upchecker');
        $f->disabledIf($pfx.'restparams', $enabledelm, 'notchecked');

        $gradetypes = array(
            upchecker::GRADE_MANUAL => upchecker::str('manualgrading'),
            upchecker::GRADE_XML => upchecker::str('xml'),
            upchecker::GRADE_TEXT => upchecker::str('text')
        );
        $f->addElement('select', $pfx.'gradetype',
                get_string('markingmethod', 'qtype_upchecker'),
                $gradetypes);
        $f->addHelpButton($pfx.'gradetype', 'markingmethod', 'qtype_upchecker');
//         $f->setType('gradetype', PARAM_INT);
        $f->disabledIf($pfx.'gradetype', $enabledelm, 'notchecked');

        $f->addElement('text', $pfx.'gradetag',
                get_string('xmlgradeelement', 'qtype_upchecker'));
        $f->setType($pfx.'gradetag', PARAM_TEXT);
        $f->addHelpButton($pfx.'gradetag', 'xmlgradeelement', 'qtype_upchecker');
        $f->disabledIf($pfx.'gradetag', $enabledelm, 'notchecked');
        $f->addElement('text', $pfx.'feedbacktag',
                get_string('xmlfeedbackelement', 'qtype_upchecker'));
        $f->setType($pfx.'feedbacktag', PARAM_TEXT);
        $f->addHelpButton($pfx.'feedbacktag', 'xmlfeedbackelement', 'qtype_upchecker');
        $f->disabledIf($pfx.'feedbacktag', $enabledelm, 'notchecked');
        /*
         $f->addElement('text', 'answertag',
                 get_string('xmlanswerelement', 'qtype_upchecker'));
        $f->setType('answertag', PARAM_TEXT);
        $f->setHelpButton(
                'answertag',
                array('answertag',
                        get_string('xmlanswerelement', 'qtype_upchecker'),
                        'qtype_upchecker'));
        */

//         $f->addElement('text', $pfx.'questionurl', upchecker::str('questionhtml'),
//                 array('size' => 60));
        $f->setType($pfx.'questionurl', PARAM_TEXT);
        $f->disabledIf($pfx.'questionurl', $enabledelm, 'notchecked');
        $f->addElement('text', $pfx.'uploadfilename', upchecker::str('uploadfilename'),
                array('size' => 60));
        $f->setType($pfx.'uploadfilename', PARAM_TEXT);
        $f->addHelpButton($pfx.'uploadfilename', 'uploadfilename', 'qtype_upchecker');
        $f->disabledIf($pfx.'uploadfilename', $enabledelm, 'notchecked');

        //        $f->addElement('static', 'answersinstruct', get_string('correctanswers', 'quiz'), get_string('filloutoneanswer', 'quiz'));
//         $f->closeHeaderBefore('answersinstruct');

        $opts = array(
            upchecker::STORAGE_MOODLE => upchecker::str('moodle'),
            upchecker::STORAGE_DROPBOX => upchecker::str('dropbox')
        );
        $f->addElement('select', $pfx.'storagetype', upchecker::str('storagetype'), $opts);
        $f->setType($pfx.'storagetype', PARAM_ALPHA);
        $f->disabledIf($pfx.'storagetype', $enabledelm, 'notchecked');
//         $f->setDefault('storagetype', 'dropbox');

        $f->addElement('hidden', $pfx.'storagelogin', '');
        $f->setType($pfx.'storagelogin', PARAM_TEXT);
        $f->addElement('hidden', $pfx.'storagepassword', '');
        $f->setType($pfx.'storagepassword', PARAM_TEXT);

        $c = new config($this);
        $c->setDefaults($f);

        //XXX
//         $cmt = $this->assignment->get_feedback_plugin_by_type('comments');
//         var_dump($cmt);
//         var_dump($cmt->is_enabled());
    }

    /**
     * Save the settings for file submission plugin
     *
     * @param stdClass $data
     * @return bool
     */
    public function save_settings(stdClass $data) {
        $c = new config($data);
        $c->apply($this);
        return true;
    }

    /**
     * File format options
     *
     * @return array
     */
    private function get_file_options() {
        $fileoptions = array('subdirs'=>1,
                                'maxbytes'=>$this->get_config('maxsizebytes'),
                                'maxfiles'=>$this->get_config('maxfiles'),
                                'accepted_types'=>'*',
                                'return_types'=>FILE_INTERNAL);
        if ($fileoptions['maxbytes'] == 0) {
            // Use module default.
            $fileoptions['maxbytes'] = get_config('assignsubmission_upchecker', 'maxbytes');
        }
        return $fileoptions;
    }

    /**
     * Add elements to submission form
     *
     * @param mixed $submission stdClass|null
     * @param MoodleQuickForm $mform
     * @param stdClass $data
     * @return bool
     */
    public function get_form_elements($submission, MoodleQuickForm $mform, stdClass $data) {

        $mform->addElement('static', 'caution', upchecker::str('caution'),
            format_text($this->get_config('caution'), FORMAT_PLAIN));
        $mform->addElement('static', 'example', upchecker::str('example'),
            format_text($this->get_config('example'), FORMAT_PLAIN));
        $mform->addElement('static', 'hint', upchecker::str('hint'),
            format_text($this->get_config('hint'), FORMAT_PLAIN));

        if ($this->get_config('maxfiles') <= 0) {
            return false;
        }

        $fileoptions = $this->get_file_options();
        $submissionid = $submission ? $submission->id : 0;

        $data = file_prepare_standard_filemanager($data,
                                                  'files',
                                                  $fileoptions,
                                                  $this->assignment->get_context(),
                                                  'assignsubmission_upchecker',
                                                  ASSIGNSUBMISSION_UPCHECKER_FILEAREA,
                                                  $submissionid);
        $mform->addElement('filemanager', 'files_filemanager', $this->get_name(), null, $fileoptions);

        return true;
    }

    /**
     * Count the number of files
     *
     * @param int $submissionid
     * @param string $area
     * @return int
     */
    private function count_files($submissionid, $area) {

        $fs = get_file_storage();
        $files = $fs->get_area_files($this->assignment->get_context()->id,
                                     'assignsubmission_upchecker',
                                     $area,
                                     $submissionid,
                                     'id',
                                     false);

        return count($files);
    }

    /**
     * Save the files and trigger plagiarism plugin, if enabled,
     * to scan the uploaded files via events trigger
     *
     * @param stdClass $submission
     * @param stdClass $data
     * @return bool
     */
    public function save(stdClass $submission, stdClass $data) {
        global $USER, $DB;

        $fileoptions = $this->get_file_options();

        $data = file_postupdate_standard_filemanager($data,
                                                     'files',
                                                     $fileoptions,
                                                     $this->assignment->get_context(),
                                                     'assignsubmission_upchecker',
                                                     ASSIGNSUBMISSION_UPCHECKER_FILEAREA,
                                                     $submission->id);

        $upcheckersubmission = $this->get_upchecker_submission($submission->id);

        // Plagiarism code event trigger when files are uploaded.

        $fs = get_file_storage();
        $files = $fs->get_area_files($this->assignment->get_context()->id,
                                     'assignsubmission_upchecker',
                                     ASSIGNSUBMISSION_UPCHECKER_FILEAREA,
                                     $submission->id,
                                     'id',
                                     false);

        foreach ($files as $file) {
            $this->store_file($file);
        }

        $rg = new remote_grading();
        $checkurl = $this->get_config('checkurl');
        $rg->post_file($checkurl, 'file', reset($files), '');
        $result = $rg->get_result();

        $gradetype = $this->get_config('gradetype');
        switch ($gradetype) {
            case upchecker::GRADE_XML:
                $parseopts = (object)[
                    'gradetag' => $this->get_config('gradetag'),
                    'feedbacktag' => $this->get_config('feedbacktag')
                ];
                $grading = $rg->parse_result_xml($parseopts);

                // グレード書き込み
                $grade = $this->assignment->get_user_grade($submission->userid, true);
                // 期限超過してたら減らす
                $assinst = $this->assignment->get_instance();

                if (time() > $assinst->duedate)
                    $grade->grade = $grading->grade * $this->get_config('lategrade');
                else
                    $grade->grade = $grading->grade;

                $this->assignment->update_grade($grade);

                $grading->feedback .= ' rawgrade: '.$grading->grade;

                // フィードバック書き込み
                /* @var $plgcm assign_feedback_comments */
                $plgcm = $this->assignment->get_feedback_plugin_by_type('comments');
                if ($plgcm->is_enabled()) {
                    $cmt = $plgcm->get_feedback_comments($grade->id);
                    if ($cmt) {
                        $cmt->commenttext = $grading->feedback;
                        $DB->update_record('assignfeedback_comments', $cmt);
                    } else {
                        $cmt = (object)[
                            'assignment' => $this->assignment->get_instance()->id,
                            'grade' => $grade->id,
                            'commenttext' => $grading->feedback
                        ];
                        $DB->insert_record('assignfeedback_comments', $cmt);
                    }
                }

                break;

            case upchecker::GRADE_TEXT:
                $grading = $rg->parse_result_text();
                break;
        }


//         die;

        $count = $this->count_files($submission->id, ASSIGNSUBMISSION_UPCHECKER_FILEAREA);

        $params = array(
            'context' => context_module::instance($this->assignment->get_course_module()->id),
            'courseid' => $this->assignment->get_course()->id,
            'objectid' => $submission->id,
            'other' => array(
                'content' => '',
                'pathnamehashes' => array_keys($files)
            )
        );
        if (!empty($submission->userid) && ($submission->userid != $USER->id)) {
            $params['relateduserid'] = $submission->userid;
        }
        $event = \assignsubmission_upchecker\event\assessable_uploaded::create($params);
        $event->set_legacy_files($files);
        $event->trigger();

        $groupname = null;
        $groupid = 0;
        // Get the group name as other fields are not transcribed in the logs and this information is important.
        if (empty($submission->userid) && !empty($submission->groupid)) {
            $groupname = $DB->get_field('groups', 'name', array('id' => $submission->groupid), '*', MUST_EXIST);
            $groupid = $submission->groupid;
        } else {
            $params['relateduserid'] = $submission->userid;
        }

        // Unset the objectid and other field from params for use in submission events.
        unset($params['objectid']);
        unset($params['other']);
        $params['other'] = array(
            'submissionid' => $submission->id,
            'submissionattempt' => $submission->attemptnumber,
            'submissionstatus' => $submission->status,
            'filesubmissioncount' => $count,
            'groupid' => $groupid,
            'groupname' => $groupname
        );

        if ($upcheckersubmission) {
            $upcheckersubmission->numfiles = $this->count_files($submission->id,
                                                           ASSIGNSUBMISSION_UPCHECKER_FILEAREA);
            $updatestatus = $DB->update_record('assignsubmission_upchecker', $upcheckersubmission);
            $params['objectid'] = $upcheckersubmission->id;

            $event = \assignsubmission_upchecker\event\submission_updated::create($params);
            $event->set_assign($this->assignment);
            $event->trigger();
            return $updatestatus;
        } else {
            $upcheckersubmission = new stdClass();
            $upcheckersubmission->numfiles = $this->count_files($submission->id,
                                                           ASSIGNSUBMISSION_UPCHECKER_FILEAREA);
            $upcheckersubmission->submission = $submission->id;
            $upcheckersubmission->assignment = $this->assignment->get_instance()->id;
            $upcheckersubmission->id = $DB->insert_record('assignsubmission_upchecker', $upcheckersubmission);
            $params['objectid'] = $upcheckersubmission->id;

            $event = \assignsubmission_upchecker\event\submission_created::create($params);
            $event->set_assign($this->assignment);
            $event->trigger();
            return $upcheckersubmission->id > 0;
        }
    }

    /**
     * Produce a list of files suitable for export that represent this feedback or submission
     *
     * @param stdClass $submission The submission
     * @param stdClass $user The user record - unused
     * @return array - return an array of files indexed by filename
     */
    public function get_files(stdClass $submission, stdClass $user) {
        $result = array();
        $fs = get_file_storage();

        $files = $fs->get_area_files($this->assignment->get_context()->id,
                                     'assignsubmission_upchecker',
                                     ASSIGNSUBMISSION_UPCHECKER_FILEAREA,
                                     $submission->id,
                                     'timemodified',
                                     false);

        foreach ($files as $file) {
            $result[$file->get_filename()] = $file;
        }
        return $result;
    }

    /**
     * Display the list of files  in the submission status table
     *
     * @param stdClass $submission
     * @param bool $showviewlink Set this to true if the list of files is long
     * @return string
     */
    public function view_summary(stdClass $submission, & $showviewlink) {
        //TODO
//         return 'VIEW SUMMARY';
        $count = $this->count_files($submission->id, ASSIGNSUBMISSION_UPCHECKER_FILEAREA);

        // Show we show a link to view all files for this plugin?
        $showviewlink = $count > ASSIGNSUBMISSION_UPCHECKER_MAXSUMMARYFILES;
        if ($count <= ASSIGNSUBMISSION_UPCHECKER_MAXSUMMARYFILES) {
            return $this->assignment->render_area_files('assignsubmission_upchecker',
                                                        ASSIGNSUBMISSION_UPCHECKER_FILEAREA,
                                                        $submission->id);
        } else {
            return get_string('countfiles', 'assignsubmission_upchecker', $count);
        }
    }

    /**
     * No full submission view - the summary contains the list of files and that is the whole submission
     *
     * @param stdClass $submission
     * @return string
     */
    public function view(stdClass $submission) {
        return $this->assignment->render_area_files('assignsubmission_upchecker',
                                                    ASSIGNSUBMISSION_UPCHECKER_FILEAREA,
                                                    $submission->id);
    }

    public function view_header() {
        $duedate = $this->get_config('duedate');
        $lategrade = $this->get_config('lategrade');

        if ($duedate)
            return upchecker::str('limitpoint').': '.upchecker::format_percentage($lategrade);
        return '';
    }

    /**
     * Return true if this plugin can upgrade an old Moodle 2.2 assignment of this type
     * and version.
     *
     * @param string $type
     * @param int $version
     * @return bool True if upgrade is possible
     */
    public function can_upgrade($type, $version) {

        $uploadsingletype ='uploadsingle';
        $uploadtype ='upload';

        if (($type == $uploadsingletype || $type == $uploadtype) && $version >= 2011112900) {
            return true;
        }
        return false;
    }


    /**
     * Upgrade the settings from the old assignment
     * to the new plugin based one
     *
     * @param context $oldcontext - the old assignment context
     * @param stdClass $oldassignment - the old assignment data record
     * @param string $log record log events here
     * @return bool Was it a success? (false will trigger rollback)
     */
    public function upgrade_settings(context $oldcontext, stdClass $oldassignment, & $log) {
        global $DB;

        if ($oldassignment->assignmenttype == 'uploadsingle') {
            $this->set_config('maxfilesubmissions', 1);
            $this->set_config('maxsubmissionsizebytes', $oldassignment->maxbytes);
            return true;
        } else if ($oldassignment->assignmenttype == 'upload') {
            $this->set_config('maxfilesubmissions', $oldassignment->var1);
            $this->set_config('maxsubmissionsizebytes', $oldassignment->maxbytes);

            // Advanced file upload uses a different setting to do the same thing.
            $DB->set_field('assign',
                           'submissiondrafts',
                           $oldassignment->var4,
                           array('id'=>$this->assignment->get_instance()->id));

            // Convert advanced file upload "hide description before due date" setting.
            $alwaysshow = 0;
            if (!$oldassignment->var3) {
                $alwaysshow = 1;
            }
            $DB->set_field('assign',
                           'alwaysshowdescription',
                           $alwaysshow,
                           array('id'=>$this->assignment->get_instance()->id));
            return true;
        }
    }

    /**
     * Upgrade the submission from the old assignment to the new one
     *
     * @param context $oldcontext The context of the old assignment
     * @param stdClass $oldassignment The data record for the old oldassignment
     * @param stdClass $oldsubmission The data record for the old submission
     * @param stdClass $submission The data record for the new submission
     * @param string $log Record upgrade messages in the log
     * @return bool true or false - false will trigger a rollback
     */
    public function upgrade(context $oldcontext,
                            stdClass $oldassignment,
                            stdClass $oldsubmission,
                            stdClass $submission,
                            & $log) {
        global $DB;

        $upcheckersubmission = new stdClass();

        $upcheckersubmission->numfiles = $oldsubmission->numfiles;
        $upcheckersubmission->submission = $submission->id;
        $upcheckersubmission->assignment = $this->assignment->get_instance()->id;

        if (!$DB->insert_record('assignsubmission_upchecker', $upcheckersubmission) > 0) {
            $log .= get_string('couldnotconvertsubmission', 'mod_assign', $submission->userid);
            return false;
        }

        // Now copy the area files.
        $this->assignment->copy_area_files_for_upgrade($oldcontext->id,
                                                        'mod_assignment',
                                                        'submission',
                                                        $oldsubmission->id,
                                                        $this->assignment->get_context()->id,
                                                        'assignsubmission_upchecker',
                                                        ASSIGNSUBMISSION_UPCHECKER_FILEAREA,
                                                        $submission->id);

        return true;
    }

    /**
     * The assignment has been deleted - cleanup
     *
     * @return bool
     */
    public function delete_instance() {
        global $DB;
        // Will throw exception on failure.
        $DB->delete_records('assignsubmission_upchecker',
                            array('assignment'=>$this->assignment->get_instance()->id));

        return true;
    }

    /**
     * Formatting for log info
     *
     * @param stdClass $submission The submission
     * @return string
     */
    public function format_for_log(stdClass $submission) {
        // Format the info for each submission plugin (will be added to log).
        $filecount = $this->count_files($submission->id, ASSIGNSUBMISSION_UPCHECKER_FILEAREA);

        return get_string('numfilesforlog', 'assignsubmission_upchecker', $filecount);
    }

    /**
     * Return true if there are no submission files
     * @param stdClass $submission
     */
    public function is_empty(stdClass $submission) {
        return $this->count_files($submission->id, ASSIGNSUBMISSION_UPCHECKER_FILEAREA) == 0;
    }

    /**
     * Get file areas returns a list of areas this plugin stores files
     * @return array - An array of fileareas (keys) and descriptions (values)
     */
    public function get_file_areas() {
        return array(ASSIGNSUBMISSION_UPCHECKER_FILEAREA=>$this->get_name());
    }

    /**
     * Copy the student's submission from a previous submission. Used when a student opts to base their resubmission
     * on the last submission.
     * @param stdClass $sourcesubmission
     * @param stdClass $destsubmission
     */
    public function copy_submission(stdClass $sourcesubmission, stdClass $destsubmission) {
        global $DB;

        // Copy the files across.
        $contextid = $this->assignment->get_context()->id;
        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid,
                                     'assignsubmission_upchecker',
                                     ASSIGNSUBMISSION_UPCHECKER_FILEAREA,
                                     $sourcesubmission->id,
                                     'id',
                                     false);
        foreach ($files as $file) {
            $fieldupdates = array('itemid' => $destsubmission->id);
            $fs->create_file_from_storedfile($fieldupdates, $file);
        }

        // Copy the assignsubmission_upchecker record.
        if ($upcheckersubmission = $this->get_upchecker_submission($sourcesubmission->id)) {
            unset($upcheckersubmission->id);
            $upcheckersubmission->submission = $destsubmission->id;
            $DB->insert_record('assignsubmission_upchecker', $upcheckersubmission);
        }
        return true;
    }

    /**
     * Return a description of external params suitable for uploading a file submission from a webservice.
     *
     * @return external_description|null
     */
    public function get_external_parameters() {
        return array(
            'files_filemanager' => new external_value(
                PARAM_INT,
                'The id of a draft area containing files for this submission.'
            )
        );
    }

    private function store_file(stored_file $file) {
        global $CFG, $DB, $COURSE;

        if ($this->get_config('storagetype') == upchecker::STORAGE_DROPBOX) {
            $tmpdir = make_temp_directory(upchecker::COMPONENT);
            $tmpfile = tempnam($tmpdir, 'asuc_');
            //TODO copy_content_to_temp というのもある
            $file->copy_content_to($tmpfile);

            $setting = $DB->get_record('block_upchecker_setting_crs', ['course' => $COURSE->id]);

            // TODO エラーチェック
            $dropbox = new dropbox([
                'access_token' => $setting->accesstoken,
                'access_token_secret' => $setting->accesssecret
            ]);

            $filename = $file->get_filename();

            $this->replaceparams = (object)array(
                    'origname' => pathinfo($filename, PATHINFO_FILENAME),
                    'origext' => pathinfo($filename, PATHINFO_EXTENSION)
            );

            $uploadas = '/'.$this->replace_upload_filename($this->get_config('uploadfilename'), $file);
            $result = $dropbox->put_file($tmpfile, $uploadas);

            if (!empty($result->error)) {
                debugging('Dropboxエラー: '.$result->error);
            }

            @unlink($tmpfile);
        }
    }

    public function replace_upload_filename($filename, stored_file $file) {
    return preg_replace_callback('/\{([a-z_]+?)\}/',
            // array($this, 'replace_upload_filename_callback'),
            function ($matches) use($file) {
                global $USER, $DB;

                $varname = $matches[1];
                // if ($this->resyncfileuser) {
                // $user = $this->resyncfileuser;
                // } else {
                // $user = $USER;
                // }
                if ($file) {
                    $user = $DB->get_record('user', [
                            'id' => $file->get_userid()
                    ]);
                } else {
                    $user = $USER;
                }
//                 $quizattempt = $this->get_quiz_attempt_by_file_item_id($file->get_itemid());

                switch ($varname) {
                    case 'filename':
                        // return $this->replaceparams->origname;
                        return pathinfo($file->get_filename(), PATHINFO_FILENAME);
                    case 'ext':
                        // return $this->replaceparams->origext;
                        return pathinfo($file->get_filename(), PATHINFO_EXTENSION);
                    case 'quizname':
                        $quiz = $this->get_quiz();
                        return $quiz->name;
                    case 'quizid':
                        $quiz = $this->get_quiz();
                        return $quiz->id;
                    case 'cmid':
                        $quiz = $this->get_quiz();
                        $cm = get_coursemodule_from_instance('quiz', $quiz->id);
                        return $cm->id;
                    case 'questionname':
                        return $this->name;
                    case 'questionid':
                        return $this->id;
                    case 'lastname':
                        return $user->lastname;
                    case 'firstname':
                        return $user->firstname;
                    case 'fullname':
                        return fullname($user);
                    case 'username':
                        return $user->username;
                    case 'idnumber':
                        return $user->idnumber;
                    case 'email':
                        return $user->email;
                    case 'institution':
                        return $user->institution;
                    case 'department':
                        return $user->department;
                    case 'date':
                        // return date('Y-m-d', $this->get_question_attempt_field('timemodified'));
// 							return date('Y-m-d', $file->get_timemodified());
//                             return date('Y-m-d', $quizattempt->timestart);
                        return date('Y-m-d');//TODO
                            case 'time':
                            // return date('H-i-s', $this->get_question_attempt_field('timemodified'));
// 							return date('H-i-s', $file->get_timemodified());
//                             return date('H-i-s', $quizattempt->timestart);
                                return date('H-i-s');//TODO
                        default:
                            return $matches[0];
                    }
            }, $filename);
    }
}
