## JobCRM Platform

JobCRM is a role-based customer relationship and job management platform designed for service businesses. It provides dedicated portals for administrators, managers, and field users to streamline job intake, vendor coordination, invoicing, and customer communications.

### Key Features
- Multi-role dashboards for admins, managers, and users
- Job lifecycle tracking with comments, notes, and attachments
- Vendor management and bulk assignment workflows
- Real-time notifications and in-app messaging
- Invoice generation for multiple brands with reusable templates
- Authentication, profile management, and password reset flows

### Project Structure
- `admin/` – Admin panel pages, APIs, assets, and invoice templates
- `manager/` – Manager portal with job oversight, notes, and vendor tools
- `user/` – Field/user portal for assigned jobs and vendor collaboration
- `assets/` – Shared front-end resources for authentication and UI
- `config/` – Database connection settings (`database.php`)
- `uploads/` – User-generated files such as job pictures and completion proofs

### Requirements
- PHP 8.1+ with required extensions (PDO, JSON, CURL, GD)
- MySQL 8.0+
- Composer (optional) for dependency management
- Node.js 18+ (optional) if you plan to recompile front-end assets

### Local Development
1. Clone the repository:
   ```bash
   git clone git@github.com:akamanuallah/jobcrm.git
   cd jobcrm
   ```
2. Copy the environment template and adjust settings:
   ```bash
   cp config/database.example.php config/database.php
   ```
   Update database credentials, SMTP details, and file storage paths as needed.
3. Import the database schema:
   ```bash
   mysql -u <user> -p <database> < database/schema.sql
   ```
4. Serve the project (example using XAMPP):
   - Move the project into `htdocs`
   - Start Apache and MySQL from the XAMPP control panel
   - Visit `http://localhost/jobcrm`

### Deployment Notes
- Configure a virtual host pointing to the `jobcrm` public directory
- Set correct file permissions for `uploads/`
- Protect API endpoints with HTTPS and secure session configuration
- Schedule cron jobs for reminder managers if automated notifications are required

### Testing
- Unit and feature tests can be added with PHPUnit; run `vendor/bin/phpunit`
- Front-end behavior can be tested with Cypress or Playwright (not included by default)

### Contact
- Website: [amaanullah.com](https://amaanullah.com)
- Email: [info@amaanullah.com](mailto:info@amaanullah.com)

For bug reports or feature requests, please open an issue or get in touch via the contact details above.


