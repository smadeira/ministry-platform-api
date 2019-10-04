# Changelog for Ministry Platform API Wrapper

## 3.0.3 (2019-10-04)
- Updated composer.json to utilize Illuminte 5.x OR 6.x to support Laravel 6.x

## 3.0.2 (2019-09-20)
- Added skip() method to allow manual pagination.  Usage could look like this to get 20 rows starting with the 40th record.
```php
$rowsToSkip = 40;
$contacts = $mp->table('Contacts')
        ->select("Contacts.Contact_ID, Household_ID, First_Name, Middle_Name, Last_Name, Display_Name, Gender_ID_Table.[Gender], Nickname")        
        ->top(20)
        ->skip($rowsToSkip)
        ->get();
```
You can increment $rowsToSkip and make the next request.

## 3.0.1 (2019-07-17)
- Changed vlucas/phpdotenv to version 3 to be compatible with Laravel 5.8 and other packages that need dotenv 3.

## 2.3.4 (2019-05-15)
- Added deleteMultiple() method to allow deleting muiltiple records in one API call

Example usage:

```php
// IDs are Event_Participant_IDs (primary Key)
$rec = ['IDs' => [309599,309598] ];

$ep = $mp->table('Event_Participants')
             ->records($rec)
             ->deleteMultiple();
```

## 2.3.3 (2019-02-06)
- Fixed a timing issue in client credentials and authorization code authentication.  Made the getToken request 
synchronous to ensure it compeletes before calling the API.


## 2.3.0 (2018-07-25)
- Restructured code to handle multiple grant types (authorization_code and client_credentials.)

- Added authorization_code grant type to API wrapper.

- Created Laravel middleware to handle the authorization code flow. Middleware file is in the middleware folder 
and should be moved to the middleware folder (app\Http\Middleware, by default) of your application.

- Added Documentation folder for implementation guides - currently only the Laravel Middleware guide is available.

- Added getSingle() on the Tables endpoint to get a single record in this form: /tables/{table}/{id}.  Specify 
the table name, the id and the optional select list.

  Example usage: 
  
  ```php
  $contact = $mp->table('Contacts')  			 
                  ->select('Contact_ID, Display_Name, Email_address, Mobile_Phone')
                  ->record(67102)
                  ->getSingle();

```

## 2.2.10 (2018-07-01)

- Fixed distinct() to parse true, false, 1 and 0 and convert to a string that the API requires.

- Fixed a bug where post() and put() methods were not returning the updated record or the subset 
specified by the select() method.
