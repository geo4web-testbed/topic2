# Task 3:

In task 3 we address the issue of creating a searchable datastore.

To make the datastore searchable Spotzi will provide the users with an API that makes a direct connection to the database. 
Each user has his own API key which can be used to search datasets they have access to. 
This API can also be used to programmatically edit the datasets. 
Again, only if the user has the rights to edit. (Issues from task 1 are taken into account here)
Public datasets can be accessed without an API key. 
This service will not only allow the user to search the data, but also allows the users to export the data in their preferred format. 
Format types Spotzi will offer will contain text files like CSV, XML or JSON but also PNG/JPGs can be exported trough this API.