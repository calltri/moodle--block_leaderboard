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
 * Downloads all leaderboard data into a csv file.
 *
 * @package    blocks_leaderboard
 * @copyright  2019 Erik Fox
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


require_once('../../../config.php'); // Specify path to moodle /config.php file.
require_once($CFG->libdir . '/csvlib.class.php'); // Require csv library for exporting a csv file.
require_login(); // Require valid moodle login.  Will redirect to login page if not logged in.

global $DB, $CFG;

// Get required parameters from the url.
$cid = required_param('id', PARAM_INT);
$start = required_param('start', PARAM_RAW);
$end = required_param('end', PARAM_RAW);

// Set the url for this page.
$url = new moodle_url('/blocks/leaderboard/classes/data_loader.php', array('id' => $cid, 'start' => $start, 'end' => $end));
$PAGE->set_url($url);

// The row for all column names in the csv.
$csv[0] = array(
    'studentid',
    'module_id',
    'modulename',
    'module_type',
    'timefinished',
    'daysearly',
    'attempts',
    'daysspaced',
    'discussionid',
    'postid',
    'isresponse'
);

$count = 1;
// Get all groups from the course.
$groups = $DB->get_records('groups', array('courseid' => $cid));
foreach ($groups as $group) {
    // Get each member of the group.
    $students = groups_get_members($group->id, $fields = 'u.*', $sort = 'lastname ASC');
    foreach ($students as $student) {
        // Get each members past contributions and add them to an array.
        $studentactivities = $DB->get_records('block_leaderboard_assignment', array('studentid' => $student->id));
        foreach ($studentactivities as $activity) {
            if ($activity->timefinished >= $start && $activity->timefinished <= $end) {
                $csv[$count] = array(
                    $activity->studentid,
                    $activity->activityid,
                    $activity->modulename,
                    'Assignment',
                    $activity->timefinished,
                    $activity->daysearly,
                    'null',
                    'null',
                    'null',
                    'null',
                    'null'
                );
                $count++;
            }
        }
        $studentquizzes = $DB->get_records('block_leaderboard_quiz', array('studentid' => $student->id));
        foreach ($studentquizzes as $quiz) {
            if ($quiz->timefinished >= $start && $quiz->timefinished <= $end) {
                $csv[$count] = array(
                    $quiz->studentid,
                    $quiz->quizid,
                    $quiz->modulename,
                    'Quiz',
                    $quiz->timefinished,
                    $quiz->daysearly,
                    $quiz->attempts,
                    round($quiz->daysspaced, 2),
                    'null',
                    'null',
                    'null'
                );
                $count++;
            }
        }
        $studentchoices = $DB->get_records('block_leaderboard_choice', array('studentid' => $student->id));
        foreach ($studentchoices as $choice) {
            if ($choice->timefinished >= $start && $choice->timefinished <= $end) {
                $csv[$count] = array(
                    $choice->studentid,
                    $choice->choiceid,
                    $choice->modulename,
                    'Choice',
                    $choice->timefinished,
                    'null',
                    'null',
                    'null',
                    'null',
                    'null',
                    'null'
                );
                $count++;
            }
        }
        $studentforumposts = $DB->get_records('block_leaderboard_forum', array('studentid' => $student->id));
        foreach ($studentforumposts as $post) {
            if ($post->timefinished >= $start && $post->timefinished <= $end) {
                $csv[$count] = array(
                    $post->studentid,
                    $post->forumid,
                    $post->modulename,
                    'Forum',
                    $post->timefinished,
                    'null',
                    'null',
                    'null',
                    $post->discussionid,
                    $post->postid,
                    $post->isresponse

                );
                $count++;
            }
        }
    }
}

// Create a new file.
$filename = clean_filename('data');
$csvexport = new csv_export_writer();
$csvexport->set_filename($filename);

// Add data to the file.
foreach ($csv as $line) {
    $csvexport->add_data($line);
}

// Download the file.
$csvexport->download_file();
die;