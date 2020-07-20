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

/*
 * Author: Erik Fox
 * Date Created: 5/22/18
 * Last Updated: 9/21/18
 */

defined('MOODLE_INTERNAL') || die();

class block_leaderboard_functions{
    /**
     * Gets the number of points earned given a number of days submitted early.
     *
     * @param int $daysbeforesubmission The number days submitted early.
     * @param string $type Either 'assignment' or 'quiz' indicating which point scale to look at.
     * @return int The number of points earned.
     */
    public static function get_early_submission_points($daysbeforesubmission, $type) {
        for ($x = 1; $x <= 5; $x++) {
            $currenttime = get_config('leaderboard', $type.'time'.$x);
            $nexttime = INF;
            if ($x < 5) {
                $nexttime = get_config('leaderboard', $type.'time'.($x + 1));
            }
            if ($daysbeforesubmission >= $currenttime && $daysbeforesubmission < $nexttime) {
                return get_config('leaderboard', $type.'points'.$x);
            }
        }
        return 0;
    }

    /**
     * Gets the number of points earned given a number of quiz attempts.
     *
     * @param int $attempts The number of attempts.
     * @return int The number of points earned.
     */
    public static function get_quiz_attempts_points($attempts) {
        $maxattempts = get_config('leaderboard', 'quizattempts');
        if ($attempts == 0) {
            return 0;
        } else if ($attempts <= $maxattempts) {
            return get_config('leaderboard', 'quizattemptspoints') * ($attempts - 1);
        }
        return get_config('leaderboard', 'quizattemptspoints') * ($maxattempts);
    }
    /**
     * Gets the number of points earned given an amount of time spaced since the last quiz.
     *
     * @param float $quizspacing The amount of time spaced in days.
     * @return int The number of points earned.
     */
    public static function get_quiz_spacing_points($quizspacing) {
        for ($x = 1; $x <= 3; $x++) {
            $currentspacing = get_config('leaderboard', 'quizspacing'.$x);
            $nextspacing = INF;
            if ($x < 3) {
                $nextspacing = get_config('leaderboard', 'quizspacing'.($x + 1));
            }
            if ($quizspacing >= $currentspacing && $quizspacing < $nextspacing) {
                return get_config('leaderboard', 'quizspacingpoints'.$x);
            }
        }
        return 0;
    }
    /**
     * Gets a the current range of dates the leaderboard will look at and indicates the range with a start and end in unix time.
     * @param int $coursid The id of the current course.
     * @return stdClass An object with a range indicated by integer values 'start' and 'end'.
     */
    public static function get_date_range($courseid) {
        global $DB;
        $sql = "SELECT course.startdate,course.enddate
                FROM {course} course
                WHERE course.id = ?;";

        $course = $DB->get_record_sql($sql, array($courseid));

        $start = $course->startdate;
        $end = $course->enddate;
        if ($end == 0) {
            $end = (int)$start + 61516800;
        }

        $reset1ut = 0;
        $reset2ut = 0;

        $reset1 = get_config('leaderboard', 'reset1');
        $reset2 = get_config('leaderboard', 'reset2');

        if ($reset1 != ''  && $reset2 != '') {
            $reset1ut = strtotime($reset1);
            $reset2ut = strtotime($reset2);
        }

        if (time() < $reset1ut) {
            $end = $reset1ut;
        } else if (time() >= $reset1ut && time() < $reset2ut) {
            $start = $reset1ut;
            $end = $reset2ut;
        } else if (time() >= $reset2) {
            $start = $reset2ut;
        }

        $daterange = new stdClass();
        $daterange->start = $start;
        $daterange->end = $end;
        return $daterange;
    }

