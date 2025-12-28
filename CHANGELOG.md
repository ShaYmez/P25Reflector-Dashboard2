# Changelog

All notable changes to this project will be documented in this file.

## [2.0.1] - 2025-01-28

### Added
- Initial release of P25Reflector-Dashboard2
- Adapted from YSFReflector-Dashboard2 with same layout and methods
- Modern glass-morphism UI with Tailwind CSS
- Real-time monitoring with JavaScript polling (5-second intervals)
- Support for P25Reflector log format parsing
- Connected repeaters display
- Last heard list with transmission tracking
- System information display (CPU, temperature, disk usage)
- GDPR-compliant callsign anonymization
- QRZ.com integration for callsign lookups
- Responsive design for desktop, tablet, and mobile
- Initial setup wizard (setup.php)
- Logo support (local files or URL)
- 180-second transmission timeout for proper QSO handling
- Smart transmission end detection

### Technical Details
- PHP 7.4 - 8.3 compatible
- P25 log format: "Transmission from X at Y to TG Z"
- Gateway list format: "Currently linked repeaters:"
- End transmission marker: "Received end of transmission"

### Credits
- Based on YSFReflector-Dashboard2 by M0VUB
- Adapted for P25Reflector log format
