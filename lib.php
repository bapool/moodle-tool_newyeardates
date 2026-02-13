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
 * Library functions for tool_newyeardates
 *
 * @package    tool_newyeardates
 * @copyright  2026 Brian Pool
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Extend the course navigation to add the new year dates link
 *
 * @param navigation_node $parentnode The parent navigation node
 * @param stdClass $course The course object
 * @param context_course $context The course context
 */
function tool_newyeardates_extend_navigation_course($parentnode, $course, $context) {
    if (has_capability('moodle/course:update', $context)) {
        $url = new moodle_url('/admin/tool/newyeardates/index.php', array('id' => $course->id));
        $node = navigation_node::create(
            get_string('newyeardates', 'tool_newyeardates'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            'newyeardates',
            new pix_icon('i/calendar', '')
        );
        
        $parentnode->add_node($node);
    }
}

/**
 * Get all activities and assignments with dates that need updating
 *
 * @param int $courseid Course ID
 * @param int $oldstartdate Old course start date timestamp
 * @param int $newstartdate New course start date timestamp
 * @return array Array of activities with date changes
 */
function tool_newyeardates_get_activities_with_dates($courseid, $oldstartdate, $newstartdate) {
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
        $datefields = tool_newyeardates_get_date_fields_for_module($cm->modname);
        
        foreach ($datefields as $field) {
            // Skip if field doesn't exist, is empty, zero, or null.
            if (!isset($module->$field) || empty($module->$field) || $module->$field <= 0) {
                continue;
            }
            
            $olddate = $module->$field;
            
            // Additional validation: skip dates that are too old (before year 2000).
            // This catches corrupted or default values.
            if ($olddate < 946684800) { // Unix timestamp for 2000-01-01.
                continue;
            }
            
            $newdate = tool_newyeardates_calculate_new_date($olddate, $oldstartyear, $newstartyear, $oldstartmonth, $newstartmonth);
            
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
    
    return $activities;
}

/**
 * Get date fields for a specific module type
 *
 * @param string $modname Module name
 * @return array Array of date field names
 */
function tool_newyeardates_get_date_fields_for_module($modname) {
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

/**
 * Calculate new date based on course start date change
 *
 * @param int $olddate Original date timestamp
 * @param int $oldyear Old course start year
 * @param int $newyear New course start year
 * @param int $oldstartmonth Old course start month
 * @param int $newstartmonth New course start month
 * @return int New date timestamp
 */
function tool_newyeardates_calculate_new_date($olddate, $oldyear, $newyear, $oldstartmonth, $newstartmonth) {
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
 * Apply date changes to activities
 *
 * @param array $activities Array of activities with changes
 * @return int Count of updated dates
 */
function tool_newyeardates_apply_date_changes($activities) {
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
                
                // Trigger course_module_updated event.
                $cm = get_coursemodule_from_id($activity['modulename'], $activity['cmid'], 0, false, MUST_EXIST);
                $event = \core\event\course_module_updated::create_from_cm($cm);
                $event->trigger();
                
                // Rebuild course cache.
                rebuild_course_cache($courseid, true);
            }
        }
    }
    
    return $count;
}
