/**
 * Admin main entry point
 */
import { render } from '@wordpress/element';
import './styles/tailwind.css';

// Import components
import SubmissionsTable from './components/SubmissionsTable';
import EmailLogs from './components/EmailLogs';
import Settings from './components/Settings';

// Render components based on the container ID
const submissionsRoot = document.getElementById('igpr-submissions-root');
if (submissionsRoot) {
  render(<SubmissionsTable />, submissionsRoot);
}

const emailLogsRoot = document.getElementById('igpr-email-logs-root');
if (emailLogsRoot) {
  render(<EmailLogs />, emailLogsRoot);
}

const settingsRoot = document.getElementById('igpr-settings-root');
if (settingsRoot) {
  render(<Settings />, settingsRoot);
}