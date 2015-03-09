#JengaScript Instant API Mocker
This API mocker is a quick and dirty development solution that mocks an API and allows CORS so that application front-ends can utilize it from anywhere. Allowing CORS on the client-side is trivial and this approach requires no integration (or code to remove) once the production API is ready.

##Usage
Drop the contents of this folder onto an apache server and add whatever dummy data you want in JSON files.
Name these files according to the url you would like them to be accessible at (i.e. for an endpoint at /things/ add a file called things.json to the top directory).
The mocker also supports folders (i.e. for /things/little-things/ put a file called little-things.json inside a folder called things).
See example at http://www.JengaScript.com/data/
