# 🎮 Game Over Bar Kiosk Display

A dynamic kiosk display system that keeps your patrons entertained and informed with the latest BBC news while showcasing your venue's awesome moments!

## 🌟 Features

- **Live BBC News Ticker** - Keep your customers up to date with the latest headlines
- **Smooth Image Slideshow** - Show off your venue's best moments in style
- **Zero-interaction Required** - Set it and forget it! Perfect for public displays
- **Lightweight & Fast** - No heavy frameworks, just pure JavaScript goodness

## 🎯 Perfect For

- Bar/Pub Digital Displays
- Gaming Venue Information Screens
- Event Space Announcements
- Anywhere you want to combine news and images!

## 🛠️ Setup

1. Clone this repository
2. Add your images to `content/4x3/` directory
3. Point your browser to index.php
4. Enjoy your professional-looking display!

## 📁 Directory Structure

├── content/
│ └── 4x3/ # Add your slideshow images here
├── images/ # Logo images
├── scripts/
│ ├── slideshow.js # Handles image transitions
│ └── ticker.js # Manages news ticker
├── style/
│ ├── slideshow.css
│ └── ticker.css
└── index.php # Main display file

## ⚙️ Customization

### Slideshow Timing

javascript
// in scripts/slideshow.js
const slideSpeed = 10000; // Adjust slide duration (in milliseconds)

### News Ticker Speed

javascript
// in scripts/ticker.js
const scrollSpeed = 15000; // Adjust scroll speed (in milliseconds)
const showSpeed = 250;     // Adjust transition speed

## 📝 Requirements

- PHP-enabled web server
- Modern web browser
- Internet connection (for BBC news feed)

## 🎨 Styling

Want to change the look? The display is easily customizable through the CSS files in the `style/` directory.

## 🐛 Troubleshooting

- **No images showing?** Make sure your images are in the `content/4x3/` directory
- **News ticker empty?** Check your internet connection and BBC feed access
- **Transitions janky?** Try adjusting the timing constants in the JavaScript files

## 📜 License

This project is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

## 🎉 Credits

- News feed content provided by BBC
- Built with ❤️ for the Game Over Bar

## 🤝 Contributing

Found a bug? Want to add a feature? Pull requests are welcome!
