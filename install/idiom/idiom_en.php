<?php
// Language strings for Cliqon 3 installation in English
global $istr; 
$istr = [
	
	'Cliqon Install', 'Directories', 'Directories writable', 'Configuration', 'Configuration file', 'Database', // 5
	'Create database', 'Finish', 'Ready to go', 'Preview', 'Create Configuration File',     // 10
	'Introduction', 'Unzip and copy files to directory', 'Certain directories may need to be created', 'Certain Directories must be writeable', 'Unzip and copy files to the chosen web server directory', // 15
	'Directory Check', 'Finish Clicked', 'Copy and edit the basic configuration file', 'Create and Edit the Configuration file', 'There was an error creating the Configuration File', // 20 is used
	'Database Type', 'Database Name', 'Username', 'Password', 'Administrative User', // 25
	'Server', 'Port', 'Administrative Password', 'Language Codes and Names', 'Language Codes and Flags',   // 30
	'Creating the database tables', 'Importing the initial data', 'Create tables', 'Import Data', 'Installation finished', // 35
	'Post installation tasks', 'Remove the installation directory', 'Installation problems', 'Your initial administration tasks', 'Remove the installation directory', // 40
	'Troubleshooting', 'File system', 'Next', 'Previous', 'Finish', // 45
	'Site URL', 'Site Email', 'yourwebsite.com', 'admin@yourwebsite.com', 'Edit configuration file',  // 50       
];

$istr[51] = 'We dislike web systems that will not function unless every step of the Install process has been carried out by the install function. There is always something that can go wrong with a fully automated process. All the Steps of the Cliqon installation process could be performed manually and this install procedure is a guide to that procedure.'; 

$istr[52] = 'You will unzip the files of the Cliqon system and copy them to the Directory and Domain of your choice.';

$istr[53] = 'The original compressed file contains certain important but initially empty Directories - these are: <strong>\tmp, \cache</strong> and <strong>\data</strong>. If for some reason the copying procedure does not allow for the copying of empty directories, these must be created.';

$istr[54] = 'The following Directories must be set to be writeable (permanently) - <strong>\tmp and \cache</strong>. If you intend to use SQLite then it is presumed that the database text file will be stored and in <trong>\data</strong> and if so, that must be writeable as well. ';

$istr[55] = 'Configuration files are stored in <strong>\config</strong> and depending on the nature of the system, the directories to which files and images will be uploaded, such as <strong>\views\uploads</strong> will need to be writeable as well. Config files are plain text files in a special Configuration INI format.';

$istr[56] = 'The main configuration file <strong>config\config.cfg</strong> contains all the fundamental settings for a Cliqon System. To ensure that the install process runs the first time the system is accessed, a Config file does not exist and is created by copying from the Install Directory. Press the button to create an initial Config file.';

$istr[57] = 'On the form below, we tell the Cliqon system three things: The Database details including the name and type of server, database and the user credentials to access the database. Which languages will be used by the system and finally the initial administrator name and password to access the system. For full details of the Configuration file, please see the Cliqon Manual which is found in the \docs directory.';

$istr[58] = 'In order to provide access to the Administrative system, an Administrative User is created. You can access the system using this User. When you have successfully accessed the Administrative system, you should create more Operators with more appropriate access privileges.';

$istr[59] = 'If this is a multi-lingual system, please create entries for the two character language code and associated Language names and flag or other language orientated image. In each case, the system generates the key and value pairs by exploding the submitted entry by virtue of a comma and then a pipe character. This is the normal mechanism used throughout Cliqon for string to array definitions.';

$istr[60] = 'Cliqon uses the Redbean ORM for all Database activities. This ensures that the system can support a number of Server types, is very secure and is very easy to work with when design new modules. Please visit <a href="http://RedbeanPHP.Com/" target="_blank">RedbeanPHP.Com</a> for more information.';

$istr[61] = 'There are many advantages to using an ORM and one of them is the ease by which the Tables are created and Data imported. Cliqon has a data dictionary file - <strong>\models\model.cfg</strong> in which the tables and fields are defined. Initial data is held as SQL files in the \data subdirectory. Press the button below and the tables will be created and data imported into these tables or error information will be displayed.';

$istr[62] = 'Create administrator';

$istr[63] = 'The installation process does not create Administrative Users in the Database, only in the Configuration file. You should access the Admin facility to create these users. We have noted that language translations may need to be created. Please now read the Manual to understand how Cliqon works and what you can do with it.';

$istr[64] = 'When you have completed the installation process, you should remove the installation Directory as it can be be run again and there is nothing to prevent an unauthorised person from creating problems in certain situations.';

$istr[65] = 'If the Installation process does not work, please use the <a href="http://forum.cliqon.com/" target="_blank">Support Forum</a> to discuss the problem and obtain a solution.';

$istr[66] = 'Creating the database';

$istr[67] = 'If you need to create the database, press this button. If the privileges to create the database are not the same as the user that will access the database, you will need to edit the Config file.';

$istr[68] = 'Create Database';

$istr[69] = 'Site Title';

$istr[70] = 'Site Description';

$istr[71] = 'Create an Administrative User';
$istr[72] = 'Exists';
$istr[73] = 'and is writeable';
$istr[74] = 'but is not writeable';
$istr[75] = 'Does not exist';
$istr[76] = 'Root user';
$istr[77] = 'Root password';