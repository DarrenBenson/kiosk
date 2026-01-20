# Contributing to Kiosk

Thank you for considering contributing to the Game Over Bar kiosk display.

## How to Contribute

### Reporting Issues

- Search existing issues before creating a new one
- Use the issue templates when available
- Include clear reproduction steps for bugs
- Describe expected vs actual behaviour

### Pull Requests

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/your-feature`)
3. Make your changes
4. Test on a local PHP server
5. Commit with clear messages
6. Push to your fork
7. Open a pull request

### Commit Messages

- Use present tense ("Add feature" not "Added feature")
- Keep the first line under 72 characters
- Reference issues when relevant (`Fixes #123`)

### Code Style

- **PHP**: Use `require_once __DIR__ . '/../config.php'` for config imports
- **JavaScript**: Object literal or class pattern with `init()` method
- **CSS**: Use `clamp()` for responsive sizing
- No build step - vanilla JS only

### Testing

- Test all changes on a local PHP server before submitting
- Verify API endpoints return valid JSON
- Check responsive behaviour for kiosk display (4:3 aspect ratio)

## Development Setup

```bash
# Clone the repository
git clone https://github.com/DarrenBenson/kiosk.git
cd kiosk

# Copy config template
cp config.example.php config.php

# Add your API keys to config.php

# Start local PHP server
php -S localhost:8000
```

## Questions?

Open an issue on GitHub for questions or discussion.
