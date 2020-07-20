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
 * Last Updated: 12/29/18
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Block Leaderboard observer class.
 *
 * @package    block_leaderboard
 */
class block_leaderboard_observer {

    /**
     * Add points when an assignment is submitted early.
     *
     * @param \mod_assign\event\assessable_submitted $event The event.
     * @return void
     */
    public static function assignment_submitted_handler(\mod_assign\event\assessable_submitted $event) {
        global $DB, $USER;
        $functions = new block_leaderboard_functions;

        if (user_has_role_assignment($USER->id, 5)) {
            $eventdata = new \stdClass();

            // The id of the object the event is occuring on.
            $eventid = $event->objectid;

            // The data of the submission.
            $sql = "SELECT assign.*, assign_submission.userid
                FROM {assign_submission} assign_submission
                INNER JOIN {assign} assign ON assign.id = assign_submission.assignment
                WHERE assign_submission.id = ?;";

            $assignmentdata = $DB->get_record_sql($sql, array($eventid));

            // 86400 seconds per day in unix time.
            // The function intdiv() is integer divinsion for PHP '/' is foating point division.
            $daysbeforesubmission = intdiv(($assignmentdata->duedate - $event->timecreated), 86400);

            // Set the point value.
            $points = $functions->get_early_submission_points($daysbeforesubmission, 'assignment');
            $eventdata->pointsearned = $points;
            $eventdata->studentid = $assignmentdata->userid;
            $eventdata->activityid = $eventid;
            $eventdata->timefinished = $event->timecreated;
            $eventdata->modulename = $assignmentdata->name;
            $eventdata->daysearly = $daysbeforesubmission;

            $activity = $DB->get_record('block_leaderboard_assignment',
                            array('activityid' => $eventid, 'studentid' => $assignmentdata->userid),
                            $fields = '*', $strictness = IGNORE_MISSING);

            // Insert the new data into the databese if new, update if old.
            if ($activity) {
                $eventdata->id = $activity->id;
                $DB->update_record('block_leaderboard_assignment', $eventdata);
                return;
            }
            $DB->insert_record('block_leaderboard_assignment', $eventdata);
            return;
        }
    }

    /**
     * Create a new table when a quiz is started.
     *
     * @param \mod_quiz\event\attempt_started $event The event.
     * @return void
     */
    public static function quiz_started_handler(\mod_quiz\event\attempt_started $event) {
        global $DB, $USER;
        if (user_has_role_assignment($USER->id, 5)) {
            // The id corresponding to the users current attempt.
            $currentid = $event->objectid;

            // Get the data of the quiz submission.
            $sql = "SELECT quiz.*
                FROM {quiz_attempts} quiz_attempts
                INNER JOIN {quiz} quiz ON quiz.id = quiz_attempts.quiz
                WHERE quiz_attempts.id = ?;";

            $quiz = $DB->get_record_sql($sql, array($currentid));

            // See if data for this quiz and student have already been submitted.
            $quiztable = $DB->get_record('block_leaderboard_quiz',
                array('quizid' => $quiz->id, 'studentid' => $event->userid),
                $fields = '*',
                $strictness = IGNORE_MISSING);

            if (!$quiztable) {
                // Create a new quiz.
                $quizdata = new \stdClass();
                $quizdata->timestarted = $event->timecreated;
                $quizdata->quizid = $quiz->id;
                $quizdata->studentid = $event->userid;
                $quizdata->attempts = 0;
                $quizdata->daysearly = 0;
                $quizdata->daysspaced = 0;
                $quizdata->modulename = $quiz->name;
                $DB->insert_record('block_leaderboard_quiz', $quizdata);
            }
        }
    }

