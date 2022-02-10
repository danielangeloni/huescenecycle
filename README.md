<div align="center">
  <img src="https://user-images.githubusercontent.com/84752451/153381764-8f6fa6bc-0fc7-439c-89e3-bd01335af896.png" height="300">
  
  # huescenecycle
  
</div>

PHP script that fetches all the philips hue scenes for a specific room and picks a random one.

#  Installation
This script works best when placed on a web server. In my use case, I wrote it in PHP purely so I can put it on a web server and use HomeKit automations to make a curl request to the webserver and run the script. Alternatively, it can also be kept locally and run using a cron job.

## Parameters
Inside of the run.php file, edit the following:
```
private $group = {the ID int of the group};
private $hueIP = {the LAN IP of the hue bridge};
private $hueUsername = {the API username};
```
### Username (API token)
First we need to get a username so we can interact with the bridge via the API. To do this, go to http://{your hue IP}/debug/clip.html. 
In the URL field, enter:
```
/api/
```
And in the Message Body, enter 
```
{"devicetype":"{choose an app name}"}
```
Then click the link button on your bridge and click POST. Then you should get a response that looks like this:
```
[
    {
        "success": {
            "username": "{your new username"}"
        }
    }
]
```
Set the value of `$hueUsername` to the value of the username we just got.

### Group (Hue room)
Next, in a web browser navigate to `http://{hue IP}/api/{username}/groups`, then (Control + F or Command + F) and search for the name of the room that you want to use. Then grab the number on the left of that (the parent of the object). For example:
```
..."action":{"on":false,"alert":"none"}},"8":{"name":"Gym","lights":["9","8","4","10","3"],"sensors":...
```
In the above snippet, the room I wish to use is `Gym` and the Group ID is `8`.

Set the value of `$group` to the value of the group number we just got.

# User Customisation
## Brightness
The script currently loads the scene with a brightness of `70%`. The Hue API uses a brightness scale of 0-254 (no clue why). To adjust this, modify line 78 and set the value 179 to your desired level. To figure out your desired level use this math equation:
```
(254 / 100) * {your brightness level}
```
## Adding / Removing Scenes
This scene works as an extension to the hue app. To add / remove scenes, just modify the scenes in the room.

## Manually excluding scenes
On line 60, you can exclude scenes from the randomly chosen list. This is useful if you want to keep the scene in the Hue app and just want it to not be chosen by this script. In my case, I didn't want the script to pick Bright, Dimmed, or Nightlight. Do not remove `Scene previous` as this is not actually a scene, but it is an internal scene that Hue uses to restore the group to its previous scene/state.

# Limitations
Currently, there is no support (that I know of or could find online) with starting Dynamic scenes with the API. Even if the chosen scene is a Dynamic Scene, if its called with the API, it wont play. Instead it will just use the static fallback colors of the scene. This is a limitation with the current API and is rumoured to be fixed in a coming update.

# Purpose / My Motivation
I wanted to randomly sort through a list of scenes automatically, without having to manually chose them in the Hue app.
