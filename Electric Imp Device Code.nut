// Debouce library
#require "Button.class.nut:1.2.0"
//server.log(hardware.getdeviceid());

// Alias for gateOpen GPIO pin (active low)
gateOpen <- hardware.pin2;

// Alias for gateClose control GPIO pin (active low)
gateClose <- hardware.pin7;

// Configure 'gateOpen' to be a digital output with a starting value of digital 1 (high) (active low)
gateOpen.configure(DIGITAL_OUT, 1);

// Configure 'gateClose' to be a digital output with a starting value of digital 1 (high) (active low)
gateClose.configure(DIGITAL_OUT, 1);

// Alias for the GPIO pin that indicates the gate is moving (N.O.)
gateMovingState <- Button(hardware.pin8, DIGITAL_IN_PULLUP);

// Alias for the GPIO pin that indicates the gate is fully open (N.O.)
gateOpenState <- Button(hardware.pin9, DIGITAL_IN_PULLUP);

// Global variable to hold the gate state (Open = 1 / Closed = 0)
local lastGateOpenState = 0;

// Latch Timer object
local latchTimer = null

// Global variable indicate gate is in a latched open for xTime state (Latched Open = 1 / Closed/Free = 0)
local latchState = false;

agent.on("btn", function(data)
{
    switch (data.cmd) {
        case "open":
            gateOpen.write(0);
            if (latchTimer) imp.cancelwakeup(latchTimer);
            latchTimer = imp.wakeup(1, releaseOpen);
            server.log("Open command received");
            break;

        case "latch30m":
            gateOpen.write(0);
            if (latchTimer) imp.cancelwakeup(latchTimer);
            latchState = true;
            latchTimer = imp.wakeup(1800, releaseLatch);
            server.log("Latch30m command received");
            break;

        case "latch8h":
            gateOpen.write(0);
            if (latchTimer) imp.cancelwakeup(latchTimer);
            latchState = true;
            latchTimer = imp.wakeup(28800, releaseLatch);
            server.log("Latch8h command received");
            break;

        case "close":
            if (latchTimer) imp.cancelwakeup(latchTimer);
            gateOpen.write(1);
            gateClose.write(0);
            latchState = false;
            latchTimer = imp.wakeup(1, releaseClose);
            server.log("Close now command received");
            // Send close state manually
            if (lastGateOpenState == 0) break;
            local data = { "gatestate" : 3, "timer" : hardware.millis() };
            agent.send("gateStateChange", data);
            lastGateOpenState = 3;
            break;

        default:
            server.log("Button command not recognized");
    }
});

function releaseLatch() {
    latchState = false;
    if (latchTimer) imp.cancelwakeup(latchTimer);
    gateOpen.write(1);
    //server.log("Timer released Latch gateOpen switch contact");
}

function releaseOpen() {
    if (latchState) return;     // Exit if gate is latched open to prevent canceling latch
    if (latchTimer) imp.cancelwakeup(latchTimer);
    gateOpen.write(1);
    //server.log("Timer released gateOpen switch contact");
}

function releaseClose() {
    if (latchTimer) imp.cancelwakeup(latchTimer);
    gateClose.write(1);
    //server.log("Timer released gateClose switch contact");
}

gateMovingState.onPress(function() {     // The relay is activated, gate is moving
    if (lastGateOpenState == 2) return;
    server.log("Gate is opening");
    local data = { "gatestate" : 1, "timer" : hardware.millis() };
    agent.send("gateStateChange", data);
    lastGateOpenState = 1;
    local netData = imp.net.info();
    server.log("RSSI: " + netData.interface[netData.active].rssi)
}).onRelease(function() {               // The relay is released, gate is at rest
    server.log("Gate is closed");
    local data = { "gatestate" : 0, "timer" : hardware.millis() };
    agent.send("gateStateChange", data);
    lastGateOpenState = 0;
});

gateOpenState.onPress(function() {       // The relay is activated, gate is fully open
    server.log("Gate is open");
    local data = { "gatestate" : 2, "timer" : hardware.millis() };
    agent.send("gateStateChange", data);
    lastGateOpenState = 2;
}).onRelease(function() {               // The relay is released, gate is not fully open
    server.log("Gate is closing");
    local data = { "gatestate" : 3, "timer" : hardware.millis() };
    agent.send("gateStateChange", data);
    lastGateOpenState = 3;
});

// Set the timeout policy to RETURN_ON_ERROR, ie. to continue running at disconnect (loss of wifi)
server.setsendtimeoutpolicy(RETURN_ON_ERROR, WAIT_TIL_SENT, 10);

// Define the disconnection handler
function disconnectHandler(reason) {
  if (reason != SERVER_CONNECTED) {
    // Attempt to reconnect
    server.connect(disconnectHandler, 60);
  }
}

// Register the unexpected disconnection handler function, disconnectHandler()
server.onunexpecteddisconnect(disconnectHandler);
