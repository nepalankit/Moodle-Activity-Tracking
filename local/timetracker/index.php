<?php
require_once('../../config.php');
require_login();
$context = context_system::instance();
require_capability('local/timetracker:view', $context);

$PAGE->set_context($context);
$PAGE->set_url('/local/timetracker/index.php');
$PAGE->set_title(get_string('viewreport', 'local_timetracker'));
$PAGE->set_heading(get_string('viewreport', 'local_timetracker'));

echo $OUTPUT->header();

global $DB;

if ($data = data_submitted()) {
    $userid = required_param('userid', PARAM_INT);

    $records = $DB->get_records('local_timetracker', array('userid' => $userid));

    echo html_writer::start_tag('table', array('class' => 'generaltable'));
    echo html_writer::start_tag('tr');
    echo html_writer::tag('th', get_string('studentname', 'local_timetracker'));
    echo html_writer::tag('th', get_string('courseid', 'local_timetracker'));
    echo html_writer::tag('th', get_string('moduleid', 'local_timetracker'));
    echo html_writer::tag('th', get_string('starttime', 'local_timetracker'));
    echo html_writer::tag('th', get_string('endtime', 'local_timetracker'));
    echo html_writer::end_tag('tr');

    foreach ($records as $record) {
        echo html_writer::start_tag('tr');
        echo html_writer::tag('td', fullname($DB->get_record('user', array('id' => $record->userid))));
        echo html_writer::tag('td', $record->courseid);
        echo html_writer::tag('td', isset($record->moduleid) ? $record->moduleid : '-'); // Check if moduleid is set
        echo html_writer::tag('td', date('Y-m-d H:i:s', $record->starttime));
        echo html_writer::tag('td', $record->endtime ? date('Y-m-d H:i:s', $record->endtime) : '-');
        echo html_writer::end_tag('tr');
    }

    echo html_writer::end_tag('table');
} else {
    $users = $DB->get_records('user', array('deleted' => 0), 'lastname ASC, firstname ASC');

    $options = array();
    foreach ($users as $user) {
        $options[$user->id] = fullname($user);
    }

    echo '<form method="post">';
    echo '<select name="userid">';
    foreach ($options as $id => $name) {
        echo '<option value="' . $id . '">' . $name . '</option>';
    }
    echo '</select>';
    echo '<input type="submit" value="' . get_string('viewreport', 'local_timetracker') . '">';
    echo '</form>';
}

echo $OUTPUT->footer();
