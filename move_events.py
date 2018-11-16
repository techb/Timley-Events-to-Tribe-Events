import requests
import MySQLdb
import simplejson as json
from pprint import pprint

# pip3 install requests
# pip3 install simplejson
# pip3 install mysqlclient

# Command to copy a database manually, MUCH faster than using phpmyadmin to import it.
# ~$ mysql  --user=yourmysqlusername --password=yourmysqlpassword  --host=localhost thedatabasename < thedumpfile.sql

def hitAPI(payload_dict):
    endpoint = 'http://visitwv.staging.wpengine.com/api-events/'
    try:
        r = requests.post(endpoint, json=payload_dict, timeout=60)
        status = r.status_code
        resp = r.text

        return (status, resp)
    except requests.exceptions.Timeout:
        return (payload_dict['post_id'], "[-] TIMEOUT")

def queryDB(sql, getall=True):
    print('[+] Connecting to Database')
    db = MySQLdb.connect("127.0.0.1", "root", "root", "vswv-prod")
    cursor = db.cursor()
    print('[+] Runnin query: "%s"' % sql)
    cursor.execute(sql)
    if getall:
        data = cursor.fetchall()
    else:
        data = cursor.fetchone()

    db.close()
    return data

def buildEvent(row):
    # regular post to get description and title
    sql = "SELECT * FROM wp_posts WHERE id='%d'" % row[0]
    post = queryDB(sql, False)

    event = {'title': post[5].strip(), \
            'content': post[4].strip(), \
            'post_id':row[0], \
            'start':row[1], \
            'end':row[2], \
            'allday':bool(row[4]), \
            'venue':row[10].strip(), \
            'country':row[11].strip(), \
            'address':row[12].strip(), \
            'city':row[13].strip(), \
            'state':row[14].strip(), \
            'postal_code':row[15], \
            'show_map':row[16], \
            'contact_name':row[17].strip(), \
            'contact_phone':row[18].strip(), \
            'contact_email':row[19].strip(), \
            'contact_url':row[20], \
            'cost':row[21], \
            'ticket_url':row[22], \
            'latitude':float(row[29]), \
            'longitude':float(row[30]) }
    return event

def getAllEvents():
    # the old events
    sql = "SELECT * FROM wp_ai1ec_events"
    data = queryDB(sql)

    all_events = []
    print('+ Entries found: %d ' % len(data))
    for row in data:
        event = buildEvent(row)
        all_events.append(event)

    return all_events


events = getAllEvents()

while len(events) > 0:
    event = events.pop(0)
    res = hitAPI(event)
    if res[1] == "[-] TIMEOUT":
        events.append(event)
        print("-"*50)
        print("[-] TIEMOUT\n")
        print("-"*50)
    else:
        print("+"*50)
        print(res[1])

print("++ All finished =) ++")
