<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Extends the course navigation to track when a course is viewed.
 *
 * @param navigation_node $navigation The course navigation node
 * @param stdClass $course The course object
 * @param context_course $context The course context
 */
function local_timetracker_extend_navigation_course($navigation, $course, $context) {
    global $DB, $USER;

    if (isloggedin() && !isguestuser()) {
        // Check if there's an existing open record (endtime = 0)
        $existingrecord = $DB->get_record_sql(
            "SELECT * FROM {local_timetracker} 
             WHERE userid = :userid 
             AND courseid = :courseid 
            --  AND moduleid IS NULL 
             AND endtime = 0",
            array('userid' => $USER->id, 'courseid' => $course->id)
        );

        if ($existingrecord) {
            // Update the endtime of the existing record
            $existingrecord->endtime = time();
            $DB->update_record('local_timetracker', $existingrecord);
        }

        // Insert a new record for the current course view
        $record = new stdClass();
        $record->userid = $USER->id;
        $record->courseid = $course->id;
        // $record->moduleid = null; // course view, not module
        $record->starttime = time();
        $record->endtime = 0; // will be updated when the user leaves the course

        $DB->insert_record('local_timetracker', $record);
    }
}

/**
 * Handles course viewed events.
 *
 * @param \core\event\course_viewed $event The event object
 */
function local_timetracker_observer_course_viewed(\core\event\course_viewed $event) {
    global $DB;

    $userid = $event->userid;
    $courseid = $event->courseid;

    // Update the endtime for the last record for this user and course
    $sql = "UPDATE {local_timetracker}
            SET endtime = :endtime
            WHERE userid = :userid
            AND courseid = :courseid
            -- AND moduleid IS NULL
            AND endtime = 0";

    $DB->execute($sql, array('endtime' => time(), 'userid' => $userid, 'courseid' => $courseid));

    // Insert a new record for the current course view
    $record = new stdClass();
    $record->userid = $userid;
    $record->courseid = $courseid;
    // $record->moduleid = null; // course view, not module
    $record->starttime = time();
    $record->endtime = 0;

    $DB->insert_record('local_timetracker', $record);
}

/**
 * Handles module viewed events.
 *
 * @param \core\event\course_module_viewed $event The event object
 */
function local_timetracker_observer_course_module_viewed(\core\event\course_module_viewed $event) {
    global $DB;

    $userid = $event->userid;
    $courseid = $event->courseid;
    // $moduleid = $event->objectid; // Use objectid for module ID

    // Update the endtime for the last record for this user and module
    $sql = "UPDATE {local_timetracker}
            SET endtime = :endtime
            WHERE userid = :userid
            AND courseid = :courseid
            AND moduleid = :moduleid
            AND endtime = 0";

    $DB->execute($sql, array('endtime' => time(), 'userid' => $userid, 'courseid' => $courseid, 'moduleid' => $moduleid));

    // Insert a new record for the current module view
    $record = new stdClass();
    $record->userid = $userid;
    $record->courseid = $courseid;
    // $record->moduleid = $moduleid; // Store the actual module ID
    $record->starttime = time();
    $record->endtime = 0;

    $DB->insert_record('local_timetracker', $record);
}
