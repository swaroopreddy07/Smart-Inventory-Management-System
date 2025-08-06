#include <WiFi.h>
#include <HTTPClient.h>
#include "HX711.h"
#include "DHT.h"
#include <time.h>

// =====================
// WiFi & Server Settings
// =====================
const char* ssid     = "IQOO Z7s";
const char* password = "987654321";

// URL of the PHP endpoint on your web server (adjust domain/path as needed)
const char* phpServerUrl = "http://192.168.84.185/log_event.php";

// =====================
// Sensor Pins & Parameters
// -------------------------
// HX711 Load Cell Setup
#define HX711_DOUT1 13
#define HX711_SCK1  12
#define HX711_DOUT2 25
#define HX711_SCK2  26
HX711 loadCell1;
HX711 loadCell2;

// DHT11 Temperature Sensors
#define DHTPIN1 4
#define DHTPIN2 5
#define DHTTYPE DHT11
DHT dht1(DHTPIN1, DHTTYPE);
DHT dht2(DHTPIN2, DHTTYPE);

// PIR Motion Sensors
#define PIR1_PIN 15
#define PIR2_PIN 16

// =====================
// Global Variables & Thresholds
// =====================
const float weightThreshold = 50.0; // grams change required
const float orderQuantity   = 1000; // grams (1 kg order)

const float TEMP_MIN = 20.0;
const float TEMP_MAX = 30.0;

const int START_RECORD_HOUR = 9;  // 9 AM
const int END_RECORD_HOUR   = 22; // 10 PM

const int SECURITY_START = 9;
const int SECURITY_END   = 23;

float baselineWeight1 = 0.0;
float baselineWeight2 = 0.0;

float morningWeight1 = 0.0;
float eveningWeight1 = 0.0;
float morningWeight2 = 0.0;
float eveningWeight2 = 0.0;

bool orderPlacedBox1 = false;
bool orderPlacedBox2 = false;

// =====================
// Function Prototypes
// =====================
void connectToWiFi();
void initTime();
void checkDailyRecording();
void checkMotionSensors();
void checkTemperature();
void sendLogToServer(String eventType, String item, String value1, String value2 = "", String value3 = "");
tm getLocalTimeStruct();


// =====================
// Setup
// =====================
void setup() {
  Serial.begin(115200);
  Serial.println("ESP32 has started successfully!");

  // Initialize sensors
  loadCell1.begin(HX711_DOUT1, HX711_SCK1);
  loadCell1.set_scale();
  loadCell1.tare();
  loadCell2.begin(HX711_DOUT2, HX711_SCK2);
  loadCell2.set_scale();
  loadCell2.tare();
  dht1.begin();
  dht2.begin();
  
  pinMode(PIR1_PIN, INPUT);
  pinMode(PIR2_PIN, INPUT);
  
  // Connect to WiFi and sync time
  connectToWiFi();
  initTime();
}

// =====================
// Main Loop
// =====================
void loop() {
  checkDailyRecording();
  checkMotionSensors();
  checkTemperature();

  float weightBox1 = loadCell1.get_units(5);
  float weightBox2 = loadCell2.get_units(5);
  Serial.printf("Box1 Current Weight: %.2f grams\n", weightBox1);
  Serial.printf("Box2 Current Weight: %.2f grams\n", weightBox2);
  
  delay(10000); // delay 10 seconds between loops
}

// =====================
// WiFi Connection
// =====================
void connectToWiFi() {
  Serial.printf("Connecting to %s", ssid);
  WiFi.begin(ssid, password);
  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }
  Serial.println("\nWiFi connected.");
}

// =====================
// NTP Time Initialization
// =====================
void initTime() {
  configTime(0, 0, "pool.ntp.org", "time.nist.gov");  
  Serial.println("Waiting for time sync...");
  while (time(nullptr) < 100000) {
    delay(500);
  }
  Serial.println("Time synchronized.");
}

