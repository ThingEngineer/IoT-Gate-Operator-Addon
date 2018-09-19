// HTTP handler function
function httpHandler(req, resp) {
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
