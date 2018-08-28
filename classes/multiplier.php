<?php

defined('MOODLE_INTERNAL') || die();

class block_leaderboard_multiplier{
    public static function get_multiplier($points){   
        //create new object
        $multiplier_data = new stdClass();      
        $multiplier_data->previous = 1;
        $multiplier_data->next = 1;
        $multiplier_data->multiplier = 1;
        $multiplier_data->color = "";

        $color_array = array("#808080cc","#5cb85c","#1177d1","#8000809e","#ffd062");
        //determines the groups new multiplier based on thier performance
        for($x=1; $x<=5; $x++){
            if($x === 1){ //first level special case
                if($points >= 0 && $points < get_config('leaderboard','groupdata'.($x))){
                    $multiplier_data->color = $color_array[$x-1];
                    $multiplier_data->previous = 0;
                    $multiplier_data->next = get_config('leaderboard','groupdata'.$x);
                    
                    //set the span of the progess bar                    
                    $span = get_config('leaderboard','groupdata'.$x);
                    $position = get_config('leaderboard','groupdata'.$x) - $points;
                    $multiplier_data->width = (($span-$position)/$span)*100;
                }
            } else if ($x === 5) { //last level case
                if($points >= get_config('leaderboard','groupdata'.$x)) {
                    $multiplier_data->multiplier = get_config('leaderboard','multiplier'.($x));
                    $multiplier_data->color = $color_array[$x-1];
                    $multiplier_data->previous = get_config('leaderboard','groupdata'.($x));
                    $multiplier_data->next = 'MAX_LEVEL';

                    //set the span of the progess bar                    
                    $span = 100;
                    $position = get_config('leaderboard','groupdata'.($x)) + 100 - $points;
                    $multiplier_data->width = (($span-$position)/$span)*100;
                }
            } else { //all other cases
                if($points >= get_config('leaderboard','groupdata'.($x-1)) && $points < get_config('leaderboard','groupdata'.($x))) {
                    $multiplier_data->multiplier = get_config('leaderboard','multiplier'.($x));
                    $multiplier_data->color = $color_array[$x-1];
                    $multiplier_data->previous = get_config('leaderboard','groupdata'.($x-1));
                    $multiplier_data->next = get_config('leaderboard','groupdata'.$x);

                    //set the span of the progess bar
                    $span = get_config('leaderboard','groupdata'.$x) - get_config('leaderboard','groupdata'.($x-1));
                    $position = get_config('leaderboard','groupdata'.$x) - $points;
                    $multiplier_data->width = (($span-$position)/$span)*100;
                }
            }
        }

        if($multiplier_data->width < 1){ //make the progress bar red and fix style issues if it is almost empty
            $multiplier_data->style = "border-bottom-left-radius: 5px; border-top-left-radius: 5px; border-bottom-right-radius: 0px; border-top-right-radius: 0px; background-color: rgba(255,0,0,0.75)";
        } else if($multiplier_data->width < 5){ //make the progress bar red and fix style issues if it is almost empty
            $multiplier_data->style = "border-bottom-left-radius: 5px; border-top-left-radius: 5px; border-bottom-right-radius: 3px; border-top-right-radius: 3px; background-color: rgba(255,0,0,0.75)";
        } else { //else set the progress bar to normal styling
            $multiplier_data->style = "border-radius: 5px;";
        }

        //$multiplier_data->width = $width;
        return $multiplier_data;
    }
    



    //calculates the points for the student
    public static function calculate_points($student_id, $new_points){
        global $DB;
        $groups = $DB->get_records('groups');
        $num_groups = count($groups);
        $calculated_points = 0;
        if($num_groups > 0){
            //get the group the student is in and find the average group size
            $our_group = new stdClass();
            $num_students = 0;
            foreach($groups as $group){
                $group_id = $group->id;
                $our_group = $group;
                //get each member of the group
                $students = groups_get_members($group_id, $fields='u.*', $sort='lastname ASC');
                $num_students += count($students);
                foreach($students as $student){
                    if($student->id == $student_id){
                        break 2;
                    }
                }
            }
            
            //calculate average group size
            $average_group_size = intdiv($num_students, $num_groups);
            //get the groups general data
            $group_data = self::get_group_data($group,$average_group_size);
            //get the groups multiplier
            $multiplier = self::get_multiplier($group_data->points_per_week);
        
            //calculate the groups points
            $calculated_points = $new_points * $multiplier->multiplier;
        }

        return $calculated_points;
    }
    


    public static function get_average_group_size($groups){
        //determine average group size
        $num_groups = count($groups);
        $num_students = 0;
        if($num_groups > 0){
            foreach($groups as $group){
                //get each member of the group
                $students = groups_get_members($group->id, $fields='u.*', $sort='lastname ASC');
                $num_students += count($students);
            }
            $average_group_size = ceil($num_students/$num_groups);
            return $average_group_size;
        } else {
            return 0;
        }
    }


