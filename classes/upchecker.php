<?php
namespace assignsubmission_upchecker;

defined('MOODLE_INTERNAL') || die();

class upchecker {
    const COMPONENT = 'assignsubmission_upchecker';

    const GRADE_MANUAL = 'manual';
    const GRADE_XML = 'xml';
    const GRADE_TEXT = 'text';

    const STORAGE_MOODLE = 'moodle';
    const STORAGE_DROPBOX = 'dropbox';

    public static function str($identifier, $a = null) {
        return get_string($identifier, self::COMPONENT, $a);
    }

    public static function format_percentage($fraction) {
        return format_float($fraction * 100, 5, true, true) . '%';
    }
}
