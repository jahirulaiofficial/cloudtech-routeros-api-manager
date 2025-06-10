# CloudTech V4

A modern, lightweight MikroTik RouterOS API management tool with custom branding. Built with PHP 7.4+ and designed to work seamlessly with XAMPP or Laragon.

## Features

- 🚀 Modern, responsive dashboard
- 🔒 Secure authentication system
- 📊 Real-time statistics and monitoring
- 📱 Mobile-friendly interface
- 🔄 MAC address management
- 🌐 PPPoE and Hotspot management
- 🛡️ DNS Firewall rules
- 📨 Future WhatsApp integration support
- 💾 Cloud backup capabilities
- 📈 Usage statistics with Chart.js
- 🎫 Voucher system with expiry alerts

## Requirements

- PHP 7.4 or higher
- XAMPP/Laragon/Similar web server
- MikroTik Router with API access enabled
- Modern web browser
- Enabled PHP extensions:
  - curl
  - json
  - session
  - sockets

## Installation

1. Download or clone the repository:
   ```bash
   git clone https://github.com/yourusername/cloudtech-v4.git
   ```

2. Move the files to your web server directory:
   - For XAMPP: `htdocs/cloudtech-v4`
   - For Laragon: `www/cloudtech-v4`

3. Configure your MikroTik router settings:
   - Edit `config/mikrotik.php`
   - Update the router IP, username, and password

4. Configure application settings:
   - Edit `config/app.php`
   - Update admin credentials
   - Configure features as needed

5. Set proper permissions:
   ```bash
   chmod 755 -R /path/to/cloudtech-v4
   chmod 644 config/*.php
   ```

6. Access the application:
   - Open your browser
   - Navigate to: `http://localhost/cloudtech-v4`
   - Login with default credentials:
     - Username: admin
     - Password: admin123
   - **Important**: Change the default password immediately!

## Security Recommendations

1. Change default admin credentials immediately
2. Use HTTPS in production
3. Keep PHP and dependencies updated
4. Regular backup of configuration
5. Monitor access logs
6. Use strong passwords
7. Enable session timeout

## API Documentation

### Available Endpoints

- POST `/api/login.php` - User authentication
- GET `/api/status.php` - System status
- POST `/api/mac_refresh.php` - Refresh MAC list
- POST `/api/logout.php` - End session

### Example API Usage

```javascript
// Login
fetch('/api/login.php', {
    method: 'POST',
    body: new FormData(loginForm)
})
.then(response => response.json())
.then(data => console.log(data));

// Get Status
fetch('/api/status.php')
.then(response => response.json())
.then(data => console.log(data));
```

## Future Features

1. WhatsApp Integration
   - Login notifications
   - Voucher delivery
   - Usage alerts

2. Cloud Backup
   - Automated backups
   - Cloud storage integration
   - Restore functionality

3. Enhanced Statistics
   - Detailed usage graphs
   - Export capabilities
   - Custom reports

4. PPPoE Dashboard
   - User management
   - Bandwidth monitoring
   - Usage tracking

## Troubleshooting

1. Connection Issues
   - Verify RouterOS API is enabled
   - Check firewall rules
   - Confirm API credentials

2. Performance Issues
   - Check PHP memory limit
   - Optimize database queries
   - Enable caching

3. Login Problems
   - Clear browser cache
   - Check session configuration
   - Verify credentials

## Support

For issues and feature requests, please:
1. Check the documentation
2. Search existing issues
3. Create a new issue if needed

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Credits

- Built with PHP 7.4+
- UI powered by Tailwind CSS
- Charts by Chart.js
- Icons by Lucide
- Fonts by Google Fonts

## Disclaimer

This is not an official MikroTik product. Use at your own risk.
