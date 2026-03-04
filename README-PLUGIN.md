# File CA - Moodle Resource Plugin

A Moodle resource plugin that provides advanced file viewing capabilities with security and AI features.

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

1. Copy the `fileca` folder to your Moodle's `mod` directory: `/path/to/moodle/mod/fileca`

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

1. Edit `mod/fileca/classes/external.php`
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

## License

GPL v3 or later

## Credits

Based on the Moodle profile field file plugin by Shamim Rezaie

## Support

For issues and feature requests, please contact your Moodle administrator.
