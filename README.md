## Ministry Platform API Wrapper

#### For Laravel 8 support you need to download v5.x of the API wrapper

#### For dotenv 4.x support (including Laravel 7.x) you need to download v4.x of the API wrapper.

#### Note for v3: This API wrapper has been updated to work with dotenv version 3.0 (July 2019) and the new Ministry Platform oAuth changes (Spring 2018)

A PHP wrapper to access the Ministry Platform (MP) REST API.  This version is updated to
include the new changes to oAuth authentication rolling out in early 2018. Note that using the API implies a knowledge of the 
MP data model. The API gives you access to each table in the database. It is up to you to pull the right data (or POST the right data) 
to the tables and then make any connections.</p>

For example, you could create groups using the API and then you could create Participants using 
the API. After that is done, you can create Group Participants to add your participants to a group. Because Group 
Participants depends on the IDs of the group and the participant, the order in which you add the data is important.

## Installation

### Include the Package
This package is installed via Composer and the following assumes you have installed and initialized Composer for 
the project.  Please refer to the <a href="http://getcomposer.org" target="blank">Composer</a> web site for help on getting composer
installed and your initial composer.json created. 

To add the Ministry Platform API to your project, simply require this package:

```shell
composer require smadeira/ministry-platform-api
```

Or, you can edit your composer.json file directly to add the Ministry Platform API:
```
"require": {
        "php": ">=7.0.0",
        "smadeira/ministry-platform-api": "^3"
    },
```

### Update the package
After including the API Wrapper with composer, do a composer update to download the dependencies required for the 
API wrapper to function.

```
composer update
```
The update command will download all the dependencies (including the API wrapper code) to the vendor diretory.  Once this is done, you are ready to 
start development.

Mote: It's a good idea to run "composer update" every so often to download the latest version of the API wrapper and all of its dependencies.  That's the 
beauty of Composer. It manages all of that for you so you don't have to.

## Configuration
There are a few things that need to be done to configure the API wrapper to function in your environment.

### Connection Parameters
This package makes use of vlucas/phpdotenv to manage configuration variables.  In the root of your project, create a .env file with the following contents.  Ensure you
are using the correct URIs, client ID and secret for your installation.

```
# Current System Info
MP_API_ENDPOINT="https://connect.example.com/ministryplatformapi"
MP_OAUTH_DISCOVERY_ENDPOINT="https://connect.example.com/ministryplatform/oauth"
MP_API_SCOPE="http://www.thinkministry.com/dataplatform/scopes/all"

# Data from Ministry Platform API Client
MP_CLIENT_ID="mygccpco"
MP_CLIENT_SECRET="4064ec5d-f9e6-secret-code-89406642abc7"
MP_OAUTH_REDIRECT_URL="https://example1.com/oAuth"
```

### Loading the API Wrapper
At the top of your code you will need to do a couple things to get access to the API Wrapper. You need to include autoload capabilities and load the 
config settings from the .env file

This is an example of what the top of a script might look like to use the Table API and the Stored Procedures API.

```php
require_once __DIR__ . '/vendor/autoload.php';

use MinistryPlatformAPI\MinistryPlatformTableAPI as MP;
use MinistryPlatformAPI\MinistryPlatformProcAPI as PROC;

// Get environment variables
$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();

```  

## Usage
Usage is straight forward for client credentials flow.  Authenticate and execute your request.  
NOTE: if you are using Laravel and Authorization Code flow, check the laravel documentation 
in the documentation folder for a working code example.  
### Authentication
Assuming your .env parameters are correct, this will authenticate your code.  

```php
// For the Table API enpoints 
$mp = new MP();
$mp->authenticate();    

// For the Procedures API endpoint
$proc = new PROC();
$mp->authenticate();
```

### Execute select query
The API Wrapper uses the same syntax as the swagger page. You can define the table, the select statement, filter and 
orderBy clauses. This will return an array of events and then dump them to the screen.  Note that the data uses 
the familiar MP brand of SQL which is consistent with the platform.  

```php
// Get all Approved events happening in the next 30 days that are not cancelled and order by the Event Start Date
$events = $mp->table('Events')
         ->select("Event_ID, Event_Title, Event_Start_Date, Meeting_Instructions, Event_End_Date, Location_ID_Table.[Location_Name], dp_fileUniqueId AS Image_ID")
         ->filter('Events.Event_Start_Date between getdate() and dateadd(day, 30, getdate()) AND Featured_On_Calendar = 1 AND Events.[_Approved] = 1 AND ISNULL(Events.[Cancelled], 0) = 0')
         ->orderBy('Event_Start_Date')
         ->get();
         
print_r($events);
         
```

### The whole script
Here is a whole script that gets events in the next 30 days.

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use MinistryPlatformAPI\MinistryPlatformTableAPI as MP;


// Get environment variables
$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();


// Attempt to authenticate to the MP API
$mp = new MP();
$mp->authenticate();

$events = $mp->table('Events')
             ->select("Event_ID, Event_Title, Event_Start_Date, Meeting_Instructions, Event_End_Date, Location_ID_Table.[Location_Name], dp_fileUniqueId AS Image_ID")
             ->filter('Events.Event_Start_Date between getdate() and dateadd(day, 30, getdate()) AND Featured_On_Calendar = 1 AND Events.[_Approved] = 1 AND ISNULL(Events.[Cancelled], 0) = 0')
             ->orderBy('Event_Start_Date')
             ->get();

