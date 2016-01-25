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
