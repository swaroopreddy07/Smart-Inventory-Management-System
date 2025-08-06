#include <WiFi.h>
#include <HTTPClient.h>
#include <time.h>

// =====================
// WiFi Credentials
// =====================
const char* ssid     = "IQOO Z7s"; 
const char* password = "987654321";

// URL of the PHP endpoint on your web server (adjust domain/path as needed)
const char* phpServerUrl = "http://192.168.84.185/smart_inventory3/log_event.php";

// =====================
// Function Prototypes
// =====================
void connectToWiFi();
void sendLogToServer(String eventType, String item, String value1, String value2 = "", String value3 = "");
tm getLocalTimeStruct();

// =====================
// Setup
// =====================
void setup() {
  Serial.begin(115200);
  Serial.println("ESP32 connected...");


  // Connect to WiFi
  connectToWiFi();

  // Wait for a while before pushing data
  delay(5000);

  // Manually pushing test data (simulating sensor readings)
  
  // Example: Simulate a weight order event for chocolates1 (Box 1)
  sendLogToServer("order", "chocolates1", "1000");
  Serial.println("1 done");

  delay(5000);

  // Example: Simulate a consumption log for chocolates1
  sendLogToServer("consumption", "chocolates1", "500", "400", "100");
  Serial.println("2 done");

  delay(5000);

  // Example: Simulate a temperature alert for chocolates1
  sendLogToServer("temperature", "chocolates1", "35.0");
  Serial.println("3 done");

  delay(5000);

  // Example: Simulate a security alert for chocolates1
  sendLogToServer("security", "chocolates1", "Unauthorized motion detected at chocolates1");
  Serial.println("4 done");

  delay(5000);

  // Example: Simulate a weight order event for chocolates2 (Box 2)
  sendLogToServer("order", "chocolates2", "1500");
  Serial.println("5 done");

  delay(5000);

  // Example: Simulate a consumption log for chocolates2
  sendLogToServer("consumption", "chocolates2", "600", "500", "100");
  Serial.println("6 done");

  delay(5000);

  // Example: Simulate a temperature alert for chocolates2
  sendLogToServer("temperature", "chocolates2", "40.0");
  Serial.println("8 done");

  delay(5000);

  // Example: Simulate a security alert for chocolates2
  sendLogToServer("security", "chocolates2", "Unauthorized motion detected at chocolates2");
  Serial.println("8 done");

}

// =====================
// Main Loop
// =====================
void loop() {
  // The loop can be left empty for testing manual pushes only
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
// Send Event Log to PHP Server
// =====================
void sendLogToServer(String eventType, String item, String value1, String value2, String value3) {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(phpServerUrl);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");

    // Constructing POST data dynamically
    String postData = "eventType=" + eventType + "&item=" + item + "&value1=" + value1;
    if (value2.length() > 0) postData += "&value2=" + value2;
    if (value3.length() > 0) postData += "&value3=" + value3;

    Serial.println("Sending POST request: " + postData); // Debugging line

    int httpResponseCode = http.POST(postData);
    if (httpResponseCode > 0) {
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