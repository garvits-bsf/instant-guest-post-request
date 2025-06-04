import { render } from '@wordpress/element';
import SettingsPage from './pages/SettingsPage';
import SubmissionsTable from './pages/SubmissionsTable';
import EmailLogs from './pages/EmailLogs';

const settingsRoot = document.getElementById('igpr-settings-root');
if (settingsRoot) {
  render(<SettingsPage />, settingsRoot);
}

const submissionsRoot = document.getElementById('igpr-submissions-root');
if (submissionsRoot) {
  render(<SubmissionsTable />, submissionsRoot);
}

const emailLogsRoot = document.getElementById('igpr-email-logs-root');
if (emailLogsRoot) {
  render(<EmailLogs />, emailLogsRoot);
}
