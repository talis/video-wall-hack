video-wall
==========

This repo was a Talis hackday project to set up a video wall. We had a bunch of ancient laptops in the cupboard plus a 1Gb switch, and we wanted to see if we could set up a 2x2 bank of screens to do various things:

1. Show a desktop over the whole wall
1. Show an HTML5 video over the whole wall
1. Open a browser on single screens, on a round-robin basis

Thus, this repo contains several sub-projects in one: share-desktop, html5-video and browser-open. These are contained in separate folders, and may be used individually.

The wall uses one slave machine per screen, and one master controller. We used Ubuntu 12.04 and 14.04 for all of them, though any distro would likely be OK.

The 2x2 size is not fixed, and could be changed for all of these approaches. It's rather hardwired into most of this stuff, but feel free to improve!

Share desktop
---

Showing a desktop over a number screens seemed to be a natural job for screen-sharing software, so we looked at various VNC server and client packages. There are two basic strategies that can be used:

* Share the target screen with one server on the master, use the `-shared` switch, get each slave to listen on the same port, and then chop each window in some way to get the relevant tile piece
* Share the target screen in pieces on the master, get each slave to listen on different ports, and then full-screen and scale each slave viewer

The second of these was easier to do in practice, so was the solution we went with. On our old hardware, performance was a bit laggy, and so isn't suitable for video or animation -- but ordinary desktop usage on reasonable hardware would be fine for most uses.

Here's roughly how to get it running:

* Set up public key SSH access from your master machine to each of your slaves, so you can do `ssh user@192.168.1.x` without a password. This is pretty standard, but some brief details to do this have been added to master.php.
* Install x11vnc on the master: `sudo apt-get install x11vnc`
* Install sshd and a viewer on each slave: `sudo apt-get install openssh-server xtightvncviewer`
* Copy `client.php` to each slave (the path for each is configured seperately in master.php)
* Add configuration details for each slave in master.php
* Run master.php and cross your fingers

I expect the VNC server and viewer could be swapped out for others, but the scripts would probably need tweaking. The tiling is done on the server, and we do zoom-up and full-screening (removing title/scroll bars) on the client via switches.

HTML5 video
---

(todo)

Open browser
---

(todo)

Other ideas
---

In our initial scribbling session, we considered other ways to create a monitor wall:

* X.org to push tiles of video display to slave machines. We found this one to be frustrating to configure in practice!
* Streaming a video and displaying via VLC on each machine. This can be done with Raspberry Pis, since the video rendering is offloaded onto hardware. We didn't have sufficient time for this, but it'd be an interesting addition.

