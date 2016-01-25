# Task 1:

In task 1 we address the issue of updating the data.

Whereas the strength of OSM is its crowdsourcing/VGI5 capabilities, authoritative data should not be directly editable by the public. 
Instead, input from citizens should be checked and approved prior to inclusion. 
Users should, however, be allowed to tag objects freely as a means of coupling
their ontology to that of the authorities. Measures should be taken to address these issues by e.g. implementing:
- 	user-management: authoritative accounts may edit information directly, citizen/user accounts may suggest edits which affect the data only when approved by data owners. 
	No account is needed to read/download data. Data owners can only edit data they own.
- 	two-tier tagging system: 
	‘authoritative’ tags can only be managed by the dataset’s owner. 
	‘Community’ tags can be edited by anyone. 
	The latter are meant as a means for users to match their ontology to that of the data owner. 
	Users should be able to search through both sets of tags.
- a mechanism through which citizens/users can report errors and suggest changes/update to the data.

To address this issue, Spotzi is creating different authority levels in the platform. 
We're starting with the following levels:

Visibility:
- The data is visible to everyone
- The data is visible to a selected group
- The data is visible to noone

Editability:
- The data is editable by everyone
- The data is editable by a selected group
- The data is editable by noone

Furthermore we're adding the possibility to work with proposed edits.
The Authorithy of a specific data set can allow others to make proposed edits, but not edit the data source directly.


After speaking with the municipality of Bergen op Zoom we've added some other issues to this task:
- 	A new visibility/editability level: A certain selection of the dataset is visible/editable by a certain user. 
	Example: A user can see the whole dataset but can only edit his own house
-	An other important thing was the possibility to import authority settings from their own systems. 
	A municipality or other authority already has their own levels of authority restrictions. 
	The platform needs to be able to copy those configurations.
