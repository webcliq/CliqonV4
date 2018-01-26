<?php
// Language strings for Cliqon 3 installation in English
global $istr;        
$istr = array(
	
	'Cliqon Instalación', 'Directorios', 'Directories grabable', 'Configuración', 'Archivo de Configuración', 'Base de datos', // 5
	'Crear base de datos', 'Finalizar', 'Listo', 'Prevista', 'Crear archivo de configuración',     // 10
	'Introduccción', 'Descomprimir y copiar archivos al directorio', 'Algunos directorios pueden necesitar ser creado', 'Algunos directorios pueden ser grabable', 'Descomprimir y copiar los archivos en el directorio del servidor web elegido', // 15
	'Prueba del directorio', 'Finalización cliqué', 'Copiar y editar el archivo de configuración básica', 'Crear y editar el archivo de configuración', 'Se ha producido un error al crear el archivo de configuración', // 20 is used
	'Tipo de base de datos', 'Nombre de base de datos', 'Nombre de usuario', 'Contraseña', 'Usuario administrativo', // 25
	'Servidor', 'Puerta', 'Contraseña administrativa', 'Códigos de idiomas y nombres', 'Códigos de idiomas y banderas',   // 30
	'Creando las tablas de la base de datos', 'Importación de los datos iniciales', 'Crear tablas', 'Importar data', 'Instalación finalizada', // 35
	'Tareas posteriores a la instalación', 'Elimine el directorio de instalación', 'Problemas de instalación', 'Sus tareas de administración iniciales', 'Elimine el directorio de instalación', // 40
	'Solución de problemas', 'Sistema de archivos', 'Próximo', 'Previo', 'Finalizado', // 45
	'URL del sitio', 'Email del sitio', 'tusitio.es', 'admin@tusitio.es', 'Editar archivo de configuración',  // 50      
);

$istr[51] = 'We dislike web systems that will not function without the every step of the Install process being carried out by the install function. There is always something that can go wrong with a fully automated process. All the Steps of the Cliqon installation process could be performed manually and this install procedure is a guide to the procedure.'; 

$istr[52] = 'You will unzip the files of the Cliqon system and copy them to the Directory and Domain of your choice.';

$istr[53] = 'The original compressed file contains certain important but initially empty Directories - these are: <strong>\tmp, \cache</strong> and <strong>\data</strong>. If for some reason the copying procedure does not allow for the copying of empty directories, these must be created.';

$istr[54] = 'The following Directories must be set to be writeable (permanently) - <strong>\tmp and \cache</strong>. If you intend to use SQLite then it is presumed that the database text file will be stored and in <trong>\data</strong> and if so, that must be writeable as well. ';

$istr[55] = 'Configuration files are stored in <strong>\config</strong> and depending on the nature of the system, the directories to which files and images will be uploaded, such as <strong>\images</strong> will need to be writeable as well.';

$istr[56] = 'The main configuration file <strong>config\config.cfg</strong> contains all the fundamental settings for a Cliqon System. To ensure that the install process runs the first time the system is accessed, a Config file does not exist and is created by copying from the Install Directory. Press the button to create an initial Config file.';

$istr[57] = 'On the form below, we tell the Cliqon system three things: The Database details including the name and type of server, database and the user credentials to access the database. Which languages will be used by the system and finally the initial administrator name and password to access the system. For full details of the Configuration file, please see the Cliqon Manual which is found in the \docs directory.';

$istr[58] = 'In order to provide temporary access to the Administrative system, an Administrative User is created in the Configuration file. When you have successfully accessed the Administrative system, you should create more Users with better password protected privileges and remove this entry from the Configuration File.';

$istr[59] = 'If this is a multi-lingual system, please create entries for the two character language code and associated Language names and flag or other language orientated image. In each case, the system generates the key and value pairs by exploding the submitted entry by virtue of a comma and then a pipe character. This is the normal mechanism used throughout Cliqon for string to array definitions.';

$istr[60] = 'Cliqon uses the Redbean ORM for all Database activities. This ensures that the system can support a number of Server types, is very secure and is very easy to work with when design new modules. Please visit <a href="http://RedbeanPHP.Com/" target="_blank">RedbeanPHP.Com</a> for more information.';

$istr[61] = 'There are many advantages to using an ORM and one of them is the ease by which the Tables are created and Data imported. Cliqon has a data dictionary file - <strong>\models\tablecreate.cfg</strong> in which the tables and fields are defined. Initial data is held as SQL files in the \data subdirectory. Press the button below and the tables will be created and data imported into these tables or error information will be displayed.';

$istr[62] = 'Crear Administrador';

$istr[63] = 'The installation process does not create Administrative Users in the Database, only in the Configuration file. You should access the Admin facility to create these users. We have noted that language translations may need to be created. Please now read the Manual to understand how Cliqon works and what you can do with it.';

$istr[64] = 'When you have completed the installation process, you should remove the installation Directory as it can be be run again and there is nothing to prevent an unauthorised person from creating problems in certain situations.';

$istr[65] = 'If the Installation process does not work, please use the <a href="http://forum.cliqon.com/" target="_blank">Support Forum</a> to discuss the problem and obtain a solution.';

$istr[66] = 'Creating the database';

$istr[67] = 'If you need to create the database, press this button. If privileges to create the database are not the same as the user that will access the database, you will need to edit the Config file.';

$istr[68] = 'Create Database';

$istr[69] = 'Titúlo del sitio';

$istr[70] = 'Descripción del sitio';

$istr[71] = 'Crear un usuario admininistrativo';  

$istr[72] = 'Existe';
$istr[73] = 'y se puede grabado';
$istr[74] = 'pero no se puede grabado';
$istr[75] = 'No existe';
$istr[76] = 'Usuario Root';
$istr[77] = 'Contraseña Root';

