#include <WiFi.h>
#include <HTTPClient.h>
#include <Wire.h>
#include <Adafruit_SHT31.h>

// WiFi
const char* ssid = "Your_SSID";  // replace with your WiFi SSID
const char* password = "Your_PASSWORD";  // replace with your WiFi password

// API
const char* serverName = "https://your-api-endpoint.com";  // use http, not https, if local network

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

  if (!isnan(temp) && !isnan(hum)) {
    return true;
  }
  return false;
}

void setup() {
  Serial.begin(115200);
  delay(1000);

  Wire.begin(SDA_PIN, SCL_PIN);

  if (!sht.begin(0x44)) {  // 0x44 é o endereço I2C padrão do SHT31
    Serial.println("Erro ao iniciar o SHT31!");
  } else {
    Serial.println("Sensor SHT31 inicializado com sucesso.");
  }

  WiFi.begin(ssid, password);
  Serial.print("Connecting to WiFi");
  while (WiFi.status() != WL_CONNECTED) {
    delay(1000);
    Serial.print(".");
  }
  Serial.println("\nConnected to WiFi!");
  Serial.println(WiFi.localIP());
}

void loop() {
  if (readTemperatureHumidity(temperature, humidity)) {
    Serial.printf("Temp: %.2f ºC | Hum: %.2f %%\n", temperature, humidity);
    sendToServer("temperatura", String(temperature, 2));
    sendToServer("humidade", String(humidity, 2));
  } else {
    Serial.println("Error reading SHT31 sensor.");
  }

  delay(3000);
}