    /**
     * Add points when a student clicks the confirmation button to submit a quiz.
     *
     * @param \mod_quiz\event\attempt_submitted $event The event.
     * @return void
     */
    public static function quiz_submitted_handler(\mod_quiz\event\attempt_submitted $event) {
        global $DB, $USER;
        $functions = new block_leaderboard_functions;

        if (user_has_role_assignment($USER->id, 5)) {
            // The users current attempt.
            $currentid = $event->objectid;

            // The quiz.
            $sql = "SELECT quiz.*
                FROM {quiz_attempts} quiz_attempts
                INNER JOIN {quiz} quiz ON quiz.id = quiz_attempts.quiz
                WHERE quiz_attempts.id = ?;";

            $thisquiz = $DB->get_record_sql($sql, array($currentid));
            $duedate = $thisquiz->timeclose;

            // The table for the leader board block.
            $quiztable = $DB->get_record('block_leaderboard_quiz',
                array('quizid' => $thisquiz->id, 'studentid' => $event->userid),
                $fields = '*',
                $strictness = IGNORE_MISSING);

            // Add a quiz to the database if one doesn't already exist.
            if ($quiztable->timefinished === null) {
                // Ensure that a full day has passed with floor function to stop from rounding up.
                $daysbeforesubmission = intdiv(($duedate - $event->timecreated), 86400);
                if (abs($daysbeforesubmission) > 50) { // Quizzes without duedates will produce a value like -17788.
                    $daysbeforesubmission = 0;
                }

                // Gets the most recent completed quiz submission time.
                $pastquizzes = $DB->get_records('block_leaderboard_quiz', array('studentid' => $event->userid));
                $recenttimefinished = 0;
                foreach ($pastquizzes as $pastquiz) {
                    if ($pastquiz->timefinished > $recenttimefinished) {
                        $recenttimefinished = $pastquiz->timefinished;
                    }
                }
                // Make sure that days spaced doesn't go above a maximum of 5 days.
                $quizspacing = min(($quiztable->timestarted - $recenttimefinished) / (float)86400, 5.0);

                // Create data for table.
                $quiztable->daysearly = $daysbeforesubmission;
                $quiztable->daysspaced = $quizspacing;
                $quiztable->timefinished = $event->timecreated;
            }

            // Assign points for finishing early.
            $pointsearned = $functions->get_early_submission_points($quiztable->daysearly, 'quiz');
            $quiztable->pointsearned = $pointsearned;

            // Bonus points get awarded for spacing out quizzes instead of cramming (only judges the 2 most recent quizzes).
            $spacingpoints = $functions->get_quiz_spacing_points($quiztable->daysspaced);
            $quiztable->pointsearned += $spacingpoints;

            // Bonus points for attempting quiz again.
            $quiztable->attempts += 1;
            $multipleattemptpoints = $functions->get_quiz_attempts_points($quiztable->attempts);
            $quiztable->pointsearned += $multipleattemptpoints;

            $DB->update_record('block_leaderboard_quiz', $quiztable);
        }
    }

    /**
     * Add points when a student contributes to a choice module.
     *
     * @param \mod_choice\event\answer_created $event The event.
     * @return void
     */
    public static function choice_submitted_handler(\mod_choice\event\answer_created $event) {
        global $DB, $USER;
        if (user_has_role_assignment($USER->id, 5)) {
            // Get data on this choice and the current answers.
            $sql = "SELECT choice_answers.id, choice.name
                FROM {choice_answers} choice_answers
                INNER JOIN {choice} choice ON choice.id = choice_answers.choiceid
                WHERE choice_answers.id = ?;";

            $choice = $DB->get_record_sql($sql, array($event->objectid));

            if ($DB->get_record('block_leaderboard_choice', array('choiceid' => $choice->id, 'studentid' => $event->userid),
                    $fields = '*', $strictness = IGNORE_MISSING) == false) { // If new choice then add to database.
                // Create data for table.
                $choicedata = new \stdClass();
                $choicedata->studentid = $event->userid;
                $choicedata->choiceid = $choice->id;
                $choicedata->pointsearned = get_config('leaderboard', 'choicepoints');
                $choicedata->timefinished = $event->timecreated;
                $choicedata->modulename = $choice->name;

                $DB->insert_record('block_leaderboard_choice', $choicedata);
            }
        }
    }

    /**
     * Add points when a student responds to a discussion.
     *
     * @param \mod_moodleoverflow\event\post_created $event The event.
     * @return void
     */
    public static function forum_posted_handler(\mod_moodleoverflow\event\post_created $event) {
        global $DB, $USER;
        if (user_has_role_assignment($USER->id, 5)) {
            // Create data for table.
            $forumdata = new \stdClass();
            $forumdata->studentid = $event->userid;
            $forumdata->forumid = $event->other{'moodleoverflowid'};
            $forumdata->discussionid = $event->other{'discussionid'};
            $forumdata->postid = $event->objectid;
            $forumdata->isresponse = true;
            $forumdata->pointsearned = get_config('leaderboard', 'forumresponsepoints');
            $forumdata->timefinished = $event->timecreated;
            $forumdata->modulename = "Forum Response";

            $DB->insert_record('block_leaderboard_forum', $forumdata);
        }
    }

    /**
     * Add points when a student creates a discussion.
     *
     * @param \mod_moodleoverflow\event\discussion_created $event The event.
     * @return void
     */
    public static function discussion_created_handler(\mod_moodleoverflow\event\discussion_created $event) {
        global $DB, $USER;
        if (user_has_role_assignment($USER->id, 5)) {
            // Get information on the this discussion.
            $discussion = $DB->get_record('moodleoverflow_discussions',
                            array('id' => $event->objectid), $fields = '*', $strictness = IGNORE_MISSING);
            // Create data for table.
            $forumdata = new \stdClass();
            $forumdata->studentid = $event->userid;
            $forumdata->forumid = $discussion->moodleoverflow;
            $forumdata->postid = $discussion->firstpost;
            $forumdata->discussionid = $event->objectid;
            $forumdata->isresponse = false;
            $forumdata->pointsearned = get_config('leaderboard', 'forumpostpoints');
            $forumdata->timefinished = $event->timecreated;
            $forumdata->modulename = "Forum Post";

            $DB->insert_record('block_leaderboard_forum', $forumdata);
        }
    }
}