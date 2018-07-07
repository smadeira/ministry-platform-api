# Changelog for Ministry Platform API Wrapper

## 2.3.0 (2018-07-10)

- Added authorization_code grant type to API

- Added getSingle() on the Tables endpoint to get a single record in this form: /tables/{table}/{id}.  Specify the table name, the id and the optional select list.

  Example usage: 
  
  ```php
  $contact = $mp->table('Contacts')  			 
                  ->select('Contact_ID, Display_Name, Email_address, Mobile_Phone')
                  ->record(67102)
                  ->getSingle();

- Restructured code to handle multiple grant types (authorization_code and client_credentials so far)

## 2.2.10 (2018-07-01)

- Fixed distinct() to parse ture, false, 1 and 0 and convert to a string that the API requires

- Fixed a bug where post() and put() methods were not returning the updated record or the subset specified by the select() method