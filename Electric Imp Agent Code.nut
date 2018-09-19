//local isconnectedFlag = device.isconnected();

// HTTP handler function
function httpHandler(req, resp) {
    if (device.isconnected())
    {
        try {
            local d = http.jsondecode(req.body);
            //server.log(d.c);
            if (d.c == "btn") {
                //server.log(d.val);
                device.send("btn", d.val);
                resp.send(200, "OK");
            }
        } catch(ex) {
            // If there was an error, send it back in the response
            server.log("error:" + ex);
            resp.send(500, "Internal Server Error: " + ex);
        }
    } else {
        deviceStateChangeHandler(0);
    }
}

// Register HTTP handler
http.onrequest(httpHandler);

// GateStateChange handler function
function gateStateChangeHandler(data) {
    // URL to web service
    local url = "http://projects.ajillion.com/save_gate_state";

    // Set Content-Type header to json
    local headers = { "Content-Type" : "application/json" };

    // Encode received data and log
    local body = http.jsonencode(data);
    server.log(body);

    // Send the data to your web service
    http.post(url, headers, body).sendsync();
}

// Register gateStateChange handler
device.on("gateStateChange", gateStateChangeHandler);

// Register deviceStateChange handler
device.onconnect(function() {
  deviceStateChangeHandler(1);
  //isconnectedFlag = false;
});

device.ondisconnect(function() {
  deviceStateChangeHandler(0);
});

function deviceStateChangeHandler(data){
    //if (isconnectedFlag == false && data == 0) return;
    //if (data == 1) isconnectedFlag = true;

    local json = { "devicestate" : data };

    // URL to web service
    local url = "http://projects.ajillion.com/save_device_state";

    // Set Content-Type header to json
    local headers = { "Content-Type" : "application/json" };

    // Encode received data and log
    local body = http.jsonencode(json);
    server.log(body);

    // Send the data to web service
    http.post(url, headers, body).sendsync();
}
