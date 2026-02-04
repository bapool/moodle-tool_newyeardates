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

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('newyeardates', 'tool_newyeardates'));

// Display current course start date.
echo html_writer::tag('p', get_string('currentstartdate', 'tool_newyeardates') . ': ' . 
    userdate($course->startdate, get_string('strftimedatefullshort', 'langconfig')));

// Form for selecting new start date.
if (empty($newstartdatestr)) {
    echo html_writer::start_tag('form', array('method' => 'post', 'action' => ''));
    echo html_writer::tag('label', get_string('newstartdate', 'tool_newyeardates') . ': ');
    echo html_writer::empty_tag('input', array(
        'type' => 'date',
        'name' => 'newstartdate',
        'value' => date('Y-m-d', $course->startdate),
        'required' => 'required'
    ));
    echo ' ';
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id', 'value' => $courseid));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
    echo html_writer::tag('button', get_string('setactivitydates', 'tool_newyeardates'), 
        array('type' => 'submit', 'class' => 'btn btn-primary'));
    echo html_writer::end_tag('form');
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
    $activities = get_activities_with_dates($courseid, $course->startdate, $newstartdate);
    
    if (empty($activities)) {
        echo $OUTPUT->notification(get_string('nochangesneeded', 'tool_newyeardates'), 'info');
        echo $OUTPUT->continue_button(new moodle_url('/course/view.php', array('id' => $courseid)));
    } else {
        $applychanges = optional_param('apply', 0, PARAM_INT);
        
        if ($applychanges) {
            // Update course start date first.
            $course->startdate = $newstartdate;
            $DB->update_record('course', $course);
            
            // Apply the changes to activities.
            $count = apply_date_changes($activities);
            echo $OUTPUT->notification(get_string('successmessage', 'tool_newyeardates', $count), 'success');
            echo $OUTPUT->continue_button(new moodle_url('/course/view.php', array('id' => $courseid)));
        } else {
            // Preview changes.
            echo $OUTPUT->heading(get_string('changessummary', 'tool_newyeardates'), 3);
            display_changes_table($activities);
            
            // Confirm form.
            echo html_writer::start_tag('form', array('method' => 'post', 'action' => ''));
            echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id', 'value' => $courseid));
            echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'newstartdate', 'value' => $newstartdatestr));
            echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'confirm', 'value' => 1));
            echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'apply', 'value' => 1));
            echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
            echo html_writer::tag('button', get_string('confirmchanges', 'tool_newyeardates'), 
                array('type' => 'submit', 'class' => 'btn btn-primary'));
            echo ' ';
            echo html_writer::tag('a', get_string('cancel'), 
                array('href' => new moodle_url('/course/view.php', array('id' => $courseid)), 'class' => 'btn btn-secondary'));
            echo html_writer::end_tag('form');
        }
    }
}

echo $OUTPUT->footer();

/**
 * Get all activities and assignments with dates that need updating
 *
 * @param int $courseid Course ID
 * @param int $oldstartdate Old course start date timestamp
 * @param int $newstartdate New course start date timestamp
 * @return array Array of activities with date changes
 */
function get_activities_with_dates($courseid, $oldstartdate, $newstartdate) {
    global $DB, $CFG;
    
    $activities = array();
    $oldstartyear = (int)date('Y', $oldstartdate);
    $oldstartmonth = (int)date('n', $oldstartdate);
    $newstartyear = (int)date('Y', $newstartdate);
    $newstartmonth = (int)date('n', $newstartdate);
       
    // Get all course modules.
    $modinfo = get_fast_modinfo($courseid);
    $cms = $modinfo->get_cms();
    
    foreach ($cms as $cm) {
        $module = $DB->get_record($cm->modname, array('id' => $cm->instance));
        if (!$module) {
            continue;
        }
        
        // Check various date fields depending on module type.
        $datefields = get_date_fields_for_module($cm->modname);
        
        foreach ($datefields as $field) {
            if (isset($module->$field) && $module->$field > 0) {
                $olddate = $module->$field;
                $newdate = calculate_new_date($olddate, $oldstartyear, $newstartyear, $oldstartmonth, $newstartmonth);
                
                if ($newdate != $olddate) {
                    $activities[] = array(
                        'cmid' => $cm->id,
                        'modulename' => $cm->modname,
                        'instance' => $cm->instance,
                        'name' => $cm->name,
                        'field' => $field,
                        'olddate' => $olddate,
                        'newdate' => $newdate
                    );
                }
            }
        }
    }
    
    return $activities;
}

/**
 * Get date fields for a specific module type
 *
 * @param string $modname Module name
 * @return array Array of date field names
 */
