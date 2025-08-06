#include <WiFi.h>
#include <HTTPClient.h>
#include <Wire.h>
#include <Adafruit_SHT31.h>
#include <ArduinoOTA.h>

// WiFi
const char* ssid = "Your_SSID";  // replace with your WiFi SSID
const char* password = "Your_PASSWORD";  // replace with your WiFi password

// API
const char* serverName = "http://your-api-endpoint.com";  // use http, not https if local

// Sensor
#define SDA_PIN 21
#define SCL_PIN 22

Adafruit_SHT31 sht = Adafruit_SHT31();

float temperature, humidity;

void sendToServer(const String& nome, const String& valor) {
  if (WiFi.status() == WL_CONNECTED) {
    HTTPClient http;
    http.begin(serverName);
    http.addHeader("Content-Type", "application/x-www-form-urlencoded");
    String postData = "nome=" + nome + "&valor=" + valor;
    int httpResponseCode = http.POST(postData);
    Serial.printf("POST %s = %s -> HTTP %d\n", nome.c_str(), valor.c_str(), httpResponseCode);
    http.end();
  } else {
    Serial.println("WiFi desconectado.");
  }
}

bool readTemperatureHumidity(float& temp, float& hum) {
  temp = sht.readTemperature();
  hum = sht.readHumidity();
  return (!isnan(temp) && !isnan(hum));
}

void setupOTA() {
  ArduinoOTA
    .onStart([]() {
      Serial.println("Enabling OTA...");
    })
    .onEnd([]() {
      Serial.println("\nOTA Update Complete");
    })
    .onProgress([](unsigned int progress, unsigned int total) {
      Serial.printf("Progress: %u%%\r", (progress / (total / 100)));
    })
    .onError([](ota_error_t error) {
      Serial.printf("OTA Error [%u]\n", error);
    });

  ArduinoOTA.begin();
  Serial.println("OTA ready");
}

void setup() {
  Serial.begin(115200);
  delay(1000);

  Wire.begin(SDA_PIN, SCL_PIN);
  if (!sht.begin(0x44)) {
    Serial.println("Error initializing SHT31!");
  } else {
    Serial.println("HT31 sensor initialized successfully.");
  }

  WiFi.begin(ssid, password);
  Serial.print("Connecting to Wi-Fi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.print(".");
  }
  Serial.println("\nConnected to Wi-Fi!");
  Serial.println(WiFi.localIP());

  setupOTA();  // Initialize OTA
}

void loop() {
  ArduinoOTA.handle();  // Keep OTA active

  if (readTemperatureHumidity(temperature, humidity)) {
    Serial.printf("Temp: %.2f ºC | Hum: %.2f %%\n", temperature, humidity);
    sendToServer("temperatura", String(temperature, 2));
    sendToServer("humidade", String(humidity, 2));
  } else {
    Serial.println("Read error on the sensor SHT31.");
  }

  delay(3000);
}
