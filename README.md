# 🌡️ Temperature & Humidity Dashboard

This project is a web-based dashboard that collects, stores, and displays **temperature** and **humidity** data in real-time using an **ESP32** as a sensor and a **Raspberry Pi 5** as the web server.

The data is organized by hour, day, and month, and users can view calculated averages or download data directly from the interface.

## 🛠️ Built With

- ESP32 (C/C++ - Arduino Framework)
- PHP (API + dynamic pages)
- HTML5, CSS3, JavaScript
- Apache2 (Web server on Raspberry Pi)
- Bash (file management scripts)
- Cron (automated average calculation)

## 🚀 Features

- 📡 Automatic data collection via ESP32 POST requests
- 🗂️ Data storage in `.txt` files organized by month
- 📊 Hourly, daily, and monthly average calculation
- 🧮 Automatic average updates using `cron`
- 📈 Real-time average visualization (`temp.php` and `hum.php`)
- 💾 Downloadable data files from the web interface
- 🧭 Responsive design for desktop and mobile

## 📦 Installation

1. Clone the repository on the Raspberry Pi:
   ```bash
   git clone https://github.com/duartelacerda/temp-hum-dashboard.git
   cd temp-hum-dashboard
   ```
If the project includes PHP and database functionality, you can run it using a local server environment, such as XAMPP or Laragon.
   
⚠️ Disclaimer
This project was created for educational and personal experimentation purposes. Some components may be simplified or optimized for local environments only.

👤 Author
Duarte Lacerda

📄 License
This code is distributed without a license, for personal or educational use only.

go
Copiar
Editar

Se quiseres, posso também adicionar instruções específicas para o `cron_setup.sh`, screenshots da interface, o
