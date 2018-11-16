<?php

// require($_SERVER['DOCUMENT_ROOT'].'/wp-load.php');
// ^need that if not using page template and accessing this file directly

/*

Template Name: Move Events API
K.B. Carte
Aug 2018

This file is used in conjunction with move_events.py

*/


$data = json_decode( file_get_contents('php://input') );
$data->cost = unserialize($data->cost)['cost'];

// Simple dirty check to see if the data is at least the right kind
if(empty($data->title)){
    echo '<img src="https://img.memecdn.com/morphius-404_o_1938383.jpg" >';
    die();
}

$my_post = array();
$start_stamp = $data->start;
$end_stamp = $data->end;

$my_post['post_title'] = $data->title;
$my_post['post_status'] = 'publish';
$my_post['post_author'] = 37; // 37 is user ID of me, kcarte/K.B. Carte
$my_post['post_content'] = $data->content;
$my_post['EventAllDay'] = $data->allday;
$my_post['EventStartDate'] = date('Y-m-d', $start_stamp);
$my_post['EventEndDate'] = date('Y-m-d', $end_stamp);
$my_post['EventStartHour'] = date('g', $start_stamp);
$my_post['EventStartMinute'] = date('i', $start_stamp);
$my_post['EventStartMeridian'] = date('a', $start_stamp);
$my_post['EventEndHour'] = date('g', $end_stamp);
$my_post['EventEndMinute'] = date('i', $end_stamp);
$my_post['EventEndMeridian'] = date('a', $end_stamp);

if( !empty($data->cost) ){
    $my_post['EventCost'] = $data->cost;
}

$venue = array();
$venue_id = -1;
if( !empty($data->venue) ){
    $venue['Venue'] = $data->venue;
    if( !empty($data->country) ){
        $venue['Country'] = $data->country;
    }
    if( !empty($data->address) ){
        $venue['Address'] = $data->address;
    }
    if( !empty($data->city) ){
        $venue['City'] = $data->city;
    }
    if( !empty($data->state) ){
        $venue['State'] = $data->state;
    }
    // create venue ourselves since tribe_create_event lies in their docs.
    $venue_id = tribe_create_venue($venue);
    $my_post['Venue'] = ['VenueID' => $venue_id];
}

$contact = array();
$org_id = -1;
if( !empty($data->contact_name) ){
    $contact['Organizer'] = $data->contact_name;
    if( !empty($data->contact_email) ){
        $contact['Email'] = $data->contact_email;
    }
    if( !empty($data->contact_url) ){
        $contact['Website'] = $data->contact_url;
    }
    if( !empty($data->contact_phone) ){
        $contact['Phone'] = $data->contact_phone;
    }
    // create organizer ourselves since tribe_create_event lies in their docs.
    $org_id = tribe_create_organizer($contact);
    $my_post['Organizer'] = ['OrganizerID' => $org_id];
}

// putting the ticket url in custom meta since there isn't a field in the
// new plugin for it. Also adding the associated venue/organizer id's
// incase they aren't pulled in by the plug-in. Testing is showing
// it's not doing what they advertize in the docs on the backend anyway.
$my_post['meta_input'] = array( "ticket_url" => $data->ticket_url,
                                "latitude" => $data->latitude,
                                "longitude" => $data->longitude,
                                "venue_id" => $venue_id,
                                "organizer_id" => $org_id );

// create the event
$id = tribe_create_event( $my_post );

// spits out to the python console.
// var_dump($my_post);
// var_dump($venue);
// var_dump($contact);
// var_dump($id);
echo "[+] Event Created: ".$id;
