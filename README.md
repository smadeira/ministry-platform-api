<h1>Ministry Platform API Wrapper</h1>
<p>A PHP wrapper to access the Ministry Platform (MP) REST API.  This version is updated to
include the new changes to oAuth authentication rolling out in early 2018. Note that using the API inplies a knowledge of the 
MP data model. The API gives you access to each table in the database. It is up to you to pull the right data (or POST the right data) 
to the tables and then make any connections.</p>

<p>For example, you could create groups using the API and then you could create Participants using 
the API. After that is done, you can create Group Participants to add your participants to a group. Because Group 
Participants depends on the IDs of the group and the participant, the order in which you add the data is important.</p>

<p>This new version is available as a Composer Package...  More details on that as I figure out the process to include
the package and make use of it in my code.</p>