print_r($events);
```

### POSTing new Records
Data can be written to database via HTTP POST request. The new data is specified in the record and all required fields must be provided.
The record is an array of arrays (2D array) so multiple rows can be created in one API call. Note that even if you are creating only one 
row of data, the record data must still be a 2D array.

This example will add two participants to an event, each with the status of 02 Registered:
```php
// Create the array of records to POST
$rec = [];
$rec[] = ['Event_ID' => 12910, 'Participant_ID' => 46616, 'Participation_Status_ID' => 2];
$rec[] = ['Event_ID' => 12910, 'Participant_ID' => 46617, 'Participation_Status_ID' => 2];

$event = $mp->table('Event_Participants')
		->select("Event_Participant_ID, Event_ID, Participant_ID, Participation_Status_ID")
		->records($rec)			
		->post();
``` 

### Updating Records via PUT
Existing data can be updated via the HTTP PUT request.  The data to be updated requires the ID for the row (Event_ID, for example) and the fields to be updated. 
The new data is specified in the record.  The record is an array of arrays (2D array) so multiple updates can be executed in one statement. Note 
that even if you are updating only one row of data, the record data must still be a 2D array.  This PUT will update the participation status to 03 Attended

```php
$rec = [];
$rec[] = ['Event_Participant_ID' => 278456, 'Participation_Status_ID' => 3];

$event = $mp->table('Event_Participants')
		->select("Event_Participant_ID, Event_ID, Participant_ID, Participation_Status_ID")
		->records($rec)			
		->put();
```

Note that in both POSTing and PUTing, the API will return the resulting records.  If you only want to get back specific fields and 
not the whole record(s), you can specify those fields in the select() method.  Effectively, the API is doing the POST or PUT and then 
returning the results of a GET all in one operation.  

### Deleting Records
Warning: Deleting can do really bad things to your database.  Test in the sandbox! Use at your own risk.  Once it's gone, it's gone.

Existing table rows can be deleted by calling the delete method and passing the id of the row to delete.  For example, to delete the contact with 
contact_id of 24599, execute this command:

```php
$contact = $mp->table('Contacts')->delete(24599);
```

### Deleting Multiple Records
Existing rows can be deleted in mass with the deleteMultiple() method

```php
// IDs are Event_Participant_IDs (primary Key)
$rec = ['IDs' => [309599,309598] ];

$ep = $mp->table('Event_Participants')
             ->records($rec)
             ->deleteMultiple();
```


### Executing Procedures
Procedures can be executed using the Procedures API endpoint.  This example gets the selected contacts using a custom procedure written for our PCO integration.
```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use MinistryPlatformAPI\MinistryPlatformProcAPI as PROC;

// Get environment variables
$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();


// Attempt to authenticate to the MP API

$mp = new PROC();
$mp->authenticate();

$input = ['@SelectionID' => 26918];
$contacts = $mp->proc('api_MYGCC_PCOGetSelectedContacts')             
             ->procInput($input)
             ->exec();

print_r($contacts);
```

### Files Endpoints
The API wrapper now supports the files API operations. To load the Files API wrapper, do something like this:

```
require_once __DIR__ . '/vendor/autoload.php';

use MinistryPlatformAPI\MinistryPlatformFileAPI as MP;

// Get environment variables
$dotenv = Dotenv\Dotenv::create(__DIR__);
$dotenv->load();
```


### Listing Files For A Record
Provide the table name and the record_id to get information about each file
```
// Get metadata for the file(s) based on table and record id
   $fm = $mp->table('Contacts')->recordID(55309)->get();
   $metadata = json_decode($fm, true);
   print_r($metadata);
```
### Downloading Files for a Record
Using the FileId or the UniqueFileId, you can download the file.  The API returns the file as a stream that you can 
save to a local file.
```
// Get metadata for the file(s) based on table and record id
$fm = $mp->table('Contacts')->recordID(55309)->get();
$metadata = json_decode($fm, true);

// Loop through the metadata and retrieve each file
foreach ($metadata as $fileData) {

	echo 'Saving file: ' . $fileData['FileName'] . "\n";
	$fileID = $fileData['FileId'];

	$file = $mp->fileID($fileID)->get();
	$outfile = 'D:/Temp/' . $fileData['FileName'];

	file_put_contents($outfile, $file);
}
``` 
### Uploading A File
You can upload a file and metadata to a table/record.  This example includes some extra attributes.
```
$filename = 'D:/Pictures/Profile_Picture.jpg';

$response = $mp->table('Contacts')
            ->recordID(55309)
            ->file($filename)
            ->default()
            ->longestDimension(300)
            ->description('New Picture 2020')
            ->post();
```

### Modify A File
You can modify an existing file (including swapping out the image)
```
$file = $mp->fileId(63793)
              ->description('Second Picture From 2019')
              ->put();
```
### Delete a File
Pass in the FileID of the file and delete it
```
$result = $mp->delete(63792);
```