    //private static function get_student_data($student)
    public static function get_group_data($group, $average_group_size){
        global $DB, $USER;;

        $past_week_points = 0;
        $past_two_weeks_points = 0;
        $total_points = 0;
        $is_users_group = false;

        //add up all of the members points
        $students = groups_get_members($group->id, $fields='u.*', $sort='lastname ASC');
        $students_data = [];
        foreach($students as $student){
            $points = self::get_points($student);
            $past_week_points += $points->past_week;
            $past_two_weeks_points += $points->past_two_weeks;
            $total_points += $points->all;

            $student_data = new stdClass();
            $student_data->points = $points->all;
            $student_data->history = $points->history;
            $student_data->id = $student->id;
            $student_data->firstname = $student->firstname;
            $student_data->lastname = $student->lastname;
            $students_data[] = $student_data;
            
            //set to true if this student matches the current logged in $USER
            if($student->id === $USER->id){
                $is_users_group = true;
            }
        }

        //if the teams are not equal size make it a fair size
        $group_size = count($students);
        $bonus_points = 0;
        
        if($group_size != $average_group_size){
            $bonus_points = $total_points/$group_size * $average_group_size - $total_points;
            $past_week_points = $past_week_points / $group_size * $average_group_size;
            $past_two_weeks_points = $past_two_weeks_points / $group_size * $average_group_size;
            $total_points = $total_points / $group_size * $average_group_size;
        }
        //calculate the points per week
        $points_per_week = $past_week_points;
        $points_per_two_weeks = $past_two_weeks_points / 2;

        //take the one week rate if it is higher to account for slow weeks or fall/spring breaks
        if($points_per_week > $points_per_two_weeks){
            $points_per_week = $points_per_week;
        } else {
            $points_per_week = $points_per_two_weeks;
        }

        $stored_group_data = $DB->get_record('group_data_table', array('group_id'=> $group->id), $fields='*', $strictness=IGNORE_MISSING);
        if(!$stored_group_data){
            $stored_group_data = new stdClass();
            $stored_group_data->current_standing = 0;
            $stored_group_data->group_id = $group->id;
            $DB->insert_record('group_data_table',$stored_group_data);
        }     

        //load the groups data into an object
        $group_data = new stdClass();
        $group_data->name = $group->name;
        $group_data->id = $group->id;
        $group_data->past_standing = $stored_group_data->current_standing;
        $group_data->points = $total_points;
        $group_data->is_users_group = $is_users_group;
        $group_data->points_per_week = $points_per_week;
        $group_data->students_data = $students_data;
        $group_data->bonus_points = $bonus_points;
        return $group_data;
    }



    public static function get_points($student){
        global $DB;

        //create a new object
        $points = new stdClass();
        $points->all = 0;
        $points->past_week = 0;
        $points->past_two_weeks = 0;
        $student_history = [];

        //add up student points for all points, past week, past two weeks, and fill student history array
        $time = time();
        $student_activities = $DB->get_records('assignment_table', array('activity_student'=> $student->id));
        foreach($student_activities as $activity){
            $points->all += $activity->points_earned;
            if(($time - $activity->time_finished)/86400 <= 7){
                $points->past_week += $activity->points_earned;
            }
            if(($time - $activity->time_finished)/86400 <= 14){
                $points->past_two_weeks += $activity->points_earned;
            }
            $student_history[] = $activity;
        }
        $student_quizzes = $DB->get_records('quiz_table', array('student_id'=> $student->id));
        foreach($student_quizzes as $quiz){
            $points->all += $quiz->points_earned;
            if(($time - $quiz->time_finished)/86400 <= 7){
                $points->past_week += $quiz->points_earned;
            }
            if(($time - $quiz->time_finished)/86400 <= 14){
                $points->past_two_weeks += $quiz->points_earned;
            }
            $student_history[] = $quiz;
        }
        $student_choices = $DB->get_records('choice_table', array('student_id'=> $student->id));
        foreach($student_choices as $choice){
            $points->all += $choice->points_earned;
            if(($time - $choice->time_finished)/86400 <= 7){
                $points->past_week += $choice->points_earned;
            }
            if(($time - $choice->time_finished)/86400 <= 14){
                $points->past_two_weeks += $choice->points_earned;
            }
            $student_history[] = $choice;
        }
        $student_forum_posts = $DB->get_records('forum_table', array('student_id'=> $student->id));
        foreach($student_forum_posts as $post){
            $points->all += $post->points_earned;
            if(($time - $post->time_finished)/86400 <= 7){
                $points->past_week += $post->points_earned;
            }
            if(($time - $post->time_finished)/86400 <= 14){
                $points->past_two_weeks += $post->points_earned;
            }
            $student_history[] = $post;
        }

        if(count($student_history) > 1){ //only sort if there is something to sort
            usort($student_history, function ($a, $b) {
                return $b->time_finished <=> $a->time_finished;
            });
        }
        $points->history = $student_history;

        return $points;
    }
}
