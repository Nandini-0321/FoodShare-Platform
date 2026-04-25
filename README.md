# 🍱 FoodShare  
### _Connecting Surplus Food to Those Who Need It Most_

<p align="center">
  <img src="https://readme-typing-svg.demolab.com?font=Fira+Code&size=22&pause=1000&color=A29BFE&center=true&vCenter=true&width=750&lines=Reduce+Food+Waste+with+Technology;Connecting+Donors+%2C+NGOs+%26+Volunteers;Smart+%7C+Efficient+%7C+Impactful" />
</p>

<p align="center">
  <img src="https://img.shields.io/github/stars/Nandini-0321/FoodShare-Platform?style=for-the-badge&color=6366f1" />
  <img src="https://img.shields.io/github/forks/Nandini-0321/FoodShare-Platform?style=for-the-badge&color=a855f7" />
  <img src="https://img.shields.io/github/license/Nandini-0321/FoodShare-Platform?style=for-the-badge&color=ec4899" />
</p>

<p align="center">
  <img src="https://skillicons.dev/icons?i=php,html,css,js,mysql,git,github,vscode" />
</p>

---

## 📌 Overview

**FoodShare** is a sophisticated web ecosystem designed to bridge the gap between surplus food and food scarcity. In a world where tons of perfectly edible food go to waste daily, FoodShare provides a streamlined, digital bridge connecting generous **Donors**, dedicated **NGOs**, and proactive **Volunteers**.

This isn't just another donation tool; it's a mission-driven platform built with a modern tech stack to ensure efficiency, transparency, and social impact. By digitizing the logistics of food redistribution, we empower communities to combat hunger while significantly reducing environmental waste.

---

## ✨ Features

- 🍱 **Smart Donation Pipeline** — Seamless interface for donors to list surplus food with real-time status tracking.
- 🤝 **NGO Strategic Matching** — Targeted donation requests allowing NGOs to claim food based on immediate community needs.
- 🚚 **Volunteer Logistics Hub** — Dynamic assignment system for volunteers to manage pickups and last-mile deliveries.
- 📊 **Intelligent Dashboards** — Role-specific interfaces (Donor, NGO, Volunteer) providing relevant insights and action items.
- 🎨 **Premium Glassmorphism UI** — A stunning, dark-themed interface built for a modern aesthetic and superior user experience.
- 📱 **Adaptive Responsiveness** — Flawless performance across all devices, from desktop monitors to mobile screens.
- 🤖 **AI-Ready Architecture** — Integrated hooks for AI/ML features (like automated chat assistants) to enhance user interaction.
- 🔐 **Secure Role-Based Access** — Robust authentication and permission management ensuring data integrity.

---

## 🛠 Tech Stack

| Category | Technologies |
| :--- | :--- |
| **Languages** | PHP, JavaScript, SQL |
| **Frontend** | HTML5, CSS3, Vanilla JS |
| **Backend** | PHP (Custom MVC Pattern) |
| **Database** | MySQL |
| **Styling** | Modern CSS (Gradients, Glassmorphism, Dark Mode) |
| **Development** | Git, GitHub, VS Code, XAMPP |

---

## 📂 Project Structure

```text
FoodShare/
├── assets/
│   ├── css/          # Premium design system & typography
│   ├── js/           # Interactive logic & API handling
│   └── images/       # High-fidelity UI assets
├── config/
│   ├── database.php  # Secure DB connection management
│   └── schema.sql    # Core database architecture
├── controllers/      # Business logic & workflow management
├── models/           # Data structures & database interactions
├── views/            # User-facing dashboard implementations
├── partials/         # Reusable UI templates for consistency
└── index.php         # Application entry & routing
```

---

## ⚙️ How It Works

### 1. The Ecosystem Entry
Users register and authenticate into the platform, selecting their role as a **Donor**, **NGO**, or **Volunteer**. Each role unlocks a tailored set of functionalities.

### 2. Food Listing (Donors)
Donors (restaurants, households, or businesses) list their surplus food, providing details such as quantity, type, and expiration time. This immediately populates the global donation pool.

### 3. Request & Allocation (NGOs)
NGOs browse the live feed of available donations. They can request specific food items based on the populations they serve.

