# DocViewer (mod_docviewer)

A Moodle activity module that lets teachers add files as course resources with advanced viewing and security options.

**Copyright:** CentricApp LTD  
**Dev Team:** dev@centricapp.co.il

---

## Description

DocViewer allows you to upload and share files (PDF, Word, PowerPoint, Excel, etc.) with fine-grained control over how students can use them:

- **Online viewing** — Students can view files in the browser (including an integrated PDF viewer).
- **Download** — Option to allow or disable file download.
- **Print** — Option to allow or disable printing (for PDFs).
- **Copy** — Option to allow or disable copying/selecting text (for PDFs).
- **Summarize** — Optional AI-powered summarization of file content.

## Requirements

- Moodle 4.0 or later
- PHP and Moodle core requirements as per your Moodle version

## Installation

1. Copy the `docviewer` folder into your Moodle `mod/` directory (e.g. `moodledata/mod/docviewer` or `mod/docviewer`).
2. Visit **Site administration → Notifications** and complete the upgrade.
3. The activity will appear as **DocViewer** when adding an activity to a course.

## Usage

1. In a course, turn editing on and **Add an activity or resource**.
2. Choose **DocViewer**.
3. Upload a file and set:
   - **Behaviour** (inline or force download for non-PDF).
   - **Enable file download** — allow students to download the file.
   - **Enable printing** — allow printing (PDF).
   - **Enable copying** — allow text selection/copy (PDF).
   - **Enable summarize** — allow AI summarization of the content.
4. Save and display. Students will see the file according to the options you set.

## Capabilities

- `mod/docviewer:addinstance` — Add a new DocViewer resource.
- `mod/docviewer:view` — View DocViewer content.

## License

This plugin is distributed under the GNU GPL v3 or later. See the license block in the source files.

## Support

For issues or feature requests, contact the development team: **dev@centricapp.co.il**
