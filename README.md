# File CA - Moodle Resource Plugin

*Automatically synced with your [v0.app](https://v0.app) deployments*

[![Deployed on Vercel](https://img.shields.io/badge/Deployed%20on-Vercel-black?style=for-the-badge&logo=vercel)](https://vercel.com/barak-8347s-projects/v0-file-ca)
[![Built with v0](https://img.shields.io/badge/Built%20with-v0.app-black?style=for-the-badge)](https://v0.app/chat/v02r4uXkhs5)

## Overview

A Moodle resource plugin that provides advanced file viewing capabilities with security and AI features. This plugin allows teachers to share files with students while controlling download permissions, text copying, and providing AI-powered summarization.

## Features

1. **Document Conversion**: Automatically converts Word documents (.doc, .docx), PowerPoint presentations (.ppt, .pptx), and Excel spreadsheets (.xls, .xlsx) to PDF format for online viewing using Moodle's document converter system.

2. **Behaviour Settings**:
   - **Enable File Download**: Control whether students can download files (default: disabled)
   - **Enable Copying**: Control whether students can select and copy text from PDFs (default: enabled)
   - **Enable Summarize**: Allow students to generate AI-powered summaries of document content (default: enabled)

3. **Security Features**:
   - Prevent downloads when disabled
   - Prevent text selection when copying is disabled
   - Prevent right-click context menu when downloads are disabled

4. **AI Summarization**: Generate summaries of document content with a single click (requires AI service integration for production use)

## Installation

1. Copy all plugin files to your Moodle's `mod/fileca` directory: `/path/to/moodle/mod/fileca`

2. Log in to your Moodle site as an admin and visit the notifications page to complete the installation:
   `Site administration > Notifications`

3. Or run the upgrade script from the command line:
   \`\`\`bash
   php admin/cli/upgrade.php
   \`\`\`

## Configuration

### Document Converter

The plugin uses Moodle's built-in document converter system. To enable document conversion:

1. Go to `Site administration > Plugins > Document converters`
2. Enable and configure a converter (e.g., Google Drive, Unoconv, etc.)

### AI Summarization

The basic summarization feature is included but uses a simple algorithm. For production use, you should integrate with an AI service:

1. Edit `classes/external.php`
2. Update the `generate_simple_summary()` method to call your AI service (OpenAI, etc.)

## Usage

### Adding a File CA Resource

1. Turn editing on in your course
2. Click "Add an activity or resource"
3. Select "File CA"
4. Upload your file
5. Configure behaviour settings:
   - Enable/disable downloads
   - Enable/disable text copying
   - Enable/disable summarization
6. Save

### Student View

Students can:
- View files online (PDFs and converted documents)
- Navigate through PDF pages
- Download files (if enabled)
- Copy text (if enabled)
- Generate summaries (if enabled)

## Requirements

- Moodle 4.0 or later
- Document converter configured for file conversion features
- Modern web browser with JavaScript enabled

## File Structure

\`\`\`
mod/fileca/
├── version.php              # Plugin version and metadata
├── lib.php                  # Core plugin functions
├── mod_form.php            # Activity settings form
├── view.php                # Main viewing page
├── index.php               # Course activity list
├── styles.css              # Plugin styles
├── db/
│   ├── access.php          # Capability definitions
│   ├── install.xml         # Database schema
│   ├── services.php        # Web service definitions
│   └── upgrade.php         # Upgrade functions
├── lang/en/
│   └── fileca.php          # English language strings
├── classes/
│   └── external.php        # External API functions
└── amd/src/
    └── pdfviewer.js        # PDF viewer JavaScript
\`\`\`

## License

GPL v3 or later

## Credits

Based on the Moodle profile field file plugin by Shamim Rezaie

## Development

Continue building and improving this plugin on:
**[https://v0.app/chat/v02r4uXkhs5](https://v0.app/chat/v02r4uXkhs5)**

## Support

For issues and feature requests, please contact your Moodle administrator or visit the v0.app chat linked above.