// =====================
// Daily Recording & Consumption Logging
// =====================
void checkDailyRecording() {
  tm timeInfo = getLocalTimeStruct();
  
  // Record morning weight at START_RECORD_HOUR
  if (timeInfo.tm_hour == START_RECORD_HOUR && timeInfo.tm_min == 0) {
    morningWeight1 = loadCell1.get_units(5);
    morningWeight2 = loadCell2.get_units(5);
    Serial.printf("Morning weights: Box1=%.2f, Box2=%.2f\n", morningWeight1, morningWeight2);
    orderPlacedBox1 = false;
    orderPlacedBox2 = false;
    delay(60000); // avoid duplicate triggers
  }
  
  // Record evening weight at END_RECORD_HOUR and log consumption
  if (timeInfo.tm_hour == END_RECORD_HOUR && timeInfo.tm_min == 0) {
    eveningWeight1 = loadCell1.get_units(5);
    eveningWeight2 = loadCell2.get_units(5);
    Serial.printf("Evening weights: Box1=%.2f, Box2=%.2f\n", eveningWeight1, eveningWeight2);
    
    float consumption1 = morningWeight1 - eveningWeight1;
    float consumption2 = morningWeight2 - eveningWeight2;
    
    // Log consumption for both boxes via PHP
    sendLogToServer("consumption", "Box1", String(morningWeight1), String(eveningWeight1), String(consumption1));
    sendLogToServer("consumption", "Box2", String(morningWeight2), String(eveningWeight2), String(consumption2));
    
    delay(60000);
  }
}

// =====================
// Motion & Security Check
// =====================
void checkMotionSensors() {
  tm timeInfo = getLocalTimeStruct();
  
  // Box1
  if (digitalRead(PIR1_PIN) == HIGH) {
    if (timeInfo.tm_hour < SECURITY_START || timeInfo.tm_hour >= SECURITY_END) {
      // Security alert via PHP logging
      sendLogToServer("security", "Box1", "Unauthorized motion detected at Box1.");
    } else {
      if (!orderPlacedBox1) {
        baselineWeight1 = loadCell1.get_units(5);
        delay(45000);
        float newWeight = loadCell1.get_units(5);
        if ((baselineWeight1 - newWeight) * 1000 >= weightThreshold) {
          // Order event logged via PHP
          sendLogToServer("order", "Box1", String(orderQuantity));
          orderPlacedBox1 = true;
        }
      }
    }
  }
  
  // Box2
  if (digitalRead(PIR2_PIN) == HIGH) {
    if (timeInfo.tm_hour < SECURITY_START || timeInfo.tm_hour >= SECURITY_END) {
      sendLogToServer("security", "Box2", "Unauthorized motion detected at Box2.");
    } else {
      if (!orderPlacedBox2) {
        baselineWeight2 = loadCell2.get_units(5);
        delay(45000);
        float newWeight = loadCell2.get_units(5);
        if ((baselineWeight2 - newWeight) * 1000 >= weightThreshold) {
          sendLogToServer("order", "Box2", String(orderQuantity));
          orderPlacedBox2 = true;
        }
      }
    }
  }
}

// =====================
// Temperature Monitoring
// =====================
void checkTemperature() {
  float temp1 = dht1.readTemperature();
  float temp2 = dht2.readTemperature();
  
  if (isnan(temp1) || isnan(temp2)) {
    Serial.println("Failed to read from DHT sensor!");
    return;
  }
  
  if (temp1 < TEMP_MIN || temp1 > TEMP_MAX) {
    sendLogToServer("temperature", "Box1", String(temp1));
  }
  
  if (temp2 < TEMP_MIN || temp2 > TEMP_MAX) {
    sendLogToServer("temperature", "Box2", String(temp2));
  }
}

// =====================
// Send Event Log to PHP Server
// =====================
// This function sends an HTTP POST with event details. 
// The parameters are sent as strings; unused parameters can be left empty.
void sendLogToServer(String eventType, String item, String value1, String value2, String value3) {
  if(WiFi.status() == WL_CONNECTED){
    HTTPClient http;
    http.begin(phpServerUrl);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    String postData = "eventType=" + eventType + "&item=" + item + "&value1=" + value1 + "&value2=" + value2 + "&value3=" + value3;
    int httpResponseCode = http.POST(postData);
    if(httpResponseCode > 0){
      String response = http.getString();
      Serial.println("Server response: " + response);
    } else {
      Serial.println("Error sending POST: " + http.errorToString(httpResponseCode));
    }
    http.end();
  } else {
    Serial.println("WiFi not connected.");
  }
}

// =====================
// Utility: Get Local Time Structure
// =====================
tm getLocalTimeStruct() {
  time_t now;
  struct tm timeinfo;
  time(&now);
  localtime_r(&now, &timeinfo);
  return timeinfo;
}
