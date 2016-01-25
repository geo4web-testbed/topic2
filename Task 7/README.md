# Task 7:

In task 7 we address the issue of Spatial Reference Systems.

Different coordinate systems, for example RD new vs WGS84, can lead to wrongly projected data. 
Moreover this is something a lot of non-geo users do not know much about.

Our opinion is that the end user, both the authority, initiator as other stakeholders, should not need to worry about spatial reference systems. 
Most of the users are not GIS experts and don’t have any experience with spatial reference systems. 
The users should be able to import any dataset without any indication of the spatial reference system. 

To make this possible the system should recognize the spatial reference system that is used (which is fairly easy until a certain extend). 
Then the system should project and transform all the imported data to a single reference system. 

Spotzi proposes to use the WGS 84 (EPSG:43261) system so the system can handle more than just Dutch datasets. 
When the user exports the data, the system needs to project and transform the data back to the original reference system. 
This way all the data in the system will be shown in the same way.