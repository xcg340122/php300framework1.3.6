function SocketIO(address,port){
    var address = /[\d]{1,3}.[\d]{1,3}.[\d]{1,3}.[\d]{1,3}/.test(address)? address : '127.0.0.1';
    var events = {};
    var callbacks = {};
    var ws = new WebSocket('ws://' + address + ':' + port);
    ws.onmessage = function(msg){
        var data = JSON.parse(msg.data);
        var event = data.event;
        var etype = data.etype;
        var msg = data.msg;
        if(etype == 'callback' && typeof callbacks[event] !== undefined){
            callbacks[event].call(this,msg);
        }else if(typeof events[event] !== undefined){
            events[event].call(this,msg)
        }
    }

    this.on = function(event,callback){
       switch (event){
           case 'connect':
               ws.onopen = callback;
               break;
           case 'close':
               ws.onclose = callback;
               break;
           default:
               events[event] = callback;
               break;
       }
    }

    this.emit = function(event, msg, callback){
        if(typeof callback !== undefined ) callbacks[event] = callback;
        var data = {
            'event':event,
            'etype': typeof callback !== undefined ? 'callback' : 'event',
            'msg':msg
        };
        ws.send(JSON.stringify(data));
    }

    this.response = function(event, msg){
        var data = {
            'event':event,
            'etype': 'callback' ,
            'msg':msg
        };
        ws.send(JSON.stringify(data));
    }

    this.close = function(){
        ws.close();
    }

}



