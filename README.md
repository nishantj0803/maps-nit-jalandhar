# Maps NIT Jalandhar: Interactive Campus Navigation

## Overview

**Maps NIT Jalandhar** is a web-based interactive navigation system designed specifically for the Dr. B. R. Ambedkar National Institute of Technology, Jalandhar campus. This application assists students, faculty, and visitors in easily finding their way around the campus, offering a unique advantage: **the ability to discover efficient shortcut paths that might not be apparent on conventional mapping applications.**

Users can register and log in to access the interactive map, select their starting point and desired destination within the campus, and the system will calculate and display the most efficient route. This is achieved using the **A\* (A-star) search algorithm** for optimal pathfinding, leveraging a custom-defined graph of campus locations and connections.

## Key Features

* **User Authentication:** Secure registration and login system for personalized access.
* **Interactive Campus Map:** Utilizes Leaflet.js with OpenStreetMap tiles to provide a dynamic and user-friendly map interface.
* **Intelligent Shortcut Pathfinding:** Employs the A\* search algorithm to identify and display the shortest and most efficient routes, including common shortcuts known to campus insiders.
* **Point-to-Point Navigation:** Allows users to select a start and end location from a predefined list of important campus landmarks and buildings.
* **Clear Route Visualization:** Displays the calculated path directly on the map for easy understanding.
* **Responsive Design:** Ensures usability across various devices, including desktops, tablets, and mobile phones.

## Why Maps NIT Jalandhar?

Navigating a large campus can often be challenging, especially for newcomers. While standard mapping tools provide general directions, they often miss the nuanced, unofficial shortcuts that can save significant time. This project aims to bridge that gap by incorporating local campus knowledge into its pathfinding logic, offering truly optimized routes.

## Tech Stack

* **Frontend:**
    * HTML5
    * CSS3
    * JavaScript
    * [Leaflet.js](https://leafletjs.com/) (for interactive maps)
* **Backend:**
    * PHP
* **Database:**
    * MySQL (for user data and potentially location/graph data if not hardcoded)
* **Pathfinding Algorithm:**
    * A\* (A-star) Search Algorithm

## Project Structure


maps-nit-jalandhar/
├── css/
│   └── style.css           # Main stylesheet
├── images/
│   └── background.jpg      # Background image for login/signup pages
├── index.html              # Login page
├── signup.html             # Registration page
├── home.php                # Main map and navigation interface
├── login.php               # Handles login logic
├── signup.php              # Handles registration logic
├── logout.php              # Handles logout logic
└── README.md               # This file

*(Adjust the structure based on your final organization if it differs, e.g., if you have a separate `src/` or `includes/` folder for PHP files.)*

## Setup and Installation

To run this project locally, you will need a web server environment that supports PHP and MySQL (like XAMPP, WAMP, MAMP, or a custom LAMP/LEMP stack).

1.  **Clone the Repository:**
    ```bash
    git clone [https://github.com/your-username/maps-nit-jalandhar.git](https://github.com/your-username/maps-nit-jalandhar.git)
    cd maps-nit-jalandhar
    ```

2.  **Database Setup:**
    * Ensure your MySQL server is running.
    * Create a database named `user_db` (or as specified in your PHP connection files).
    * Import the necessary table structure. You'll need a `users` table. Example SQL:
        ```sql
        CREATE TABLE IF NOT EXISTS `users` (
          `id` INT AUTO_INCREMENT PRIMARY KEY,
          `email` VARCHAR(255) NOT NULL UNIQUE,
          `password` VARCHAR(255) NOT NULL -- Ensure this can store your hashed passwords (e.g., VARCHAR(32) for MD5, VARCHAR(255) for password_hash())
        );
        ```
    * Update the database credentials (`$servername`, `$username`, `$password`, `$dbname`) in `login.php` and `signup.php` if they differ from your local setup.

3.  **Place Project Files:**
    * Copy the entire `maps-nit-jalandhar` project folder into your web server's document root (e.g., `htdocs` for XAMPP/MAMP, `www` for WAMP).

4.  **Access the Application:**
    * Open your web browser and navigate to `http://localhost/maps-nit-jalandhar/` (or the appropriate path if you placed it in a subdirectory).

## Usage

1.  **Register:** If you are a new user, click on the "Sign-Up" link on the login page (`index.html`) and create an account.
2.  **Login:** Enter your credentials on the login page to access the main application.
3.  **Navigate:**
    * Once logged in (`home.php`), you will see the campus map.
    * Use the "From" and "To" dropdown menus to select your starting location and destination.
    * Click the "Show Route" button.
    * The shortest path, including potential shortcuts, will be displayed on the map.
4.  **Logout:** Click the "Logout" link to end your session.

## Future Enhancements (Suggestions)

* Implement `password_hash()` and `password_verify()` for more secure password management.
* Allow users to click on the map to select start/end points.
* Add more points of interest and details for each location.
* Incorporate real-time user location (with permission).
* Admin panel for managing locations and paths.

## Contributing

Contributions are welcome! If you have suggestions or improvements, feel free to fork the repository, make your changes, and submit a pull request.

---

*This README provides a template. You should customize it further with specific details about your implementation, screenshots, and any other relevant information.*
