# Task 2:

In task 2 we address the issue working with existing workflows.


Consider the means by which suggested changes/updates can be fed into other workflows; 
Or show how such a workflow around OSM could long term replace the management of the initially imported Dutch dataset.

Spotzi addresses this issue from 2 different viewpoints:
1. To make sure that a spatial data platform can be connected to existing workflows, we need to make sure the platform is accessable by other programs.
2. We also need the platform to send feedback or trigger certain events in existing workflows.

It needs to be possible to have the platform as input for an existing workflow or output. 



#1. Input

Every user or organisation already has their own systems in which their spatial data is used. 
Because all these different organisations use different systems, there will always be need for some programming to be able to automatically connect to our platform.
We cannot create a platform that joins seamlessly with any system.
However, we will try to make the interconnection as easy as possible. 
To demonstrate this we will create a plugin that connects the platform to an existing QGIS (let's keep it open source for now) system.


#2. Output
The other part of using the platform as an part of a workflow is by triggering certain events after someone used the platform. 
This can be sending an email or text message to an designated person or sending a request to a webservice.