### 4. Pickup & Delivery (Volunteers)
Once a request is matched, volunteers receive notifications to facilitate the logistics. They handle the physical movement of food from the donor's location to the NGO's distribution point.

### 5. Real-Time Tracking
The entire lifecycle—from listing to delivery—is tracked via the role-based dashboards, ensuring every gram of food is accounted for and reaches those in need.

---

## ▶️ Installation & Setup

Follow these steps to get your local development environment running:

### 1️⃣ Prerequisites
- Ensure **XAMPP** (Apache & MySQL) is installed on your machine.
- A modern web browser (Chrome, Firefox, or Brave).

### 2️⃣ Clone the Repository
```bash
git clone https://github.com/Nandini-0321/FoodShare-Platform.git
```

### 3️⃣ Setup the Workspace
Move the `FoodShare` directory to your XAMPP server's root:
`C:\xampp\htdocs\FoodShare`

### 4️⃣ Database Configuration
- Open **phpMyAdmin** (`http://localhost/phpmyadmin`).
- Create a new database named `foodshare`.
- Import the provided `config/schema.sql` to initialize tables.

### 5️⃣ Environment Secrets
Update your database credentials (username, password) and API keys (if using AI features) in:
- `config/database.php`
- `config/ai_config.php`

### 6️⃣ Launch
Open your browser and navigate to:
`http://localhost/FoodShare`

---

## 📸 Screenshots / Demo

> [!TIP]
> **Capture the Impact:**
> - Show the **Glassmorphism Dashboards** in action.
> - Record a **GIF** of a donor adding food and it appearing on the NGO feed.
> - Display the **Mobile-Responsive** views to highlight portability.

### 🎬 Potential Demo Ideas
1. **The Life of a Donation:** A fast-paced GIF showing food being listed, claimed, and delivered.
2. **Dashboard Tour:** A scroll through the sleek, dark-themed donor and volunteer panels.
3. **Responsive Flip:** Showing the UI resizing seamlessly from Desktop to Mobile.

---

## 🚀 Future Roadmap

- [ ] **GPS Real-Time Tracking:** Integration with Map APIs for live volunteer delivery tracking.
- [ ] **Smart Notification System:** Push notifications for NGOs when fresh food is listed nearby.
- [ ] **Impact Analytics:** Advanced ML-based reporting on food waste reduction and donation trends.
- [ ] **Blockchain Integration:** Transparent ledger for donation history and volunteer rewards.
- [ ] **Mobile App:** Native iOS/Android versions for easier field use by volunteers.

---

## 👨‍💻 Author

**Nandini R**  
*Aspiring Software Engineer*

I am a Computer Science Engineering student dedicated to building intelligent, real-world systems. My expertise spans across **Python, Java, and Full-Stack Web Development**, with a profound interest in **AI/ML and Prompt Engineering**.

I thrive on creating practical applications that solve tangible problems—whether it’s streamlining agricultural trade, building intuitive AI chatbots, or, as seen here, optimizing social resource distribution. My background includes internships in AI and Machine Learning, which I leverage to design scalable and efficient architectures.

Beyond FoodShare, I have worked on diverse systems including:
- **AgriTrade:** A platform empowering farmers through digital auctions.
- **AI-Powered Assistants:** Custom chatbots designed for intent-driven interaction.
- **Intelligent Fraud Detection:** Systems utilizing ML to enhance financial security.

---

## 📬 Contact

I am always open to discussing new opportunities, collaborations, or innovative ideas. Let's connect!

- **Email:** [nandini.33218@gmail.com](mailto:nandini.33218@gmail.com)
- **Phone:** +91 7204385773
- **LinkedIn:** [nandini3](http://www.linkedin.com/in/nandini3)
- **GitHub:** [Nandini-0321](https://github.com/Nandini-0321)

*“Feel free to connect for collaborations or opportunities.”*

---

<p align="center">
  <img src="https://github-readme-stats.vercel.app/api?username=Nandini-0321&show_icons=true&theme=tokyonight&hide_border=true&count_private=true" alt="Nandini's GitHub Stats" />
  <img src="https://github-readme-streak-stats.herokuapp.com/?user=Nandini-0321&theme=tokyonight&hide_border=true" alt="Nandini's GitHub Streak" />
</p>

<p align="center">
  Generated with ❤️ by Antigravity for FoodShare
</p>
