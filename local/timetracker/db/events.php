<?php
defined('MOODLE_INTERNAL') || die();

$observers = array(
    array(
        'eventname'   => '\core\event\course_viewed',
        'callback'    => 'local_timetracker_observer_course_viewed',
    ),
    array(
        'eventname'   => '\core\event\course_module_viewed',
        'callback'    => 'local_timetracker_observer_course_module_viewed', // Changed to course_module_viewed
    ),
);
