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
 * Last Updated: 7/15/18
 */
defined('MOODLE_INTERNAL') || die();

$capabilities = array(
        'block/leaderboard:addinstance' => array(
            'riskbitmask' => RISK_SPAM | RISK_XSS,

            'captype' => 'write',
            'contextlevel' => CONTEXT_BLOCK,
            'archetypes' => array(
                'editingteacher' => CAP_ALLOW,
                'manager' => CAP_ALLOW
            ),

            'clonepermissionsfrom' => 'moodle/site:manageblocks'
        ),

        'block/leaderboard:viewaddupdatemodule' => array(
            'captype' => 'read',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                'user' => CAP_ALLOW
            )
        ),

        'block/leaderboard:viewdeletemodule' => array(
            'captype' => 'read',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                'user' => CAP_ALLOW
            )
        ),

        'block/leaderboard:view' => array(
            'captype' => 'read',
            'contextlevel' => CONTEXT_COURSE,
            'archetypes' => array(
                'guest' => CAP_ALLOW,
                'user' => CAP_ALLOW,
                'manager' => CAP_ALLOW
            )
        ),

        'block/leaderboard:submit' => array(
            'riskbitmask' => RISK_SPAM,
            'captype' => 'write',
            'contextlevel' => CONTEXT_MODULE,
            'archetypes' => array(
                'student' => CAP_ALLOW
            )
        ),

);