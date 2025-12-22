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
const int typeDelay = 10;
const int typeSlashDelay = 500;


void sleepme(char c) {
  // Sleep for a given period
  if(c == 'S') { delay(2); return; }
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
void typeme(String s, int offset) {
  for(int i=offset; i<s.length(); i++) {
    const char c = s.charAt(i);
    Keyboard.print(c);
    delay(c == '/' ? typeSlashDelay : typeDelay);
  }
  Keyboard.println("");
  delay(typeDelay);
}

WiFiClient wifi_client;
HttpClient http_client = HttpClient(wifi_client, serverAddress, serverPort);

void setup() {
  // put your setup code here, to run once:
  pinMode(LED_BUILTIN, OUTPUT); // initialize as output pin

  int wifiStatus = WL_IDLE_STATUS;
  blinkme("sl");
  while (wifiStatus != WL_CONNECTED) {
    // Connect to WPA/WPA2 network:
    wifiStatus = WiFi.begin(ssid, pass);
    // wait N seconds for connection:
    delay(8000);
  }
  blinkme("sss");
  Keyboard.begin();
  // TODO Reconnect if wifi lost
  // https://docs.arduino.cc/language-reference/en/functions/wifi/wificlass/#wifistatus
}



void loop() {
  // put your main code here, to run repeatedly:
  http_client.get(url);
  int statusCode = http_client.responseStatusCode();
  String response = http_client.responseBody();
  if(statusCode == 200) { 
    if(response.startsWith("NOTHING")) {
      blinkme("S");
    }
    else if(response.startsWith("COMMAND,")) {
      typeme(response, 8);
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