    /**
     * Updates a groups standing in the leaderboard indicating whether they moved up, down, or stayed.
     * @param stdClass $groupdata Various data about the group
     * @param int $currentstanding The groups current standing.
     * @return string An html element with the image for the appropriate icon to display.
     */
    public static function update_standing($groupdata, $currentstanding) {
        global $DB;
        // Table icon urls.
        $upurl = new moodle_url('/blocks/leaderboard/pix/up.svg');
        $downurl = new moodle_url('/blocks/leaderboard/pix/down.svg');
        $stayurl = new moodle_url('/blocks/leaderboard/pix/stay.svg');

        $move = $groupdata->lastmove; // 0 for up, 1 for down, 2 for stay.
        $paststanding = $groupdata->currentstanding;
        $symbol = " ";
        if (strlen((string)$groupdata->time_updated) > 8) {
            $groupdata->time_updated = (int)date("Ymd");
        }
        if ($paststanding === null) {
            $paststanding = $currentstanding;
            $move = 2;
        }

        if ($groupdata->time_updated < (int)date("Ymd")) {
            // Use neutral visual queue if a group hasn't moved standings in a set amount of time.
            if ($currentstanding == $paststanding) {
                $symbol = '<img src='.$stayurl.'>';
                $move = 2;
            }
            $groupdata->time_updated = (int)date("Ymd");
        }
        // Only allow change in visual queues for moving up or down when page loads.
        if ($paststanding > $currentstanding) {
            $symbol = '<img src='.$upurl.'>';
            $move = 0;
        } else if ($paststanding < $currentstanding) {
            $symbol = '<img src='.$downurl.'>';
            $move = 1;
        } else { // Otherwise look at the last move taken by the group.
            if ($move == 0) {
                $symbol = '<img src='.$upurl.'>';
            } else if ($move == 1) {
                $symbol = '<img src='.$downurl.'>';
            } else if ($move == 2) {
                $symbol = '<img src='.$stayurl.'>';
            }
        }
        // Update the groups current standing.
        if ($groupdata->id) {
            $storedgroupdata = $DB->get_record('block_leaderboard_group_data',
                                    array('groupid' => $groupdata->id), $fields = '*', $strictness = IGNORE_MISSING);
            $storedgroupdata->currentstanding = $currentstanding;
            $storedgroupdata->lastmove = $move;
            $storedgroupdata->timeupdated = $groupdata->time_updated;
            $DB->update_record('block_leaderboard_group_data', $storedgroupdata);
        }

        return $symbol;
    }

    /**
     * Calculates the average group size based on a list of groups.
     * @param array $groups A list of groups.
     * @return int The average number of students per group.
     */
    public static function get_average_group_size($groups) {
        // Determine average group size.
        $numgroups = count($groups);
        $numstudents = 0;
        if ($numgroups > 0) {
            foreach ($groups as $group) {
                // Get each member of the group.
                $students = groups_get_members($group->id, $fields = 'u.*', $sort = 'lastname ASC');
                $numstudents += count($students);
            }
            $averagegroupsize = ceil($numstudents / $numgroups);
            return $averagegroupsize;
        } else {
            return 0;
        }
    }

