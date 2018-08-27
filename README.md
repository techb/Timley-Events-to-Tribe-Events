# All in One Event Calendar TO Tribe Event Calendar
>Copy events from the Timely event plugin to the Tribe event plugin

Pulls the info from the old Timely event calendat plugin from the database directly. Then creates json to POST to an endpoint. The endpoint will consume the json and create a new event/venue/organizer, respectivly, in the new Tribe event calendar plugin.

This uses Python3 to connect to the databse and send the POST data. Php is running on the endpoint as a wordpress page template. This could be done by POSTing to the file directly as well.

I used a dump of the production database on my local machiene. You could query the DB remotely, but doing it local was quicker and didn't have to have the user/pass info for the remote DB

## Requirements
### Pyhton 3
- `pip3 install requests`
- `pip3 install simplejson`
- `pip3 install mysqlclient`

## Installation

- add the .sql to your local XAMPP/MAMPP db
- `mysql  --user=yourmysqlusername --password=yourmysqlpassword  --host=localhost thedatabasename < thedumpfile.sql`
- `move-events-endpoint.php` should be place in your themes directory on your remote server, dev or staging preffered


## Usage example
- `~$ python3 move_events.py `