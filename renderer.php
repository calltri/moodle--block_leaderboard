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

defined('MOODLE_INTERNAL') || die;

class block_leaderboard_renderer extends plugin_renderer_base {
    /**
     * Gets the content to be displayed in the block.
     *
     * @return string The content to be displayed in the block.
     */
    public function leaderboard_block() {
        global $DB, $OUTPUT, $COURSE;

        // Prepare the data for the table.
        $courseid  = $COURSE->id;
        $functions = new block_leaderboard_functions;
        $daterange = $functions->get_date_range($courseid);
        $start = $daterange->start;
        $end = $daterange->end;

        $url = new moodle_url('/blocks/leaderboard/index.php', array('id' => $courseid, 'start' => $start, 'end' => $end));

        // Get all groups from the current course.
        $groups = $DB->get_records('groups', array('courseid' => $courseid));
        // Only display content in the block if there are groups.
        if (count($groups) > 0) {
            $averagegroupsize = $functions->get_average_group_size($groups);
            // Get data for the groups.
            $groupdataarray = array();
            foreach ($groups as $group) {
                $groupdataarray[] = $functions->get_group_data($group, $averagegroupsize, $start, $end);
            }

            // Sort groups by points.
            if (count($groupdataarray) > 1) { // Only sort if there is something to sort.
                usort($groupdataarray, function ($a, $b) {
                    return $b->points <=> $a->points;
                });
            }

            // Create an html table.
            $table = new html_table();
            // Fill the html table and get the current users group.
            $this->create_leaderboard($groupdataarray, $table, $functions);
        } else {
            // Create empty table.
            $table = new html_table();
            $table->head = array(get_string('num', 'block_leaderboard'), " ",
                            get_string('group', 'block_leaderboard'), get_string('points', 'block_leaderboard'));
            $row = new html_table_row(array("", "", get_string('nogroupsfound', 'block_leaderboard'), ""));
            $table->data[] = $row;
        }

        // Display the block content..
        $output = "";
        $output .= "<block_header>".get_string('rankings', 'block_leaderboard')."</block_header><br>";
        $output .= html_writer::table($table);
        $output .= $OUTPUT->single_button($url, get_string('viewfullleaderboard', 'block_leaderboard'), 'get');
        return $output;
    }

    /**
     * Gets the number of points earned given an amount of time spaced since the last quiz.
     *
     * @param array $groupdataarray An array of group data.
     * @param html_table $table A html table to put data into.
     * @param block_leaderboard_functions $functions A class of helper functions.
     * @return int The number of points earned.
     */
    public function create_leaderboard($groupdataarray, $table, $functions) {
        $moreurl = new moodle_url('/blocks/leaderboard/pix/more.svg');

        // Add table header.
        $table->head = array(get_string('num', 'block_leaderboard'), " ",
                        get_string('group', 'block_leaderboard'), get_string('points', 'block_leaderboard'));
        // Add groups to the table.

        // Rank the groups.
        $rankarray = $functions->rank_groups($groupdataarray);

        foreach ($groupdataarray as $groupdata) {
            // Set the groups current standing to the groups current index in the sorted array.
            $groupindex = array_search($groupdata, $groupdataarray);
            $currentstanding = $rankarray[$groupindex];

            $symbol = $functions->update_standing($groupdata, $currentstanding);

            // Add the top three groups row to the table.
            if ($currentstanding <= 3) {
                // Add the group to the table.
                $row = new html_table_row(array($currentstanding, $symbol, $groupdata->name, round($groupdata->points)));
                if ($groupdata->isusersgroup) { // Bold current users group.
                    $row->attributes['class'] = 'this_group rank'.$currentstanding;
                } else { // Don't bold.
                    $row->attributes['class'] = 'rank'.$currentstanding;
                }
                $table->data[] = $row;
            } else {
                if ($groupdata->isusersgroup) {
                    // Include a visual break in the table if the group has a standing of 5 or greater.
                    if ($currentstanding > 4) {
                        $breakrow = new html_table_row(array("", "", '<img src='.$moreurl.'>', ""));
                        $breakrow->attributes['class'] = 'break_row';
                        $table->data[] = $breakrow;
                    }
                    // Add the current users group to the table.
                    $row = new html_table_row(array($currentstanding, $symbol, $groupdata->name, round($groupdata->points)));
                    $row->attributes['class'] = 'this_group';
                    $table->data[] = $row;
                }
            }
        }
    }
}