    /**
     * Gets all of the data about a group of students during a specific date range.
     * @param stdClass $group A group of students.
     * @param int $averagegroupsize The average number of students per group.
     * @param int $start A unit timestamp.
     * @param int $end A unix timestamp.
     * @return stdClass All important data about a group of students.
     */
    public static function get_group_data($group, $averagegroupsize, $start, $end) {
        global $DB, $USER;

        $pastweekpoints = 0;
        $pasttwoweekspoints = 0;
        $totalpoints = 0;
        $isusersgroup = false;

        // Add up all of the members points.
        $students = groups_get_members($group->id, $fields = 'u.*', $sort = 'lastname ASC');
        $studentsdata = [];
        foreach ($students as $student) {
            $points = self::get_points($student, $start, $end);
            $pastweekpoints += $points->pastweek;
            $pasttwoweekspoints += $points->pasttwoweeks;
            $totalpoints += $points->all;

            $studentdata = new stdClass();
            $studentdata->points = $points->all;
            $studentdata->history = $points->history;
            $studentdata->id = $student->id;
            $studentdata->firstname = $student->firstname;
            $studentdata->lastname = $student->lastname;
            $studentsdata[] = $studentdata;

            // Set to true if this student matches the current logged in $USER.
            if ($student->id === $USER->id) {
                $isusersgroup = true;
            }
        }

        // If the teams are not equal size make it a fair size.
        $groupsize = count($students);
        $bonuspoints = 0;

        if ($groupsize != $averagegroupsize) {
            $bonuspoints = $totalpoints / $groupsize * $averagegroupsize - $totalpoints;
            $pastweekpoints = $pastweekpoints / $groupsize * $averagegroupsize;
            $pasttwoweekspoints = $pasttwoweekspoints / $groupsize * $averagegroupsize;
            $totalpoints = $totalpoints / $groupsize * $averagegroupsize;
        }
        // Calculate the points per week.
        $pointsperweek = $pastweekpoints;
        $pointspertwoweeks = $pasttwoweekspoints / 2;

        // Take the one week rate if it is higher to account for slow weeks or fall/spring breaks.
        if ($pointsperweek > $pointspertwoweeks) {
            $pointsperweek = $pointsperweek;
        } else {
            $pointsperweek = $pointspertwoweeks;
        }
        $pointsperweek = round($pointsperweek);

        $storedgroupdata = $DB->get_record('block_leaderboard_group_data',
                                array('groupid' => $group->id), $fields = '*', $strictness = IGNORE_MISSING);
        if (!$storedgroupdata) {
            $storedgroupdata = new stdClass();
            $storedgroupdata->currentstanding = 0;
            $storedgroupdata->lastmove = 2;
            $storedgroupdata->timeupdated = floor((time() - 7 * 60) / 86400);
            $storedgroupdata->groupid = $group->id;
            $DB->insert_record('block_leaderboard_group_data', $storedgroupdata);
        } else if (strlen((string)$storedgroupdata->currentstanding) < 3) {
            $storedgroupdata->currentstanding = $storedgroupdata->currentstanding;
            $storedgroupdata->groupid = $group->id;
            $DB->update_record('block_leaderboard_group_data', $storedgroupdata);
        }

        // Load the groups data into an object.
        $groupdata = new stdClass();
        $groupdata->name = $group->name;
        $groupdata->id = $group->id;
        $groupdata->currentstanding = $storedgroupdata->currentstanding;
        $groupdata->lastmove = $storedgroupdata->lastmove;
        $groupdata->time_updated = $storedgroupdata->timeupdated;
        $groupdata->points = $totalpoints;
        $groupdata->isusersgroup = $isusersgroup;
        $groupdata->pointsperweek = $pointsperweek;
        $groupdata->studentsdata = $studentsdata;
        $groupdata->bonuspoints = $bonuspoints;
        return $groupdata;
    }

