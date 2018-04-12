# Usora
Usora is a user management system including auth, user settings, user information, and single sign on.

## Single Sign On
### How it Works
Users can be signed in on any domain running this package with a single login attempt from any of the domains as long as they are all connected to the same usora database. This is possible by setting authentication cookies on all participating domains after the login succeeds using html img tags.