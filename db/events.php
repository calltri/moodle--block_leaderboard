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
 * Last Updated: 8/20/18
 */

defined('MOODLE_INTERNAL') || die();

$observers = array (
// Assignment Events.
    array (
        'eventname' => '\mod_assign\event\assessable_submitted',
        'callback'  => 'block_leaderboard_observer::assignment_submitted_handler',
    ),

// Quiz Events.
    array (
        'eventname' => '\mod_quiz\event\attempt_started',
        'callback'  => 'block_leaderboard_observer::quiz_started_handler',
    ),
    array (
        'eventname' => '\mod_quiz\event\attempt_submitted',
        'callback'  => 'block_leaderboard_observer::quiz_submitted_handler',
    ),
    array (
        'eventname' => '\mod_quiz\event\course_module_viewed',
        'callback'  => 'block_leaderboard_observer::quizmodule_viewed_handler',
    ),
    array (
        'eventname' => '\mod_quiz\event\attempt_becameoverdue',
        'callback'  => 'block_leaderboard_observer::quiz_overdue_handler',
    ),

// Forum Events.
    array (
        'eventname' => '\mod_moodleoverflow\event\post_created',
        'callback'  => 'block_leaderboard_observer::forum_posted_handler',
    ),
    array (
        'eventname' => '\mod_moodleoverflow\event\discussion_created',
        'callback'  => 'block_leaderboard_observer::discussion_created_handler',
    ),
// Choice Events.
    array (
        'eventname' => '\mod_choice\event\answer_created',
        'callback'  => 'block_leaderboard_observer::choice_submitted_handler',
    ),
);
