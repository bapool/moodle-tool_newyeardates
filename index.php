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
 * Main interface for tool_newyeardates
 *
 * @package    tool_newyeardates
 * @copyright  2026 Brian Pool
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

$courseid = required_param('id', PARAM_INT);
$newstartdatestr = optional_param('newstartdate', '', PARAM_TEXT);
$confirm = optional_param('confirm', 0, PARAM_INT);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
$context = context_course::instance($courseid);

require_login($course);
require_capability('moodle/course:update', $context);

$PAGE->set_url('/admin/tool/newyeardates/index.php', array('id' => $courseid));
$PAGE->set_context($context);
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('newyeardates', 'tool_newyeardates'));
$PAGE->set_heading($course->fullname);

// Get the renderer.
$output = $PAGE->get_renderer('tool_newyeardates');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('newyeardates', 'tool_newyeardates'));

// Show the date selection form or process changes.
if (empty($newstartdatestr)) {
    // Display the date selection form.
    $form = new \tool_newyeardates\output\date_selection_form($course);
    echo $output->render($form);
} else {
    // Process and preview/apply changes.
    require_sesskey();
    
    $newstartdate = strtotime($newstartdatestr);
    
    if (!$newstartdate) {
        echo $OUTPUT->notification(get_string('errorupdating', 'tool_newyeardates'), 'error');
        echo $OUTPUT->footer();
        exit;
    }
    
    // Get all course modules with dates.
    $activities = tool_newyeardates_get_activities_with_dates($courseid, $course->startdate, $newstartdate);
    
    if (empty($activities)) {
        echo $OUTPUT->notification(get_string('nochangesneeded', 'tool_newyeardates'), 'info');
        echo $OUTPUT->continue_button(new moodle_url('/course/view.php', array('id' => $courseid)));
    } else {
        $applychanges = optional_param('apply', 0, PARAM_INT);
        
        if ($applychanges) {
            // Update course start date first.
            $course->startdate = $newstartdate;
            $DB->update_record('course', $course);
            
            // Trigger course_updated event.
            $event = \core\event\course_updated::create(array(
                'objectid' => $course->id,
                'context' => $context,
                'other' => array('shortname' => $course->shortname, 'fullname' => $course->fullname)
            ));
            $event->trigger();
            
            // Apply the changes to activities.
            $count = tool_newyeardates_apply_date_changes($activities);
            echo $OUTPUT->notification(get_string('successmessage', 'tool_newyeardates', $count), 'success');
            echo $OUTPUT->continue_button(new moodle_url('/course/view.php', array('id' => $courseid)));
        } else {
            // Preview changes.
            $preview = new \tool_newyeardates\output\changes_preview($activities, $courseid, $newstartdatestr);
            echo $output->render($preview);
        }
    }
}

echo $OUTPUT->footer();
