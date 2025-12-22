// Magic keyboard

#include <WiFiNINA.h>
#include <ArduinoHttpClient.h>
#include <Keyboard.h>

// ------------- CONFIG ----------------
char ssid[] = "...";
char pass[] = "...";
char serverAddress[] = "192.168.100.100";
const int serverPort = 80;
char url[] = "/URLPATH/magic-keyboard/command.php?device=0";
const int loopDelay = 3000;
const int typeDelay = 40;


void sleepme(char c) {
  // Sleep for a given period
  if(c == 'S') { delay(4); return; }
  if(c == 's') { delay(200); return; }
  if(c == 'm') { delay(500); return; }
  if(c == 'l') { delay(1000); return; }
  delay(5000);
}

void blinkme(String pattern) {
  // Blink LED in a pattern
  // pattern: made of chars s,m,l,x -- see sleepme()
  int l = pattern.length();
  for(int i=0; i<l; i++) {
    digitalWrite(LED_BUILTIN, HIGH);
    sleepme(pattern.charAt(i));
    digitalWrite(LED_BUILTIN, LOW);
    sleepme('s');
  }
}

// https://docs.arduino.cc/language-reference/en/functions/usb/Keyboard/
// https://docs.arduino.cc/language-reference/en/functions/usb/Keyboard/keyboardModifiers/
// https://github.com/arduino-libraries/Keyboard/blob/master/src/KeyboardLayout_en_US.cpp
// The first character defines a "no-print" character that can be used for delays
// The second character defines a "newline" character
void typeme(String s, int offset) {
  if(s.length() < offset + 2) { blinkme("lssl"); return; }
  const char noprint = s.charAt(offset);
  const char newline = s.charAt(offset+1);
  for(int i=offset+2; i<s.length(); i++) {
    const char c = s.charAt(i);
    if(c == newline) { Keyboard.println(""); }
    else if(c != noprint) { Keyboard.write(c); }
    delay(typeDelay);
  }
}

WiFiClient wifi_client;
HttpClient http_client = HttpClient(wifi_client, serverAddress, serverPort);

void wifiConnect() {
  int wifiStatus = WL_IDLE_STATUS;
  blinkme("sl");
  while (wifiStatus != WL_CONNECTED) {
    // Connect to WPA/WPA2 network:
    wifiStatus = WiFi.begin(ssid, pass);
    // wait N seconds for connection:
    delay(5000);
  }
  blinkme("sss");
}

void setup() {
  // put your setup code here, to run once:
  pinMode(LED_BUILTIN, OUTPUT); // initialize as output pin
  wifiConnect();
  Keyboard.begin(KeyboardLayout_en_US);
}



void loop() {
  // put your main code here, to run repeatedly:

  // https://docs.arduino.cc/language-reference/en/functions/wifi/wificlass/#wifistatus
  if(WiFi.status() != WL_CONNECTED) {
    blinkme("ssll");
    WiFi.disconnect();
    wifiConnect();
  }

  http_client.setHttpResponseTimeout(8000);
  http_client.get(url);
  int statusCode = http_client.responseStatusCode();
  String response = http_client.responseBody();
  if(statusCode == 200) { 
    if(response.startsWith("NOTHING")) {
      blinkme("S");
    }
    else if(response.startsWith("COMMAND")) {
      typeme(response, 7);
      blinkme("sl");
    }
    else {
      blinkme("lsl");
    }
  }
  else {
    blinkme("lll");
  }

  delay(loopDelay);
}


// https://docs.arduino.cc/hardware/nano-33-iot/
// https://docs.arduino.cc/tutorials/nano-33-iot/wifi-connection/
