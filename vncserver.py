import flask
from flask import Flask
import multiprocessing
import subprocess
import os
import urllib

app = Flask(__name__)

mode_fullscreen = 0
mode_windowed = 1
current_mode = mode_fullscreen
process = None

current_monitor = 0
monitors = [
    'mark@192.168.100.1', 'talis@192.168.100.2', 
    'talis@192.168.100.3', 'jon@192.168.100.4']
processes = []


def kill_processes():
    global processes

    processes = []
    os.system('killall -9 php')
    os.system('killall -9 x11vnc')


def setup_mode(mode):
    print 'setup'
    if current_mode == mode:
        return

    kill_processes()

    print 'fullscreen'
    if mode == mode_fullscreen:
        print 'kill'
        setup_fullscreen()

    


def run_master():
    os.system('/usr/bin/master')


def setup_fullscreen():
    global process
    process = multiprocessing.Process(target=run_master)
    process.start()
    return 'launched full screen'



def launch_on_master():
    url = urllib.unquote_plus(flask.request.args.get('url'))
    cmd = './spawn_browser '+ url   
    process = subprocess.Popen(cmd, shell=True, stdout=subprocess.PIPE)
    global processes
    processes.append(process)
    return 'launching ' + url + ' on master'


def get_current_monitor():
    global current_monitor
    monitor = current_monitor
    current_monitor = 0 if monitor + 1 >= len(monitors) else monitor + 1
    return monitors[monitor]


def launch_externally():
    host = get_current_monitor()
    url = urllib.unquote_plus(flask.request.args.get('url'))
    cmd = './spawn_remote_browser '+ host +' '+ url   
    process = subprocess.Popen(cmd, shell=True, stdout=subprocess.PIPE)
    
    global processes
    processes.append(process.returncode)
    return 'launched ' + url + ' on ' + host


@app.route("/spawn_window/<mode>")
def spawn_window(mode):
    if mode == 'fullscreen':
        setup_mode(mode_fullscreen)
        return launch_on_master()
    elif mode == 'windowed':
        setup_mode(mode_windowed)
        return launch_externally()
    

if __name__ == "__main__":
    app.run(debug=True)
