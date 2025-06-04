# Instant Guest Post Request

A WordPress plugin allowing visitors to submit guest posts that admins can review from the dashboard.

Use the `[igpr_form]` shortcode to display the submission form on any page. The form is styled with TailwindCSS and handles validation on submission.

The admin screens are built with React and styled using TailwindCSS as well.

## Development

### JavaScript Build
Build admin assets:
```bash
npm run build:admin
```

Start development server:
```bash
npm run start:admin
```

### Tailwind CSS Setup
This plugin uses Tailwind CSS for styling. To work with the styles:

1. Install dependencies:
   ```
   npm install
   ```

2. Build CSS:
   ```
   npm run build:css
   ```

3. Watch for CSS changes during development:
   ```
   npm run watch:css
   ```