    /**
     * Gets all of the points for a student during a specific date range.
     * @param stdClass $student A student and it's data.
     * @param int $start A unix timestamp.
     * @param int $end A unix timestamp.
     * @return stdClass An object with data about points earned by the student.
     */
    public static function get_points($student, $start, $end) {
        global $DB;

        // Create a new object.
        $points = new stdClass();
        $points->all = 0;
        $points->pastweek = 0;
        $points->pasttwoweeks = 0;
        $points->history = [];
        $studenthistory = [];

        // Add up student points for all points, past week, past two weeks, and fill student history array.

        // ACTIVITY.
        $sql = "SELECT block_leaderboard_assignment.*, assign.duedate
                FROM {block_leaderboard_assignment} block_leaderboard_assignment
                INNER JOIN {assign} assign ON assign.id = block_leaderboard_assignment.activityid
                WHERE block_leaderboard_assignment.studentid = ?;";

        $studentactivities = $DB->get_records_sql($sql, array($student->id));
        $pointsdata = self::get_module_points($studentactivities, $start, $end);
        $points->all += $pointsdata->all;
        $points->pastweek += $pointsdata->pastweek;
        $points->pasttwoweeks += $pointsdata->pasttwoweeks;
        $points->history = $pointsdata->history;

        // QUIZ.
        $sql = "SELECT block_leaderboard_quiz.*, quiz.timeclose
                FROM {block_leaderboard_quiz} block_leaderboard_quiz
                INNER JOIN {quiz} quiz ON quiz.id = block_leaderboard_quiz.quizid
                WHERE block_leaderboard_quiz.studentid = ? AND block_leaderboard_quiz.timefinished IS NOT NULL;";

        $studentquizzes = $DB->get_records_sql($sql, array($student->id));
        $pointsdata = self::get_module_points($studentquizzes, $start, $end);
        $points->all += $pointsdata->all;
        $points->pastweek += $pointsdata->pastweek;
        $points->pasttwoweeks += $pointsdata->pasttwoweeks;
        $points->history += array_merge($points->history, $pointsdata->history);

        // CHOICE.
        $studentchoices = $DB->get_records('block_leaderboard_choice', array('studentid' => $student->id));
        $pointsdata = self::get_module_points($studentchoices, $start, $end);
        $points->all += $pointsdata->all;
        $points->pastweek += $pointsdata->pastweek;
        $points->pasttwoweeks += $pointsdata->pasttwoweeks;
        $points->history += array_merge($points->history, $pointsdata->history);

        // FORUM.
        $studentforumposts = $DB->get_records('block_leaderboard_forum', array('studentid' => $student->id));
        $pointsdata = self::get_module_points($studentforumposts, $start, $end);
        $points->all += $pointsdata->all;
        $points->pastweek += $pointsdata->pastweek;
        $points->pasttwoweeks += $pointsdata->pasttwoweeks;
        $points->history = array_merge($points->history, $pointsdata->history);

        $studenthistory = $points->history;
        if (count($studenthistory) > 1) { // Only sort if there is something to sort.
            usort($studenthistory, function ($a, $b) {
                return $b->timefinished <=> $a->timefinished;
            });
        }
        $points->history = $studenthistory;
        return $points;
    }

    /**
     * Rankes the sorted groups accounting for ties.
     * @param array $groupdataarray An array of group data sorted from most to least points earned.
     * @return array An array of ranks corresponding by index to each group in $groupdataarray.
     */
    public static function rank_groups($groupdataarray) {
        $rankarray = [];
        $count = 1;
        $position = 1;
        for ($i = 0; $i < count($groupdataarray); $i++) {
            $position++;
            $rankarray[$i] = $count;
            if ($i < (count($groupdataarray) - 1)) {
                if ($groupdataarray[$i]->points != $groupdataarray[$i + 1]->points) {
                    $count = $position;
                }
            }
        }
        return $rankarray;
    }

    /**
     * Gets information on all points for a specific module given a date range.
     * @param array $coursid The id of the current course.
     * @param int $start A unix timestamp.
     * @param int $end A unix timestamp.
     * @param stdClass $points An object containing running data about points information from other sources
     * @return stdClass An object with all points information from this source added on to it.
     */
    public static function get_module_points($list, $start, $end) {
        $points = new stdClass;
        $points->all = 0;
        $points->pastweek = 0;
        $points->pasttwoweeks = 0;
        $points->history = [];
        $time = time();
        foreach ($list as $activity) {
            $duedate = $activity->timefinished;
            if (isset($activity->duedate)) {
                $duedate = $activity->duedate;
            } else if (isset($activity->timeclose)) {
                $duedate = $activity->timeclose;
            }
            if ($time >= $duedate && $duedate >= $start && $duedate <= $end && $activity->modulename != '') {
                $points->all += $activity->pointsearned;
                if (($time - $activity->timefinished) / 86400 <= 7) {
                    $points->pastweek += $activity->pointsearned;
                }
                if (($time - $activity->timefinished) / 86400 <= 14) {
                    $points->pasttwoweeks += $activity->pointsearned;
                }
                $points->history[] = $activity;
            }
        }
        return $points;
    }
}