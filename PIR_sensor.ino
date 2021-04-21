// Libraries 
#include <WiFiEsp.h>
#include <SoftwareSerial.h>
#include <ArduinoJson.hpp>
#include <ArduinoJson.h>
#include "DHT.h"

// Define digital pins 
#define DHTPIN 4        // Digital pin connected to the DHT sensor
#define DHTTYPE DHT11   // DHT 11
#define LED 12          // Digital pin connected to the Red LED
#define LIGHTS 13       // Digital pin connected to the lab's Lights
#define BUZZER 7        // Digital pin connected to the buzzer
#define PIR 5

DHT dht(DHTPIN, DHTTYPE);         // Initialize DHT
SoftwareSerial WiFiSerial(2, 3);  // RX, TX

// Define variables
long BaudRate = 9600;
char ssid[] = "INFINITUM67F2_2.4";    // SSID
char pass[] = "Ma2F3YqgAb";           // WiFi password
int status = WL_IDLE_STATUS;

char server[] = "192.168.1.65";       // IP address
int port = 25000;                     // Server TCP port

WiFiEspClient client;                 // Initialize WiFi client             
unsigned long lastSend;
unsigned long lastRead;
unsigned long lastMotion;

boolean alarm = false;               // Checks if the alarm is on
boolean alarm_running = false;       // Verify if the alarm is running
String data = "";                    // Will receive the full response message from the server

void setup() {
  Serial.begin(9600);
  WiFiSerial.begin(BaudRate);
  // Initialize ESP module
  WiFi.init(&WiFiSerial);             // Associate serial port and initialize WiFi
  delay(100);
  // Try to make the connection
  if (WiFi.status() == WL_NO_SHIELD){
    Serial.println(F("WiFi module not detected"));
    // don't continue
    while(true);
  }

  // Attempt to connect to WiFi network
  while (status != WL_CONNECTED){
    Serial.print(F("Trying to connect to the WPA SSID: "));
    Serial.println(ssid);
    // Connecting to networking WPA/WPA2
    status = WiFi.begin(ssid, pass);
    delay(200);
  }

  // There is connection 
  Serial.println(F("Successful connection with WiFi network"));
  printWifiStatus();
  Serial.println();
  Serial.println(F("Starting the connection to the TCP server..."));
  // If you get a connection, report back via serial
  if (client.connect(server, port)){
    Serial.println(F("Established connection"));
  }

  // Declaring pinMode
  pinMode(LED, OUTPUT);
  pinMode(LIGHTS, OUTPUT);
  pinMode(PIR, INPUT);
  dht.begin();
  Serial.println(F("Initialize sensors ..."));
}

void loop() {
  
  if(alarm_running == true){
    make_alarm();
  }
  
  status = WiFi.status();
  if (status != WL_CONNECTED){
    while(status != WL_CONNECTED){
      Serial.print(F("Attempting to connect to WPA SSID: "));
      Serial.println(ssid);
      // Connect to WPA/WPA2 network
      status = WiFi.begin(ssid, pass);
      delay(200);
    }  
    Serial.println(F("Connected to AP"));
  }

  // Wait for 15 seconds to calibrate sensors
  if (millis() > 15000){
    // Send the JSON with the results of the sensors
    sendJSON();
  }
  
  // If the TCP server responds, read the characters
  data = "";
  while(client.available()){
    char c = client.read();
    data = data + c;  
  }
  
  // If data has something, this would be a JSON
  if (data != ""){
    Serial.println(data);
    // Send JSON and try to read it
    readJSON(data);
  }
  
}

void readJSON(String json){
  StaticJsonDocument<300> doc;
  // If it's not a JSON, this would make an error
  DeserializationError error = deserializeJson(doc, json);
  if (error) { 
    Serial.println(F("Invalid response received")); 
  }else{
    // Try to read the JSON and the corresponding changes
    Serial.println(F("Processing response ... "));
    if (doc["status"] == true){
      alarm = doc["alarm"];
      if (alarm == false){
        alarm_running = false;
      }  
    }else{
      String error = doc["error"];
      Serial.println(error);
    }   
  }
}

void make_alarm(){
  digitalWrite(LED, HIGH);
  
  tone(BUZZER, 2000);
  delay(1000);  
  tone(BUZZER, 3000);
  delay(1000);
  tone(BUZZER, 2000);
  delay(1000);  
  tone(BUZZER, 3000);
  delay(1000);
}

void reconnect(){
  Serial.print(F("Connecting to TCP server..."));
  if (client.connect(server, port)){
    Serial.println(F("...Established connection!"));  
  } else{
    Serial.print(F("[FAIL] [rc = "));
    Serial.println(F(" : Retrying in 1 second]"));
    //delay(1000);  
  }
}

void printWifiStatus(){
  Serial.print(F("SSID: "));
  Serial.println(WiFi.SSID());
  IPAddress ip = WiFi.localIP();
  Serial.print(F("IP adress: "));
  Serial.println(ip);  
}

void sendJSON(){
  // After 5 seconds reads the sensor
  if (millis() - lastRead > 5000){
    // Variable to serialize in JSON
    String json;
    // Document for creating the JSON           
    StaticJsonDocument<300> doc;     
    // Read temperature as Celsius (the default)
    float temperature = dht.readTemperature();
    // Read humidity
    float humidity = dht.readHumidity();
    // If there is motion
    int PIR_value = digitalRead(PIR);
    // Verify motion
    boolean motion;
    
    if (isnan(humidity) || isnan(temperature)) {
      Serial.println(F("Failed to read from DHT sensor!"));
    }
    
    if (PIR_value == HIGH && alarm == false) {
      digitalWrite(LIGHTS, HIGH);
      digitalWrite(LED, HIGH);
      delay(3000);
      digitalWrite(LED, LOW);
      motion = true;
      lastMotion = millis();
      
    }else if(PIR_value == HIGH && alarm == true){
      digitalWrite(LED, HIGH);
      digitalWrite(LIGHTS, LOW);
      alarm_running = true;
      motion = true;

    }else if(PIR_value == LOW && alarm == true){
      motion = false;
      digitalWrite(LIGHTS, LOW);
      
    }else{
      if (millis() - lastMotion > 60000 * 3){
        digitalWrite(LIGHTS, LOW);  
      }
      digitalWrite(LED, LOW);
      noTone(BUZZER);
      motion = false;
    }

    // Create JSON
    doc["lab"] = 1;
    doc["temperature"] = temperature;
    doc["humidity"] = humidity;
    doc["motion"] = motion;
    doc["alarm_running"] = alarm_running;
    serializeJson(doc, json);

    // Send message through TCP server
    if (millis() < 60000 || millis() - lastSend > 60000 * 5 || motion == true || (alarm_running == true && millis() - lastSend > 5000)){
      if(!client.connected()){
        reconnect();  
      }
      
      client.print(json);
      lastSend = millis();
      Serial.println(F("Data sent\n"));
    }
    
    // Show the message on the Serial Monitor
    Serial.println(json);
    lastRead = millis();
    
  }
}
