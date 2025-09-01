# Application Works - User Types and Roles

## User Roles

There are 3 types of users in the System
1) Administrators - Application configuration and security.
2) Producers - Factory workers that produce Jobs that have been ordered by Consumers.
3) Consumers - End users in the dashboard that can order a Job.

Under each of the types there are roles that allow for various levels of access and functionality
1) Administrators
   1) SuperAdmin - Access to all application settings a security configurations
   2) Admin - Access to most things - excluded from security configurations
2) Producers
   1) Manager - Report level read only access. Cannot produce jobs or change job statuses
   2) Supervisor - Currently unused but future plans to allow management of Operators
   3) Operator - People that produce the Jobs on the factory floor
3) Consumers
   1) SuperUser - Currently unused but future plans to allow management of Users
   2) User - General user that can order or create a Job
