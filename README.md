# AuraLink 🌌

**AuraLink** is a web platform built with **HTML, CSS, JavaScript, PHP, and MySQL**, focused on emotional expression and connection.  
Created as our **first hackathon project**, it allows users to share moods through visual cards, draw their thoughts freely, and discover meaningful connections through **aura-based card matching** for mental well-being.

---

## 🚀 Features
- 🎨 Mood Cards – Share your emotions through visual cards  
- ✏️ Free Drawing Space – Express thoughts through drawing  
- 🔮 Aura Matching – Connect with users who have similar emotional states  
- 💬 Meaningful Interaction – Discover supportive connections  

---

## 🛠️ Technologies Used
- **Frontend:** HTML, CSS, JavaScript  
- **Backend:** PHP  
- **Database:** MySQL  
- **Server:** XAMPP (Apache + MySQL)

---

## 📥 Installation Guide

Follow the steps below to run the project locally.

### 1. Install XAMPP
Download and install XAMPP from:  
https://www.apachefriends.org/index.html

After installation, open **XAMPP Control Panel** and start:
- Apache
- MySQL

---

### 2. Clone or Download the Project

Clone the repository:

```bash
git clone https://github.com/your-username/AuraLink.git
```

Or download the ZIP and extract it.

---

### 3. Move the Project to htdocs

Copy the project folder to:

```
C:\xampp\htdocs\
```

Example:

```
C:\xampp\htdocs\AuraLink
```

---

### 4. Import the Database

1. Open your browser and go to:

```
http://localhost/phpmyadmin
```

2. Create a new database (example):

```
auralink_db
```

3. Select the created database.

4. Go to the **Import** tab.

5. Upload the file:

```
db.sql
```

6. Click **Go** to import the database.

---

### 5. Configure Database Connection (if required)

Open the configuration file (example: `config.php`) and update database credentials:

```php
$host = "localhost";
$user = "root";
$password = "";
$database = "auralink_db";
```

---

### 6. Run the Project

Open your browser and go to:

```
http://localhost/AuraLink
```

---

## 📌 Project Goal

AuraLink aims to create a safe digital space where people can **express emotions creatively and connect with others experiencing similar feelings**, promoting **mental well-being through shared expression and empathy**.

---

## 👨‍💻 Team

Developed as part of a **hackathon project** by passionate developers exploring the intersection of **technology and emotional well-being**.

---

## 📄 License
This project is for educational purposes.
