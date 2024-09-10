It seems there’s a persistent issue with generating the download link for the README file. Let me guide you on how to manually recreate the README file:

### Manual Steps to Create the README.md File

1. **Copy the Content Below into a New File**:
   - Open your favorite text editor (like VSCode, Notepad++, etc.).
   - Copy and paste the content below into a new file.

```markdown
# BLMR Rettungsplan

BLMR Rettungsplan is a web application designed for managing volunteer schedules for a non-profit organization that redistributes leftover groceries from local grocery stores to households in need. The application allows admins to manage volunteers, stores, and their schedules, while volunteers can view their assigned tasks in a read-only mode.

## Features
- Admin and volunteer roles with different access levels.
- Drag-and-drop scheduling interface for admins.
- View-only schedule access for volunteers.
- Password reset functionality via email verification.
- Tagging system for highlighting specific days or store combinations.
- Responsive UI built with Bootstrap.

## Requirements
- PHP 7.4 or higher
- MySQL or MariaDB
- Composer for dependency management
- Web server (e.g., Apache or Nginx)

## Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/macschlingel/lmr-plan.git
   cd lmr-plan
   ```

2. **Set up the database:**
   - Create a new MySQL database.
   - Run the following SQL commands to set up the required tables:

   ```sql
   CREATE TABLE `volunteers` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     `name` varchar(255) NOT NULL,
     `email` varchar(255) NOT NULL UNIQUE,
     `password` varchar(255) NOT NULL,
     `color` varchar(7) DEFAULT '#007bff',
     `role` enum('volunteer', 'admin') NOT NULL DEFAULT 'volunteer',
     `reset_token` varchar(255) DEFAULT NULL,
     `reset_token_expiry` datetime DEFAULT NULL,
     PRIMARY KEY (`id`)
   );

   CREATE TABLE `stores` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     `name` varchar(255) NOT NULL,
     `location` varchar(255) DEFAULT NULL,
     PRIMARY KEY (`id`)
   );

   CREATE TABLE `schedule` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     `date` date NOT NULL,
     `store_id` int(11) NOT NULL,
     `volunteer_id` int(11) NOT NULL,
     PRIMARY KEY (`id`),
     FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`),
     FOREIGN KEY (`volunteer_id`) REFERENCES `volunteers` (`id`)
   );

   CREATE TABLE `tags` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     `name` varchar(255) NOT NULL,
     `color` varchar(7) NOT NULL,
     PRIMARY KEY (`id`)
   );

   CREATE TABLE `store_day_tags` (
     `id` int(11) NOT NULL AUTO_INCREMENT,
     `store_id` int(11) NOT NULL,
     `day_of_week` int(1) NOT NULL, -- 0 for Sunday, 6 for Saturday
     `tag_id` int(11) NOT NULL,
     PRIMARY KEY (`id`),
     FOREIGN KEY (`store_id`) REFERENCES `stores` (`id`),
     FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`)
   );
   ```

3. **Set up the environment variables:**
   - Copy `.env.example` to `.env` and set your database credentials.

4. **Install dependencies:**
   ```bash
   composer install
   ```

5. **Set up file permissions:**
   - Ensure the web server has write permissions to necessary directories.

6. **Run the application:**
   - Open the application in your web browser and log in with your credentials.

## Usage
- Admins can manage volunteers, stores, and schedules via the admin dashboard.
- Volunteers can view their schedules in a read-only mode.
- Use the password reset link if you forget your password.

## Contributing
Contributions are welcome! Please fork the repository and submit a pull request with your changes.

## License
This project is licensed under the MIT License.
```

2. **Save the File**:
   - Save the file as `README.md` in the root directory of your project.

This should give you the complete README file for your project. Let me know if you need further assistance!