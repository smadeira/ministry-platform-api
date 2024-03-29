# Changelog for Ministry Platform API Wrapper

## 5.1.0 (2022-06-14)
- Added support for userID and GlobalFilterId parameters
- Added postGet() method on the Table API to execute a "GET" request using a POST verb. Supports the /tabe/{tablename}/get endpoint 
in the MP API. Like the get() method, this will return all rows that meet the criteria which could be thousande of rows. Sample Code:

```php  
$idList = [385423, 385424];
$sel = "Donation_Date, Donor_ID_Table_Contact_ID_Table.Display_Name, Donation_Amount, Payment_Type_ID_Table.[Payment_Type]";
$filter = "Donation_Date > '2022-05-01' AND Donations.Payment_Type_ID <> 6 and Donations.Domain_ID = 1";

$donations = $mp->table('Donations')
  ->select($sel)
  ->filter($filter)
  ->orderBy('Donation_Amount')
  ->ids($idList)
  ->postGet(); 
```
- Updated README.md to reflect that the latest versions of phpdotenv have a different method for initialization. 
To use getenv(), we need the createUnsafeImmutable() method because getenv() is not thread-safe. 
 
```php
// Get environment variables
$dotenv = Dotenv\Dotenv::createUnsafeImmutable(__DIR__);
$dotenv->load();
```
## 5.0.1 (2020-12-17)
- Added compatibility for Laravel 8 with updated dependencies
- Added requirement for php 7.3 or greater


## 4.0.x (2020-08) 
- Added compatibility for Laravel 7 with update dependencies

## 3.1.6 (2020-08-15)
- Removed compatibility with Laravel 7 (required upgrade to phpdotenv 4.x) because it brok all backward compatibility.  Laravel 7 with new phpdotenv will be released as version 4.x.

## 3.1.0 (2020-05-14)
- Added support for the /files API endpoints - full CRUD.  See Readme for some examples and the code
for deatils.
- Updated composer.json to utilize Illuminate 5.x, 6.x or 7.x to support Laravel 7.x

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