function get_date_fields_for_module($modname) {
    $datefields = array();
    
    switch ($modname) {
        case 'assign':
            $datefields = array('allowsubmissionsfromdate', 'duedate', 'cutoffdate', 'gradingduedate');
            break;
        case 'quiz':
            $datefields = array('timeopen', 'timeclose');
            break;
        case 'forum':
            $datefields = array('duedate', 'cutoffdate');
            break;
        case 'lesson':
            $datefields = array('available', 'deadline');
            break;
        case 'choice':
            $datefields = array('timeopen', 'timeclose');
            break;
        case 'feedback':
            $datefields = array('timeopen', 'timeclose');
            break;
        case 'workshop':
            $datefields = array('submissionstart', 'submissionend', 'assessmentstart', 'assessmentend');
            break;
        case 'data':
            $datefields = array('timeavailablefrom', 'timeavailableto', 'timeviewfrom', 'timeviewto');
            break;
        case 'glossary':
            $datefields = array('assesstimestart', 'assesstimefinish');
            break;
        case 'scorm':
            $datefields = array('timeopen', 'timeclose');
            break;
        default:
            // Generic check for common date fields.
            $datefields = array('timeopen', 'timeclose', 'duedate', 'cutoffdate');
            break;
    }
    
    return $datefields;
}


function calculate_new_date($olddate, $oldyear, $newyear, $oldstartmonth, $newstartmonth) {
    $month = (int)date('n', $olddate);
    $day = (int)date('j', $olddate);
    $hour = (int)date('G', $olddate);
    $minute = (int)date('i', $olddate);
    $second = (int)date('s', $olddate);
    $activityyear = (int)date('Y', $olddate);
    
    // Get term configuration
    $term1month = (int)get_config('tool_newyeardates', 'term1month');
    $term2month = (int)get_config('tool_newyeardates', 'term2month');
    
    if (empty($term1month)) {
        $term1month = 8;
    }
    if (empty($term2month)) {
        $term2month = 1;
    }
    
    // Determine terms
    $oldisterm1 = ($oldstartmonth == $term1month);
    $newisterm1 = ($newstartmonth == $term1month);
    $oldisterm2 = ($oldstartmonth == $term2month);
    $newisterm2 = ($newstartmonth == $term2month);
    
    $targetyear = $newyear;
    $targetmonth = $month;
    
    // If old and new are same term, just align years
    if (($oldisterm1 && $newisterm1) || ($oldisterm2 && $newisterm2)) {
        // Calculate year offset from activity date to new course year
        $yearoffset = $newyear - $activityyear;
        
        // Handle two-year span courses
        if ($oldstartmonth >= $newstartmonth) {
            // Term spans two calendar years (e.g., Aug-May)
            if ($month >= $oldstartmonth) {
                $targetyear = $newyear;
            } else {
                $targetyear = $newyear + 1;
            }
        } else {
            $targetyear = $newyear;
        }
        $targetmonth = $month;
        
    } else {
        // Term conversion - shift months
        $targetyear = $newyear;
        $monthoffset = $newstartmonth - $oldstartmonth;
        
        $targetmonth = $month + $monthoffset;
        if ($targetmonth > 12) {
            $targetmonth -= 12;
        } else if ($targetmonth < 1) {
            $targetmonth += 12;
        }
    }
    
    return mktime($hour, $minute, $second, $targetmonth, $day, $targetyear);
}

/**
 * Display table of changes
 *
 * @param array $activities Array of activities with changes
 */
function display_changes_table($activities) {
    // Sort activities by old date
    usort($activities, function($a, $b) {
        return $a['olddate'] - $b['olddate'];
    });
    
    $table = new html_table();
    $table->head = array(
        get_string('activityname', 'tool_newyeardates'),
        get_string('currentdate', 'tool_newyeardates'),
        get_string('newdate', 'tool_newyeardates')
    );
    
    foreach ($activities as $activity) {
        $table->data[] = array(
            $activity['name'] . ' (' . $activity['field'] . ')',
            userdate($activity['olddate'], get_string('strftimedatefullshort', 'langconfig')),
            userdate($activity['newdate'], get_string('strftimedatefullshort', 'langconfig'))
        );
    }
    
    echo html_writer::table($table);
}

/**
 * Apply date changes to activities
 *
 * @param array $activities Array of activities with changes
 * @return int Count of updated dates
 */
function apply_date_changes($activities) {
    global $DB, $CFG;
    
    $count = 0;
    $courseid = 0;
    
    foreach ($activities as $activity) {
        if ($courseid == 0) {
            $courseid = $DB->get_field('course_modules', 'course', array('id' => $activity['cmid']));
        }
        
        $module = $DB->get_record($activity['modulename'], array('id' => $activity['instance']));
        if ($module) {
            $field = $activity['field'];
            $module->$field = $activity['newdate'];
            $module->timemodified = time();
            
            if ($DB->update_record($activity['modulename'], $module)) {
                $count++;
                
                // Rebuild course cache.
                rebuild_course_cache($courseid, true);
            }
        }
    }
    
    return $count;
}
