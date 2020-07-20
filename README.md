8/10/2018
# Project Documentation
This documentation gives an insight into the way this plugin is coded as well as a few tips for coding in PHP and Moodle that I tripped me up. Refer to it if you don't understand the purpose of a file or a function.

## Project overview
This section will give a general overview of the project.

### Structure

[block_leaderboard.php](#block_leaderboard.php)<br/>
classes/<br/>

&nbsp;&nbsp;&nbsp;[data_loader.php](#classesobserverphp)<br/>
&nbsp;&nbsp;&nbsp;[functions.php](#classesobserverphp)<br/>
&nbsp;&nbsp;&nbsp;[observer.php](#classesobserverphp)<br/>
db/<br/>
&nbsp;&nbsp;&nbsp;[access.php](#accessphp)<br/>
&nbsp;&nbsp;&nbsp;[events.php](#dbeventsphp)<br/>
&nbsp;&nbsp;&nbsp;[install.xml](#installxml)<br/>
&nbsp;&nbsp;&nbsp;[index.php](#indexphp)<br/>
javascript/<br/>
[tableEvents.js](#javascripttableEventsjs)<br/>
lang/<br/>
 &nbsp;&nbsp;&nbsp;en/<br/>
 &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;[block_leaderboard.php](#langenblock_leaderboardphp)<br/>
pix/<br/> 
&nbsp;&nbsp;&nbsp; [icon.svg](#adding-pictures-and-icons)<br/>
[renderer.php](#rendererphp)<br/>
[settings.php](#settingsphp)<br/>
[styles.css](#stylescss)<br/>
[version.php](#versionphp)<br/>

The structure of this plugin is based off of Moodle's standards. Moodle uses something called [automatic class loading](https://docs.moodle.org/dev/Automatic_class_loading) where it knows where to look for certain peices of the plugin. This means that cetrain directory and file names should adhere to Moodle's standards.

### Coding Style and Standards
Moodle and PHP use underscore notation. So it is best to get in the practice of using it so that function calls and variables come more naturally. Moodle also make use of something called frankenstyle notation where function names correspond to their location in the directory. The most classic example of this is the leaderboard_block.php This lets moodle know that leaderboard is a block plugin, and therefore in the blocks directory.

## PHP Oddities
Start all PHP files with `<?php`.
### Variables
All variables start with a `$`
### Type
Moodle is not strongly typed
### `stdClass()`
`stdClass` is an empty PHP class. They are kind of like an object. You can define them anywhere, fill them with any data, and pass them like variables. To define them use:
```
$data = new stdClass();
```
This will create a new empty class. Get and set new elements with values using the `->` symbol.
```
$data->new = $newElement;
$element = $data->newElement.
```
This is useful for packaging data together and returning multiple values from a function.

### Static, $this, self
Use `$this->function()` for non-static function calls within the same class. `->` is used for all non-static calls.
Use `self::function()` for static function calls within the same class. `::` is used for all static call.
Mixing static and non-static functions/function calls is a bad idea, and you will get compile errors or warning if you do. Remember you can't call a non-static function from a static function.

### Strings
`‘’` Display strings almost exactly the way they are typed. So things like `\n` for a new line do not work.
`“”` Is slightly slower because it is interpreted by PHP. It will display new lines and variables can be included directly in the string. `"This is all interpreted:\n $variable"`.

HTML tags (such as `<div>String</div>`) work in both `''` and `""`.

A dot `.` is used for string concatenation in PHP, and `.=`  concatenates and assigns the new string to a variable.
### Break Statements
break statements function the same as in most programing languages, but have an added feature: ‘break;’ will break out of a loop ‘break 2;’ will break out of two loops
### Integer vs Float Division
`/` is floating point division in PHP.
`intdiv($numerator,$denominator)` is integer division.

### Equivalence Operators
`==` since PHP is not strongly typed `==` can be used to determine equivalence between values and that are of different type. So a `"1" == 1` will return true.
`===` only returns true if the value and type are the same.
### Useful Functions
`$jsonObject = json_encode($object);` makes the object a JSON object. This makes it so that you can see the objects values when logging the values

## Relevant Moodle Info
### Global Variables
Moodle has some global variables that can return important information. To define them just use
```
global $COURSE //is the course that the current user is accessing.
global $USER; ///is the current logged in user
global $DB; ///is the database for the course.
global $OUTPUT; //is the pages output
```
These variables need to be defined within the scope to be used.

### Debugging
For debugging PHP and Moodle you can log to the browsers console using javascript (PHP doesn't have it's own built in function). These string will show up in a few of the tabs of the console, however the info tab is the least cluttered.
```
echo("<script>console.log('STRING: ".$string."');</script>");
```

This may not be the “correct way”. But it is the simplest way to get quick feedback and print debug.

Moodle also offers developer settings  to tell you what line things are breaking at and display detected warnings and errors. turn this on in the admin settings menu under admin settings->development->debugging 

### Purging the Cache and Bumping Version Number
Every time you change something in the `db/` directory you will need to purge the cache.
#### version.php
Bumping the version number in this file lets Moodle know that there is a new build of your plugin and the database needs to be updated. This automatically purges the cache.

[More on Moodle development](https://docs.moodle.org/dev/Main_Page)
## Block Set UP
This section will tell you how to display content to the block and overview what the relevant files do.

### block_leaderboard.php
The block_leaderboard.php file handles the entire blocks setup. Every block has a similar file with the standard name `block_blockname` located in the blocks root directory. There are 5 functions in this class that are important:
* `init()` is the first function run by the block. All that is done in this function is setting the block’s title. The title what the block is called when the admin or teacher looks for it in Moodle's list of blocks.
* `has_config ()` function tells Moodle that this block uses settings located in the settings.php file.
* `get_content()` function in this file is what the block displays. This function uses a renderer (renderer.php) to get the blocks content.
* `hide_header()` does exactly what the function states. It hides the blocks header(its title). Return false if you want to display the header.
* `html_attributes()` sets  the blocks html attributes. Currently the only attribute set is the class attribute so that the block can be located in css and javascript files

[More info on blocks](https://docs.moodle.org/dev/Blocks)
## Display Content
### Display to Block
#### renderer.php
This file handles the specific rendering of the block. Blocks that use renderers all have a standard file named renderer.php located in the blocks root directory.

To add new content to the block go to the `$output` variable and add:
```
$output .= ‘new content’;
```
The dot `.` is the operator for string concatenation in PHP and `.=` allows you to concatenate strings on separate lines for easier code readability. 
* Note: The block displays content in the order of the output strings.

[More info on renderers](https://docs.moodle.org/dev/Renderer)

### Display Content to the Leaderboard Page
#### index.php
This file displays the full leaderboard page. It uses Moodle's Page API instead of a renderer. It functions similarly to renderer.php, but with one distinct difference. It uses `echo` to display the content instead of returning an output string.
`echo ‘new content’;`

[More info on pages](https://docs.moodle.org/dev/Page_API)

### Add Custom Styling
#### Styling With HTML & CSS
If you would like to style your strings or include other content. There are a couple of ways of going about this.
The most straight forward way is to include HTML tags and use CSS.
```
$string = '<div class="info">'.$string.'</div>';
```
You can see that in this code we are defining an HTML element `div` with the class 		`info` containing the value `string`. `$string` can then be displayed Moodle and it will retain its HTML attributes.

Moodle also has built in HTML classes and functions that can simplify the process.
```
$table = new html_table();
$table->attributes['class'] = 'generaltable leaderboard';
$table->head = array("col1","col2","col3");

$row = new html_table_row(array(1,2,3));
$row->attributes['class'] = 'content';
$table->data[] = row;

$output .= html_writer::table($table);
```  

Here is an example with one of Moodle's HTML table writer class. You can see that we are creating table and a row with custom class attributes. Some of these Moodle functions come pre-packaged with class definitions and attributes. For example tables come with the class `generaltable` and in order to not overwrite this we need to include it in the class definition or concatenate the new definition with a `.`. This way we aren't removing Moodle's styling for the table.

#### styles.css
styles.css is where all CSS styling for the block is stored. Rolling this all together of you wanted to display a new string in the block with the class to make bold you would add the following code:
```
//PHP for displaying in block
//Located in file renderer.php 
public funtion leaderboard_block($course){
  ...
  $string = 'Hello World'
  $output = '';
  $output .= '<div class="info">'.$string.'</div>';
  return $output //don't forget to return the string to be rendered
}
//PHP for displaying on leaderboard page
//in index.php
...
$string = 'Hello World'
$echo '<div class="info">'.$string.'</div>';
...

//CSS in styles.css
div.info {
    font-weight: bold;
    color: black;
}
```
You can inspect webpages to see the html hierarchy and how to navigate to certain elements. It is best to be specific when styling elements so you don't change any extra elements.
* Note: Styling takes some time to update, so you may have to wait a minute to see the results. Every once and a while you many have to use `!important`(eg. `color: black !important;`) to override a style set by Moodle that doesn't want to change.
  
[More info on styles](https://docs.moodle.org/dev/CSS_Coding_Style)

#### Adding Pictures and Icons
Pictures and icons should all be located in the pix directory. Moodle has built in functions that can automatically locate and display images, but the way that I found worked best was to just use HTML. Create a url with the path to the file you want to include and then add it as an HTML image. 
```
$url = new moodle_url('/blocks/leaderboard/pix/icon.svg');
$icon = '<img src='.$url.'>';
```
`$icon` can then be displayed anywhere that a string can be displayed and it is easy to attach classes to the pictures for further styling. `moodle_url()` will automatically locate Moodle's root directory in the file system so there is no need to include the path to Moodle's root directory.

#### Add Javascript
You can also use HTML tags to execute javascript. Since PHP only updates on page loads javascript is necessary for changing a page without reloading it.
##### tableEvent.js
This file controls the leaderboard drop-downs. It is included in `index.php` via the following call:
```
$PAGE->requires->js(new moodle_url('/blocks/leaderboard/javascript/tableEvents.js'));
```

## Add and Change Strings for the Block
All strings that are displayed in Moodle should be stored in the strings file. This way if somebody wants to add support for a new language they only have to change one file.
### lang/en/block_leaderboard.php
This file stores all of the strings for the block. 
To add a string add:

`$string['string_title'] = 'string value';`

To get a string use:

`$string = get_string('string_title', 'block_leaderboard);`

[More info on strings](https://docs.moodle.org/dev/String_API)

## Create a new Page
To create a page add a new PHP file to the block's directory and add the following code to it. index.php is an example of a page in Moodle.
```
//display where you want the option to navigate to a new page
$url = new moodle_url('/blocks/leaderboard/filename.php', array('id' => $courseid));
output .= $OUTPUT->single_button($url,get_string('view_full_leaderboard','block_leaderboard'),'get');
```
You need to include the `array('id' => $courseid)`. This adds the course's id as a parameter to the end of the url so that it can be accessed in the new page.
* Note: `single_button()` is not necessary to navigate to a new page. You can use a link or any other manner of navigation. Also `output .= ` should be changed to whatever display method you happen to be using.

Include the following code at the start of `/blocks/leaderboard/filename.php`
```
require_once('../../config.php');
global $DB;

// course id
$cid = required_param('id', PARAM_INT);

$course = $DB->get_record('course', array('id'=>$cid), '*', MUST_EXIST);

//this line or some other form of moodle validation is required
require_course_login($course, true);

//this page's url (the file path)
$url = new moodle_url('/blocks/leaderboard/filename.php', array('id' => $course->id));

//setup page 
//$PAGE is the global variable refering to this page, use it to set the page's attributes 
$PAGE->set_pagelayout('incourse');
$PAGE->set_url($url);
$PAGE->set_title('Page Title');
$PAGE->set_heading($course->fullname);
$PAGE->add_body_class("page"); //adds an id to the page's html element

echo $OUTPUT->header();

//page content

echo $OUTPUT->footer();
```
The function `required_param('id', PARAM_INT);` is why we needed to include the parameters at the end of the url. This function asserts that the parameter is there and assigns it to a variable. If you want to include an optional parameter you can do that with `optional_param()`.

Make sure you `echo` the header and footer for so Moodle can format everything correctly.

[More info on pages](https://docs.moodle.org/dev/Page_API)

## Monitor a New Event
This section will overview the event monitoring system and walk through the process of adding a new event to monitor.
### db/events.php
This file is where Moodle looks to see what events you are monitoring. In order to monitor an event you need to include the path to the event as well as the function that you want to call when the event is triggered.
```
$observers = array (
	array (
        'eventname' => '\mod_modwithevent\event\event_name,
        'callback'  => 'block_leaderboard_observer::function_called',
    ),
	//more events to be monitered
);
```

### classes/observer.php
This file is where all event listener functions are stored. Any time you monitor an event the function that is called must be located in this file.
```
public static function function_called\mod_modwithevent\event\event_name $event){
	$eventid = $event->id;
	//code executed when event is triggered
}
```
### Understanding the event data
`$event` is an object so it can't be displayed as a log message. You can fix this by using the PHP function `$event_data = json_encode($event);`. Now `$event_data` will be displayed as a JSON object and will look something like this:
```
{"eventname":"\mod_modwithevent\event\event_name","id":"1",
"objecttable":"event_table","objectid":110,"crud":"c","edulevel":2,
"contextid":215,"contextlevel":70,"contextinstanceid":"136","userid":"2",
"courseid":"4","timecreated":1533757045, other:{otherdata:0}}
```
Now that you know what the values in the object are, you can access them with:

`$id = $event->id`

Remember that elements enclosed in`[]` instead of `{}` are arrays and need to be accessed with indexes. To get nested objects use:

`$otherdata = $event->other{'otherdata'}`.

Often times an event will have an `objecttable` and `objectid`. These generally refer to a specific instance of an event, such as a quiz attempt, whereas the `id` will refer to the id of the quiz its self.

You can also find these values in admin-settings->development->XMLDB-editor and clicking on the doc at the top. This displays all of the SQL tables being used by Moodle.

[More info on events](https://docs.moodle.org/dev/Event_2)

## Database
If you are accessing the database then you need to have global $DB; defined within the scope (AKA  at the start of each function that uses the database).
### Getting Database Records
You can get all records from a database table using Moodle's `get_records()` function.

```
$records = $DB->get_records('database_table_name');
```

You can also get a single record from a database table using Moodle's `get_record()` function.

```
$record = $DB->get_record('database_table_name', array('id'=> $id), $fields='*', $strictness=IGNORE_MISSING);
```

The first parameter is the name of the table second parameter is an array of conditions that need to be met by the record (id's are unique so they are the best way of searching). The third parameter can return certain fields from a record instead of an entire record. The fourth parameter tells the function to return false if a record isn't found. You can replace the strictness with `IGNORE_MULTIPLE` to return the first value found, or with `MUST_EXIST` to throw an exception if no record or multiple are records found.

### Inserting Database Records
To inset a new table into the database create a `stdClass()`, load the relevant data into the class, then use Moodle's `insert_record()` function.
```
$record = new stdClass();
$record->value = $value;
$record->value2 = $value2;
$DB->insert_record('database_table_name', $record);
```
### Updating Database Records
For updating values in the database you need to include the records id as one of its values so that it knows where to find it. The easiest way to get this is to use the get function to get the data modify whatever values you wish to update that data then use that same variable to update the database with Moodle's `update_record()` function.
```
$record = new stdClass();
$record = $DB->get_record('database_table_name', array('name'=> $name), $fields='*', $strictness=IGNORE_MISSING);
$record->value = $new_value;
$DB->update_record('database_table_name', $record);
```

[More info on data manipulation](https://docs.moodle.org/dev/Data_manipulation_API)

### Creating Database Records

#### install.xml
Moodle suggests that you do not hand code any new field into this file and use their database tool instead.

#### XMLBD Editor
This is Moodle's preferred way of creating and maintaining database tables. To access it go into admin settings->development->XMLDB-editor. In that page you will see all of the plugins with database tables. Click on '[Load]' and then '[Edit]. From there you will be able to create new tables and update old ones. Changes will be pushed to install.xml when saving.

* Note: To apply these changes you will need to uninstall and reinstall the plugin, however the entire database will be wiped. The other option is to include an upgrade.php file, and bump the version number to upgrade the database.
[Info on adding upgrade.php](https://docs.moodle.org/dev/Upgrade_API)

[More info on creating database records](https://docs.moodle.org/dev/Using_XMLDB)

## Add New Settings
### settings.php
This file stores all of the settings. To add a setting add the following to the settings file:
```
$settings->add(new admin_setting_configtext(
	'leaderboard/uniqueSettingName',
	'Setting Label',
	'Setting Description',
	$defaultvalue
));
```

It is important to include the block name, `leaderboard` in this case so that Moodle knows which settings file to check. The setting also needs a unique name so that it is not overridden by other settings.

Moodle isn't great about applying defaults so to make sure that no settings are left blank add the following code:
```
if(get_config('leaderboard','uniqueSettingName'.$x) === ''){
set_config('uniqueSettingName',$defaultvalue,'leaderboard');
}
```
This ensures that if a setting is left unfilled by accident it gets filled in automatically before the admin leaves the page. `get_config()` and `set_config()` are also the getters and setters for the settings and they can be called from any file.

[More info on settings here](https://docs.moodle.org/dev/Admin_settings)
## Modify the Multiplier/Points System
### functions.php
All of the functions corresponding to points and the multipliers are found in this file.
```
//static call
$data = block_leaderboard_multiplier::get_multiplier($points_per_week);

//non-static call
$multiplier = new block_leaderboard_multiplier;
$data = $multiplier->get_multiplier($points_per_week);
```

#### `get_multiplier($points_per_week)`
This function calculates all required multiplier data based on a points-per-week value. This function is called in `functions.php` and `renderer.php`. 

It requires the folowing parameters:
```
$points_per_week //the number of points-per-week a group has 
```

It returns an object containing the following:

```
$data->previous //the points required to reach the previous multiplier
$data->next //the points required to reach the next multiplier
$data->multiplier //the current multiplier
$data->color //the color of the progress bar
$data->width //the width of the progess bar to fill (out of 100)
$data->style //extra styling for the progress bar
```

#### `get_points($student)`
This function gets all of a students points and history. This function is called in `functions.php` and `renderer.php`. 

It requires the following parameters:
```
$student //a student object 
```

It returns an object containing the following:

```
$data->previous //the points required to reach the previous multiplier
$data->all //all of a students points
$data->past_week //the students points for the last week
$data->past_two_weeks //the students points for the last two weeks
$data->history //an array of all of the students event object
```

#### `get_group_data($group,$average_group_size)`
This function gets important group data. This function is called in `functions.php`, `renderer.php`, and `index.php`. 

It requires the following parameters:
```
$student //a moodle student record 
```

It returns an object containing the following:

```
$data->name //the groups name
$data->id //the groups id
$data->past_standing //the groups last standing in the leaderboard
$data->points //the groups total points
$data->is_users_group //true if this is the current user group
$data->points_per_week //the teams points-per-week
$data->students_data //an array of student data objects
$data->bonus_points //the amount of extra points
```
#### `get_average_group_size($groups)`
This function calculates the average group size. Used in `renderer.php`. 

It requires the following parameters:
```
$groups //all groups records 
```

It returns an integer with the average group size.

#### `calculate_points($student_id, $new_points)`
This function calculates applies the multipliers to the points whenever a student earns them. Used in `observer.php`. 

It requires the following parameters:
```
$student_id //the if of student earning the points
$new_points // the number of new points the student earned
```

It returns an integer with the multiplier applied to the students points.

## Modify What Gets Downloaded
### data_loader.php
This file controls what data gets loaded into the csv file before downloading. It only has one function that loads the data.

#### `load_data_file($groups)`
It requires the following parameters:
```
$groups //all groups records
```

It returns nothing.

## Miscellaneous
### access.php
You probably won't need to touch this file but it controls capabilities and what the current user is and isn't allowed to use.

[More info on access API](https://docs.moodle.org/dev/Access_API)
