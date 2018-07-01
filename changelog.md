# Changelog for Ministry Platform API Wrapper


## 2.2.10 (2018-07-01)

- Fixed distinct() to parse ture, false, 1 and 0 and convert to a string that the API requires

- Fixed a bug where post() and put() methods were not returning the updated record or the subset specified by the select() method